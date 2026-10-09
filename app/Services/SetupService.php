<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SetupDemoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupService
{
    public function __construct(private SpecialtyService $specialties)
    {
    }

    public function complete(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $this->storeClinicSettings($data);

            $primaryDoctor = $this->createStaffUser($data['owner_doctor'], 'doctor', [
                'appointment_mode' => $data['appointment_mode'],
                'queue_daily_limit' => $data['appointment_mode'] === 'queue'
                    ? ($data['queue_daily_limit'] ?? null)
                    : null,
            ]);

            foreach ($data['doctors'] ?? [] as $doctor) {
                $this->createStaffUser($doctor, 'doctor', [
                    'appointment_mode' => $data['appointment_mode'],
                    'queue_daily_limit' => $data['appointment_mode'] === 'queue'
                        ? ($data['queue_daily_limit'] ?? null)
                        : null,
                ]);
            }

            foreach ($data['receptionists'] ?? [] as $receptionist) {
                $this->createStaffUser($receptionist, 'receptionist');
            }

            if ($data['environment_mode'] === 'demo') {
                app(SetupDemoSeeder::class)->run($data['clinic_specialty']);
            }

            Setting::setSetupComplete(true);

            return $primaryDoctor;
        });
    }

    /**
     * Build the showcase clinic without asking a presenter to configure it.
     * The same accounts are documented in the README and can be used after
     * logging out to demonstrate the receptionist experience as well.
     */
    public function completeDemo(string $specialty = SpecialtyService::DEFAULT_SPECIALTY): User
    {
        $data = [
            'environment_mode' => 'demo',
            'clinic_name' => 'Demo Medical Clinic',
            'clinic_phone' => '+1234567890',
            'clinic_address' => '123 Medical Center Dr, City, State 12345',
            'clinic_email' => 'hello@demo-clinic.test',
            'clinic_website' => '',
            'clinic_specialty' => $specialty,
            'appointment_mode' => 'scheduled',
            'appointment_duration' => 30,
            'queue_daily_limit' => null,
            'owner_doctor' => [
                'name' => 'Dr. John Smith',
                'email' => 'doctor@example.com',
                'password' => 'password',
                'phone' => '+1234567890',
                'address' => '123 Medical Center Dr, City, State 12345',
            ],
            'doctors' => [],
            'receptionists' => [[
                'name' => 'Sarah Johnson',
                'email' => 'receptionist@example.com',
                'password' => 'password',
                'phone' => '+1234567891',
                'address' => '456 Front Desk Ave, City, State 12345',
            ]],
        ];

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
            $data["{$day}_working"] = true;
            $data["{$day}_start"] = '09:00';
            $data["{$day}_end"] = '17:00';
        }

        foreach (['saturday', 'sunday'] as $day) {
            $data["{$day}_working"] = false;
            $data["{$day}_start"] = '09:00';
            $data["{$day}_end"] = '13:00';
        }

        return $this->complete($data);
    }

    private function storeClinicSettings(array $data): void
    {
        Setting::setClinicInfo([
            'name' => $data['clinic_name'],
            'address' => $data['clinic_address'],
            'phone' => $data['clinic_phone'],
            'email' => $data['clinic_email'] ?? '',
            'website' => $data['clinic_website'] ?? '',
        ]);

        $this->specialties->setCurrent($data['clinic_specialty']);
        Setting::setAppointmentDuration($data['appointment_duration']);
        Setting::set('deployment_mode', $data['environment_mode'], 'string');
        Setting::set('default_appointment_mode', $data['appointment_mode'], 'string');
        Setting::set(
            'default_queue_daily_limit',
            $data['appointment_mode'] === 'queue' ? ($data['queue_daily_limit'] ?? '') : '',
            'integer'
        );

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $working = !empty($data["{$day}_working"]);

            Setting::set("{$day}_working", $working ? '1' : '0', 'boolean');
            // Times are only validated (and may be omitted) for non-working days.
            Setting::set("{$day}_start", $data["{$day}_start"] ?? '09:00', 'time');
            Setting::set("{$day}_end", $data["{$day}_end"] ?? '17:00', 'time');
        }
    }

    private function createStaffUser(array $attributes, string $role, array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $attributes['name'],
            'email' => strtolower($attributes['email']),
            'password' => Hash::make($attributes['password']),
            'role' => $role,
            'phone' => $attributes['phone'] ?? null,
            'address' => $attributes['address'] ?? null,
        ], $extra));
    }
}
