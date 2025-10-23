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
        $patients = \App\Models\Patient::all();
        $doctors = \App\Models\User::where('role', 'doctor')->get();
        
        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('No patients or doctors found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $appointmentReasons = [
            'Routine check-up', 'Annual physical exam', 'Follow-up appointment', 'Blood pressure monitoring',
            'Diabetes management', 'Vaccination', 'Lab results review', 'Medication review',
            'Chest pain evaluation', 'Headache consultation', 'Skin condition examination', 'Joint pain assessment',
            'Respiratory issues', 'Digestive problems', 'Mental health consultation', 'Injury assessment',
            'Preventive care', 'Vision problems', 'Hearing issues', 'Allergy consultation',
            'Cold and flu symptoms', 'Back pain evaluation', 'Weight management', 'Sleep disorder consultation'
        ];

        $appointmentNotes = [
            'Patient reports feeling well overall',
            'Needs blood work follow-up in 3 months',
            'Continue current medications',
            'Referred to specialist for further evaluation',
            'Patient education provided on lifestyle changes',
            'Prescription adjusted based on recent symptoms',
            'Vital signs within normal limits',
            'Patient shows improvement since last visit',
            'Discussed treatment options with patient',
            'Scheduled for additional testing next week'
        ];

        // Working hours time slots (9 AM to 5 PM with 30-minute intervals)
        $timeSlots = [
            '09:00:00', '09:30:00', '10:00:00', '10:30:00', '11:00:00', '11:30:00',
            '12:00:00', '12:30:00', '13:00:00', '13:30:00', '14:00:00', '14:30:00',
            '15:00:00', '15:30:00', '16:00:00', '16:30:00', '17:00:00'
        ];

        $currentDate = Carbon::now();
        $appointmentCount = 0;

        // Generate appointments for 4 days starting from today
        for ($dayOffset = 0; $dayOffset < 4; $dayOffset++) {
            $date = $currentDate->copy()->addDays($dayOffset);
            
            // Skip Sundays (assuming clinic is closed)
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Determine number of appointments per day
            $dailyAppointments = rand(6, 12);
            if ($date->dayOfWeek === Carbon::SATURDAY) {
                $dailyAppointments = rand(3, 6); // Fewer appointments on Saturday
            }

            // Randomly select and shuffle time slots
            $shuffledTimeSlots = $timeSlots;
            shuffle($shuffledTimeSlots);
            $usedTimeSlots = array_slice($shuffledTimeSlots, 0, min($dailyAppointments, count($timeSlots)));
            sort($usedTimeSlots); // Sort to maintain chronological order

            foreach ($usedTimeSlots as $timeSlot) {
                $selectedPatient = $patients->random();
                $selectedDoctor = $doctors->random();
                
                \App\Models\Appointment::create([
                    'patient_id' => $selectedPatient->id,
                    'doctor_id' => $selectedDoctor->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'appointment_time' => $timeSlot,
                    'status' => 'scheduled', // Only scheduled appointments as requested
                    'reason' => $appointmentReasons[array_rand($appointmentReasons)],
                    'notes' => $appointmentNotes[array_rand($appointmentNotes)],
                    'is_current' => false, // No current appointments in seeder
                ]);

                $appointmentCount++;
            }
        }

        $this->command->info("$appointmentCount scheduled appointments seeded successfully for 4 days starting from today!");
    }
}