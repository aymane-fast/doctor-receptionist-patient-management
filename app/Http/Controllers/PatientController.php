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
                $search = $request->search;
                $terms = array_filter(explode(' ', trim($search)));
                
                $query->where(function($q) use ($search, $terms) {
                    // Search individual fields
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('id_card_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                    
                    // Handle multiple terms for full name search
                    if (count($terms) >= 2) {
                        $q->orWhere(function ($subQuery) use ($terms) {
                            foreach ($terms as $i => $firstTerm) {
                                foreach ($terms as $j => $lastTerm) {
                                    if ($i !== $j) {
                                        $subQuery->orWhere(function ($combo) use ($firstTerm, $lastTerm) {
                                            $combo->where('first_name', 'like', "%{$firstTerm}%")
                                                  ->where('last_name', 'like', "%{$lastTerm}%");
                                        });
                                    }
                                }
                            }
                        });
                    }
                    
                    // Search in concatenated full name
                    foreach ($terms as $term) {
                        if (strlen($term) >= 2) {
                            $q->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
                        }
                    }
                });
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
                    $errorMessage .= 'Next available time is ' . $nextWorking->locale(app()->getLocale())->isoFormat('dddd, D MMM [à] HH:mm') . '.';
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
        $doctor = $user->role === 'doctor' ? $user : \App\Models\User::where('role', 'doctor')->first();
        
        if (!$doctor) {
            return [
                'success' => false,
                'message' => 'No doctor available for appointment booking.'
            ];
        }

        if ($doctor->usesQueueMode()) {
            try {
                app(AppointmentSchedulingService::class)->createQueueAppointment([
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
        
        // Use submitted date and time, or calculate automatically
        $appointmentDate = $request->appointment_date ?? Carbon::now()->format('Y-m-d');
        $appointmentTime = $request->appointment_time;
        
        // If no time provided, calculate it automatically
        $appointmentDuration = Setting::getAppointmentDuration();

        if (!$appointmentTime) {
            $currentTime = Carbon::now();
            
            // Get working hours for the appointment date
            $dayName = strtolower(Carbon::parse($appointmentDate)->format('l'));
            $workingHours = Setting::getWorkingHours($dayName);
            
            if (!$workingHours['is_working']) {
                return [
                    'success' => false,
                    'message' => 'Cannot book appointment - closed on ' . ucfirst($dayName) . '.'
                ];
            }
            
            // Find the last appointment for this doctor on the selected date
            $lastAppointment = Appointment::where('doctor_id', $doctor->id)
                                         ->whereDate('appointment_date', $appointmentDate)
                                         ->orderBy('appointment_time', 'desc')
                                         ->first();
            
            // Calculate next available time slot
            if ($lastAppointment) {
                $nextTime = Carbon::parse($appointmentDate . ' ' . $lastAppointment->appointment_time)
                    ->addMinutes($appointmentDuration);
            } else {
                // Start from current time or working start time, whichever is later
                $workingStart = Carbon::parse($appointmentDate . ' ' . $workingHours['start_time']);
                $nextTime = $currentTime->gt($workingStart) ? $currentTime->copy()->addMinutes(15) : $workingStart;
            }
            
            // Check if appointment would be within working hours
            $workingEnd = Carbon::parse($appointmentDate . ' ' . $workingHours['end_time']);
            $appointmentEnd = $nextTime->copy()->addMinutes($appointmentDuration);
            
            if ($appointmentEnd->gt($workingEnd)) {
                return [
                    'success' => false,
                    'message' => 'Cannot book appointment - would extend past working hours. Working hours are ' . 
                               $workingHours['start_time'] . ' - ' . $workingHours['end_time'] . '.'
                ];
            }
            
            $appointmentTime = $nextTime->format('H:i');
        } else {
            // Validate the submitted time is within working hours
            $dayName = strtolower(Carbon::parse($appointmentDate)->format('l'));
            $workingHours = Setting::getWorkingHours($dayName);
            
            if (!$workingHours['is_working']) {
                return [
                    'success' => false,
                    'message' => 'Cannot book appointment - closed on ' . ucfirst($dayName) . '.'
                ];
            }
            
            $submittedTime = Carbon::parse($appointmentDate . ' ' . $appointmentTime);
            $workingStart = Carbon::parse($appointmentDate . ' ' . $workingHours['start_time']);
            $workingEnd = Carbon::parse($appointmentDate . ' ' . $workingHours['end_time']);
            
            if ($submittedTime->lt($workingStart) || $submittedTime->copy()->addMinutes($appointmentDuration)->gt($workingEnd)) {
                return [
                    'success' => false,
                    'message' => 'Selected time is outside working hours (' . 
                               $workingHours['start_time'] . ' - ' . $workingHours['end_time'] . ').'
                ];
            }
        }

        $hasConflict = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $appointmentDate)
            ->whereTime('appointment_time', Carbon::parse($appointmentTime)->format('H:i:s'))
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->exists();

        if ($hasConflict) {
            return [
                'success' => false,
                'message' => 'Doctor already has an appointment at this time.'
            ];
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
