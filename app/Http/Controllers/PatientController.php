<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Setting;
use App\Services\AppointmentSchedulingService;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request)
    {
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
    public function store(Request $request)
    {
        // More flexible validation - only name fields are required
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'id_card_number' => 'required|string|max:255|unique:patients,id_card_number',
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
        ]);

        $patient = Patient::create($validated);

        // Handle automatic appointment booking for today
        if ($request->has('book_today') && $request->book_today) {
            // Check if we're within working hours for appointments
            if (!Setting::isWithinWorkingHours()) {
                $nextWorking = Setting::getNextWorkingTime();
                $errorMessage = 'Cannot book appointment outside working hours. ';
                if ($nextWorking) {
                    $errorMessage .= 'Next available time is ' . $nextWorking->locale(app()->getLocale())->isoFormat('dddd, D MMM HH:mm') . '.';
                }
                return back()->withErrors(['book_today' => $errorMessage])->withInput();
            }
            
            $appointmentResult = $this->createTodayAppointment($patient, $request);
            if (!$appointmentResult['success']) {
                return back()->withErrors(['book_today' => $appointmentResult['message']])->withInput();
            }
            
            // For walk-in patients, redirect to dashboard after successful booking
            return redirect()->route('dashboard')
                            ->with('success', 'Patient created successfully and appointment booked for today!');
        }

        // For regular patient creation, redirect to the patient's profile
        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient created successfully!');
    }

    /**
     * Create an appointment for today for a new patient
     */
    private function createTodayAppointment($patient, $request)
    {
        // Get the authenticated user - if not a doctor, get the first available doctor
        $user = auth()->user();
        $doctor = $user->isDoctor() ? $user : \App\Models\User::where('role', 'doctor')->first();

        if (!$doctor) {
            return [
                'success' => false,
                'message' => 'No doctor available for appointment booking.'
            ];
        }

        $scheduler = app(AppointmentSchedulingService::class);

        if ($doctor->usesQueueMode()) {
            try {
                $scheduler->createQueueAppointment([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $request->appointment_date ?? Carbon::now()->format('Y-m-d'),
                    'reason' => $request->appointment_reason ?: __('appointments.walk_in_consultation'),
                    'notes' => 'Walk-in patient appointment',
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

        if (!$appointmentTime) {
            $slot = $scheduler->nextAvailableSlot($doctor->id, $appointmentDate);

            if (!$slot) {
                return [
                    'success' => false,
                    'message' => 'No available slots remain within working hours for this day.'
                ];
            }

            $appointmentTime = $slot['time'];
        } else {
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
                    'message' => 'Doctor already has an appointment at this time.'
                ];
            }
        }

        $priority = $request->appointment_priority ?: 'normal';
        $notes = 'Walk-in patient appointment';
        if (in_array($priority, ['urgent', 'emergency'], true)) {
            $notes .= ' - Priority: ' . ucfirst($priority);
        }
        
        // Create the appointment
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'reason' => $request->appointment_reason ?: __('appointments.walk_in_consultation'),
            'status' => 'scheduled',
            'notes' => $notes,
        ]);
        
        return ['success' => true];
    }

    /**
     * Display the specified patient
     */
    public function show(Patient $patient)
    {
        $patient->load(['appointments.doctor', 'medicalRecords.doctor', 'prescriptions.doctor']);
        
        $recentAppointments = $patient->appointments()
                                    ->with('doctor')
                                    ->latest('appointment_date')
                                    ->take(5)
                                    ->get();

        $recentRecords = $patient->medicalRecords()
                                ->with('doctor')
                                ->latest('visit_date')
                                ->take(5)
                                ->get();

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
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'id_card_number' => 'required|string|max:255|unique:patients,id_card_number,' . $patient->id,
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient updated successfully!');
    }

    /**
     * Remove the specified patient
     */
    public function destroy(Patient $patient)
    {
        // Deleting a patient cascades to appointments, medical records and
        // prescriptions — restrict this to doctors.
        if (!auth()->user()->isDoctor()) {
            abort(403, 'Only doctors can delete patient records.');
        }

        $patient->delete();

        return redirect()->route('patients.index')
                        ->with('success', 'Patient deleted successfully!');
    }
    
    /**
     * Generate AI summary for patient
     */
    public function aiSummary(Patient $patient)
    {
        $summary = \App\Services\AIService::generatePatientSummary($patient);
        return response()->json($summary);
    }
}
