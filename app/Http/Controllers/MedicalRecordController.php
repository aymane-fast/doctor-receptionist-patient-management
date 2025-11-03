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

        $medicalRecords = collect(); // Empty collection by default
        $hasSearchQuery = false;

        // Only query records if there's a search term or date filter
        if ($request->filled('search') || $request->filled('date')) {
            $hasSearchQuery = true;
            
            $query = MedicalRecord::with(['patient', 'doctor', 'appointment'])
                                 ->where('doctor_id', Auth::id());

            // Search by patient information
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $terms = array_filter(explode(' ', trim($searchTerm)));
                
                $query->whereHas('patient', function($q) use ($searchTerm, $terms) {
                    // Search individual fields
                    $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('id_card_number', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                    
                    // Handle multiple terms for full name search
                    if (count($terms) >= 2) {
                        $q->orWhere(function ($subQuery) use ($terms) {
                            foreach ($terms as $i => $firstTerm) {
                                foreach ($terms as $j => $lastTerm) {
                                    if ($i !== $j) {
                                        $subQuery->orWhere(function ($combo) use ($firstTerm, $lastTerm) {
                                            $combo->where('first_name', 'LIKE', "%{$firstTerm}%")
                                                  ->where('last_name', 'LIKE', "%{$lastTerm}%");
                                        });
                                    }
                                }
                            }
                        });
                    }
                    
                    // Search in concatenated full name
                    foreach ($terms as $term) {
                        if (strlen($term) >= 2) {
                            $q->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
                        }
                    }
                });
            }

            // Filter by date
            if ($request->has('date') && $request->date) {
                $query->whereDate('visit_date', $request->date);
            }

            $medicalRecords = $query->latest('visit_date')->paginate(15);
        }

        return view('medical-records.index', compact('medicalRecords', 'hasSearchQuery'));
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
        $selectedPatient = null;
        
        if ($request->has('appointment_id')) {
            $appointment = Appointment::with('patient')->findOrFail($request->appointment_id);
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
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $patients = Patient::where(function($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('id_card_number', 'like', "%{$query}%");
        })
        ->limit(10)
        ->get()
        ->map(function($patient) {
            return [
                'id' => $patient->id,
                'name' => $patient->first_name . ' ' . $patient->last_name,
                'phone' => $patient->phone,
                'email' => $patient->email,
                'display' => $patient->first_name . ' ' . $patient->last_name . ' - ' . $patient->phone
            ];
        });
        
        return response()->json($patients);
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

        $patients = Patient::orderBy('first_name')->orderBy('last_name')->get();

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
