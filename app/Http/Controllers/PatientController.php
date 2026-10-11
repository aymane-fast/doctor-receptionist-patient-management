<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Setting;
use App\Services\AppointmentSchedulingService;
use App\Services\PatientProfileService;
use App\Services\SpecialtyService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255', 'date' => 'nullable|date']);
        $patients = collect(); // Empty collection by default
        $hasSearchQuery = false;

        // Only query patients if there's a search term or date filter
        if ($request->filled('search') || $request->filled('date')) {
            $hasSearchQuery = true;

            $query = Patient::query();

            // Search functionality
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Filter by creation date
            if ($request->has('date') && $request->date) {
                $query->whereDate('created_at', $request->date);
            }

            $patients = $query->latest()->paginate(15);
        }

        return view('patients.index', compact('patients', 'hasSearchQuery'));
    }

    /**
     * Show the form for creating a new patient
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request, SpecialtyService $specialties)
    {
        // Shared identity/clinical fields plus practice-specific contact rules.
        $validated = $request->validate(array_merge([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',

            // Quick booking validation
            'book_today' => 'nullable|boolean',
            'appointment_date' => 'nullable|required_if:book_today,1|date|after_or_equal:today',
            'appointment_time' => 'nullable|date_format:H:i',
            'appointment_reason' => 'nullable|string|max:500',
            'appointment_priority' => 'nullable|in:normal,urgent,emergency',
        ], $specialties->validationRules('patient'), app(PatientProfileService::class)->rules()));

        if ($request->has('specialty_data')) {
            $validated['specialty_data'] = $specialties->mergeSubmittedData(
                [],
                $validated['specialty_data'] ?? [],
                'patient'
            );
        }

        // A walk-in registration and its appointment are one user action. Keep
        // them atomic so a full queue, unavailable slot, or closed clinic does
        // not leave an unexpected patient record behind after a failed submit.
        if ($request->boolean('book_today')) {
            $patient = null;

            try {
                \DB::transaction(function () use (&$patient, $validated, $request, $specialties) {
                    $patient = app(PatientProfileService::class)->save($validated);

                    if (Carbon::parse($request->appointment_date)->isToday() && ! Setting::isWithinWorkingHours()) {
                        $nextWorking = Setting::getNextWorkingTime();
                        $message = 'Cannot book appointment outside working hours. ';
                        if ($nextWorking) {
                            $message .= 'Next available time is '.$nextWorking->locale(app()->getLocale())->isoFormat('dddd, D MMM HH:mm').'.';
                        }

                        throw new \DomainException($message);
                    }

                    $appointmentResult = $this->createTodayAppointment($patient, $request, $specialties);
                    if (! $appointmentResult['success']) {
                        throw new \DomainException($appointmentResult['message']);
                    }
                });
            } catch (\DomainException $e) {
                return back()->withErrors(['book_today' => $e->getMessage()])->withInput();
            }

            return redirect()->route('dashboard')
                ->with('success', __('Patient created successfully and appointment booked!'));
        }

        $patient = app(PatientProfileService::class)->save($validated);

        // For regular patient creation, redirect to the patient's profile
        return redirect()->route('patients.show', $patient)
            ->with('success', __('Patient created successfully!'));
    }

    /**
     * Create an appointment for today for a new patient
     */
    private function createTodayAppointment(Patient $patient, Request $request, SpecialtyService $specialties): array
    {
        // Get the authenticated user - if not a doctor, get the first available doctor
        $user = auth()->user();
        $doctor = $user->isDoctor() ? $user : \App\Models\User::where('role', 'doctor')->first();

        if (! $doctor) {
            return [
                'success' => false,
                'message' => __('No doctor available for appointment booking.'),
            ];
        }

        $scheduler = app(AppointmentSchedulingService::class);

        if ($doctor->usesQueueMode()) {
            $queueDate = $request->appointment_date ?? Carbon::now()->format('Y-m-d');

            try {
                $scheduler->createQueueAppointment([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $queueDate,
                    'reason' => $request->appointment_reason ?: __('appointments.walk_in_consultation'),
                    'notes' => 'Walk-in patient appointment',
                    // A walk-in joining today's queue is standing at the desk
                    'checked_in_at' => Carbon::parse($queueDate)->isToday() ? now() : null,
                ]);
            } catch (\InvalidArgumentException $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }

            return ['success' => true];
        }

        // Use submitted date and time, or find the next open slot automatically
        $appointmentDate = $request->appointment_date ?? Carbon::now()->format('Y-m-d');
        $appointmentTime = $request->appointment_time;

        if (! $appointmentTime) {
            $slot = $scheduler->nextAvailableSlot($doctor->id, $appointmentDate);

            if (! $slot) {
                return [
                    'success' => false,
                    'message' => __('No available slots remain within working hours for this day.'),
                ];
            }

            $appointmentTime = $slot['time'];
        } else {
            if (Carbon::parse($appointmentDate.' '.$appointmentTime)->isPast()) {
                return ['success' => false, 'message' => __('Appointment time has already passed. Please choose a future time.')];
            }

            $workingHoursError = $scheduler->validateScheduledTime($appointmentDate, $appointmentTime);
            if ($workingHoursError) {
                return [
                    'success' => false,
                    'message' => $workingHoursError,
                ];
            }

            if ($scheduler->hasScheduledConflict($doctor->id, $appointmentDate, $appointmentTime)) {
                return [
                    'success' => false,
                    'message' => __('Doctor already has an appointment at this time.'),
                ];
            }
        }

        $priority = $request->appointment_priority ?: 'normal';
        $notes = 'Walk-in patient appointment';
        if (in_array($priority, ['urgent', 'emergency'], true)) {
            $notes .= ' - Priority: '.ucfirst($priority);
        }

        // Create inside a per-doctor/day lock so a simultaneous booking at
        // the front desk cannot land in the same slot.
        try {
            \DB::transaction(function () use ($patient, $doctor, $appointmentDate, $appointmentTime, $scheduler, $request, $notes, $specialties) {
                Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('appointment_date', $appointmentDate)
                    ->lockForUpdate()
                    ->count();

                if ($scheduler->hasScheduledConflict($doctor->id, $appointmentDate, $appointmentTime)) {
                    throw new \InvalidArgumentException(__('Doctor already has an appointment at this time.'));
                }

                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $appointmentDate,
                    'appointment_time' => $appointmentTime,
                    'reason' => $request->appointment_reason ?: $specialties->defaultAppointmentReason(),
                    'status' => 'scheduled',
                    'checked_in_at' => Carbon::parse($appointmentDate)->isToday() ? now() : null,
                    'notes' => $notes,
                ]);
            });
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return ['success' => true];
    }

    /**
     * Display the specified patient
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'appointments.doctor',
            'medicalRecords.doctor',
            'medicalRecords.prescriptions',
            'prescriptions.doctor',
        ]);

        $recentAppointments = $patient->appointments
            ->sortByDesc('appointment_date')
            ->take(5)
            ->values();

        $recentRecords = $patient->medicalRecords
            ->sortByDesc('visit_date')
            ->take(5)
            ->values();

        return view('patients.show', compact('patient', 'recentAppointments', 'recentRecords'));
    }

    /**
     * Show the form for editing the patient
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, Patient $patient, SpecialtyService $specialties)
    {
        $validated = $request->validate(array_merge([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
        ], $specialties->validationRules('patient'), app(PatientProfileService::class)->rules($patient)));

        if ($request->has('specialty_data')) {
            $validated['specialty_data'] = $specialties->mergeSubmittedData(
                $patient->specialty_data ?? [],
                $validated['specialty_data'] ?? [],
                'patient'
            );
        }

        app(PatientProfileService::class)->save($validated, $patient);

        return redirect()->route('patients.show', $patient)
            ->with('success', __('Patient updated successfully!'));
    }

    /**
     * Remove the specified patient
     */
    public function destroy(Patient $patient)
    {
        // Deleting a patient cascades to appointments, medical records and
        // prescriptions — restrict this to doctors.
        if (! auth()->user()->isDoctor()) {
            abort(403, __('Only doctors can delete patient records.'));
        }

        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', __('Patient deleted successfully!'));
    }

    /**
     * Generate AI summary for patient
     */
    public function aiSummary(Patient $patient)
    {
        abort_unless(auth()->user()->isDoctor(), 403, 'Clinical summaries are available to doctors only.');
        $summary = \App\Services\AIService::generatePatientSummary($patient);

        return response()->json($summary);
    }
}
