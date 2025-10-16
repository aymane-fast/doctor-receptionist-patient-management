<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        // Text search across patient name, patient number, phone, id card
        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id_card_number', 'like', "%{$search}%");
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

        $doctors = User::where('role', 'doctor')->get();
        $patients = Patient::all();

        return view('appointments.index', compact('appointments', 'doctors', 'patients'));
    }

    /**
     * Show the form for creating a new appointment
     */
    public function create()
    {
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->get();
        
        return view('appointments.create', compact('patients', 'doctors'));
    }

    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'nullable|in:scheduled,in_progress,completed,cancelled',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'scheduled';
        }

        // Validate appointment is during working hours
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

        // Check if appointment is too close to end of working hours (less than 30 minutes)
        $dayName = strtolower($appointmentDateTime->format('l'));
        $workingHours = Setting::getWorkingHours($dayName);
        if ($workingHours && $workingHours['is_working']) {
            $endTime = Carbon::parse($validated['appointment_date'] . ' ' . $workingHours['end_time']);
            $appointmentEndTime = $appointmentDateTime->copy()->addMinutes(30); // Assume 30-minute appointments
            
            if ($appointmentEndTime->gt($endTime)) {
                return back()->withErrors(['appointment_time' => 
                    'Appointment would extend past working hours. Last appointment should be scheduled at least 30 minutes before closing time (' . 
                    $workingHours['end_time'] . ').'
                ]);
            }
        }

        // Check for conflicts
        $existingAppointment = Appointment::where('doctor_id', $validated['doctor_id'])
                                        ->whereDate('appointment_date', $validated['appointment_date'])
                                        ->whereTime('appointment_time', $validated['appointment_time'])
                                        ->where('status', '!=', 'cancelled')
                                        ->first();

        if ($existingAppointment) {
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

        // Clear any existing current for this doctor for today
        Appointment::where('doctor_id', $appointment->doctor_id)
            ->whereDate('appointment_date', $appointment->appointment_date)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        // Mark this appointment as current and in progress if scheduled
        $appointment->is_current = true;
        if ($appointment->status === 'scheduled') {
            $appointment->status = 'in_progress';
        }
        $appointment->save();

        return back()->with('success', 'Current patient set.');
    }

    /**
     * Mark the current patient as done and move to next
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

        // Find current appointment for today
        $current = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', today())
            ->where('is_current', true)
            ->first();

        if ($current) {
            $current->update([
                'is_current' => false,
                'status' => 'completed',
            ]);
        }

        // Auto-assign the next scheduled appointment today as current
        $next = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', today())
            ->where('status', 'scheduled')
            ->orderBy('appointment_time')
            ->first();

        if ($next) {
            $next->update([
                'is_current' => true,
                'status' => 'in_progress',
            ]);
        }

        return back()->with('success', $current ? 'Marked current as done.' : 'No current appointment.') ;
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
}
