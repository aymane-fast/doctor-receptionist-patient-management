<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $statistics = $this->gatherStatistics();
        return view('statistics.index', compact('statistics'));
    }

    private function gatherStatistics()
    {
        // Basic counts
        $totalPatients = Patient::count();
        $totalAppointments = Appointment::count();
        $activeDoctors = User::where('role', 'doctor')->count();
        
        // New patients this month
        $newPatientsThisMonth = Patient::whereMonth('created_at', Carbon::now()->month)
                                     ->whereYear('created_at', Carbon::now()->year)
                                     ->count();
        
        // Appointments this week
        $appointmentsThisWeek = Appointment::whereBetween('appointment_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        
        // Success rate (completed appointments percentage)
        $completedAppointments = Appointment::where('status', 'completed')->count();
        $successRate = $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 1) : 0;
        
        // Status distribution
        $scheduledAppointments = Appointment::where('status', 'scheduled')->count();
        $inProgressAppointments = Appointment::where('status', 'in_progress')->count();
        $cancelledAppointments = Appointment::where('status', 'cancelled')->count();
        
        // Trend data (last 7 days)
        $trendData = [];
        $trendLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $trendLabels[] = $date->format('M j');
            $trendData[] = Appointment::whereDate('appointment_date', $date)->count();
        }
        
        // Peak hours analysis
        $hourlyDistribution = [];
        for ($hour = 8; $hour <= 17; $hour++) {
            $count = Appointment::whereRaw('HOUR(appointment_time) = ?', [$hour])->count();
            $hourlyDistribution[] = $count;
        }
        
        // Age groups - Fixed calculation
        $ageGroups = [
            ['range' => '0-18', 'count' => 0, 'percentage' => 0],
            ['range' => '19-30', 'count' => 0, 'percentage' => 0],
            ['range' => '31-50', 'count' => 0, 'percentage' => 0],
            ['range' => '51-70', 'count' => 0, 'percentage' => 0],
            ['range' => '70+', 'count' => 0, 'percentage' => 0],
        ];
        
        // Get all patients with valid birth dates
        $patients = Patient::whereNotNull('birth_date')->get();
        
        foreach ($patients as $patient) {
            try {
                $birthDate = Carbon::parse($patient->birth_date);
                $age = $birthDate->age;
                
                if ($age <= 18) {
                    $ageGroups[0]['count']++;
                } elseif ($age <= 30) {
                    $ageGroups[1]['count']++;
                } elseif ($age <= 50) {
                    $ageGroups[2]['count']++;
                } elseif ($age <= 70) {
                    $ageGroups[3]['count']++;
                } else {
                    $ageGroups[4]['count']++;
                }
            } catch (\Exception $e) {
                // Skip invalid dates
                continue;
            }
        }
        
        // Calculate percentages based on patients with valid birth dates
        $totalPatientsWithAge = $patients->count();
        foreach ($ageGroups as &$group) {
            $group['percentage'] = $totalPatientsWithAge > 0 ? round(($group['count'] / $totalPatientsWithAge) * 100, 1) : 0;
        }
        
        // Revenue calculations
        $consultationPrice = \App\Models\Setting::where('key', 'consultation_price')->first();
        $pricePerConsultation = $consultationPrice ? (float) $consultationPrice->value : 50.0; // Default 50 if not set
        
        // Monthly revenue for the last 12 months
        $monthlyRevenue = [];
        $monthlyLabels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->format('M Y');
            
            $completedAppointmentsCount = Appointment::where('status', 'completed')
                ->whereYear('appointment_date', $month->year)
                ->whereMonth('appointment_date', $month->month)
                ->count();
                
            $monthlyRevenue[] = $completedAppointmentsCount * $pricePerConsultation;
        }
        
        // Total revenue calculations
        $totalRevenue = array_sum($monthlyRevenue);
        $thisMonthRevenue = end($monthlyRevenue);
        $lastMonthRevenue = count($monthlyRevenue) > 1 ? $monthlyRevenue[count($monthlyRevenue) - 2] : 0;
        $revenueGrowth = $lastMonthRevenue > 0 ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;
        
        // Revenue breakdown by appointment type
        $revenueByType = [
            ['type' => 'General Consultation', 'amount' => $thisMonthRevenue * 0.6, 'count' => (int)($completedAppointments * 0.6)],
            ['type' => 'Follow-up Visit', 'amount' => $thisMonthRevenue * 0.25, 'count' => (int)($completedAppointments * 0.25)],
            ['type' => 'Emergency Visit', 'amount' => $thisMonthRevenue * 0.15, 'count' => (int)($completedAppointments * 0.15)],
        ];
        
        // Recent activities
        $recentActivities = [];
        
        // Recent appointments
        $recentAppointments = Appointment::with(['patient', 'doctor'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        foreach ($recentAppointments as $appointment) {
            $recentActivities[] = [
                'title' => 'New Appointment Scheduled',
                'description' => "Appointment for {$appointment->patient->first_name} {$appointment->patient->last_name} with Dr. {$appointment->doctor->name}",
                'time' => $appointment->created_at->diffForHumans(),
                'icon' => 'fas fa-calendar-plus',
                'color' => 'from-blue-500 to-blue-600'
            ];
        }
        
        // Recent patients
        $recentPatients = Patient::orderBy('created_at', 'desc')->take(3)->get();
        foreach ($recentPatients as $patient) {
            $recentActivities[] = [
                'title' => 'New Patient Registered',
                'description' => "{$patient->first_name} {$patient->last_name} joined the clinic",
                'time' => $patient->created_at->diffForHumans(),
                'icon' => 'fas fa-user-plus',
                'color' => 'from-green-500 to-green-600'
            ];
        }
        
        // Recent medical records
        $recentRecords = MedicalRecord::with(['patient', 'doctor'])
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
        
        foreach ($recentRecords as $record) {
            $recentActivities[] = [
                'title' => 'Medical Record Created',
                'description' => "New record for {$record->patient->first_name} {$record->patient->last_name}",
                'time' => $record->created_at->diffForHumans(),
                'icon' => 'fas fa-file-medical',
                'color' => 'from-purple-500 to-purple-600'
            ];
        }
        
        // Sort activities by time
        usort($recentActivities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        $recentActivities = array_slice($recentActivities, 0, 10);
        
        // Top patients (most visits)
        $topPatients = Patient::withCount('appointments')
            ->orderBy('appointments_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($patient) {
                return [
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'phone' => $patient->phone,
                    'visits' => $patient->appointments_count
                ];
            })
            ->toArray();
        
        return [
            'total_patients' => $totalPatients,
            'total_appointments' => $totalAppointments,
            'active_doctors' => $activeDoctors,
            'new_patients_this_month' => $newPatientsThisMonth,
            'appointments_this_week' => $appointmentsThisWeek,
            'success_rate' => $successRate,
            'completed_appointments' => $completedAppointments,
            'scheduled_appointments' => $scheduledAppointments,
            'in_progress_appointments' => $inProgressAppointments,
            'cancelled_appointments' => $cancelledAppointments,
            'trend_data' => $trendData,
            'trend_labels' => $trendLabels,
            'hourly_distribution' => $hourlyDistribution,
            'age_groups' => $ageGroups,
            'recent_activities' => $recentActivities,
            'top_patients' => $topPatients,
            // Revenue data
            'consultation_price' => $pricePerConsultation,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_revenue_labels' => $monthlyLabels,
            'total_revenue' => $totalRevenue,
            'this_month_revenue' => $thisMonthRevenue,
            'revenue_growth' => $revenueGrowth,
            'revenue_by_type' => $revenueByType
        ];
    }

    public function api()
    {
        return response()->json($this->gatherStatistics());
    }

    public function getTrendData(Request $request)
    {
        $timeframe = $request->get('timeframe', 'week');
        
        switch ($timeframe) {
            case 'week':
                return $this->getWeeklyTrend();
            case 'month':
                return $this->getMonthlyTrend();
            case 'year':
                return $this->getYearlyTrend();
            default:
                return $this->getWeeklyTrend();
        }
    }

    private function getWeeklyTrend()
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D j');  // Mon 1
            $data[] = Appointment::whereDate('appointment_date', $date)->count();
        }
        
        return response()->json(['labels' => $labels, 'data' => $data]);
    }

    private function getMonthlyTrend()
    {
        $data = [];
        $labels = [];
        $daysInMonth = Carbon::now()->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::now()->startOfMonth()->addDays($day - 1);
            $labels[] = $date->format('j');  // Day number
            $data[] = Appointment::whereDate('appointment_date', $date)->count();
        }
        
        return response()->json(['labels' => $labels, 'data' => $data]);
    }

    private function getYearlyTrend()
    {
        $data = [];
        $labels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');  // Jan 2025
            $monthCount = Appointment::whereYear('appointment_date', $month->year)
                                   ->whereMonth('appointment_date', $month->month)
                                   ->count();
            $data[] = $monthCount;
        }
        
        return response()->json(['labels' => $labels, 'data' => $data]);
    }
}