<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Wednesday mid-morning; default working hours are 09:00-17:00.
    Carbon::setTestNow(Carbon::parse('2026-06-10 09:30:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

// ---- Check-in ----------------------------------------------------------

it('checks a patient in and records the arrival time', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['appointment_time' => '10:00:00']);

    $this->actingAs($receptionist)
        ->post(route('appointments.check-in', $appointment))
        ->assertSessionHasNoErrors();

    $appointment->refresh();

    expect($appointment->isCheckedIn())->toBeTrue()
        ->and($appointment->status)->toBe('scheduled')
        ->and($appointment->checked_in_at)->not->toBeNull();
});

it('does not allow checking in a completed appointment', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['status' => 'completed']);

    $this->actingAs($receptionist)
        ->post(route('appointments.check-in', $appointment))
        ->assertSessionHas('error');

    expect($appointment->fresh()->isCheckedIn())->toBeFalse();
});

// ---- No-show -----------------------------------------------------------

it('marks a scheduled appointment as a no-show', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create();

    $this->actingAs($receptionist)
        ->post(route('appointments.no-show', $appointment))
        ->assertSessionHasNoErrors();

    expect($appointment->fresh()->status)->toBe('no_show');
});

it('refuses to mark a completed appointment as a no-show', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['status' => 'completed']);

    $this->actingAs($receptionist)
        ->post(route('appointments.no-show', $appointment))
        ->assertSessionHas('error');

    expect($appointment->fresh()->status)->toBe('completed');
});

it('refuses to mark a patient in consultation as a no-show', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['status' => 'in_progress']);

    $this->actingAs($receptionist)
        ->post(route('appointments.no-show', $appointment))
        ->assertSessionHas('error');

    expect($appointment->fresh()->status)->toBe('in_progress');
});

// ---- Call-next prefers arrived patients --------------------------------

it('calls the arrived patient first even if another is scheduled earlier', function () {
    $doctor = User::factory()->doctor()->create();

    $earlyNotArrived = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'scheduled',
    ]);
    $laterArrived = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '11:00:00',
        'status' => 'scheduled',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($doctor)->post(route('dashboard.initialize-first-patient'));

    expect($laterArrived->fresh()->status)->toBe('in_progress')
        ->and($earlyNotArrived->fresh()->status)->toBe('scheduled');
});

it('falls back to the planned order when nobody has arrived', function () {
    $doctor = User::factory()->doctor()->create();

    $first = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'scheduled',
    ]);
    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '10:00:00',
        'status' => 'scheduled',
    ]);

    $this->actingAs($doctor)->post(route('dashboard.initialize-first-patient'));

    expect($first->fresh()->status)->toBe('in_progress');
});

it('completing a consultation calls the next arrived patient', function () {
    $doctor = User::factory()->doctor()->create();

    $current = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'in_progress',
    ]);
    $earlyWaiting = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:30:00',
        'status' => 'scheduled',
    ]);
    $arrived = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '12:00:00',
        'status' => 'scheduled',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($doctor)->post(route('appointments.mark-current-done'));

    expect($current->fresh()->status)->toBe('completed')
        ->and($arrived->fresh()->status)->toBe('in_progress')
        ->and($earlyWaiting->fresh()->status)->toBe('scheduled');
});

// ---- Duplicate same-day booking guard ----------------------------------

it('prevents booking a patient twice with the same doctor on one day', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'patient_id' => $patient->id,
        'appointment_date' => '2026-06-11',
        'appointment_time' => '09:00:00',
        'status' => 'scheduled',
    ]);

    $this->actingAs($receptionist)->from(route('appointments.create'))->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-11',
        'appointment_time' => '10:00',
    ])->assertSessionHasErrors('patient_id');

    expect(Appointment::where('patient_id', $patient->id)->count())->toBe(1);
});

it('allows the same patient to book different doctors on one day', function () {
    $doctorA = User::factory()->doctor()->create();
    $doctorB = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    Appointment::factory()->create([
        'doctor_id' => $doctorA->id,
        'patient_id' => $patient->id,
        'appointment_date' => '2026-06-11',
        'appointment_time' => '09:00:00',
    ]);

    $this->actingAs($receptionist)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctorB->id,
        'appointment_date' => '2026-06-11',
        'appointment_time' => '09:00',
    ])->assertSessionHasNoErrors();

    expect(Appointment::where('patient_id', $patient->id)->count())->toBe(2);
});

// ---- Guard rails on cancel / set-current -------------------------------

it('refuses to cancel an already-completed appointment', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['status' => 'completed']);

    $this->actingAs($receptionist)
        ->post(route('appointments.cancel', $appointment))
        ->assertSessionHas('error');

    expect($appointment->fresh()->status)->toBe('completed');
});

it('refuses to set a cancelled appointment as current', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['status' => 'cancelled']);

    $this->actingAs($receptionist)
        ->post(route('appointments.set-current', $appointment))
        ->assertSessionHas('error');

    expect($appointment->fresh()->status)->toBe('cancelled');
});

it('setting a patient as current marks them arrived', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'status' => 'scheduled',
    ]);

    $this->actingAs($doctor)->post(route('appointments.set-current', $appointment));

    $appointment->refresh();

    expect($appointment->status)->toBe('in_progress')
        ->and($appointment->isCheckedIn())->toBeTrue();
});
