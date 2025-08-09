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

        // Ensure there's a current appointment, otherwise auto-assign first scheduled today
        $current = Appointment::with(['patient'])
            ->where('doctor_id', Auth::id())
            ->today()
            ->where('is_current', true)
            ->first();

        if (!$current) {
            $first = Appointment::with('patient')
                ->where('doctor_id', Auth::id())
                ->today()
                ->where('status', 'scheduled')
                ->orderBy('appointment_time')
                ->first();
            if ($first) {
                Appointment::where('doctor_id', Auth::id())
                    ->today()
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
                $first->update(['is_current' => true, 'status' => 'in_progress']);
                $current = $first;
            }
        }

        $patient = $current ? $current->patient()->with(['appointments.doctor', 'medicalRecords.doctor', 'prescriptions.doctor'])->first() : null;

        return view('doctor.current', [
            'currentAppointment' => $current,
            'patient' => $patient,
        ]);
    }
}


