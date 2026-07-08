<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Wednesday morning; default working hours are 09:00-17:00.
    Carbon::setTestNow(Carbon::parse('2026-06-10 08:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates a scheduled appointment inside working hours', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $response = $this->actingAs($receptionist)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:00',
        'reason' => 'Check-up',
    ]);

    $appointment = Appointment::first();

    expect($appointment)->not->toBeNull()
        ->and($appointment->status)->toBe('scheduled')
        ->and($appointment->appointment_mode)->toBe('scheduled');

    $response->assertRedirect(route('appointments.show', $appointment));
});

it('rejects a scheduled appointment outside working hours', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($receptionist)->from(route('appointments.create'))->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '18:00',
    ])->assertSessionHasErrors('appointment_time');

    expect(Appointment::count())->toBe(0);
});

it('rejects booking a time that has already passed today', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 12:00:00'));

    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($receptionist)->from(route('appointments.create'))->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:30',
    ])->assertSessionHasErrors('appointment_time');
});

it('prevents assigning appointments to a receptionist', function () {
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($receptionist)->from(route('appointments.create'))->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $receptionist->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:00',
    ])->assertSessionHasErrors('doctor_id');
});

it('hides other doctors appointments from a doctor', function () {
    $doctorA = User::factory()->doctor()->create();
    $doctorB = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctorA->id]);

    $this->actingAs($doctorB)->get(route('appointments.show', $appointment))->assertForbidden();
    $this->actingAs($doctorA)->get(route('appointments.show', $appointment))->assertOk();
});

it('lets receptionists view any appointment', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create();

    $this->actingAs($receptionist)->get(route('appointments.show', $appointment))->assertOk();
});

it('updates appointment status and rejects unknown statuses', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create();

    $this->actingAs($receptionist)->patch(route('appointments.update-status', $appointment), [
        'status' => 'completed',
    ])->assertSessionHasNoErrors();

    expect($appointment->fresh()->status)->toBe('completed');

    $this->actingAs($receptionist)->from(route('appointments.show', $appointment))
        ->patch(route('appointments.update-status', $appointment), ['status' => 'nonsense'])
        ->assertSessionHasErrors('status');
});

it('promotes the next patient when the current one is cancelled', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    $current = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'in_progress',
    ]);
    $next = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:30:00',
        'status' => 'scheduled',
    ]);

    $this->actingAs($receptionist)->post(route('appointments.cancel', $current));

    expect($current->fresh()->status)->toBe('cancelled')
        ->and($next->fresh()->status)->toBe('in_progress');
});

it('completes the current consultation and calls the next patient', function () {
    $doctor = User::factory()->doctor()->create();

    $current = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'in_progress',
    ]);
    $next = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:30:00',
        'status' => 'scheduled',
    ]);

    $this->actingAs($doctor)->post(route('appointments.mark-current-done'));

    expect($current->fresh()->status)->toBe('completed')
        ->and($next->fresh()->status)->toBe('in_progress');
});

it('enforces the queue daily limit', function () {
    $doctor = User::factory()->doctor()->queueMode(1)->create();
    $receptionist = User::factory()->receptionist()->create();
    $first = Patient::factory()->create();
    $second = Patient::factory()->create();

    $this->actingAs($receptionist)->post(route('appointments.store'), [
        'patient_id' => $first->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ])->assertSessionHasNoErrors();

    $this->actingAs($receptionist)->from(route('appointments.create'))->post(route('appointments.store'), [
        'patient_id' => $second->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ])->assertSessionHasErrors('appointment_date');

    expect(Appointment::count())->toBe(1);
});

it('reorders the queue with move up and down', function () {
    $doctor = User::factory()->doctor()->queueMode()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patients = Patient::factory()->count(2)->create();

    foreach ($patients as $patient) {
        $this->actingAs($receptionist)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-10',
        ]);
    }

    [$first, $second] = Appointment::orderBy('queue_position')->get();

    $this->actingAs($receptionist)->post(route('appointments.queue-up', $second));

    expect($second->fresh()->queue_position)->toBe(1)
        ->and($first->fresh()->queue_position)->toBe(2);

    $this->actingAs($receptionist)->post(route('appointments.queue-down', $second));

    expect($second->fresh()->queue_position)->toBe(2)
        ->and($first->fresh()->queue_position)->toBe(1);
});

it('recalculates queue positions when an entry is cancelled', function () {
    $doctor = User::factory()->doctor()->queueMode()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patients = Patient::factory()->count(3)->create();

    foreach ($patients as $patient) {
        $this->actingAs($receptionist)->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-10',
        ]);
    }

    [$first, $second, $third] = Appointment::orderBy('queue_position')->get();

    $this->actingAs($receptionist)->post(route('appointments.cancel', $second));

    expect($first->fresh()->queue_position)->toBe(1)
        ->and($third->fresh()->queue_position)->toBe(2)
        ->and($second->fresh()->status)->toBe('cancelled');
});

it('books a walk-in appointment when creating a patient', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 10:00:00'));

    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->post('/patients', [
        'first_name' => 'Walkin',
        'last_name' => 'Patient',
        'phone' => '+212600000009',
        'id_card_number' => 'WLK001',
        'book_today' => '1',
        'appointment_date' => '2026-06-10',
    ])->assertRedirect(route('dashboard'));

    $appointment = Appointment::first();

    expect($appointment)->not->toBeNull()
        ->and($appointment->doctor_id)->toBe($doctor->id)
        ->and($appointment->appointment_date->toDateString())->toBe('2026-06-10');
});

it('shows the queue page for a queue-mode doctor', function () {
    $doctor = User::factory()->doctor()->queueMode()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create(['first_name' => 'Queueman']);

    $this->actingAs($receptionist)->post(route('appointments.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ]);

    $this->actingAs($receptionist)
        ->get(route('appointments.queue', ['doctor_id' => $doctor->id, 'date' => '2026-06-10']))
        ->assertOk()
        ->assertSee('Queueman');
});

it('rejects an invalid date on the next-available-slot API', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)
        ->getJson('/api/appointments/next-available-slot?date=not-a-date')
        ->assertStatus(422);
});

it('returns the next free slot from the availability API', function () {
    $doctor = User::factory()->doctor()->create();

    $response = $this->actingAs($doctor)
        ->getJson('/api/appointments/next-available-slot?date=2026-06-11');

    $response->assertOk()
        ->assertJsonPath('available', true)
        ->assertJsonPath('mode', 'scheduled')
        ->assertJsonPath('next_slot.time', '09:00');
});
