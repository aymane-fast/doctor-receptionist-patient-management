<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;

class PatientController extends Controller
{
    /**
     * Display a listing of patients
     */
    public function index(Request $request)
    {
        $query = Patient::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(15);

        return view('patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'id_card_number' => 'nullable|string|max:255',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
        ]);

        $patient = Patient::create($validated);

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient created successfully!');
    }

    /**
     * Display the specified patient
     */
    public function show(Patient $patient)
    {
        $patient->load(['appointments.doctor', 'medicalRecords.doctor', 'prescriptions.doctor', 'documents']);
        
        $recentAppointments = $patient->appointments()
                                    ->with('doctor')
                                    ->latest('appointment_date')
                                    ->take(5)
                                    ->get();

        $recentRecords = $patient->medicalRecords()
                                ->with('doctor')
                                ->latest('visit_date')
                                ->take(5)
                                ->get();

        return view('patients.show', compact('patient', 'recentAppointments', 'recentRecords'));
    }

    /**
     * Show the form for editing the patient
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'id_card_number' => 'nullable|string|max:255',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:255',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)
                        ->with('success', 'Patient updated successfully!');
    }

    /**
     * Remove the specified patient
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')
                        ->with('success', 'Patient deleted successfully!');
    }
}
