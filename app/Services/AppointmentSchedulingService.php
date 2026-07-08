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

        if (!$workingHours['is_working'] || empty($workingHours['start_time']) || empty($workingHours['end_time'])) {
            return null;
        }

        return [
            'start' => Carbon::parse($appointmentDate->toDateString() . ' ' . $workingHours['start_time']),
            'end' => Carbon::parse($appointmentDate->toDateString() . ' ' . $workingHours['end_time']),
            'raw' => $workingHours,
        ];
    }

    public function validateScheduledTime(string $date, string $time): ?string
    {
        $window = $this->workingWindow($date);
        $appointmentStart = Carbon::parse($date . ' ' . $time);
        $appointmentEnd = $appointmentStart->copy()->addMinutes($this->duration());

        if (!$window) {
            return 'We are closed on ' . $appointmentStart->format('l') . '.';
        }

        if ($appointmentStart->lt($window['start']) || $appointmentEnd->gt($window['end'])) {
            return 'Appointment time is outside working hours. Working hours for ' .
                $appointmentStart->format('l') . ' are ' .
                $window['raw']['start_time'] . ' - ' . $window['raw']['end_time'] . '.';
        }

        return null;
    }

    public function hasScheduledConflict(int $doctorId, string $date, string $time, ?int $excludeId = null): bool
    {
        $slotStart = Carbon::parse($date . ' ' . $time);
        $slotEnd = $slotStart->copy()->addMinutes($this->duration());

        return $this->blockingAppointments($doctorId, $date, $excludeId)
            ->contains(function (Appointment $appointment) use ($slotStart, $slotEnd) {
                $existingStart = Carbon::parse($appointment->appointment_date->toDateString() . ' ' . $appointment->appointment_time->format('H:i:s'));
                $existingEnd = $existingStart->copy()->addMinutes($this->duration());

                return $slotStart->lt($existingEnd) && $slotEnd->gt($existingStart);
            });
    }

    public function availableSlots(int $doctorId, string|Carbon $date, ?int $excludeId = null): array
    {
        $appointmentDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $window = $this->workingWindow($appointmentDate);

        if (!$window) {
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
        $current = $start->copy();

        while ($current->copy()->addMinutes($this->duration())->lte($window['end'])) {
            if (!$this->hasScheduledConflict($doctorId, $appointmentDate->toDateString(), $current->format('H:i'), $excludeId)) {
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
        if (!$this->workingWindow($data['appointment_date'])) {
            throw new \InvalidArgumentException('No working hours are configured for this day.');
        }

        return DB::transaction(function () use ($data) {
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

            $position = $this->nextQueuePosition($data['doctor_id'], $data['appointment_date']);
            $estimatedTime = $this->estimatedQueueTime($data['appointment_date'], $position);

            $appointment = Appointment::create(array_merge($data, [
                'appointment_mode' => 'queue',
                'appointment_time' => $estimatedTime->format('H:i:s'),
                'estimated_time' => $estimatedTime->format('H:i:s'),
                'queue_number' => $position,
                'queue_position' => $position,
                'status' => 'scheduled',
            ]));

            $this->recalculateQueue($appointment->doctor_id, $appointment->appointment_date->toDateString());

            return $appointment->refresh();
        });
    }

    public function recalculateQueue(int $doctorId, string|Carbon $date): void
    {
        $appointments = $this->queueAppointments($doctorId, $date);

        foreach ($appointments->values() as $index => $appointment) {
            $position = $index + 1;
            $estimatedTime = $this->estimatedQueueTime($appointment->appointment_date, $position);

            $appointment->updateQuietly([
                'queue_position' => $position,
                'queue_number' => $position,
                'estimated_time' => $estimatedTime->format('H:i:s'),
                'appointment_time' => $estimatedTime->format('H:i:s'),
            ]);
        }
    }

    public function moveQueueAppointment(Appointment $appointment, string $direction): bool
    {
        if (!$appointment->isQueueMode() || in_array($appointment->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'desc' : 'asc';

        $swapWith = Appointment::where('doctor_id', $appointment->doctor_id)
            ->whereDate('appointment_date', $appointment->appointment_date)
            ->where('appointment_mode', 'queue')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('queue_position', $operator, $appointment->queue_position)
            ->orderBy('queue_position', $order)
            ->first();

        if (!$swapWith) {
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
        if (!$doctor || !$doctor->queue_daily_limit) {
            return null;
        }

        $count = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($count >= $doctor->queue_daily_limit) {
            return 'This doctor has reached the queue limit for this day.';
        }

        return null;
    }

    public function nextQueuePosition(int $doctorId, string|Carbon $date): int
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->where('status', '!=', 'cancelled')
            ->max('queue_position') + 1;
    }

    public function estimatedQueueTime(string|Carbon $date, int $position): Carbon
    {
        $appointmentDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $window = $this->workingWindow($appointmentDate);
        $start = $window ? $window['start'] : Carbon::parse($appointmentDate->toDateString() . ' 09:00');

        return $start->copy()->addMinutes(($position - 1) * $this->duration());
    }

    public function queueAppointments(int $doctorId, string|Carbon $date): Collection
    {
        return Appointment::with(['patient', 'doctor'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_mode', 'queue')
            ->where('status', '!=', 'cancelled')
            ->orderBy('queue_position')
            ->orderBy('created_at')
            ->get();
    }

    private function blockingAppointments(int $doctorId, string|Carbon $date, ?int $excludeId = null): Collection
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed'])
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
