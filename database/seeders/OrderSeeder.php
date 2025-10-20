<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use App\Models\MedicalRecord;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $doctor = User::where('role', 'doctor')->first();
        $medicalRecords = MedicalRecord::all();

        if ($patients->isEmpty() || !$doctor) {
            $this->command->warn('No patients or doctor found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $orderTypes = ['lab', 'scan'];
        $orderStatuses = ['requested', 'completed', 'cancelled'];
        
        // Lab tests
        $labTests = [
            'Complete Blood Count (CBC)',
            'Basic Metabolic Panel',
            'Lipid Panel',
            'Thyroid Function Tests',
            'Liver Function Tests',
            'Hemoglobin A1C',
            'Vitamin D Level',
            'Iron Studies',
            'Urinalysis',
            'Prostate Specific Antigen (PSA)',
            'C-Reactive Protein',
            'Fasting Glucose',
            'Blood Type and Rh',
            'Coagulation Panel',
            'Comprehensive Metabolic Panel',
        ];

        // Imaging scans
        $scanTests = [
            'Chest X-Ray',
            'Abdominal Ultrasound',
            'CT Scan - Head',
            'CT Scan - Chest',
            'CT Scan - Abdomen',
            'MRI - Brain',
            'MRI - Spine',
            'Echocardiogram',
            'Mammography',
            'Bone Density Scan (DEXA)',
            'Pelvic Ultrasound',
            'Thyroid Ultrasound',
            'Cardiac Stress Test',
            'Colonoscopy',
            'Upper Endoscopy',
        ];

        $ordersCreated = 0;

        // Create 60-80 orders spanning the last 4 months
        for ($i = 0; $i < 70; $i++) {
            $patient = $patients->random();
            $orderType = $orderTypes[array_rand($orderTypes)];
            
            // Select appropriate test based on order type
            if ($orderType === 'lab') {
                $testName = $labTests[array_rand($labTests)];
            } else {
                $testName = $scanTests[array_rand($scanTests)];
            }
            
            // Random date within last 4 months
            $daysBack = rand(1, 120);
            $requestedDate = Carbon::now()->subDays($daysBack);
            
            // Status distribution: 70% completed, 25% requested, 5% cancelled
            $statusRand = rand(1, 100);
            if ($statusRand <= 70) {
                $status = 'completed';
            } elseif ($statusRand <= 95) {
                $status = 'requested';
            } else {
                $status = 'cancelled';
            }

            // Sometimes link to a medical record (40% chance)
            $medicalRecord = null;
            if (rand(1, 100) <= 40) {
                $patientRecords = $medicalRecords->where('patient_id', $patient->id);
                if ($patientRecords->isNotEmpty()) {
                    $medicalRecord = $patientRecords->random();
                }
            }

            $order = [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'medical_record_id' => $medicalRecord ? $medicalRecord->id : null,
                'order_type' => $orderType,
                'test_name' => $testName,
                'requested_date' => $requestedDate->format('Y-m-d'),
                'status' => $status,
                'notes' => $this->generateOrderNotes($status, $orderType, $testName),
                'created_at' => $requestedDate,
                'updated_at' => $status === 'completed' ? $requestedDate->addDays(rand(1, 7)) : $requestedDate->addDays(rand(0, 3)),
            ];

            Order::create($order);
            $ordersCreated++;
        }

        $this->command->info("Successfully created {$ordersCreated} lab and scan orders spanning the last 4 months!");
    }

    /**
     * Generate realistic order notes based on status and order type
     */
    private function generateOrderNotes($status, $orderType, $testName): ?string
    {
        $notes = [];

        switch ($status) {
            case 'requested':
                $notes[] = ucfirst($orderType) . ' order requested.';
                $notes[] = 'Patient to be contacted for scheduling.';
                if ($orderType === 'scan') {
                    $notes[] = 'Prior authorization may be required.';
                }
                break;
            
            case 'completed':
                $notes[] = ucfirst($orderType) . ' completed successfully.';
                $notes[] = 'Results available in patient record.';
                if ($orderType === 'lab') {
                    $notes[] = 'Lab values within normal limits.';
                } else {
                    $notes[] = 'Imaging study shows no acute findings.';
                }
                break;
            
            case 'cancelled':
                $cancelReasons = [
                    'Patient requested cancellation.',
                    'Insurance authorization denied.',
                    'Patient did not show for appointment.',
                    'Doctor changed order.',
                    'Test no longer clinically indicated.',
                ];
                $notes[] = 'Order cancelled: ' . $cancelReasons[array_rand($cancelReasons)];
                break;
        }

        return implode(' ', $notes);
    }
}