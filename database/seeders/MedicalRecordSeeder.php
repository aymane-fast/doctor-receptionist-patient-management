<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $doctor = User::where('role', 'doctor')->first();

        if ($patients->isEmpty() || !$doctor) {
            $this->command->warn('No patients or doctors found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $symptoms = [
            'Headache and fatigue', 'Chest pain', 'Shortness of breath', 'Back pain', 'Joint pain',
            'Fever and chills', 'Nausea and vomiting', 'Dizziness', 'Muscle weakness', 'Skin rash',
            'Abdominal pain', 'Cough and congestion', 'Vision problems', 'Hearing loss', 'Sleep disturbances',
            'Anxiety and stress', 'Depression symptoms', 'Memory issues', 'Weight loss', 'Weight gain'
        ];

        $diagnoses = [
            'Hypertension', 'Diabetes Type 2', 'Asthma', 'Arthritis', 'COPD', 'Heart disease',
            'Migraine', 'Depression', 'Anxiety disorder', 'Sleep apnea', 'Allergic rhinitis',
            'Gastroesophageal reflux', 'Osteoporosis', 'Thyroid disorder', 'Chronic pain syndrome',
            'Viral infection', 'Bacterial infection', 'Muscle strain', 'Skin condition', 'Vitamin deficiency'
        ];

        $treatments = [
            'Medication therapy', 'Physical therapy', 'Lifestyle modifications', 'Surgery recommended',
            'Follow-up in 2 weeks', 'Blood work ordered', 'Imaging studies required', 'Specialist referral',
            'Home monitoring', 'Diet and exercise plan', 'Stress management techniques', 'Pain management',
            'Breathing exercises', 'Wound care', 'Vaccination schedule', 'Regular check-ups'
        ];

        // Generate 200+ medical records over the past 6 months
        $recordCount = 0;
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        for ($i = 0; $i < 220; $i++) {
            $visitDate = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );
            
            $selectedPatient = $patients->random();
            
            // Generate realistic vital signs
            $weight = rand(450, 1200) / 10; // 45-120 kg
            $height = rand(150, 200); // 150-200 cm
            $systolic = rand(110, 160);
            $diastolic = rand(70, 100);
            $temperature = rand(360, 390) / 10; // 36.0-39.0°C
            $heartRate = rand(60, 100);

            MedicalRecord::create([
                'patient_id' => $selectedPatient->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => null,
                'visit_date' => $visitDate->format('Y-m-d'),
                'symptoms' => $symptoms[array_rand($symptoms)],
                'diagnosis' => $diagnoses[array_rand($diagnoses)],
                'treatment' => $treatments[array_rand($treatments)],
                'notes' => 'Patient monitoring and follow-up care provided. Treatment plan adjusted as needed.',
                'weight' => $weight,
                'height' => $height,
                'blood_pressure' => "$systolic/$diastolic",
                'temperature' => $temperature,
                'heart_rate' => $heartRate,
            ]);

            $recordCount++;
        }

        $this->command->info("$recordCount medical records seeded successfully spanning 6 months!");
    }
}