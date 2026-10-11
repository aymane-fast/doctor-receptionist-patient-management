<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard based on user role
     */
    public function index()
    {
        request()->validate([
            'patient_search' => 'nullable|string|max:255',
            'record_patient_search' => 'nullable|string|max:255',
            'patient_name_search' => 'nullable|string|max:255',
            'doctor_filter' => 'nullable|integer',
            'status_filter' => 'nullable|in:scheduled,in_progress,completed,cancelled,no_show',
            'record_date_from' => 'nullable|date', 'record_date_to' => 'nullable|date',
            'reg_date_from' => 'nullable|date', 'reg_date_to' => 'nullable|date',
            'age_from' => 'nullable|integer|min:0|max:150', 'age_to' => 'nullable|integer|min:0|max:150',
        ]);
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
            $todayAppointmentsQuery->whereHas('patient', fn ($query) => $query->search($searchTerm));
        }

        if (request('status_filter')) {
            $todayAppointmentsQuery->where('status', request('status_filter'));
        }

        $todayAppointments = $todayAppointmentsQuery
            ->orderBy('appointment_time')
            ->paginate(5, ['*'], 'doc_today_page')->withQueryString();

        $currentAppointment = Appointment::with('patient')
            ->where('doctor_id', Auth::id())
            ->today()
            ->where('status', 'in_progress')
            ->first();
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
            $recentRecordsQuery->whereHas('patient', fn ($query) => $query->search($searchTerm));
        }

        if (request('record_date_from')) {
            $recentRecordsQuery->whereDate('visit_date', '>=', request('record_date_from'));
        }

        if (request('record_date_to')) {
            $recentRecordsQuery->whereDate('visit_date', '<=', request('record_date_to'));
        }

        $recentRecords = $recentRecordsQuery
            ->latest('visit_date')
            ->paginate(5, ['*'], 'doc_records_page')->withQueryString();

        $stats = [
            'total_patients' => Patient::whereHas('appointments', function ($query) {
                $query->where('doctor_id', Auth::id());
            })->count(),
            'today_appointments' => Appointment::where('doctor_id', Auth::id())
                ->today()
                ->count(),
            'pending_appointments' => Appointment::where('doctor_id', Auth::id())
                ->where('status', 'scheduled')
                ->where('appointment_date', '>=', today())
                ->count(),
            'completed_today' => Appointment::where('doctor_id', Auth::id())
                ->today()
                ->where('status', 'completed')
                ->count(),
        ];

        // Get next appointment for this doctor
        $nextAppointment = null;
        if ($currentAppointment) {
            $nextAppointment = Appointment::with('patient')
                ->where('doctor_id', Auth::id())
                ->today()
                ->where('status', 'scheduled')
                ->whereTime('appointment_time', '>', $currentAppointment->appointment_time)
                ->orderBy('appointment_time')
                ->first();
        }

        return view('dashboard.doctor', compact('todayAppointments', 'currentAppointment', 'nextAppointment', 'upcomingAppointments', 'recentRecords', 'stats'));
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
            $todayAppointmentsQuery->whereHas('patient', fn ($query) => $query->search($searchTerm));
        }

        if (request('doctor_filter')) {
            $todayAppointmentsQuery->where('doctor_id', request('doctor_filter'));
        }

        if (request('status_filter')) {
            $todayAppointmentsQuery->where('status', request('status_filter'));
        }

        $todayAppointments = $todayAppointmentsQuery
            ->orderBy('appointment_time')
            ->paginate(6, ['*'], 'today_page')->withQueryString();

        $currentByDoctor = Appointment::with(['patient', 'doctor'])
            ->today()
            ->where('status', 'in_progress')
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
            $recentPatientsQuery->search(request('patient_name_search'));
        }

        // Age X means born at most X years (and at most X+1 years minus a day) ago.
        if (is_numeric(request('age_from'))) {
            $recentPatientsQuery->whereDate('birth_date', '<=', now()->subYears((int) request('age_from'))->toDateString());
        }

        if (is_numeric(request('age_to'))) {
            $recentPatientsQuery->whereDate('birth_date', '>', now()->subYears((int) request('age_to') + 1)->toDateString());
        }

        if (request('reg_date_from')) {
            $recentPatientsQuery->whereDate('created_at', '>=', request('reg_date_from'));
        }

        if (request('reg_date_to')) {
            $recentPatientsQuery->whereDate('created_at', '<=', request('reg_date_to'));
        }

        $recentPatients = $recentPatientsQuery
            ->latest()
            ->paginate(5, ['*'], 'recent_patients_page')->withQueryString();

        $stats = [
            'total_patients' => Patient::count(),
            'today_appointments' => Appointment::today()->count(),
            'scheduled_appointments' => Appointment::where('status', 'scheduled')
                ->where('appointment_date', '>=', today())
                ->count(),
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

    /**
     * Initialize first patient of the day (only call this once per doctor per day)
     */
    public function initializeFirstPatient(Request $request)
    {
        $request->validate([
            'doctor_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('users', 'id')->where('role', 'doctor')],
        ]);

        $doctorId = $request->doctor_id ?: Auth::id();

        // Only receptionists can initialize for other doctors
        if ($request->doctor_id && ! Auth::user()->isReceptionist()) {
            abort(403);
        }

        $result = \DB::transaction(function () use ($doctorId) {
            // Serialize with other staff starting/completing consultations
            Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', today())
                ->lockForUpdate()
                ->count();

            // Check if there's already a current patient
            $existing = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', today())
                ->where('status', 'in_progress')
                ->exists();

            if ($existing) {
                return ['success' => false, 'message' => __('A patient is already set as current.')];
            }

            // Call the next patient (arrived patients first)
            $next = app(\App\Services\AppointmentSchedulingService::class)->promoteNextPatient($doctorId);

            if (! $next) {
                return ['success' => false, 'message' => __('No scheduled appointments found for today.')];
            }

            return ['success' => true, 'message' => __('Next patient (:name) is now ready.', ['name' => $next->patient->full_name])];
        });

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
        }
    }
}
