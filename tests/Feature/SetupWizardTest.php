<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;

function validSetupPayload(array $overrides = []): array
{
    $payload = [
        'environment_mode' => 'demo',
        'clinic_name' => 'Atlas Family Clinic',
        'clinic_phone' => '+212600000000',
        'clinic_address' => '123 Atlas Avenue, Casablanca',
        'clinic_email' => 'hello@atlas-clinic.test',
        'clinic_website' => 'https://atlas-clinic.test',
        'appointment_mode' => 'queue',
        'appointment_duration' => 15,
        'queue_daily_limit' => 12,
        'owner_doctor' => [
            'name' => 'Dr. Owner',
            'email' => 'owner@clinic.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+212611111111',
            'address' => 'Owner Address',
        ],
        'doctors' => [
            [
                'name' => 'Dr. Second',
                'email' => 'doctor2@clinic.test',
                'password' => 'password123',
                'phone' => '+212622222222',
                'address' => 'Doctor Address',
            ],
        ],
        'receptionists' => [
            [
                'name' => 'Front Desk',
                'email' => 'desk@clinic.test',
                'password' => 'password123',
                'phone' => '+212633333333',
                'address' => 'Reception Address',
            ],
        ],
    ];

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
        $payload["{$day}_working"] = '1';
        $payload["{$day}_start"] = '09:00';
        $payload["{$day}_end"] = '17:00';
    }

    foreach (['saturday', 'sunday'] as $day) {
        $payload["{$day}_start"] = '09:00';
        $payload["{$day}_end"] = '13:00';
    }

    return array_replace_recursive($payload, $overrides);
}

it('redirects guests to setup until the wizard is completed', function () {
    Setting::setSetupComplete(false);

    $this->get('/login')->assertRedirect(route('setup.show'));
    $this->get(route('setup.show'))->assertOk();
});

it('completes demo setup, creates staff, and seeds queue appointments', function () {
    Setting::setSetupComplete(false);

    $this->post(route('setup.store'), validSetupPayload())
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();

    expect(Setting::isSetupComplete())->toBeTrue()
        ->and(Setting::getDeploymentMode())->toBe('demo')
        ->and(Setting::getDefaultAppointmentMode())->toBe('queue')
        ->and(Setting::get('clinic_name'))->toBe('Atlas Family Clinic');

    expect(User::where('role', 'doctor')->count())->toBe(2)
        ->and(User::where('role', 'receptionist')->count())->toBe(1)
        ->and(Patient::count())->toBeGreaterThan(0)
        ->and(Appointment::count())->toBeGreaterThan(0)
        ->and(Appointment::where('appointment_mode', 'queue')->count())->toBe(Appointment::count());
});
