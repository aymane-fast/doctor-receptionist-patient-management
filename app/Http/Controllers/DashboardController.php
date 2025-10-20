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
        // Today's appointments with filtering
        $todayAppointmentsQuery = Appointment::with(['patient', 'doctor'])
            ->where('doctor_id', Auth::id())
            ->today();

        // Apply appointment filters
        if (request('patient_search')) {
            $searchTerm = request('patient_search');
            $todayAppointmentsQuery->whereHas('patient', function($query) use ($searchTerm) {
                $query->where('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_card_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $searchTerm . '%']);
            });
        }

        if (request('status_filter')) {
            $todayAppointmentsQuery->where('status', request('status_filter'));
        }

        $todayAppointments = $todayAppointmentsQuery
            ->orderBy('appointment_time')
            ->paginate(4, ['*'], 'doc_today_page');

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

        // Medical records with filtering
        $recentRecordsQuery = MedicalRecord::with('patient')
            ->where('doctor_id', Auth::id());

        // Apply medical records filters
        if (request('record_patient_search')) {
            $searchTerm = request('record_patient_search');
            $recentRecordsQuery->whereHas('patient', function($query) use ($searchTerm) {
                $query->where('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_card_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $searchTerm . '%']);
            });
        }

        if (request('record_date_from')) {
            $recentRecordsQuery->whereDate('visit_date', '>=', request('record_date_from'));
        }

        if (request('record_date_to')) {
            $recentRecordsQuery->whereDate('visit_date', '<=', request('record_date_to'));
        }

        $recentRecords = $recentRecordsQuery
            ->latest('visit_date')
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
        // Today's appointments with filtering
        $todayAppointmentsQuery = Appointment::with(['patient', 'doctor'])
            ->today();

        // Apply appointment filters
        if (request('patient_search')) {
            $searchTerm = request('patient_search');
            $todayAppointmentsQuery->whereHas('patient', function($query) use ($searchTerm) {
                $query->where('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_card_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $searchTerm . '%']);
            });
        }

        if (request('doctor_filter')) {
            $todayAppointmentsQuery->where('doctor_id', request('doctor_filter'));
        }

        if (request('status_filter')) {
            $todayAppointmentsQuery->where('status', request('status_filter'));
        }

        $todayAppointments = $todayAppointmentsQuery
            ->orderBy('appointment_time')
            ->paginate(4, ['*'], 'today_page');

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

        // Recent patients with filtering
        $recentPatientsQuery = Patient::query();

        // Apply patient filters
        if (request('patient_name_search')) {
            $searchTerm = request('patient_name_search');
            $recentPatientsQuery->where(function($query) use ($searchTerm) {
                $query->where('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                      ->orWhere('id_card_number', 'like', '%' . $searchTerm . '%')
                      ->orWhere('email', 'like', '%' . $searchTerm . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $searchTerm . '%']);
            });
        }

        if (request('age_from')) {
            $recentPatientsQuery->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= ?', [request('age_from')]);
        }

        if (request('age_to')) {
            $recentPatientsQuery->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) <= ?', [request('age_to')]);
        }

        if (request('reg_date_from')) {
            $recentPatientsQuery->whereDate('created_at', '>=', request('reg_date_from'));
        }

        if (request('reg_date_to')) {
            $recentPatientsQuery->whereDate('created_at', '<=', request('reg_date_to'));
        }

        $recentPatients = $recentPatientsQuery
            ->latest()
            ->paginate(5, ['*'], 'recent_patients_page');

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
