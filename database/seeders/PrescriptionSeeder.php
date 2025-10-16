<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicalRecords = MedicalRecord::all();
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('No patients or doctors found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $prescriptions = [
            // Basic prescriptions using the original table structure
            [
                'patient_id' => $patients->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => $medicalRecords->first()->id ?? null,
                'prescribed_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'medication_name' => 'Acetaminophen',
                'dosage' => '500mg',
                'frequency' => 'Twice daily',
                'duration_days' => 14,
                'instructions' => 'For headache relief. Take with food to avoid stomach upset. Do not exceed 4g per day.',
            ],
            [
                'patient_id' => $patients->skip(1)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => $medicalRecords->skip(1)->first()->id ?? null,
                'prescribed_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'medication_name' => 'Lisinopril',
                'dosage' => '10mg',
                'frequency' => 'Once daily',
                'duration_days' => 90,
                'instructions' => 'Hypertension management. Take in the morning. Monitor blood pressure regularly. Watch for dry cough.',
            ],
            [
                'patient_id' => $patients->skip(2)->first()->id,
                'doctor_id' => $doctors->skip(1)->first()->id ?? $doctors->first()->id,
                'medical_record_id' => $medicalRecords->skip(2)->first()->id ?? null,
                'prescribed_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'medication_name' => 'Ibuprofen',
                'dosage' => '400mg',
                'frequency' => 'Three times daily',
                'duration_days' => 7,
                'instructions' => 'Symptomatic treatment for viral infection. Take with food for pain and fever relief.',
            ],
            [
                'patient_id' => $patients->skip(4)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => $medicalRecords->skip(4)->first()->id ?? null,
                'prescribed_date' => Carbon::now()->subDays(21)->format('Y-m-d'),
                'medication_name' => 'Metformin',
                'dosage' => '500mg',
                'frequency' => 'Twice daily',
                'duration_days' => 90,
                'instructions' => 'Diabetes management. Take with breakfast and dinner. Monitor blood glucose. Adjust dose based on glucose readings.',
            ],
            [
                'patient_id' => $patients->first()->id,
                'doctor_id' => $doctors->skip(1)->first()->id ?? $doctors->first()->id,
                'medical_record_id' => $medicalRecords->skip(5)->first()->id ?? null,
                'prescribed_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'medication_name' => 'Fluticasone Inhaler',
                'dosage' => '125mcg',
                'frequency' => 'Twice daily',
                'duration_days' => 30,
                'instructions' => 'Asthma management. Rinse mouth after use. Use spacer device with inhaler.',
            ],
            // Additional prescriptions
            [
                'patient_id' => $patients->skip(3)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => null,
                'prescribed_date' => Carbon::now()->subDays(45)->format('Y-m-d'),
                'medication_name' => 'Amoxicillin',
                'dosage' => '500mg',
                'frequency' => 'Three times daily',
                'duration_days' => 10,
                'instructions' => 'Antibiotic treatment. Complete full course even if feeling better. Take with or without food.',
            ],
            [
                'patient_id' => $patients->skip(2)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => null,
                'prescribed_date' => Carbon::now()->subDays(60)->format('Y-m-d'),
                'medication_name' => 'Naproxen',
                'dosage' => '220mg',
                'frequency' => 'Twice daily',
                'duration_days' => 5,
                'instructions' => 'Pain management after minor procedure. Take with food to prevent stomach upset.',
            ],
            [
                'patient_id' => $patients->skip(3)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => $medicalRecords->skip(3)->first()->id ?? null,
                'prescribed_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'medication_name' => 'Vitamin D3',
                'dosage' => '1000 IU',
                'frequency' => 'Once daily',
                'duration_days' => 90,
                'instructions' => 'Vitamin D supplementation for deficiency. Take with largest meal for better absorption.',
            ],
            [
                'patient_id' => $patients->skip(1)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'medical_record_id' => null,
                'prescribed_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'medication_name' => 'Omeprazole',
                'dosage' => '20mg',
                'frequency' => 'Once daily',
                'duration_days' => 30,
                'instructions' => 'For acid reflux. Take 30 minutes before breakfast. Swallow whole, do not crush.',
            ],
            [
                'patient_id' => $patients->skip(4)->first()->id,
                'doctor_id' => $doctors->skip(1)->first()->id ?? $doctors->first()->id,
                'medical_record_id' => null,
                'prescribed_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'medication_name' => 'Atorvastatin',
                'dosage' => '10mg',
                'frequency' => 'Once daily',
                'duration_days' => 90,
                'instructions' => 'Cholesterol management. Take in the evening. Monitor liver function periodically.',
            ],
        ];

        foreach ($prescriptions as $prescriptionData) {
            Prescription::create($prescriptionData);
        }

        $this->command->info('Prescriptions seeded successfully with detailed medication information!');
    }
}