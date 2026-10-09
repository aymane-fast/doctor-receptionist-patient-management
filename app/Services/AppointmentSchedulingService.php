<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AppointmentSchedulingService
{
    public function duration(): int
    {
        return max(1, Setting::getAppointmentDuration());
    }

    public function workingHoursForDate(string|Carbon $date): array
    {
        $appointmentDate = $date instanceof Carbon ? $date : Carbon::parse($date);

        return Setting::getWorkingHours(strtolower($appointmentDate->format('l')));
    }

    public function workingWindow(string|Carbon $date): ?array
    {
        $appointmentDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $workingHours = $this->workingHoursForDate($appointmentDate);

        if (! $workingHours['is_working'] || empty($workingHours['start_time']) || empty($workingHours['end_time'])) {
            return null;
        }

        return [
            'start' => Carbon::parse($appointmentDate->toDateString().' '.$workingHours['start_time']),
            'end' => Carbon::parse($appointmentDate->toDateString().' '.$workingHours['end_time']),
            'raw' => $workingHours,
        ];
    }

    public function validateScheduledTime(string $date, string $time): ?string
    {
        $window = $this->workingWindow($date);
        $appointmentStart = Carbon::parse($date.' '.$time);
        $appointmentEnd = $appointmentStart->copy()->addMinutes($this->duration());

        if (! $window) {
            return 'We are closed on '.$appointmentStart->format('l').'.';
        }

        if ($appointmentStart->lt($window['start']) || $appointmentEnd->gt($window['end'])) {
            return 'Appointment time is outside working hours. Working hours for '.
                $appointmentStart->format('l').' are '.
                $window['raw']['start_time'].' - '.$window['raw']['end_time'].'.';
        }

        return null;
    }

    public function hasScheduledConflict(int $doctorId, string $date, string $time, ?int $excludeId = null): bool
    {
        $slotStart = Carbon::parse($date.' '.$time);
        $slotEnd = $slotStart->copy()->addMinutes($this->duration());

        return $this->blockingAppointments($doctorId, $date, $excludeId)
            ->contains(function (Appointment $appointment) use ($slotStart, $slotEnd) {
                $existingStart = Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->appointment_time->format('H:i:s'));
                $existingEnd = $existingStart->copy()->addMinutes($this->duration());

                return $slotStart->lt($existingEnd) && $slotEnd->gt($existingStart);
            });
    }

    public function availableSlots(int $doctorId, string|Carbon $date, ?int $excludeId = null): array
    {
        $appointmentDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $window = $this->workingWindow($appointmentDate);

        if (! $window) {
            return [];
        }

        $start = $window['start'];
        if ($appointmentDate->isToday()) {
            $start = $this->roundUpToInterval(now()->addMinutes($this->duration()));
            if ($start->lt($window['start'])) {
                $start = $window['start'];
            }
        }

        $slots = [];
        // Load the day's blockers once, not once for every candidate slot.
        $blocking = $this->blockingAppointments($doctorId, $appointmentDate, $excludeId);
        $current = $start->copy();

        while ($current->copy()->addMinutes($this->duration())->lte($window['end'])) {
            $slotEnd = $current->copy()->addMinutes($this->duration());
            $conflict = $blocking->contains(function (Appointment $appointment) use ($current, $slotEnd) {
                $existingStart = Carbon::parse($appointment->appointment_date->toDateString().' '.$appointment->appointment_time->format('H:i:s'));

                return $current->lt($existingStart->copy()->addMinutes($this->duration())) && $slotEnd->gt($existingStart);
            });
            if (! $conflict) {
                $slots[] = [
                    'time' => $current->format('H:i'),
                    'formatted' => $current->format('H:i'),
                    'display' => $current->format('g:i A'),
                ];
            }

            $current->addMinutes($this->duration());
        }

        return $slots;
    }

    public function nextAvailableSlot(int $doctorId, string|Carbon $date, ?int $excludeId = null): ?array
    {
        $slots = $this->availableSlots($doctorId, $date, $excludeId);

        return $slots[0] ?? null;
    }

    public function nextWorkingDayWithSlot(int $doctorId, Carbon $fromDate, int $daysAhead = 7): ?array
    {
        $date = $fromDate->copy()->addDay();

        for ($i = 0; $i < $daysAhead; $i++) {
            $slot = $this->nextAvailableSlot($doctorId, $date);
            if ($slot) {
                return [
                    'date' => $date->toDateString(),
                    'slot' => $slot,
                ];
            }

            $date->addDay();
        }

        return null;
    }

    public function createQueueAppointment(array $data): Appointment
    {
        if (! $this->workingWindow($data['appointment_date'])) {
            throw new \InvalidArgumentException('No working hours are configured for this day.');
        }

        return DB::transaction(function () use ($data) {
            // Lock a row that exists even before the day's first appointment.
            \App\Models\User::whereKey($data['doctor_id'])->lockForUpdate()->firstOrFail();
            if ($this->hasActiveSameDayAppointment($data['patient_id'], $data['doctor_id'], $data['appointment_date'])) {
                throw new \InvalidArgumentException(__('appointments.already_has_appointment_today'));
            }
            // Serialize concurrent queue joins for the same doctor/day so two
            // requests cannot be handed the same queue number.
            Appointment::where('doctor_id', $data['doctor_id'])
                ->whereDate('appointment_date', $data['appointment_date'])
                ->where('appointment_mode', 'queue')
                ->lockForUpdate()
                ->count();

            $limitError = $this->validateQueueCapacity($data['doctor_id'], $data['appointment_date']);
            if ($limitError) {
                throw new \InvalidArgumentException($limitError);
            }

            $estimatedTime = $this->estimatedQueueJoinTime($data['doctor_id'], $data['appointment_date']);

            $appointment = Appointment::create(array_merge($data, [
                'appointment_mode' => 'queue',
                'appointment_time' => $estimatedTime->format('H:i:s'),
                'estimated_time' => $estimatedTime->format('H:i:s'),
                'queue_number' => $this->nextQueueNumber($data['doctor_id'], $data['appointment_date']),
                'queue_position' => $this->nextQueuePosition($data['doctor_id'], $data['appointment_date']),
                'status' => 'scheduled',
            ]));

            $this->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());

            return $appointment->refresh();
        });
    }

    /**
     * Re-sequence queue positions and refresh estimated times.
     *
     * Ticket numbers (queue_number) are never rewritten — a patient told
     * "you are #7" stays #7 all day. Only patients still waiting get new
     * estimates, anchored to the current time so a 3 PM walk-in is not
     * promised a 9 AM slot.
     */
    public function recalculateQueue(int $doctorId, string|Carbon $date): void
    {
        $appointmentDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        $appointments = $this->queueAppointments($doctorId, $appointmentDate);

        foreach ($appointments->values() as $index => $appointment) {
            if ($appointment->queue_position !== $index + 1) {
                $appointment->updateQuietly(['queue_position' => $index + 1]);
            }
        }

        $base = $this->queueEstimateBase($doctorId, $appointmentDate);
        $waiting = $appointments->where('status', 'scheduled')->values();

        foreach ($waiting as $index => $appointment) {
            $estimated = $base->copy()->addMinutes($index * $this->duration());

            $appointment->updateQuietly([
                'estimated_time' => $estimated->format('H:i:s'),
                'appointment_time' => $estimated->format('H:i:s'),
            ]);
        }
    }

    public function moveQueueAppointment(Appointment $appointment, string $direction): bool
    {
        if (! $appointment->isQueueMode() || $appointment->status !== 'scheduled') {
            return false;
        }

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';

        $swapWith = Appointment::where('doctor_id', $appointment->doctor_id)
            ->whereDate('appointment_date', $appointment->appointment_date)
            ->where('appointment_mode', 'queue')
            ->where('status', 'scheduled')
            ->where('queue_position', $operator, $appointment->queue_position)
            ->orderBy('queue_position', $order)
            ->first();

        if (! $swapWith) {
            return false;
        }

        $currentPosition = $appointment->queue_position;
        $appointment->update(['queue_position' => $swapWith->queue_position]);
        $swapWith->update(['queue_position' => $currentPosition]);

        $this->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());

        return true;
    }

    public function validateQueueCapacity(int $doctorId, string|Carbon $date): ?string
    {
        $doctor = \App\Models\User::find($doctorId);
        if (! $doctor || ! $doctor->queue_daily_limit) {
            return null;
        }

        $count = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        if ($count >= $doctor->queue_daily_limit) {
            return 'This doctor has reached the queue limit for this day.';
        }

        return null;
    }

    /**
     * Next ticket number for the day. Numbers are never reused, even when a
     * ticket was cancelled or marked no-show.
     */
    public function nextQueueNumber(int $doctorId, string|Carbon $date): int
    {
        return (int) Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->max('queue_number') + 1;
    }

    public function nextQueuePosition(int $doctorId, string|Carbon $date): int
    {
        return (int) Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->max('queue_position') + 1;
    }

    /**
     * Estimated consultation time for a patient joining the queue right now.
     */
    public function estimatedQueueJoinTime(int $doctorId, string|Carbon $date): Carbon
    {
        $appointmentDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        $waitingCount = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('appointment_mode', 'queue')
            ->where('status', 'scheduled')
            ->count();

        return $this->queueEstimateBase($doctorId, $appointmentDate)
            ->addMinutes($waitingCount * $this->duration());
    }

    /**
     * Where queue estimates start from: the opening time, or the current
     * time once the day is underway, plus the consultation currently in
     * progress (which has to finish first).
     */
    private function queueEstimateBase(int $doctorId, Carbon $date): Carbon
    {
        $window = $this->workingWindow($date);
        $base = $window ? $window['start']->copy() : Carbon::parse($date->toDateString().' 09:00');

        if ($date->isToday() && now()->gt($base)) {
            $base = now()->copy()->startOfMinute();
        }

        $hasInProgress = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('status', 'in_progress')
            ->exists();

        if ($hasInProgress) {
            $base->addMinutes($this->duration());
        }

        return $base;
    }

    /**
     * Promote today's next patient to "in consultation" for this doctor.
     * Patients who have checked in (arrived) always come first; if nobody
     * has arrived yet, fall back to the planned order.
     */
    public function promoteNextPatient(int $doctorId): ?Appointment
    {
        $waiting = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', today())
            ->where('status', 'scheduled');

        $next = (clone $waiting)
            ->whereNotNull('checked_in_at')
            ->orderBy('appointment_time')
            ->orderBy('queue_position')
            ->first()
            ?? $waiting
                ->orderBy('appointment_time')
                ->orderBy('queue_position')
                ->first();

        $next?->update([
            'status' => 'in_progress',
            // The patient is in the consultation room, so they have arrived.
            'checked_in_at' => $next->checked_in_at ?? now(),
        ]);

        return $next;
    }

    /**
     * Whether the patient already holds an active appointment with this
     * doctor on the given day (guards against accidental double booking).
     */
    public function hasActiveSameDayAppointment(int $patientId, int $doctorId, string|Carbon $date, ?int $excludeId = null): bool
    {
        return Appointment::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }

    public function queueAppointments(int $doctorId, string|Carbon $date): Collection
    {
        return Appointment::with(['patient', 'doctor'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('queue_position')
            ->orderBy('created_at')
            ->get();
    }

    private function blockingAppointments(int $doctorId, string|Carbon $date, ?int $excludeId = null): Collection
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->get();
    }

    private function roundUpToInterval(Carbon $time): Carbon
    {
        $duration = $this->duration();
        $minutes = (int) ceil($time->minute / $duration) * $duration;

        if ($minutes >= 60) {
            $time->addHour()->minute(0);
        } else {
            $time->minute($minutes);
        }

        return $time->second(0);
    }
}
