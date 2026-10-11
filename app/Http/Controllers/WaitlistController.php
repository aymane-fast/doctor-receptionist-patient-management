<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Services\AppointmentSchedulingService;
use App\Services\SpecialtyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WaitlistController extends Controller
{
    public function __construct(
        private AppointmentSchedulingService $scheduler,
        private SpecialtyService $specialties,
    ) {}

    /**
     * The list of patients waiting for an opening.
     */
    public function index()
    {
        $entries = WaitlistEntry::with(['patient', 'doctor'])
            ->waiting()
            ->when(Auth::user()->isDoctor(), fn ($query) => $query->where('doctor_id', Auth::id()))
            ->orderBy('created_at')
            ->get();

        $doctors = Auth::user()->isDoctor()
            ? User::where('id', Auth::id())->get()
            : User::where('role', 'doctor')->orderBy('name')->get();

        $selectedPatient = is_scalar(old('patient_id')) ? \App\Models\Patient::find((int) old('patient_id')) : null;
        return view('waitlist.index', compact('entries', 'doctors', 'selectedPatient'));
    }

    /**
     * Add a patient to the waitlist.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isDoctor()) {
            $request->merge(['doctor_id' => Auth::id()]);
        }

        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'doctor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'doctor'),
            ],
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'reason' => 'nullable|string|max:500',
        ]);

        $alreadyWaiting = WaitlistEntry::waiting()
            ->where('patient_id', $validated['patient_id'])
            ->where('doctor_id', $validated['doctor_id'])
            ->exists();

        if ($alreadyWaiting) {
            return back()->withErrors(['patient_id' => __('appointments.waitlist_duplicate')])->withInput();
        }

        WaitlistEntry::create($validated);

        return redirect()->route('waitlist.index')->with('success', __('appointments.waitlist_added'));
    }

    /**
     * Book the waitlisted patient into the next opening for their doctor.
     */
    public function book(WaitlistEntry $entry)
    {
        $this->authorizeEntry($entry);

        if ($entry->status !== 'waiting') {
            return back()->with('error', __('appointments.waitlist_not_waiting'));
        }

        $doctor = $entry->doctor;
        $startDate = $entry->preferred_date && $entry->preferred_date->isFuture()
            ? $entry->preferred_date->copy()
            : today();

        if ($doctor->usesQueueMode()) {
            try {
                $appointment = $this->scheduler->createQueueAppointment([
                    'patient_id' => $entry->patient_id,
                    'doctor_id' => $entry->doctor_id,
                    'appointment_date' => $startDate->toDateString(),
                    'reason' => $entry->reason ?: $this->specialties->defaultAppointmentReason(),
                    'notes' => 'Booked from waitlist',
                ]);
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage());
            }

            $entry->update(['status' => 'booked']);

            return redirect()->route('appointments.show', $appointment)
                ->with('success', __('appointments.waitlist_booked_queue', [
                    'number' => $appointment->queue_number,
                    'time' => $appointment->displayTime(),
                ]));
        }

        // Scheduled mode: first free slot in the next two weeks where the
        // patient does not already have an appointment with this doctor.
        for ($i = 0; $i <= 14; $i++) {
            $candidate = $startDate->copy()->addDays($i);

            if ($this->scheduler->hasActiveSameDayAppointment($entry->patient_id, $entry->doctor_id, $candidate)) {
                continue;
            }

            // Lock the day and re-check the slot before creating, so two
            // waitlist bookings cannot claim the same opening at once.
            $appointment = DB::transaction(function () use ($entry, $candidate) {
                Appointment::where('doctor_id', $entry->doctor_id)
                    ->whereDate('appointment_date', $candidate)
                    ->lockForUpdate()
                    ->count();

                $slot = $this->scheduler->nextAvailableSlot($entry->doctor_id, $candidate);
                if (! $slot) {
                    return null;
                }

                $created = Appointment::create([
                    'patient_id' => $entry->patient_id,
                    'doctor_id' => $entry->doctor_id,
                    'appointment_date' => $candidate->toDateString(),
                    'appointment_time' => $slot['time'],
                    'status' => 'scheduled',
                    'reason' => $entry->reason ?: $this->specialties->defaultAppointmentReason(),
                    'notes' => 'Booked from waitlist',
                ]);

                $entry->update(['status' => 'booked']);

                return $created;
            });

            if (! $appointment) {
                continue;
            }

            $datetime = $appointment->appointment_date->format('M j, Y').' at '.$appointment->displayTime();

            return redirect()->route('appointments.show', $appointment)
                ->with('success', __('appointments.waitlist_booked', ['datetime' => $datetime]));
        }

        return back()->with('error', __('appointments.rebook_no_slot'));
    }

    /**
     * Remove a patient from the waitlist.
     */
    public function cancel(WaitlistEntry $entry)
    {
        $this->authorizeEntry($entry);

        if ($entry->status === 'waiting') {
            $entry->update(['status' => 'cancelled']);
        }

        return back()->with('success', __('appointments.waitlist_removed'));
    }

    /**
     * Receptionists manage every entry; doctors only their own.
     */
    private function authorizeEntry(WaitlistEntry $entry): void
    {
        if (Auth::user()->isReceptionist()) {
            return;
        }

        if (Auth::user()->isDoctor() && $entry->doctor_id === Auth::id()) {
            return;
        }

        abort(403);
    }
}
