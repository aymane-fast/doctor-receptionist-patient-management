<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Services\PatientSearchService;

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

            // Search by patient information
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->whereHas('patient', fn ($q) => $q->search($searchTerm));
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

        $selectedPatient = null;
        
        if ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        $medicalRecords = MedicalRecord::with('patient')
            ->where('doctor_id', Auth::id())
            ->latest('visit_date')->get();

        return view('prescriptions.create', compact('selectedPatient', 'medicalRecords'));
    }

    /**
     * API endpoint for patient search autocomplete
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([]);
        }
        
        $patients = PatientSearchService::search($query);
        
        // Format for autocomplete display
        $formattedPatients = $patients->map(function ($patient) {
            return [
                'id' => $patient['id'],
                'name' => $patient['name'],
                'phone' => $patient['phone'] ?? 'No phone',
                'email' => $patient['email'] ?? 'No email',
                'display' => $patient['display']
            ];
        });
        
        return response()->json($formattedPatients);
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

        if (!empty($validated['medical_record_id'])) {
            $medicalRecord = MedicalRecord::where('id', $validated['medical_record_id'])
                ->where('doctor_id', Auth::id())
                ->firstOrFail();

            $validated['patient_id'] = $medicalRecord->patient_id;
        }

        $prescription = \DB::transaction(function () use ($validated) {
            $prescription = Prescription::create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => Auth::id(),
                'medical_record_id' => $validated['medical_record_id'] ?? null,
                'prescribed_date' => $validated['prescribed_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncItems($prescription, $validated['items']);

            return $prescription;
        });

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully!');
    }

    /**
     * Replace the prescription's medication items with the submitted set.
     */
    private function syncItems(Prescription $prescription, array $items): void
    {
        $prescription->items()->delete();

        foreach ($items as $item) {
            $prescription->items()->create([
                'medication_name' => $item['medication_name'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration_days' => $item['duration_days'] ?? null,
                'instructions' => $item['instructions'] ?? null,
            ]);
        }
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
            'prescribed_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.medication_name' => 'required|string|max:255',
            'items.*.dosage' => 'required|string|max:255',
            'items.*.frequency' => 'required|string|max:255',
            'items.*.duration_days' => 'nullable|integer|min:1',
            'items.*.instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['medical_record_id'])) {
            $medicalRecord = MedicalRecord::where('id', $validated['medical_record_id'])
                ->where('doctor_id', Auth::id())
                ->firstOrFail();

            $validated['patient_id'] = $medicalRecord->patient_id;
        }

        \DB::transaction(function () use ($prescription, $validated) {
            $prescription->update([
                'patient_id' => $validated['patient_id'],
                'medical_record_id' => $validated['medical_record_id'] ?? null,
                'prescribed_date' => $validated['prescribed_date'],
                'notes' => $validated['notes'] ?? null,
                // Clear legacy single-medication columns now that items are authoritative.
                'medication_name' => null,
                'dosage' => null,
                'frequency' => null,
                'duration_days' => null,
                'instructions' => null,
            ]);

            $this->syncItems($prescription, $validated['items']);
        });

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
