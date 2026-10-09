<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use App\Services\SpecialtyService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function __construct(
        private AppointmentSchedulingService $scheduler,
        private SpecialtyService $specialties,
    )
    {
    }

    public function run(): void
    {
        $patients = Patient::all();
        $doctors = User::where('role', 'doctor')->orderBy('id')->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command?->warn('No patients or doctors found. Please run PatientSeeder and UserSeeder first.');
            return;
        }

        $appointmentReasons = $this->specialties->appointmentReasons();

        $appointmentNotes = [
            'Patient reports feeling well overall.',
            'Needs blood work follow-up in 3 months.',
            'Continue current medications and monitor symptoms.',
            'Discussed lifestyle adjustments and next follow-up.',
            'Prescription adjusted based on recent symptoms.',
        ];

        $patientIndex = 0;
        $appointmentCount = 0;
        $currentDate = Carbon::now()->startOfDay();

        for ($dayOffset = 0; $dayOffset < 4; $dayOffset++) {
            $date = $currentDate->copy()->addDays($dayOffset);
            $window = $this->scheduler->workingWindow($date);

            if (!$window) {
                continue;
            }

            $capacity = max(1, (int) floor($window['start']->diffInMinutes($window['end']) / $this->scheduler->duration()));

            foreach ($doctors as $doctor) {
                $dailyAppointments = min($capacity, max(3, min($capacity, rand(4, 8))));

                if ($doctor->usesQueueMode()) {
                    for ($i = 0; $i < $dailyAppointments; $i++) {
                        $patient = $patients[$patientIndex % $patients->count()];
                        $patientIndex++;

                        $this->scheduler->createQueueAppointment([
                            'patient_id' => $patient->id,
                            'doctor_id' => $doctor->id,
                            'appointment_date' => $date->toDateString(),
                            'reason' => $appointmentReasons[array_rand($appointmentReasons)],
                            'notes' => $appointmentNotes[array_rand($appointmentNotes)],
                        ]);

                        $appointmentCount++;
                    }

                    continue;
                }

                $time = $window['start']->copy();

                for ($i = 0; $i < $dailyAppointments; $i++) {
                    $patient = $patients[$patientIndex % $patients->count()];
                    $patientIndex++;

                    Appointment::create([
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'appointment_mode' => 'scheduled',
                        'appointment_date' => $date->toDateString(),
                        'appointment_time' => $time->format('H:i:s'),
                        'status' => 'scheduled',
                        'reason' => $appointmentReasons[array_rand($appointmentReasons)],
                        'notes' => $appointmentNotes[array_rand($appointmentNotes)],
                    ]);

                    $time->addMinutes($this->scheduler->duration());
                    $appointmentCount++;
                }
            }

            $this->command?->info("{$date->toDateString()}: appointments generated using clinic hours and doctor mode settings.");
        }

        $this->command?->info("Seeded {$appointmentCount} appointments using scheduled and queue-aware rules.");
    }
}
