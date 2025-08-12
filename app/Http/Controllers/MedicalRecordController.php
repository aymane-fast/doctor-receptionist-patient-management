<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Appointment;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of medical records
     */
    public function index(Request $request)
    {
        // Only doctors can access medical records
        if (!Auth::user()->isDoctor()) {
            abort(403, 'Unauthorized access to medical records.');
        }

        $query = MedicalRecord::with(['patient', 'doctor', 'appointment'])
                             ->where('doctor_id', Auth::id());

        // Search by patient
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        $medicalRecords = $query->latest('visit_date')->paginate(15);
        $patients = Patient::whereHas('appointments', function($q) {
            $q->where('doctor_id', Auth::id());
        })->get();

        return view('medical-records.index', compact('medicalRecords', 'patients'));
    }

    /**
     * Show the form for creating a new medical record
     */
    public function create(Request $request)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403, 'Unauthorized access to medical records.');
        }

        $appointment = null;
        if ($request->has('appointment_id')) {
            $appointment = Appointment::with('patient')->findOrFail($request->appointment_id);
        }

        $patients = Patient::whereHas('appointments', function($q) {
            $q->where('doctor_id', Auth::id());
        })->get();

        return view('medical-records.create', compact('patients', 'appointment'));
    }

    /**
     * Store a newly created medical record
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403, 'Unauthorized access to medical records.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'blood_pressure' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:0',
        ]);

        $validated['doctor_id'] = Auth::id();

        $record = MedicalRecord::create($validated);

        // Update appointment status if linked
        if ($request->appointment_id) {
            $appointment = Appointment::find($request->appointment_id);
            if ($appointment) {
                $appointment->update(['status' => 'completed']);
            }
        }

        return redirect()->route('medical-records.show', $record)
                        ->with('success', 'Medical record created successfully!');
    }

    /**
     * Display the specified medical record
     */
    public function show(MedicalRecord $medicalRecord)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this medical record.');
        }

        $medicalRecord->load(['patient', 'doctor', 'appointment', 'prescriptions']);
        
        return view('medical-records.show', compact('medicalRecord'));
    }

    /**
     * Show the form for editing the medical record
     */
    public function edit(MedicalRecord $medicalRecord)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this medical record.');
        }

        $patients = Patient::whereHas('appointments', function($q) {
            $q->where('doctor_id', Auth::id());
        })->get();

        return view('medical-records.edit', compact('medicalRecord', 'patients'));
    }

    /**
     * Update the specified medical record
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this medical record.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'blood_pressure' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:0',
        ]);

        $medicalRecord->update($validated);

        return redirect()->route('medical-records.show', $medicalRecord)
                        ->with('success', 'Medical record updated successfully!');
    }

    /**
     * Remove the specified medical record
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this medical record.');
        }

        $medicalRecord->delete();

        return redirect()->route('medical-records.index')
                        ->with('success', 'Medical record deleted successfully!');
    }
}
