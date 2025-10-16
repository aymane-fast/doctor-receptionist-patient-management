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
        $completedAppointments = Appointment::where('status', 'completed')->get();
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('No patients or doctors found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $medicalRecords = [
            // Medical records for completed appointments
            [
                'patient_id' => $patients->first()->id,
                'doctor_id' => $doctors->first()->id,
                'appointment_id' => $completedAppointments->first()->id ?? null,
                'visit_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'symptoms' => 'Patient complains of persistent headaches for the past week, accompanied by mild nausea and sensitivity to light.',
                'diagnosis' => 'Tension headache, possibly stress-related. No signs of neurological complications.',
                'treatment' => 'Prescribed acetaminophen 500mg twice daily. Recommended stress management techniques and regular sleep schedule.',
                'notes' => 'Patient appears stressed due to work pressure. Advised to follow up if symptoms persist beyond one week.',
                'weight' => 68.5,
                'height' => 165,
                'blood_pressure' => '125/82',
                'temperature' => 36.8,
                'heart_rate' => 78,
            ],
            [
                'patient_id' => $patients->skip(1)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'appointment_id' => $completedAppointments->skip(1)->first()->id ?? null,
                'visit_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'symptoms' => 'Hypertension follow-up visit. Patient reports taking medication as prescribed. No side effects noted.',
                'diagnosis' => 'Hypertension - well controlled with current medication regimen.',
                'treatment' => 'Continue current medication: Lisinopril 10mg daily. Maintain low-sodium diet and regular exercise.',
                'notes' => 'Blood pressure readings show improvement. Patient compliant with treatment plan. Next follow-up in 3 months.',
                'weight' => 82.3,
                'height' => 178,
                'blood_pressure' => '132/85',
                'temperature' => 36.6,
                'heart_rate' => 72,
            ],
            [
                'patient_id' => $patients->skip(2)->first()->id,
                'doctor_id' => $doctors->skip(1)->first()->id ?? $doctors->first()->id,
                'appointment_id' => $completedAppointments->skip(2)->first()->id ?? null,
                'visit_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'symptoms' => 'Acute onset of fever, body aches, runny nose, and sore throat. Symptoms started 2 days ago.',
                'diagnosis' => 'Viral upper respiratory infection (common cold).',
                'treatment' => 'Symptomatic treatment with rest, fluids, and OTC pain relievers. Throat lozenges for sore throat.',
                'notes' => 'Symptoms consistent with viral infection. No antibiotic needed. Patient advised to return if symptoms worsen or persist beyond 7 days.',
                'weight' => 59.2,
                'height' => 162,
                'blood_pressure' => '118/75',
                'temperature' => 38.2,
                'heart_rate' => 85,
            ],
            // Additional medical records without appointments
            [
                'patient_id' => $patients->skip(3)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'appointment_id' => null,
                'visit_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'symptoms' => 'Annual health screening. Patient reports feeling generally well with no specific complaints.',
                'diagnosis' => 'Overall good health. Mild vitamin D deficiency noted in lab results.',
                'treatment' => 'Recommended vitamin D3 supplement 1000 IU daily. Continue healthy lifestyle habits.',
                'notes' => 'All vital signs within normal limits. Lab work shows slight vitamin D deficiency but otherwise normal. Encouraged to maintain current exercise routine.',
                'weight' => 75.8,
                'height' => 172,
                'blood_pressure' => '122/78',
                'temperature' => 36.7,
                'heart_rate' => 68,
            ],
            [
                'patient_id' => $patients->skip(4)->first()->id,
                'doctor_id' => $doctors->first()->id,
                'appointment_id' => null,
                'visit_date' => Carbon::now()->subDays(21)->format('Y-m-d'),
                'symptoms' => 'Diabetes management visit. Patient monitoring blood glucose levels regularly. Reports occasional episodes of hypoglycemia.',
                'diagnosis' => 'Type 2 Diabetes Mellitus - requires medication adjustment due to hypoglycemic episodes.',
                'treatment' => 'Reduced metformin dose to 500mg twice daily. Continue dietary modifications and blood glucose monitoring.',
                'notes' => 'HbA1c improved from previous visit. Discussed signs and management of hypoglycemia. Patient well-educated about condition.',
                'weight' => 88.1,
                'height' => 175,
                'blood_pressure' => '138/88',
                'temperature' => 36.5,
                'heart_rate' => 74,
            ],
            [
                'patient_id' => $patients->first()->id,
                'doctor_id' => $doctors->skip(1)->first()->id ?? $doctors->first()->id,
                'appointment_id' => null,
                'visit_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'symptoms' => 'Chronic asthma management. Patient reports increased shortness of breath and wheezing over past month.',
                'diagnosis' => 'Asthma exacerbation, likely triggered by seasonal allergens.',
                'treatment' => 'Increased inhaled corticosteroid dose. Added antihistamine for allergy control. Peak flow monitoring recommended.',
                'notes' => 'Lung function tests show mild obstruction. Patient educated on proper inhaler technique. Environmental trigger avoidance discussed.',
                'weight' => 68.5,
                'height' => 165,
                'blood_pressure' => '120/80',
                'temperature' => 36.9,
                'heart_rate' => 82,
            ],
        ];

        foreach ($medicalRecords as $recordData) {
            MedicalRecord::create($recordData);
        }

        $this->command->info('Medical records seeded successfully with detailed clinical information!');
    }
}