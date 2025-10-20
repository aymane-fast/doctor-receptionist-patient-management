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

        $prescriptions = collect(); // Empty collection by default
        $hasSearchQuery = false;

        // Only query prescriptions if there's a search term or date filter
        if ($request->filled('search') || $request->filled('date')) {
            $hasSearchQuery = true;
            
            $query = Prescription::with(['patient', 'doctor', 'medicalRecord'])
                ->where('doctor_id', Auth::id());

            // Search by patient name, phone, or patient ID
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->whereHas('patient', function($q) use ($searchTerm) {
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('patient_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('id_card_number', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter by prescribed date
            if ($request->has('date') && $request->date) {
                $query->whereDate('prescribed_date', $request->date);
            }

            $prescriptions = $query->latest('prescribed_date')->paginate(15);
        }

        return view('prescriptions.index', compact('prescriptions', 'hasSearchQuery'));
    }

    public function create(Request $request)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403);
        }

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();

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
            'prescribed_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.medication_name' => 'required|string|max:255',
            'items.*.dosage' => 'required|string|max:255',
            'items.*.frequency' => 'required|string|max:255',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => Auth::id(),
            'medical_record_id' => $validated['medical_record_id'] ?? null,
            'prescribed_date' => $validated['prescribed_date'],
            // legacy columns required by current schema
            'medication_name' => 'Multiple medications',
            'dosage' => '-',
            'frequency' => '-',
            'duration_days' => 0,
            'instructions' => null,
        ]);

        // Create prescription items
        foreach ($validated['items'] as $item) {
            $prescription->items()->create([
                'medication_name' => $item['medication_name'],
                'dosage' => $item['dosage'], 
                'frequency' => $item['frequency'],
                'duration_days' => $item['duration_days'] ?? null,
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully!');
    }

    public function show(Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $prescription->load(['patient', 'doctor', 'medicalRecord', 'items']);
        return view('prescriptions.show', compact('prescription'));
    }

    public function print(Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $prescription->load(['patient', 'doctor', 'items']);
        return view('prescriptions.print', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        if (!Auth::user()->isDoctor() || $prescription->doctor_id !== Auth::id()) {
            abort(403);
        }

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();

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
