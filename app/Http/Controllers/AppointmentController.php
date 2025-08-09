<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by doctor for receptionists
        if ($request->has('doctor_id') && Auth::user()->isReceptionist()) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // If user is doctor, only show their appointments
        if (Auth::user()->isDoctor()) {
            $query->where('doctor_id', Auth::id());
        }

        $appointments = $query->latest('appointment_date')
                             ->latest('appointment_time')
                             ->paginate(15);

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
}
