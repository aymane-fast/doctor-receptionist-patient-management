<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->get();
        
        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('No patients or doctors found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $appointments = [
            // Past appointments (last week)
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'appointment_time' => '09:00:00',
                'status' => 'completed',
                'reason' => 'Routine check-up',
                'notes' => 'Annual health examination',
                'is_current' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'appointment_time' => '10:30:00',
                'status' => 'completed',
                'reason' => 'Follow-up appointment',
                'notes' => 'Blood pressure monitoring',
                'is_current' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'appointment_time' => '14:00:00',
                'status' => 'completed',
                'reason' => 'Walk-in consultation',
                'notes' => 'Flu symptoms',
                'is_current' => false,
            ],

            // Today's appointments
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::today()->format('Y-m-d'),
                'appointment_time' => '09:00:00',
                'status' => 'scheduled',
                'reason' => 'General consultation',
                'notes' => 'Patient reports headaches',
                'is_current' => true,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::today()->format('Y-m-d'),
                'appointment_time' => '11:00:00',
                'status' => 'in_progress',
                'reason' => 'Emergency consultation',
                'notes' => 'Chest pain evaluation',
                'is_current' => true,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::today()->format('Y-m-d'),
                'appointment_time' => '15:30:00',
                'status' => 'scheduled',
                'reason' => 'Routine checkup',
                'notes' => 'Diabetes management',
                'is_current' => true,
            ],

            // Tomorrow's appointments
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
                'appointment_time' => '08:30:00',
                'status' => 'scheduled',
                'reason' => 'Follow-up appointment',
                'notes' => 'Post-surgery check',
                'is_current' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
                'appointment_time' => '13:00:00',
                'status' => 'scheduled',
                'reason' => 'General consultation',
                'notes' => 'Skin condition examination',
                'is_current' => false,
            ],

            // Next week appointments
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'appointment_time' => '10:00:00',
                'status' => 'scheduled',
                'reason' => 'Vaccination',
                'notes' => 'Annual flu shot',
                'is_current' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'appointment_time' => '14:30:00',
                'status' => 'scheduled',
                'reason' => 'Lab results review',
                'notes' => 'Blood work discussion',
                'is_current' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'appointment_time' => '09:30:00',
                'status' => 'scheduled',
                'reason' => 'Routine check-up',
                'notes' => 'Monthly health monitoring',
                'is_current' => false,
            ],
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'appointment_time' => '16:00:00',
                'status' => 'scheduled',
                'reason' => 'Medication review',
                'notes' => 'Prescription adjustments',
                'is_current' => false,
            ],

            // Cancelled appointment
            [
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'appointment_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'appointment_time' => '11:30:00',
                'status' => 'cancelled',
                'reason' => 'General consultation',
                'notes' => 'Patient cancelled due to scheduling conflict',
                'is_current' => false,
            ],
        ];

        foreach ($appointments as $appointmentData) {
            Appointment::create($appointmentData);
        }

        $this->command->info('Appointments seeded successfully with various dates and statuses!');
    }
}