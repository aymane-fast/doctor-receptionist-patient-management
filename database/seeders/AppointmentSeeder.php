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
        $doctor = \App\Models\User::where('role', 'doctor')->first();
        
        if ($patients->isEmpty() || !$doctor) {
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

        $timeSlots = [
            '08:00:00', '08:30:00', '09:00:00', '09:30:00', '10:00:00', '10:30:00',
            '11:00:00', '11:30:00', '13:00:00', '13:30:00', '14:00:00', '14:30:00',
            '15:00:00', '15:30:00', '16:00:00', '16:30:00', '17:00:00'
        ];

        $currentDate = Carbon::now();
        $startDate = $currentDate->copy()->subWeeks(2);
        $endDate = $currentDate->copy()->addWeeks(2);
        
        $appointmentCount = 0;
        $currentAppointmentSet = false;

        // Generate appointments for each day (skip Sundays)
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            $dailyAppointments = rand(4, 8);
            if ($date->dayOfWeek === Carbon::SATURDAY) {
                $dailyAppointments = rand(2, 4);
            }

            $shuffledTimeSlots = $timeSlots;
            shuffle($shuffledTimeSlots);
            $usedTimeSlots = array_slice($shuffledTimeSlots, 0, $dailyAppointments);
            sort($usedTimeSlots);

            foreach ($usedTimeSlots as $timeSlot) {
                $selectedPatient = $patients->random();
                
                if ($date->isPast()) {
                    $status = rand(1, 10) <= 9 ? 'completed' : 'cancelled';
                    $isCurrent = false;
                } elseif ($date->isToday()) {
                    $statusOptions = ['scheduled', 'in_progress', 'completed'];
                    $status = $statusOptions[array_rand($statusOptions)];
                    
                    if (!$currentAppointmentSet && $status === 'in_progress') {
                        $isCurrent = true;
                        $currentAppointmentSet = true;
                    } else {
                        $isCurrent = false;
                    }
                } else {
                    $status = rand(1, 10) <= 9 ? 'scheduled' : 'cancelled';
                    $isCurrent = false;
                }

                \App\Models\Appointment::create([
                    'patient_id' => $selectedPatient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $date->format('Y-m-d'),
                    'appointment_time' => $timeSlot,
                    'status' => $status,
                    'reason' => $appointmentReasons[array_rand($appointmentReasons)],
                    'notes' => $appointmentNotes[array_rand($appointmentNotes)],
                    'is_current' => $isCurrent,
                ]);

                $appointmentCount++;
            }
        }

        // Ensure at least one current appointment for today
        if (!$currentAppointmentSet) {
            $todayAppointment = \App\Models\Appointment::where('appointment_date', $currentDate->format('Y-m-d'))
                ->where('status', 'scheduled')
                ->first();
            
            if ($todayAppointment) {
                $todayAppointment->update([
                    'status' => 'in_progress',
                    'is_current' => true
                ]);
            }
        }

        $this->command->info("$appointmentCount appointments seeded successfully spanning 4 weeks!");
    }
}