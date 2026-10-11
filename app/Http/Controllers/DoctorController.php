<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class DoctorController extends Controller
{
    /**
     * Show the doctor's current patient workspace
     */
    public function current()
    {
        if (!Auth::user()->isDoctor()) {
            abort(403);
        }

        // Get current appointment (in progress status)
        $current = Appointment::with(['patient'])
            ->where('doctor_id', Auth::id())
            ->today()
            ->where('status', 'in_progress')
            ->first();

        $patient = $current?->patient;
        $record = $current?->medicalRecord;
        if ($record && ($record->doctor_id !== Auth::id() || $record->patient_id !== $patient->id)) abort(409);
        $history = $patient ? $patient->medicalRecords()->where('doctor_id', Auth::id())->latest('visit_date')->limit(10)->get() : collect();
        $prescriptions = $patient ? $patient->prescriptions()->where('doctor_id', Auth::id())->with('items')->latest()->limit(10)->get() : collect();
        $waiting = Appointment::with('patient')->where('doctor_id', Auth::id())->today()->where('status', 'scheduled')
            ->inOperationalOrder()->get();

        return view('doctor.workspace', [
            'currentAppointment' => $current,
            'patient' => $patient,
            'record' => $record, 'history' => $history, 'prescriptions' => $prescriptions, 'waiting' => $waiting,
        ]);
    }
}


