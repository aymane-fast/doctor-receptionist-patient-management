<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\SetupDemoSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupService
{
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
                app(SetupDemoSeeder::class)->run();
            }

            Setting::setSetupComplete(true);

            return $primaryDoctor;
        });
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
            Setting::set("{$day}_start", $data["{$day}_start"], 'time');
            Setting::set("{$day}_end", $data["{$day}_end"], 'time');
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
