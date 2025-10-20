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
        $doctor = User::where('role', 'doctor')->first();

        if ($patients->isEmpty() || !$doctor) {
            $this->command->warn('No patients or doctor found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        // Common medications with realistic data
        $medications = [
            ['name' => 'Acetaminophen', 'dosage' => '500mg', 'frequency' => 'Twice daily', 'duration' => 14, 'instructions' => 'For headache relief. Take with food to avoid stomach upset. Do not exceed 4g per day.'],
            ['name' => 'Ibuprofen', 'dosage' => '400mg', 'frequency' => 'Three times daily', 'duration' => 7, 'instructions' => 'Take with food for pain and fever relief.'],
            ['name' => 'Lisinopril', 'dosage' => '10mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Hypertension management. Take in the morning. Monitor blood pressure regularly.'],
            ['name' => 'Metformin', 'dosage' => '500mg', 'frequency' => 'Twice daily', 'duration' => 90, 'instructions' => 'Diabetes management. Take with breakfast and dinner. Monitor blood glucose.'],
            ['name' => 'Omeprazole', 'dosage' => '20mg', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'For acid reflux. Take 30 minutes before breakfast. Swallow whole, do not crush.'],
            ['name' => 'Atorvastatin', 'dosage' => '10mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Cholesterol management. Take in the evening. Monitor liver function periodically.'],
            ['name' => 'Amoxicillin', 'dosage' => '500mg', 'frequency' => 'Three times daily', 'duration' => 10, 'instructions' => 'Antibiotic treatment. Complete full course even if feeling better.'],
            ['name' => 'Fluticasone Inhaler', 'dosage' => '125mcg', 'frequency' => 'Twice daily', 'duration' => 30, 'instructions' => 'Asthma management. Rinse mouth after use. Use spacer device with inhaler.'],
            ['name' => 'Naproxen', 'dosage' => '220mg', 'frequency' => 'Twice daily', 'duration' => 5, 'instructions' => 'Pain management. Take with food to prevent stomach upset.'],
            ['name' => 'Vitamin D3', 'dosage' => '1000 IU', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Vitamin D supplementation for deficiency. Take with largest meal for better absorption.'],
            ['name' => 'Levothyroxine', 'dosage' => '50mcg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Thyroid hormone replacement. Take on empty stomach, 30 minutes before breakfast.'],
            ['name' => 'Amlodipine', 'dosage' => '5mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Blood pressure management. Take at the same time each day. Watch for ankle swelling.'],
            ['name' => 'Sertraline', 'dosage' => '50mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Depression management. Take with food. May take 4-6 weeks for full effect.'],
            ['name' => 'Loratadine', 'dosage' => '10mg', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Allergy symptoms. Non-drowsy antihistamine. Can be taken with or without food.'],
            ['name' => 'Montelukast', 'dosage' => '10mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Asthma prevention. Take in the evening. Continue even when feeling well.'],
            ['name' => 'Cephalexin', 'dosage' => '500mg', 'frequency' => 'Four times daily', 'duration' => 7, 'instructions' => 'Antibiotic for skin infections. Take every 6 hours. Complete full course.'],
            ['name' => 'Prednisone', 'dosage' => '20mg', 'frequency' => 'Once daily', 'duration' => 5, 'instructions' => 'Anti-inflammatory. Take with food. Do not stop abruptly. Short-term use only.'],
            ['name' => 'Gabapentin', 'dosage' => '300mg', 'frequency' => 'Three times daily', 'duration' => 30, 'instructions' => 'Nerve pain management. Start with lower dose and gradually increase. May cause drowsiness.'],
            ['name' => 'Pantoprazole', 'dosage' => '40mg', 'frequency' => 'Once daily', 'duration' => 30, 'instructions' => 'Acid reflux management. Take before first meal of the day.'],
            ['name' => 'Warfarin', 'dosage' => '5mg', 'frequency' => 'Once daily', 'duration' => 90, 'instructions' => 'Blood thinner. Take at same time daily. Regular INR monitoring required.'],
        ];

        $prescriptionsCreated = 0;

        // Create 80-120 prescriptions spanning the last 6 months
        for ($i = 0; $i < 100; $i++) {
            $patient = $patients->random();
            $medication = $medications[array_rand($medications)];
            
            // Some prescriptions linked to medical records, some standalone
            $medicalRecord = null;
            if (rand(1, 100) <= 60) { // 60% chance of being linked to a medical record
                $patientRecords = $medicalRecords->where('patient_id', $patient->id);
                if ($patientRecords->isNotEmpty()) {
                    $medicalRecord = $patientRecords->random();
                }
            }

            // Random date within last 6 months
            $daysBack = rand(1, 180);
            $prescribedDate = Carbon::now()->subDays($daysBack);

            // Adjust dosage and duration slightly for variety
            $dosageVariations = ['250mg', '500mg', '750mg', '1000mg', '5mg', '10mg', '20mg', '40mg'];
            $frequencyVariations = ['Once daily', 'Twice daily', 'Three times daily', 'Four times daily', 'As needed'];
            $durationVariations = [5, 7, 10, 14, 21, 30, 60, 90];

            $prescription = [
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'medical_record_id' => $medicalRecord ? $medicalRecord->id : null,
                'prescribed_date' => $prescribedDate->format('Y-m-d'),
                'medication_name' => $medication['name'],
                'dosage' => rand(1, 100) <= 70 ? $medication['dosage'] : $dosageVariations[array_rand($dosageVariations)],
                'frequency' => rand(1, 100) <= 80 ? $medication['frequency'] : $frequencyVariations[array_rand($frequencyVariations)],
                'duration_days' => rand(1, 100) <= 70 ? $medication['duration'] : $durationVariations[array_rand($durationVariations)],
                'instructions' => $medication['instructions'],
            ];

            Prescription::create($prescription);
            $prescriptionsCreated++;
        }

        $this->command->info("Successfully created {$prescriptionsCreated} prescriptions spanning the last 6 months!");
    }
}