<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Appointment;
use App\Services\PatientSearchService;
use App\Services\SpecialtyService;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of medical records
     */
    public function index(Request $request)
    {
        // Only doctors can access medical records
        if (!Auth::user()->isDoctor()) {
            abort(403, __('Unauthorized access to medical records.'));
        }

        $request->validate(['search' => 'nullable|string|max:255', 'date' => 'nullable|date', 'patient_id' => 'nullable|integer|exists:patients,id']);
        $medicalRecords = collect(); // Empty collection by default
        $hasSearchQuery = false;

        // Only query records if there's a search term or date filter
        if ($request->filled('search') || $request->filled('date') || $request->filled('patient_id')) {
            $hasSearchQuery = true;
            
            $query = MedicalRecord::with(['patient', 'doctor', 'appointment'])
                                 ->where('doctor_id', Auth::id());
            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->integer('patient_id'));
            }

            // Search by patient information
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->whereHas('patient', fn ($q) => $q->search($searchTerm));
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('visit_date', $request->date);
            }

            $medicalRecords = $query->latest('visit_date')->paginate(15)->withQueryString();
        }

        return view('medical-records.index', compact('medicalRecords', 'hasSearchQuery'));
    }

    /**
     * Show the form for creating a new medical record
     */
    public function create(Request $request)
    {
        $request->validate([
            'patient_id' => 'nullable|integer|exists:patients,id',
            'appointment_id' => 'nullable|integer|exists:appointments,id',
        ]);
        if (!Auth::user()->isDoctor()) {
            abort(403, __('Unauthorized access to medical records.'));
        }

        $appointment = null;
        $selectedPatient = null;
        
        if ($request->has('appointment_id')) {
            $appointment = Appointment::with('patient')
                ->where('doctor_id', Auth::id())
                ->findOrFail($request->appointment_id);
            $selectedPatient = $appointment->patient;
            if ($record = $appointment->medicalRecord) {
                return redirect()->route('medical-records.edit', $record);
            }
        } elseif ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }
        if (!$selectedPatient && is_scalar($request->old('patient_id'))) {
            $selectedPatient = Patient::find((int) $request->old('patient_id'));
        }

        return view('medical-records.create', compact('selectedPatient', 'appointment'));
    }

    /**
     * API endpoint for patient search autocomplete
     */
    public function searchPatients(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:255', 'query' => 'nullable|string|max:255']);
        // Accept both 'q' and 'query' parameters for compatibility
        $query = $request->get('q', $request->get('query', ''));
        
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

    /**
     * Store a newly created medical record
     */
    public function store(Request $request, SpecialtyService $specialties)
    {
        if (!Auth::user()->isDoctor()) {
            abort(403, __('Unauthorized access to medical records.'));
        }

        $validated = $request->validate(array_merge([
            'patient_id' => 'required|integer|exists:patients,id',
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id', \Illuminate\Validation\Rule::unique('medical_records', 'appointment_id')],
            'visit_date' => 'required|date|before_or_equal:today',
            'visit_type' => 'nullable|in:consultation,follow-up,emergency,routine-checkup',
            'history_present_illness' => 'nullable|string|max:16000',
            'physical_examination' => 'nullable|string|max:16000',
            'symptoms' => 'nullable|string|max:16000',
            'diagnosis' => 'nullable|string|max:16000',
            'treatment' => 'nullable|string|max:16000',
            'notes' => 'nullable|string|max:16000',
            'weight' => 'nullable|numeric|min:0|max:999.99',
            'height' => 'nullable|numeric|min:0|max:999.99',
            'blood_pressure' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric|min:0|max:60',
            'heart_rate' => 'nullable|integer|min:0|max:2147483647',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
            'return_to_workspace' => 'nullable|boolean',
        ], $specialties->validationRules('record')));

        if ($request->has('specialty_data')) {
            $validated['specialty_data'] = $specialties->mergeSubmittedData(
                [],
                $validated['specialty_data'] ?? [],
                'record'
            );
        }

        $validated['doctor_id'] = Auth::id();
        $validated['temperature_unit'] = 'C';
        unset($validated['return_to_workspace']);
        $appointment = null;

        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::where('id', $validated['appointment_id'])
                ->where('doctor_id', Auth::id())
                ->firstOrFail();

            if ((int) $validated['patient_id'] !== $appointment->patient_id) {
                throw \Illuminate\Validation\ValidationException::withMessages(['patient_id' => __('workflow.context_mismatch')]);
            }
        }

        $record = \DB::transaction(function () use ($validated, $appointment) {
            if ($appointment) {
                \App\Models\User::whereKey($appointment->doctor_id)->lockForUpdate()->firstOrFail();
                $appointment->refresh();
                if (MedicalRecord::where('appointment_id', $appointment->id)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'appointment_id' => __('A consultation record already exists for this appointment.'),
                    ]);
                }
            }
            $record = MedicalRecord::create($validated);
            // Saving observations is not finishing the visit. The doctor may
            // still need to prescribe, order tests or attach results.

            return $record;
        });

        return redirect()->route($request->boolean('return_to_workspace') && $appointment?->fresh()->status === 'in_progress'
            ? 'doctor.current' : 'medical-records.show', $request->boolean('return_to_workspace') && $appointment?->fresh()->status === 'in_progress' ? [] : [$record])
                        ->with('success', __('Medical record created successfully!'));
    }

    /**
     * Display the specified medical record
     */
    public function show(MedicalRecord $medicalRecord)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, __('Unauthorized access to this medical record.'));
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
            abort(403, __('Unauthorized access to this medical record.'));
        }

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();
        $appointments = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->where('patient_id', $medicalRecord->patient_id)
            ->latest('appointment_date')
            ->get();

        return view('medical-records.edit', compact('medicalRecord', 'patients', 'appointments'));
    }

    /**
     * Update the specified medical record
     */
    public function update(Request $request, MedicalRecord $medicalRecord, SpecialtyService $specialties)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, __('Unauthorized access to this medical record.'));
        }

        $validated = $request->validate(array_merge([
            'patient_id' => 'required|integer|exists:patients,id',
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id', \Illuminate\Validation\Rule::unique('medical_records', 'appointment_id')->ignore($medicalRecord->id)],
            'visit_date' => 'required|date|before_or_equal:today',
            'visit_type' => 'nullable|in:consultation,follow-up,emergency,routine-checkup',
            'history_present_illness' => 'nullable|string|max:16000',
            'physical_examination' => 'nullable|string|max:16000',
            'symptoms' => 'nullable|string|max:16000',
            'diagnosis' => 'nullable|string|max:16000',
            'treatment' => 'nullable|string|max:16000',
            'notes' => 'nullable|string|max:16000',
            'weight' => 'nullable|numeric|min:0|max:999.99',
            'height' => 'nullable|numeric|min:0|max:999.99',
            'blood_pressure' => 'nullable|string|max:255',
            'temperature' => 'nullable|numeric|min:0|max:'.match ($request->input('temperature_unit', $medicalRecord->temperature_unit)) { 'C' => 60, 'F' => 150, default => 999.9 },
            'heart_rate' => 'nullable|integer|min:0|max:2147483647',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
            'temperature_unit' => 'nullable|in:C,F',
            'return_to_workspace' => 'nullable|boolean',
        ], $specialties->validationRules('record')));

        if ($request->has('specialty_data')) {
            $validated['specialty_data'] = $specialties->mergeSubmittedData(
                $medicalRecord->specialty_data ?? [],
                $validated['specialty_data'] ?? [],
                'record'
            );
        }

        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::where('id', $validated['appointment_id'])
                ->where('doctor_id', Auth::id())
                ->firstOrFail();

            if ((int) $validated['patient_id'] !== $appointment->patient_id) {
                throw \Illuminate\Validation\ValidationException::withMessages(['patient_id' => __('workflow.context_mismatch')]);
            }
        }

        if (((int) $validated['patient_id'] !== $medicalRecord->patient_id
            || ($request->has('appointment_id') && (int) ($validated['appointment_id'] ?? 0) !== (int) $medicalRecord->appointment_id))
            && ($medicalRecord->prescriptions()->exists() || $medicalRecord->orders()->exists() || $medicalRecord->documents()->exists())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'patient_id' => __('workflow.context_mismatch'),
            ]);
        }

        unset($validated['return_to_workspace']);
        $medicalRecord->update($validated);

        return redirect()->route($request->boolean('return_to_workspace') && $medicalRecord->appointment?->status === 'in_progress'
            ? 'doctor.current' : 'medical-records.show', $request->boolean('return_to_workspace') && $medicalRecord->appointment?->status === 'in_progress' ? [] : [$medicalRecord])
                        ->with('success', __('Medical record updated successfully!'));
    }

    /**
     * Remove the specified medical record
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        if (!Auth::user()->isDoctor() || $medicalRecord->doctor_id !== Auth::id()) {
            abort(403, __('Unauthorized access to this medical record.'));
        }

        $medicalRecord->delete();

        return redirect()->route('medical-records.index')
                        ->with('success', __('Medical record deleted successfully!'));
    }
}
