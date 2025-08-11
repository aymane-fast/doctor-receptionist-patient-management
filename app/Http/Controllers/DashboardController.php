<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;

class DashboardController extends Controller
{
    /**
     * Show the dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isDoctor()) {
            return $this->doctorDashboard();
        } else {
            return $this->receptionistDashboard();
        }
    }

    /**
     * Doctor dashboard with medical data
     */
    private function doctorDashboard()
    {
        $todayAppointments = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->today()
            ->orderBy('appointment_time')
            ->paginate(10, ['*'], 'doc_today_page');

        $currentAppointment = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->today()
            ->where('is_current', true)
            ->first();

        // Auto-assign first scheduled appointment as current if none is set
        if (!$currentAppointment) {
            $firstScheduled = Appointment::with('patient')
                ->where('doctor_id', Auth::id())
                ->today()
                ->where('status', 'scheduled')
                ->orderBy('appointment_time')
                ->first();

            if ($firstScheduled) {
                // Clear any strays just in case
                Appointment::where('doctor_id', Auth::id())
                    ->today()
                    ->where('is_current', true)
                    ->update(['is_current' => false]);

                $firstScheduled->update([
                    'is_current' => true,
                    'status' => 'in_progress',
                ]);

                $currentAppointment = $firstScheduled;
            }
        }

        $upcomingAppointments = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->upcoming()
            ->limit(5)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $recentRecords = MedicalRecord::with('patient')
            ->where('doctor_id', Auth::id())
            ->latest()
            ->paginate(5, ['*'], 'doc_records_page');

        $stats = [
            'total_patients' => Patient::whereHas('appointments', function($query) {
                $query->where('doctor_id', Auth::id());
            })->count(),
            'today_appointments' => Appointment::where('doctor_id', Auth::id())
                ->today()
                ->count(),
            'pending_appointments' => Appointment::where('doctor_id', Auth::id())
                ->where('status', 'scheduled')
                ->count(),
            'completed_today' => Appointment::where('doctor_id', Auth::id())
                ->today()
                ->where('status', 'completed')
                ->count(),
        ];

        return view('dashboard.doctor', compact('todayAppointments', 'currentAppointment', 'upcomingAppointments', 'recentRecords', 'stats'));
    }

    /**
     * Receptionist dashboard with patient and appointment data
     */
    private function receptionistDashboard()
    {
        $todayAppointments = Appointment::with(['patient', 'doctor'])
            ->today()
            ->orderBy('appointment_time')
            ->paginate(10, ['*'], 'today_page');

        $currentByDoctor = Appointment::with(['patient', 'doctor'])
            ->today()
            ->where('is_current', true)
            ->get()
            ->keyBy('doctor_id');

        $upcomingAppointments = Appointment::with(['patient', 'doctor'])
            ->upcoming()
            ->limit(10)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $recentPatients = Patient::latest()->paginate(5, ['*'], 'recent_patients_page');

        $stats = [
            'total_patients' => Patient::count(),
            'today_appointments' => Appointment::today()->count(),
            'scheduled_appointments' => Appointment::where('status', 'scheduled')->count(),
            'new_patients_this_week' => Patient::where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        // Compute next-up per doctor
        $doctorIdsToday = Appointment::today()->pluck('doctor_id')->unique();
        $nextByDoctor = [];
        foreach ($doctorIdsToday as $docId) {
            $current = $currentByDoctor->get($docId);
            $nextQuery = Appointment::with(['patient', 'doctor'])
                ->where('doctor_id', $docId)
                ->today()
                ->where('status', 'scheduled')
                ->orderBy('appointment_time');
            if ($current) {
                $nextQuery->whereTime('appointment_time', '>', $current->appointment_time);
            }
            $nextByDoctor[$docId] = $nextQuery->first();
        }

        return view('dashboard.receptionist', compact('todayAppointments', 'currentByDoctor', 'nextByDoctor', 'upcomingAppointments', 'recentPatients', 'stats'));
    }
}
