<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Order;
use App\Models\User;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ClinicDataExportService
{
    /**
     * Export all clinic data to a comprehensive CSV file
     */
    public function exportAllData()
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "clinic_complete_export_{$timestamp}.csv";
        $tempDir = storage_path('app/temp');
        
        // Create temp directory if it doesn't exist
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $filePath = $tempDir . '/' . $filename;
        $file = fopen($filePath, 'w');
        
        // Add BOM for UTF-8
        fwrite($file, "\xEF\xBB\xBF");
        
        // Write all data sections to single file
        $this->writeOverviewSection($file);
        $this->writePatientsSection($file);
        $this->writeAppointmentsSection($file);
        $this->writeMedicalRecordsSection($file);
        $this->writePrescriptionsSection($file);
        $this->writeOrdersSection($file);
        $this->writeStatisticsSection($file);
        
        fclose($file);
        
        return $filePath;
    }
    
    /**
     * Write overview section to file
     */
    private function writeOverviewSection($file)
    {
        $clinicName = Setting::get('clinic_name', 'Medical Clinic');
        
        $data = [
            ['=== CLINIC OVERVIEW ===', ''],
            ['Clinic Name', $clinicName],
            ['Export Date', Carbon::now()->format('F j, Y g:i A')],
            ['', ''],
            ['=== DATA SUMMARY ===', ''],
            ['Total Patients', Patient::count()],
            ['Total Appointments', Appointment::count()],
            ['Completed Appointments', Appointment::where('status', 'completed')->count()],
            ['Pending Appointments', Appointment::where('status', 'scheduled')->count()],
            ['Medical Records', MedicalRecord::count()],
            ['Prescriptions', Prescription::count()],
            ['Lab Orders', Order::count()],
            ['Active Doctors', User::where('role', 'doctor')->count()],
            ['', ''],
            ['=== DATE RANGES ===', ''],
            ['First Patient Registration', optional(Patient::oldest()->first())->created_at?->format('M j, Y') ?? 'N/A'],
            ['First Appointment', optional(optional(Appointment::oldest()->first())->appointment_date)?->format('M j, Y') ?? 'N/A'],
            ['Latest Appointment', optional(optional(Appointment::latest('appointment_date')->first())->appointment_date)?->format('M j, Y') ?? 'N/A'],
            ['', ''],
            ['=== CLINIC INFORMATION ===', ''],
            ['Address', Setting::get('clinic_address', 'N/A')],
            ['Phone', Setting::get('clinic_phone', 'N/A')],
            ['Email', Setting::get('clinic_email', 'N/A')],
            ['Website', Setting::get('clinic_website', 'N/A')],
            ['', ''],
            ['', ''],
        ];
        
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
    }
    
    /**
     * Write patients section to file
     */
    private function writePatientsSection($file)
    {
        // Section header
        fputcsv($file, ['=== PATIENTS ===']);
        
        // Headers
        $headers = [
            'Patient ID', 'First Name', 'Last Name', 'Birth Date', 'Age', 'Gender',
            'Phone', 'Email', 'Address', 'ID Card Number', 'Allergies',
            'Chronic Conditions', 'Emergency Contact', 'Emergency Phone',
            'Total Appointments', 'Last Appointment', 'Registration Date'
        ];
        
        fputcsv($file, $headers);
        
        // Data
        $patients = Patient::with(['appointments' => function($query) {
            $query->orderBy('appointment_date', 'desc');
        }])->get();
        
        foreach ($patients as $patient) {
            $lastAppointment = $patient->appointments->first();
            
            $row = [
                $patient->patient_id,
                $patient->first_name,
                $patient->last_name,
                optional($patient->birth_date)->format('Y-m-d'),
                $patient->age,
                ucfirst($patient->gender),
                $patient->phone,
                $patient->email,
                $patient->address,
                $patient->id_card_number,
                $patient->allergies,
                $patient->chronic_conditions,
                $patient->emergency_contact_name,
                $patient->emergency_contact_phone,
                $patient->appointments->count(),
                optional($lastAppointment)->appointment_date?->format('Y-m-d'),
                $patient->created_at->format('Y-m-d')
            ];
            
            fputcsv($file, $row);
        }
        
        // Add spacing
        fputcsv($file, ['']);
        fputcsv($file, ['']);
    }
    
    /**
     * Write appointments section to file
     */
    private function writeAppointmentsSection($file)
    {
        // Section header
        fputcsv($file, ['=== APPOINTMENTS ===']);
        
        // Headers
        $headers = [
            'Appointment ID', 'Patient ID', 'Patient Name', 'Doctor Name',
            'Appointment Date', 'Appointment Time', 'Status', 'Reason',
            'Notes', 'Is Current', 'Created Date'
        ];
        
        fputcsv($file, $headers);
        
        // Data
        $appointments = Appointment::with(['patient', 'doctor'])
                                  ->orderBy('appointment_date', 'desc')
                                  ->get();
        
        foreach ($appointments as $appointment) {
            $row = [
                'APT-' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT),
                $appointment->patient->patient_id,
                $appointment->patient->full_name,
                $appointment->doctor->name ?? 'N/A',
                optional($appointment->appointment_date)->format('Y-m-d'),
                optional($appointment->appointment_time)->format('H:i'),
                ucfirst($appointment->status),
                $appointment->reason,
                $appointment->notes,
                $appointment->is_current ? 'Yes' : 'No',
                $appointment->created_at->format('Y-m-d H:i')
            ];
            
            fputcsv($file, $row);
        }
        
        // Add spacing
        fputcsv($file, ['']);
        fputcsv($file, ['']);
    }
    
    /**
     * Write medical records section to file
     */
    private function writeMedicalRecordsSection($file)
    {
        // Section header
        fputcsv($file, ['=== MEDICAL RECORDS ===']);
        
        // Headers
        $headers = [
            'Record ID', 'Patient ID', 'Patient Name', 'Doctor Name', 'Visit Date',
            'Symptoms', 'Diagnosis', 'Treatment', 'Weight (kg)', 'Height (cm)',
            'Blood Pressure', 'Temperature (°C)', 'Heart Rate (bpm)', 'Notes'
        ];
        
        fputcsv($file, $headers);
        
        // Data
        $records = MedicalRecord::with(['patient', 'doctor'])
                               ->orderBy('visit_date', 'desc')
                               ->get();
        
        foreach ($records as $record) {
            $row = [
                'MR-' . str_pad($record->id, 6, '0', STR_PAD_LEFT),
                $record->patient->patient_id,
                $record->patient->full_name,
                $record->doctor->name ?? 'N/A',
                optional($record->visit_date)->format('Y-m-d'),
                $record->symptoms,
                $record->diagnosis,
                $record->treatment,
                $record->weight,
                $record->height,
                $record->blood_pressure,
                $record->temperature,
                $record->heart_rate,
                $record->notes
            ];
            
            fputcsv($file, $row);
        }
        
        // Add spacing
        fputcsv($file, ['']);
        fputcsv($file, ['']);
    }
    
    /**
     * Write prescriptions section to file
     */
    private function writePrescriptionsSection($file)
    {
        // Section header
        fputcsv($file, ['=== PRESCRIPTIONS ===']);
        
        // Headers
        $headers = [
            'Prescription ID', 'Patient ID', 'Patient Name', 'Doctor Name',
            'Prescribed Date', 'Medication Name', 'Dosage', 'Frequency',
            'Duration (days)', 'Instructions', 'Notes'
        ];
        
        fputcsv($file, $headers);
        
        // Data
        $prescriptions = Prescription::with(['patient', 'doctor', 'items'])
                                   ->orderBy('prescribed_date', 'desc')
                                   ->get();
        
        foreach ($prescriptions as $prescription) {
            // If prescription has items, list each item separately
            if ($prescription->items->count() > 0) {
                foreach ($prescription->items as $item) {
                    $row = [
                        'RX-' . str_pad($prescription->id, 6, '0', STR_PAD_LEFT),
                        $prescription->patient->patient_id,
                        $prescription->patient->full_name,
                        $prescription->doctor->name ?? 'N/A',
                        optional($prescription->prescribed_date)->format('Y-m-d'),
                        $item->medication_name,
                        $item->dosage,
                        $item->frequency,
                        $item->duration_days,
                        $item->instructions,
                        $prescription->notes
                    ];
                    fputcsv($file, $row);
                }
            } else {
                // Legacy single prescription format
                $row = [
                    'RX-' . str_pad($prescription->id, 6, '0', STR_PAD_LEFT),
                    $prescription->patient->patient_id,
                    $prescription->patient->full_name,
                    $prescription->doctor->name ?? 'N/A',
                    optional($prescription->prescribed_date)->format('Y-m-d'),
                    $prescription->medication_name,
                    $prescription->dosage,
                    $prescription->frequency,
                    $prescription->duration_days,
                    $prescription->instructions,
                    $prescription->notes
                ];
                fputcsv($file, $row);
            }
        }
        
        // Add spacing
        fputcsv($file, ['']);
        fputcsv($file, ['']);
    }
    
    /**
     * Write orders section to file
     */
    private function writeOrdersSection($file)
    {
        // Section header
        fputcsv($file, ['=== LAB ORDERS ===']);
        
        // Headers
        $headers = [
            'Order ID', 'Patient ID', 'Patient Name', 'Doctor Name',
            'Order Type', 'Test Name', 'Requested Date', 'Status', 'Notes'
        ];
        
        fputcsv($file, $headers);
        
        // Data
        $orders = Order::with(['patient', 'doctor'])
                      ->orderBy('requested_date', 'desc')
                      ->get();
        
        foreach ($orders as $order) {
            $row = [
                'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                $order->patient->patient_id,
                $order->patient->full_name,
                $order->doctor->name ?? 'N/A',
                ucfirst($order->order_type),
                $order->test_name,
                optional($order->requested_date)->format('Y-m-d'),
                ucfirst($order->status),
                $order->notes
            ];
            
            fputcsv($file, $row);
        }
        
        // Add spacing
        fputcsv($file, ['']);
        fputcsv($file, ['']);
    }
    
    /**
     * Write statistics section to file
     */
    private function writeStatisticsSection($file)
    {
        // Section header
        fputcsv($file, ['=== STATISTICS ===']);
        
        // Monthly appointment statistics
        fputcsv($file, ['=== MONTHLY APPOINTMENT STATISTICS ===']);
        fputcsv($file, ['Month', 'Total', 'Completed', 'Cancelled']);
        
        $monthlyStats = Appointment::selectRaw('
            YEAR(appointment_date) as year,
            MONTH(appointment_date) as month,
            COUNT(*) as total_appointments,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled
        ')
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();
        
        foreach ($monthlyStats as $stat) {
            $monthName = Carbon::create($stat->year, $stat->month, 1)->format('M Y');
            fputcsv($file, [$monthName, $stat->total_appointments, $stat->completed, $stat->cancelled]);
        }
        
        fputcsv($file, ['']);
        
        // Patient demographics
        fputcsv($file, ['=== PATIENT DEMOGRAPHICS ===']);
        fputcsv($file, ['Gender Distribution']);
        
        $genderStats = Patient::selectRaw('gender, COUNT(*) as count')
                             ->groupBy('gender')
                             ->get();
        
        foreach ($genderStats as $stat) {
            fputcsv($file, [ucfirst($stat->gender), $stat->count]);
        }
        
        fputcsv($file, ['']);
        fputcsv($file, ['Age Group Distribution']);
        
        // Age groups
        $ageGroups = [
            '0-17' => Patient::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 0 AND 17')->count(),
            '18-35' => Patient::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 35')->count(),
            '36-55' => Patient::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 36 AND 55')->count(),
            '56-75' => Patient::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 56 AND 75')->count(),
            '75+' => Patient::whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) > 75')->count(),
        ];
        
        foreach ($ageGroups as $ageGroup => $count) {
            fputcsv($file, [$ageGroup . ' years', $count]);
        }
    }
}