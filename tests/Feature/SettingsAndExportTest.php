<?php

use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;

it('blocks receptionists from doctor-only settings', function () {
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->post('/settings/working-hours', [])->assertForbidden();
    $this->actingAs($receptionist)->post('/settings/clinic-info', [])->assertForbidden();
    $this->actingAs($receptionist)->post('/settings/appointment-mode', [
        'appointment_mode' => 'queue',
    ])->assertForbidden();
});

it('lets a doctor update working hours', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->post('/settings/working-hours', [
        'monday_working' => '1',
        'monday_start' => '08:30',
        'monday_end' => '16:30',
    ])->assertRedirect(route('settings.index'));

    $hours = Setting::getWorkingHours('monday');

    expect($hours['is_working'])->toBeTrue()
        ->and($hours['start_time'])->toBe('08:30')
        ->and($hours['end_time'])->toBe('16:30')
        ->and(Setting::getWorkingHours('tuesday')['is_working'])->toBeFalse();
});

it('lets a doctor switch to queue mode with a daily limit', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->post('/settings/appointment-mode', [
        'appointment_mode' => 'queue',
        'queue_daily_limit' => 25,
    ])->assertRedirect(route('settings.index'));

    $doctor->refresh();

    expect($doctor->appointment_mode)->toBe('queue')
        ->and($doctor->queue_daily_limit)->toBe(25);

    // Switching back clears the limit
    $this->actingAs($doctor)->post('/settings/appointment-mode', [
        'appointment_mode' => 'scheduled',
    ]);

    $doctor->refresh();

    expect($doctor->appointment_mode)->toBe('scheduled')
        ->and($doctor->queue_daily_limit)->toBeNull();
});

it('updates the appointment duration', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->post('/settings/appointment-duration', [
        'appointment_duration' => 45,
    ])->assertRedirect(route('settings.index'));

    expect(Setting::getAppointmentDuration())->toBe(45);
});

it('rejects an unsupported appointment duration', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->from('/settings')->post('/settings/appointment-duration', [
        'appointment_duration' => 17,
    ])->assertSessionHasErrors('appointment_duration');
});

it('blocks receptionists from exports', function () {
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->get('/exports')->assertForbidden();
    $this->actingAs($receptionist)->post('/exports', ['data_types' => ['patients']])->assertForbidden();
});

it('exports patients as CSV with real IDs', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create(['first_name' => 'Exportine']);

    $response = $this->actingAs($doctor)->post('/exports', [
        'data_types' => ['patients'],
    ]);

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv');

    $csv = $response->getContent();

    expect($csv)->toContain(__('Patient ID'))
        ->toContain('Exportine')
        ->toContain($patient->id . ',Exportine');
});

it('exports appointments within a date range', function () {
    $doctor = User::factory()->doctor()->create();
    \App\Models\Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ]);
    \App\Models\Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2020-01-15',
    ]);

    $response = $this->actingAs($doctor)->post('/exports', [
        'data_types' => ['appointments'],
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
    ]);

    $response->assertOk();

    expect($response->getContent())->toContain('2026-06-10')
        ->not->toContain('2020-01-15');
});

it('rejects an export with a future date range', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->from('/exports')->post('/exports', [
        'data_types' => ['patients'],
        'start_date' => now()->addDay()->toDateString(),
    ])->assertSessionHasErrors('start_date');
});
