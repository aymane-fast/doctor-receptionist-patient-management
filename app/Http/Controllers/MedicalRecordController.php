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
        } elseif ($request->has('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
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
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'visit_date' => 'required|date|before_or_equal:today',
            'visit_type' => 'nullable|in:consultation,follow-up,emergency,routine-checkup',
            'history_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'blood_pressure' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:0',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
        ], $specialties->validationRules('record')));

        if ($request->has('specialty_data')) {
            $validated['specialty_data'] = $specialties->mergeSubmittedData(
                [],
                $validated['specialty_data'] ?? [],
                'record'
            );
        }

        $validated['doctor_id'] = Auth::id();
        $appointment = null;

        if (!empty($validated['appointment_id'])) {
            $appointment = Appointment::where('id', $validated['appointment_id'])
                ->where('doctor_id', Auth::id())
                ->firstOrFail();

            $validated['patient_id'] = $appointment->patient_id;
        }

        $record = \DB::transaction(function () use ($validated, $appointment) {
            $record = MedicalRecord::create($validated);
            if ($appointment) {
                $wasCurrent = $appointment->status === 'in_progress';
                $appointment->update(['status' => 'completed']);
                if ($wasCurrent && $appointment->appointment_date->isToday()) {
                    app(\App\Services\AppointmentSchedulingService::class)->promoteNextPatient($appointment->doctor_id);
                }
                if ($appointment->isQueueMode()) {
                    app(\App\Services\AppointmentSchedulingService::class)->recalculateQueue($appointment->doctor_id, $appointment->appointment_date);
                }
            }

            return $record;
        });

        return redirect()->route('medical-records.show', $record)
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
            'appointment_id' => 'nullable|integer|exists:appointments,id',
            'visit_date' => 'required|date|before_or_equal:today',
            'visit_type' => 'nullable|in:consultation,follow-up,emergency,routine-checkup',
            'history_present_illness' => 'nullable|string',
            'physical_examination' => 'nullable|string',
            'symptoms' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'blood_pressure' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0',
            'heart_rate' => 'nullable|integer|min:0',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
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

            $validated['patient_id'] = $appointment->patient_id;
        }

        if ((int) $validated['patient_id'] !== $medicalRecord->patient_id && $medicalRecord->prescriptions()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'patient_id' => __('A record with prescriptions cannot be moved to another patient.'),
            ]);
        }

        $medicalRecord->update($validated);

        return redirect()->route('medical-records.show', $medicalRecord)
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
