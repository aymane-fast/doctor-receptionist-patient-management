<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SetupDemoSeeder extends Seeder
{
    public function __construct(private AppointmentSchedulingService $scheduler)
    {
    }

    public function run(): void
    {
        $patients = $this->seedPatients();
        $this->seedAppointments($patients);
    }

    private function seedPatients(): array
    {
        $firstNames = [
            'Adam', 'Leila', 'Maya', 'Omar', 'Nina', 'Youssef', 'Sara', 'Daniel', 'Lina', 'Karim',
            'Ava', 'Noah', 'Salma', 'Elias', 'Amira', 'Jonah', 'Mila', 'Rayan', 'Hana', 'Ismael',
        ];

        $lastNames = [
            'Haddad', 'Bennani', 'Smith', 'Johnson', 'Naciri', 'Lopez', 'Rahimi', 'Stone', 'El Idrissi', 'Martin',
            'Dupont', 'Wilson', 'Saidi', 'Brown', 'Khan', 'Clark', 'Fassi', 'Walker', 'Mouline', 'Diaz',
        ];

        $addresses = [
            '12 Atlas Avenue', '48 Palm Street', '77 Central Clinic Road', '9 Cedar Lane', '145 Horizon Boulevard',
            '22 Garden Street', '81 Ocean View', '35 Olive Crescent', '16 Sunrise Court', '203 Elm Avenue',
        ];
        $allergies = ['Penicillin', 'Pollen', 'Peanuts'];
        $conditions = ['Hypertension', 'Type 2 Diabetes', 'Asthma'];

        $patients = [];

        for ($i = 1; $i <= 36; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $patients[] = Patient::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'birth_date' => now()->subYears(rand(18, 82))->subDays(rand(0, 365))->format('Y-m-d'),
                'gender' => rand(0, 1) ? 'female' : 'male',
                'phone' => '+2126' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                'email' => strtolower($firstName . '.' . $lastName . $i . '@demo-clinic.test'),
                'address' => $addresses[array_rand($addresses)],
                'id_card_number' => 'DEMO' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'allergies' => rand(1, 4) === 1 ? $allergies[array_rand($allergies)] : null,
                'chronic_conditions' => rand(1, 5) === 1 ? $conditions[array_rand($conditions)] : null,
                'emergency_contact_name' => $firstNames[array_rand($firstNames)] . ' ' . $lastName,
                'emergency_contact_phone' => '+2126' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
            ]);
        }

        return $patients;
    }

    private function seedAppointments(array $patients): void
    {
        $doctors = User::where('role', 'doctor')->orderBy('id')->get();

        if ($doctors->isEmpty()) {
            return;
        }

        $reasons = [
            'Routine follow-up', 'Blood pressure check', 'New patient consultation', 'Prescription renewal',
            'Headache assessment', 'Diabetes monitoring', 'Minor injury review', 'Lab results discussion',
        ];

        $notes = [
            'Patient requested morning availability.',
            'Bring previous test results to the visit.',
            'Review medication tolerance during the consultation.',
            'Discuss lifestyle adjustments and next follow-up.',
        ];

        $patientPool = collect($patients);
        $patientIndex = 0;

        foreach (range(-2, 5) as $offset) {
            $date = now()->startOfDay()->addDays($offset);
            $window = $this->scheduler->workingWindow($date);

            if (!$window) {
                continue;
            }

            $capacity = max(1, (int) floor($window['start']->diffInMinutes($window['end']) / $this->scheduler->duration()));

            foreach ($doctors as $doctor) {
                $appointmentCount = min($capacity, max(3, min($capacity, rand(3, 6))));

                if ($doctor->usesQueueMode()) {
                    $this->seedQueueDay($doctor, $date, $appointmentCount, $patientPool, $patientIndex, $reasons, $notes);
                    continue;
                }

                $this->seedScheduledDay($doctor, $date, $appointmentCount, $patientPool, $patientIndex, $reasons, $notes);
            }
        }
    }

    private function seedScheduledDay(
        User $doctor,
        Carbon $date,
        int $appointmentCount,
        $patientPool,
        int &$patientIndex,
        array $reasons,
        array $notes
    ): void {
        $window = $this->scheduler->workingWindow($date);

        if (!$window) {
            return;
        }

        $current = $window['start']->copy();

        for ($i = 0; $i < $appointmentCount; $i++) {
            $patient = $patientPool[$patientIndex % $patientPool->count()];
            $patientIndex++;

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_mode' => 'scheduled',
                'appointment_date' => $date->toDateString(),
                'appointment_time' => $current->format('H:i:s'),
                'status' => $this->statusForDate($date, $i),
                'reason' => $reasons[array_rand($reasons)],
                'notes' => $notes[array_rand($notes)],
            ]);

            $current->addMinutes($this->scheduler->duration());
        }
    }

    private function seedQueueDay(
        User $doctor,
        Carbon $date,
        int $appointmentCount,
        $patientPool,
        int &$patientIndex,
        array $reasons,
        array $notes
    ): void {
        $createdAppointments = [];

        for ($i = 0; $i < $appointmentCount; $i++) {
            $patient = $patientPool[$patientIndex % $patientPool->count()];
            $patientIndex++;

            $createdAppointments[] = $this->scheduler->createQueueAppointment([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => $date->toDateString(),
                'reason' => $reasons[array_rand($reasons)],
                'notes' => $notes[array_rand($notes)],
            ]);
        }

        foreach ($createdAppointments as $index => $appointment) {
            $appointment->updateQuietly([
                'status' => $this->statusForDate($date, $index),
            ]);
        }

        $this->scheduler->recalculateQueue($doctor->id, $date->toDateString());
    }

    private function statusForDate(Carbon $date, int $index): string
    {
        if ($date->isPast()) {
            return $index % 5 === 0 ? 'cancelled' : 'completed';
        }

        if ($date->isToday()) {
            if ($index === 0) {
                return 'in_progress';
            }

            if ($index === 1) {
                return 'completed';
            }
        }

        return 'scheduled';
    }
}
