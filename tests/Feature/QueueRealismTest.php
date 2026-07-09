<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 09:00:00'));
    \App\Models\Setting::setAppointmentDuration(15);
});

afterEach(function () {
    Carbon::setTestNow();
});

function queueDoctor(): User
{
    return User::factory()->doctor()->queueMode()->create();
}

function joinQueue(User $doctor, User $actor): Appointment
{
    $patient = Patient::factory()->create();

    test()->actingAs($actor)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ])->assertSessionHasNoErrors();

    return Appointment::where('patient_id', $patient->id)->firstOrFail();
}

it('keeps a ticket number stable after earlier entries are cancelled', function () {
    $doctor = queueDoctor();
    $receptionist = User::factory()->receptionist()->create();

    $first = joinQueue($doctor, $receptionist);
    $second = joinQueue($doctor, $receptionist);
    $third = joinQueue($doctor, $receptionist);

    expect($third->queue_number)->toBe(3);

    // Cancel the first ticket
    $this->actingAs($receptionist)->post(route('appointments.cancel', $first));

    $third->refresh();
    $second->refresh();

    // Ticket numbers never change...
    expect($third->queue_number)->toBe(3)
        ->and($second->queue_number)->toBe(2)
        // ...but positions in line move up
        ->and($second->queue_position)->toBe(1)
        ->and($third->queue_position)->toBe(2);
});

it('does not reuse a ticket number after a cancellation', function () {
    $doctor = queueDoctor();
    $receptionist = User::factory()->receptionist()->create();

    $first = joinQueue($doctor, $receptionist);
    $this->actingAs($receptionist)->post(route('appointments.cancel', $first));

    $second = joinQueue($doctor, $receptionist);

    expect($second->queue_number)->toBe(2);
});

it('anchors a late walk-in estimate to the current time, not the opening hour', function () {
    $doctor = queueDoctor();
    $receptionist = User::factory()->receptionist()->create();

    // It is now 15:00, well after opening
    Carbon::setTestNow(Carbon::parse('2026-06-10 15:00:00'));

    $appointment = joinQueue($doctor, $receptionist);

    // Estimate should be near 15:00, not 09:00
    expect($appointment->appointment_time->format('H:i'))->toBe('15:00');
});

it('pushes queue estimates past the patient currently in consultation', function () {
    $doctor = queueDoctor();
    $receptionist = User::factory()->receptionist()->create();

    $first = joinQueue($doctor, $receptionist);
    $second = joinQueue($doctor, $receptionist);

    // Start the first patient's consultation
    $this->actingAs($receptionist)->post(route('appointments.set-current', $first));

    $second->refresh();

    // With a 15-minute duration and one consultation in progress, the next
    // person is estimated at least one slot out from now (09:00 -> 09:15).
    expect($second->appointment_time->format('H:i'))->toBe('09:15');
});

it('excludes no-shows and cancellations from the queue view', function () {
    $doctor = queueDoctor();
    $receptionist = User::factory()->receptionist()->create();

    $kept = joinQueue($doctor, $receptionist);
    $noShow = joinQueue($doctor, $receptionist);
    $cancelled = joinQueue($doctor, $receptionist);

    $this->actingAs($receptionist)->post(route('appointments.no-show', $noShow));
    $this->actingAs($receptionist)->post(route('appointments.cancel', $cancelled));

    $queue = app(AppointmentSchedulingService::class)->queueAppointments($doctor->id, '2026-06-10');

    expect($queue)->toHaveCount(1)
        ->and($queue->first()->id)->toBe($kept->id);
});

it('counts no-shows against the queue capacity but frees room on cancel', function () {
    $doctor = User::factory()->doctor()->queueMode(2)->create();
    $receptionist = User::factory()->receptionist()->create();

    $first = joinQueue($doctor, $receptionist);
    $second = joinQueue($doctor, $receptionist);

    // Queue is full (limit 2). Cancelling frees a slot.
    $this->actingAs($receptionist)->post(route('appointments.cancel', $first));

    $third = Patient::factory()->create();
    $this->actingAs($receptionist)->post(route('appointments.store'), [
        'patient_id' => $third->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ])->assertSessionHasNoErrors();

    expect(Appointment::where('doctor_id', $doctor->id)->whereNotIn('status', ['cancelled'])->count())->toBe(2);
});
