<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\SpecialtyService;
use Carbon\Carbon;

class MedicalRecordSeeder extends Seeder
{
    public function __construct(private SpecialtyService $specialties)
    {
    }

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

        $seed = $this->specialties->profile()['seed'];
        $symptoms = $seed['symptoms'];
        $diagnoses = $seed['diagnoses'];
        $treatments = $seed['treatments'];

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
                'temperature_unit' => 'C',
                'heart_rate' => $heartRate,
                'specialty_data' => $this->sampleRecordData(),
            ]);

            $recordCount++;
        }

        $this->command->info("$recordCount medical records seeded successfully spanning 6 months!");
    }

    private function sampleRecordData(): array
    {
        return collect($this->specialties->fields('record'))
            ->mapWithKeys(function (array $field) {
                $value = match ($field['type'] ?? 'text') {
                    'select' => $field['options'][0] ?? null,
                    'date' => now()->subDays(rand(1, 90))->toDateString(),
                    default => 'Demo '.$field['label'].' recorded during consultation.',
                };

                return filled($value) ? [$field['key'] => $value] : [];
            })
            ->all();
    }
}
