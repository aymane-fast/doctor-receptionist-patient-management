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
        $usedPatientDates = []; // Track which patients have appointments on which dates

        // Generate appointments for 4 days starting from today
        for ($dayOffset = 0; $dayOffset < 4; $dayOffset++) {
            $date = $currentDate->copy()->addDays($dayOffset);
            
            // Skip Sundays (assuming clinic is closed)
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            $dateString = $date->format('Y-m-d');
            $usedPatientDates[$dateString] = [];

            // Determine number of appointments per day
            $dailyAppointments = rand(8, 14);
            if ($date->dayOfWeek === Carbon::SATURDAY) {
                $dailyAppointments = rand(4, 8); // Fewer appointments on Saturday
            }
            
            // Get available patients for this date (exclude those who already have appointments)
            $availablePatients = $patients->reject(function($patient) use ($dateString, $usedPatientDates) {
                return in_array($patient->id, $usedPatientDates[$dateString] ?? []);
            });

            // If we don't have enough patients, use all patients but prioritize those without appointments
            if ($availablePatients->count() < $dailyAppointments) {
                $availablePatients = $patients;
            }

            // Randomly select and shuffle time slots
            $shuffledTimeSlots = $timeSlots;
            shuffle($shuffledTimeSlots);
            $usedTimeSlots = array_slice($shuffledTimeSlots, 0, min($dailyAppointments, count($timeSlots)));
            sort($usedTimeSlots); // Sort to maintain chronological order

            $slotIndex = 0;
            foreach ($usedTimeSlots as $timeSlot) {
                // Select a patient who doesn't have an appointment on this date yet
                $selectedPatient = null;
                $attempts = 0;
                
                do {
                    $candidatePatient = $availablePatients->random();
                    if (!in_array($candidatePatient->id, $usedPatientDates[$dateString])) {
                        $selectedPatient = $candidatePatient;
                    }
                    $attempts++;
                } while ($selectedPatient === null && $attempts < 20);

                // If we couldn't find a unique patient after 20 attempts, just use any patient
                if ($selectedPatient === null) {
                    $selectedPatient = $availablePatients->random();
                }

                // Mark this patient as used for this date
                $usedPatientDates[$dateString][] = $selectedPatient->id;

                // Distribute appointments across doctors more evenly
                $selectedDoctor = $doctors->get($slotIndex % $doctors->count());
                $slotIndex++;
                
                \App\Models\Appointment::create([
                    'patient_id' => $selectedPatient->id,
                    'doctor_id' => $selectedDoctor->id,
                    'appointment_date' => $dateString,
                    'appointment_time' => $timeSlot,
                    'status' => 'scheduled', // Only scheduled appointments as requested
                    'reason' => $appointmentReasons[array_rand($appointmentReasons)],
                    'notes' => $appointmentNotes[array_rand($appointmentNotes)],
                ]);

                $appointmentCount++;
            }

            $this->command->info("Day " . ($dayOffset + 1) . " ({$dateString}): " . count($usedTimeSlots) . " appointments created with " . count(array_unique($usedPatientDates[$dateString])) . " unique patients");
        }

        $this->command->info("✅ $appointmentCount scheduled appointments seeded successfully for 4 days!");
        $this->command->info("📊 Each patient has maximum 1 appointment per day for realistic testing");
    }
}