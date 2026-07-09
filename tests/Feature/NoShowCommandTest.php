<?php

use App\Models\Appointment;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 08:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('marks yesterday still-scheduled appointments as no-shows', function () {
    $stale = Appointment::factory()->create([
        'appointment_date' => '2026-06-09',
        'status' => 'scheduled',
    ]);
    $completed = Appointment::factory()->create([
        'appointment_date' => '2026-06-09',
        'status' => 'completed',
    ]);
    $todayScheduled = Appointment::factory()->create([
        'appointment_date' => '2026-06-10',
        'status' => 'scheduled',
    ]);

    $this->artisan('appointments:mark-no-shows')->assertSuccessful();

    expect($stale->fresh()->status)->toBe('no_show')
        ->and($completed->fresh()->status)->toBe('completed')
        ->and($todayScheduled->fresh()->status)->toBe('scheduled');
});

it('does not touch future appointments', function () {
    $future = Appointment::factory()->create([
        'appointment_date' => '2026-06-15',
        'status' => 'scheduled',
    ]);

    $this->artisan('appointments:mark-no-shows')->assertSuccessful();

    expect($future->fresh()->status)->toBe('scheduled');
});

it('can target a specific day', function () {
    $target = Appointment::factory()->create([
        'appointment_date' => '2026-06-10',
        'status' => 'scheduled',
    ]);

    $this->artisan('appointments:mark-no-shows', ['--date' => '2026-06-10'])->assertSuccessful();

    expect($target->fresh()->status)->toBe('no_show');
});
