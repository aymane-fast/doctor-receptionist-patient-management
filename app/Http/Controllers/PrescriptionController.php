<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\MedicalRecord;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403);
        }

        $query = Prescription::with(['patient', 'doctor', 'medicalRecord'])
            ->where('doctor_id', Auth::id());

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $prescriptions = $query->latest('prescribed_date')->paginate(15);
        $patients = Patient::whereHas('appointments', function ($q) {
            $q->where('doctor_id', Auth::id());
        })->get();

        return view('prescriptions.index', compact('prescriptions', 'patients'));
    }

    public function create(Request $request)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403);
        }

        $patients = Patient::whereHas('appointments', function ($q) {
            $q->where('doctor_id', Auth::id());
        })->get();

        $medicalRecords = MedicalRecord::with('patient')
            ->where('doctor_id', Auth::id())
            ->latest('visit_date')->get();

        return view('prescriptions.create', compact('patients', 'medicalRecords'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'medication_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'instructions' => 'required|string',
            'prescribed_date' => 'required|date|before_or_equal:today',
        ]);

        $validated['doctor_id'] = Auth::id();

        $prescription = Prescription::create($validated);

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully!');
    }

    public function show(Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $prescription->load(['patient', 'doctor', 'medicalRecord']);
        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $patients = Patient::whereHas('appointments', function ($q) {
            $q->where('doctor_id', Auth::id());
        })->get();

        $medicalRecords = MedicalRecord::with('patient')
            ->where('doctor_id', Auth::id())
            ->latest('visit_date')->get();

        return view('prescriptions.edit', compact('prescription', 'patients', 'medicalRecords'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'medication_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'instructions' => 'required|string',
            'prescribed_date' => 'required|date|before_or_equal:today',
        ]);

        $prescription->update($validated);

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully!');
    }

    public function destroy(Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $prescription->delete();
        return redirect()->route('prescriptions.index')->with('success', 'Prescription deleted successfully!');
    }
}
