<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Setting;
use Carbon\Carbon;

class PatientController extends Controller
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(15);

        return view('patients.index', compact('patients'));
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
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'id_card_number' => 'nullable|string|max:255',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
            
            // Quick booking validation
            'book_today' => 'nullable|boolean',
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
                    $errorMessage .= 'Next available time is ' . $nextWorking->format('l, M j \a\t g:i A') . '.';
                }
                return back()->withErrors(['book_today' => $errorMessage])->withInput();
            }
            
            $appointmentResult = $this->createTodayAppointment($patient, $request);
            if (!$appointmentResult['success']) {
                return back()->withErrors(['book_today' => $appointmentResult['message']])->withInput();
            }
            
            return redirect()->route('patients.show', $patient)
                            ->with('success', 'Patient created successfully and appointment booked for today!');
        }

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient created successfully!');
    }

    /**
     * Create an appointment for today for a new patient
     */
    private function createTodayAppointment($patient, $request)
    {
        // Get the authenticated user (should be a doctor for this feature)
        $doctor = auth()->user();
        
        // Get today's date and time
        $today = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now();
        
        // Get today's working hours
        $dayName = strtolower($currentTime->format('l'));
        $workingHours = Setting::getWorkingHours($dayName);
        
        if (!$workingHours['is_working']) {
            return [
                'success' => false,
                'message' => 'Cannot book appointment today - we are closed on ' . ucfirst($dayName) . '.'
            ];
        }
        
        // Find the last appointment today for this doctor
        $lastAppointment = Appointment::where('doctor_id', $doctor->id)
                                     ->whereDate('appointment_date', $today)
                                     ->orderBy('appointment_time', 'desc')
                                     ->first();
        
        // Calculate next available time slot
        if ($lastAppointment) {
            // Add 30 minutes to the last appointment time
            $nextTime = Carbon::parse($today . ' ' . $lastAppointment->appointment_time)->addMinutes(30);
        } else {
            // Start from current time or working start time, whichever is later
            $workingStart = Carbon::parse($today . ' ' . $workingHours['start_time']);
            $nextTime = $currentTime->gt($workingStart) ? $currentTime->copy()->addMinutes(15) : $workingStart;
        }
        
        // Check if appointment would be within working hours
        $workingEnd = Carbon::parse($today . ' ' . $workingHours['end_time']);
        $appointmentEnd = $nextTime->copy()->addMinutes(30); // Assume 30-minute appointments
        
        if ($appointmentEnd->gt($workingEnd)) {
            return [
                'success' => false,
                'message' => 'Cannot book appointment - would extend past working hours. Working hours today are ' . 
                           $workingHours['start_time'] . ' - ' . $workingHours['end_time'] . '.'
            ];
        }
        
        // Create the appointment
        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $today,
            'appointment_time' => $nextTime->format('H:i:s'),
            'reason' => $request->appointment_reason ?: 'Walk-in consultation',
            'status' => $request->appointment_priority === 'emergency' ? 'urgent' : 'scheduled',
            'notes' => 'Auto-scheduled walk-in patient',
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
            'address' => 'required|string',
            'id_card_number' => 'nullable|string|max:255',
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
}
