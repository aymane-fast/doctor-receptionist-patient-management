<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;

function productionSetupPayload(): array
{
    $payload = [
        'environment_mode' => 'production',
        'clinic_name' => 'Prod Clinic',
        'clinic_phone' => '+212611111100',
        'clinic_address' => '1 Production Street',
        'appointment_mode' => 'scheduled',
        'appointment_duration' => 30,
        'owner_doctor' => [
            'name' => 'Dr. Prod',
            'email' => 'prod-owner@clinic.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ],
    ];

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
        $payload["{$day}_working"] = '1';
        $payload["{$day}_start"] = '09:00';
        $payload["{$day}_end"] = '17:00';
    }

    // Saturday/Sunday intentionally omit both the working flag and the times.
    return $payload;
}

it('completes production setup without demo data even when weekend times are omitted', function () {
    Setting::setSetupComplete(false);

    $this->post(route('setup.store'), productionSetupPayload())
        ->assertRedirect(route('dashboard'));

    expect(Setting::isSetupComplete())->toBeTrue()
        ->and(Setting::getDeploymentMode())->toBe('production')
        ->and(User::count())->toBe(1)
        ->and(Patient::count())->toBe(0)
        ->and(Appointment::count())->toBe(0)
        ->and(Setting::getWorkingHours('saturday')['is_working'])->toBeFalse();
});

it('blocks the wizard once setup is complete', function () {
    $this->get(route('setup.show'))->assertRedirect(route('login'));

    $this->post(route('setup.store'), productionSetupPayload())
        ->assertRedirect(route('login'));

    expect(User::count())->toBe(0);
});

it('rejects duplicate staff emails across roles', function () {
    Setting::setSetupComplete(false);

    $payload = productionSetupPayload();
    $payload['receptionists'] = [[
        'name' => 'Duplicate Email',
        'email' => 'prod-owner@clinic.test',
        'password' => 'password123',
    ]];

    $this->post(route('setup.store'), $payload)->assertSessionHasErrors('staff');

    expect(User::count())->toBe(0);
});

it('preserves a closed weekday when setup returns from a validation error', function () {
    Setting::setSetupComplete(false);
    $payload = productionSetupPayload();
    $payload['monday_working'] = '0';
    unset($payload['monday_start'], $payload['monday_end']);
    $payload['owner_doctor']['email'] = 'invalid-email';
    $this->from('/setup')->post(route('setup.store'), $payload)
        ->assertSessionHasErrors('owner_doctor.email');
    $page = $this->get(route('setup.show'))->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($page->getContent());
    $xpath = new DOMXPath($dom);
    expect($xpath->query('//input[@type="checkbox" and @name="monday_working" and @checked]')->length)->toBe(0)
        ->and($xpath->query('//input[@type="hidden" and @name="monday_working" and @value="0"]')->length)->toBe(1)
        ->and(User::count())->toBe(0);
});
