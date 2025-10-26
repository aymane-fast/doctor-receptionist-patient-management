<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Setting;
use App\Services\PatientSearchService;
use App\Http\Requests\Traits\ValidatesAppointments;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    use ValidatesAppointments;
    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        // Check if there's a search query or show_today request
        $hasSearchQuery = $request->filled('search') || $request->filled('date') || $request->filled('status') || $request->filled('doctor_id') || $request->has('show_today');
        
        if (!$hasSearchQuery) {
            // No search performed, return empty collection
            $appointments = collect();
        } else {
            $query = Appointment::with(['patient', 'doctor']);

            // Handle "Today" button
            if ($request->has('show_today')) {
                $query->whereDate('appointment_date', Carbon::today());
            }

            // Search by patient information
            if ($request->filled('search')) {
                $search = trim($request->string('search'));
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('id_card_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by date
            if ($request->filled('date')) {
                $query->whereDate('appointment_date', $request->date);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by doctor for receptionists
            if ($request->filled('doctor_id') && Auth::user()->isReceptionist()) {
                $query->where('doctor_id', $request->doctor_id);
            }

            // If user is doctor, only show their appointments
            if (Auth::user()->isDoctor()) {
                $query->where('doctor_id', Auth::id());
            }

            $appointments = $query->orderBy('appointment_date', 'desc')
                                 ->orderBy('appointment_time', 'desc')
                                 ->paginate(15)
                                 ->appends($request->query());
        }

        $doctors = User::where('role', 'doctor')->get();

        return view('appointments.index', compact('appointments', 'doctors', 'hasSearchQuery'));
    }

    /**
     * Show the form for creating a new appointment
     */
    public function create(Request $request)
    {
        $patients = collect(); // Start with empty collection
        $selectedPatient = null;
        
        // If patient_id is provided in URL, get that patient
        if ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }
        
        $doctors = User::where('role', 'doctor')->get();
        
        return view('appointments.create', compact('patients', 'doctors', 'selectedPatient'));
    }

    /**
     * API endpoint for patient search autocomplete
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        return response()->json(PatientSearchService::search($query));
    }    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getAppointmentValidationRules());

        // All new appointments start as scheduled
        $validated['status'] = 'scheduled';

        // Validate working hours
        $workingHoursError = $this->validateWorkingHours($validated['appointment_date'], $validated['appointment_time']);
        if ($workingHoursError) {
            return back()->withErrors(['appointment_time' => $workingHoursError]);
        }

        // Check for conflicts
        if ($this->hasAppointmentConflict($validated['doctor_id'], $validated['appointment_date'], $validated['appointment_time'])) {
            return back()->withErrors(['appointment_time' => 'Doctor already has an appointment at this time.']);
        }

        $appointment = Appointment::create($validated);

        return redirect()->route('appointments.show', $appointment)
                        ->with('success', 'Appointment created successfully!');
    }

    /**
     * Display the specified appointment
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'medicalRecord']);
        
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the appointment
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->get();
        
        return view('appointments.edit', compact('appointment', 'patients', 'doctors'));
    }

    /**
     * Update the specified appointment
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Validate appointment is during working hours (only if not completed/cancelled)
        if (!in_array($validated['status'], ['completed', 'cancelled'])) {
            $appointmentDateTime = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);
            
            if (!Setting::isTimeWithinWorkingHours($appointmentDateTime)) {
                $workingHours = Setting::getWorkingHours($appointmentDateTime->format('l'));
                $errorMessage = 'Appointment time is outside working hours. ';
                
                if ($workingHours && $workingHours['is_working']) {
                    $errorMessage .= 'Working hours for ' . $appointmentDateTime->format('l') . ' are ' . 
                                   $workingHours['start_time'] . ' - ' . $workingHours['end_time'] . '.';
                } else {
                    $errorMessage .= 'We are closed on ' . $appointmentDateTime->format('l') . '.';
                }
                
                return back()->withErrors(['appointment_time' => $errorMessage]);
            }

            // Check if appointment is too close to end of working hours
            $dayName = strtolower($appointmentDateTime->format('l'));
            $workingHours = Setting::getWorkingHours($dayName);
            if ($workingHours && $workingHours['is_working']) {
                $endTime = Carbon::parse($validated['appointment_date'] . ' ' . $workingHours['end_time']);
                $appointmentEndTime = $appointmentDateTime->copy()->addMinutes(30);
                
                if ($appointmentEndTime->gt($endTime)) {
                    return back()->withErrors(['appointment_time' => 
                        'Appointment would extend past working hours. Last appointment should be scheduled at least 30 minutes before closing time (' . 
                        $workingHours['end_time'] . ').'
                    ]);
                }
            }
        }

        // Check for conflicts (excluding current appointment)
        $existingAppointment = Appointment::where('doctor_id', $validated['doctor_id'])
                                        ->whereDate('appointment_date', $validated['appointment_date'])
                                        ->whereTime('appointment_time', $validated['appointment_time'])
                                        ->where('status', '!=', 'cancelled')
                                        ->where('id', '!=', $appointment->id)
                                        ->first();

        if ($existingAppointment) {
            return back()->withErrors(['appointment_time' => 'Doctor already has an appointment at this time.']);
        }

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)
                        ->with('success', 'Appointment updated successfully!');
    }

    /**
     * Remove the specified appointment
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index')
                        ->with('success', 'Appointment deleted successfully!');
    }

    /**
     * Update appointment status
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled'
        ]);

        $appointment->update(['status' => $request->status]);

        return back()->with('success', 'Appointment status updated successfully!');
    }

    /**
     * Set the current patient for the doctor's queue
     */
    public function setCurrent(Request $request, Appointment $appointment)
    {
        // Only receptionists or the owning doctor can set current
        if (!(Auth::user()->isReceptionist() || (Auth::user()->isDoctor() && $appointment->doctor_id === Auth::id()))) {
            abort(403);
        }

        // Clear any existing current status for this doctor today (don't auto-complete)
        Appointment::where('doctor_id', $appointment->doctor_id)
            ->whereDate('appointment_date', today())
            ->where('status', 'in_progress')
            ->update(['status' => 'scheduled']);

        // Set this appointment as current/in progress
        $appointment->update(['status' => 'in_progress']);

        return back()->with('success', 'Patient session started successfully.');
    }

    /**
     * Mark the current patient as done
     */
    public function markCurrentDone(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:users,id',
        ]);

        $doctorId = $request->doctor_id ?: Auth::id();

        // Only receptionists can specify a doctor; doctors can only mark their own
        if ($request->doctor_id && !Auth::user()->isReceptionist()) {
            abort(403);
        }

        // Find current appointment for today (in_progress status)
        $current = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', today())
            ->where('status', 'in_progress')
            ->first();

        if ($current) {
            $current->update(['status' => 'completed']);
            return back()->with('success', 'Patient consultation completed successfully.');
        }

        return back()->with('error', 'No active consultation found.');
    }

    /**
     * Create a follow-up appointment for a patient
     */
    public function createFollowUp(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'current_appointment_id' => 'required|exists:appointments,id',
            'follow_up_number' => 'required|integer|min:1|max:365',
            'follow_up_unit' => 'required|in:days,weeks,months',
            'follow_up_reason' => 'nullable|string|max:500',
        ]);

        // Calculate the follow-up date
        $currentAppointment = Appointment::findOrFail($validated['current_appointment_id']);
        $followUpDate = Carbon::now();
        $followUpAmount = (int) $validated['follow_up_number']; // Cast to integer
        
        switch ($validated['follow_up_unit']) {
            case 'days':
                $followUpDate->addDays($followUpAmount);
                break;
            case 'weeks':
                $followUpDate->addWeeks($followUpAmount);
                break;
            case 'months':
                $followUpDate->addMonths($followUpAmount);
                break;
        }

        // Find the next available appointment slot on that date
        $workingHours = Setting::getWorkingHours($followUpDate->format('l'));
        
        if (!$workingHours['is_working'] || $workingHours['start_time'] === null) {
            // If no working hours for that day, try the next working day (up to 7 days ahead)
            for ($i = 1; $i <= 7; $i++) {
                $testDate = $followUpDate->copy()->addDays($i);
                $testWorkingHours = Setting::getWorkingHours($testDate->format('l'));
                if ($testWorkingHours['is_working'] && $testWorkingHours['start_time'] !== null) {
                    $followUpDate = $testDate;
                    $workingHours = $testWorkingHours;
                    break;
                }
            }
        }

        // Start from working hours start time
        $appointmentTime = Carbon::parse($followUpDate->format('Y-m-d') . ' ' . $workingHours['start_time']);
        
        // Find next available slot (assuming 30-minute slots)
        while ($appointmentTime->format('H:i') <= $workingHours['end_time']) {
            $existingAppointment = Appointment::where('doctor_id', Auth::id())
                ->where('appointment_date', $appointmentTime->format('Y-m-d'))
                ->where('appointment_time', $appointmentTime->format('H:i:s'))
                ->where('status', '!=', 'cancelled')
                ->first();

            if (!$existingAppointment) {
                break; // Found available slot
            }
            
            $appointmentTime->addMinutes(30);
        }

        // Create the follow-up appointment
        $followUpAppointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => Auth::id(),
            'appointment_date' => $appointmentTime->format('Y-m-d'),
            'appointment_time' => $appointmentTime->format('H:i:s'),
            'status' => 'scheduled',
            'reason' => $validated['follow_up_reason'] ?: __('appointments.follow_up_appointment'),
            'notes' => 'Follow-up from appointment on ' . Carbon::parse($currentAppointment->appointment_date)->format('M j, Y'),
        ]);

        $timeAmount = $validated['follow_up_number'];
        $timeUnit = $validated['follow_up_unit'];
        $appointmentDateTime = $appointmentTime->format('M j, Y \a\t g:i A');

        return redirect()->route('doctor.current')->with('success', 
            "Follow-up appointment scheduled for {$timeAmount} {$timeUnit} from now on {$appointmentDateTime}");
    }

    /**
     * Get working hours for the frontend
     */
    public function getWorkingHours()
    {
        $workingHours = Setting::getAllWorkingHours();
        $currentWorkingHours = Setting::getWorkingHours();
        
        return response()->json([
            'all_days' => $workingHours,
            'today' => $currentWorkingHours,
            'is_within_hours' => Setting::isWithinWorkingHours(),
            'next_working_time' => Setting::getNextWorkingTime()?->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cancel an appointment
     */
    public function cancel(Appointment $appointment)
    {
        // Only receptionists or the owning doctor can cancel
        if (!(Auth::user()->isReceptionist() || (Auth::user()->isDoctor() && $appointment->doctor_id === Auth::id()))) {
            return back()->with('error', 'Unauthorized action.');
        }

        $appointment->update([
            'status' => 'cancelled',
            'notes' => ($appointment->notes ? $appointment->notes . "\n" : '') . 'Cancelled on ' . now()->format('Y-m-d H:i')
        ]);

        return back()->with('success', 'Appointment cancelled successfully!');
    }

    /**
     * Simple reschedule - redirect to edit page
     */
    public function reschedule(Appointment $appointment)
    {
        // Only receptionists or the owning doctor can reschedule
        if (!(Auth::user()->isReceptionist() || (Auth::user()->isDoctor() && $appointment->doctor_id === Auth::id()))) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Redirect to edit page for manual rescheduling
        return redirect()->route('appointments.edit', $appointment)
            ->with('info', 'Please select a new date and time for this appointment.');
    }

}
