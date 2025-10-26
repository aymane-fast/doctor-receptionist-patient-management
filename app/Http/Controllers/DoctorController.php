<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Patient;

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

        $patient = $current ? $current->patient()->with(['appointments.doctor', 'medicalRecords.doctor', 'prescriptions.doctor'])->first() : null;

        return view('doctor.current', [
            'currentAppointment' => $current,
            'patient' => $patient,
        ]);
    }
}


