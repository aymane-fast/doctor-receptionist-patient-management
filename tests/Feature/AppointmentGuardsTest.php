<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 09:30:00')); // Wednesday
});

afterEach(function () {
    Carbon::setTestNow();
});

// ---- Status transition matrix -------------------------------------------

it('rejects impossible status transitions', function () {
    $receptionist = User::factory()->receptionist()->create();

    $cases = [
        ['cancelled', 'completed'],
        ['no_show', 'in_progress'],
        ['no_show', 'completed'],
        ['completed', 'cancelled'],
        ['completed', 'no_show'],
        ['cancelled', 'no_show'],
    ];

    foreach ($cases as [$from, $to]) {
        $appointment = Appointment::factory()->create(['status' => $from]);

        $this->actingAs($receptionist)
            ->from(route('appointments.show', $appointment))
            ->patch(route('appointments.update-status', $appointment), ['status' => $to])
            ->assertSessionHas('error');

        expect($appointment->fresh()->status)->toBe($from);
    }
});

it('allows undo transitions back to scheduled', function () {
    $receptionist = User::factory()->receptionist()->create();

    foreach (['cancelled', 'no_show'] as $from) {
        $appointment = Appointment::factory()->create([
            'status' => $from,
            'appointment_time' => '14:00:00',
        ]);

        $this->actingAs($receptionist)
            ->patch(route('appointments.update-status', $appointment), ['status' => 'scheduled'])
            ->assertSessionHasNoErrors();

        expect($appointment->fresh()->status)->toBe('scheduled');
    }
});

it('treats a repeated status update as a harmless no-op', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create(['status' => 'completed']);

    $this->actingAs($receptionist)
        ->patch(route('appointments.update-status', $appointment), ['status' => 'completed'])
        ->assertSessionHasNoErrors();

    expect($appointment->fresh()->status)->toBe('completed');
});

it('refuses to reactivate a slot that has since been taken', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    $cancelled = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '10:00:00',
        'status' => 'cancelled',
    ]);

    // Someone else took the 10:00 slot in the meantime
    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '10:00:00',
        'status' => 'scheduled',
    ]);

    $this->actingAs($receptionist)
        ->from(route('appointments.show', $cancelled))
        ->patch(route('appointments.update-status', $cancelled), ['status' => 'scheduled'])
        ->assertSessionHas('error');

    expect($cancelled->fresh()->status)->toBe('cancelled');
});

it('reopening a completed visit to in-progress goes through the current-patient rules', function () {
    $doctor = User::factory()->doctor()->create();

    $completed = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:00:00',
        'status' => 'completed',
    ]);
    $current = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:30:00',
        'status' => 'in_progress',
    ]);

    $this->actingAs($doctor)
        ->patch(route('appointments.update-status', $completed), [
            'status' => 'in_progress',
            'previous_action' => 'waiting',
            'previous_appointment_id' => $current->id,
        ]);

    // Reopened one becomes current, the previous current returns to waiting
    expect($completed->fresh()->status)->toBe('in_progress')
        ->and($current->fresh()->status)->toBe('scheduled');
});

// ---- Date-boundary guards ------------------------------------------------

it('refuses to check in a patient before their appointment day', function () {
    $receptionist = User::factory()->receptionist()->create();
    $future = Appointment::factory()->create([
        'appointment_date' => '2026-06-12',
        'status' => 'scheduled',
    ]);

    $this->actingAs($receptionist)
        ->post(route('appointments.check-in', $future))
        ->assertSessionHas('error');

    expect($future->fresh()->isCheckedIn())->toBeFalse();
});

it('refuses to mark a future appointment as a no-show', function () {
    $receptionist = User::factory()->receptionist()->create();
    $future = Appointment::factory()->create([
        'appointment_date' => '2026-06-12',
        'status' => 'scheduled',
    ]);

    $this->actingAs($receptionist)
        ->post(route('appointments.no-show', $future))
        ->assertSessionHas('error');

    expect($future->fresh()->status)->toBe('scheduled');
});

it('refuses to start a consultation for another day', function () {
    $doctor = User::factory()->doctor()->create();
    $future = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-12',
        'status' => 'scheduled',
    ]);

    $this->actingAs($doctor)
        ->post(route('appointments.set-current', $future))
        ->assertSessionHas('error');

    expect($future->fresh()->status)->toBe('scheduled');
});

// ---- Arrival stamp hygiene ----------------------------------------------

it('clears the arrival stamp when a checked-in patient is cancelled', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create([
        'status' => 'scheduled',
        'checked_in_at' => now(),
    ]);

    $this->actingAs($receptionist)->post(route('appointments.cancel', $appointment));

    expect($appointment->fresh()->checked_in_at)->toBeNull();
});

// ---- Double-click safety on complete ------------------------------------

it('will not complete the wrong consultation from a stale page', function () {
    $doctor = User::factory()->doctor()->create();

    // A patient the page still believes is current, but who was already completed
    $stale = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '08:30:00',
        'status' => 'completed',
    ]);
    $current = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'in_progress',
    ]);

    // The stale page submits the already-completed patient's id
    $this->actingAs($doctor)->post(route('appointments.mark-current-done'), [
        'appointment_id' => $stale->id,
    ])->assertSessionHas('error');

    // The genuinely current patient is untouched
    expect($current->fresh()->status)->toBe('in_progress');
});

it('completes the intended consultation when the token matches', function () {
    $doctor = User::factory()->doctor()->create();
    $current = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
        'status' => 'in_progress',
    ]);

    $this->actingAs($doctor)->post(route('appointments.mark-current-done'), [
        'appointment_id' => $current->id,
    ]);

    expect($current->fresh()->status)->toBe('completed');
});

// ---- destroy() guards ----------------------------------------------------

it('refuses to delete completed or in-progress visits', function () {
    $doctor = User::factory()->doctor()->create();

    foreach (['completed', 'in_progress'] as $status) {
        $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => $status]);

        $this->actingAs($doctor)
            ->delete(route('appointments.destroy', $appointment))
            ->assertSessionHas('error');

        expect(Appointment::find($appointment->id))->not->toBeNull();
    }
});

it('refuses to delete an appointment that has a medical record', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'scheduled']);
    MedicalRecord::create([
        'patient_id' => $appointment->patient_id,
        'doctor_id' => $doctor->id,
        'appointment_id' => $appointment->id,
        'visit_date' => now()->toDateString(),
    ]);

    $this->actingAs($doctor)
        ->delete(route('appointments.destroy', $appointment))
        ->assertSessionHas('error');

    expect(Appointment::find($appointment->id))->not->toBeNull();
});

it('still deletes a plain scheduled appointment', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'cancelled']);

    $this->actingAs($doctor)
        ->delete(route('appointments.destroy', $appointment))
        ->assertRedirect(route('appointments.index'));

    expect(Appointment::find($appointment->id))->toBeNull();
});

// ---- update() guards -----------------------------------------------------

it('will not repoint an appointment with a medical record to another patient', function () {
    $doctor = User::factory()->doctor()->create();
    $otherPatient = Patient::factory()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'status' => 'completed',
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:00:00',
    ]);
    MedicalRecord::create([
        'patient_id' => $appointment->patient_id,
        'doctor_id' => $doctor->id,
        'appointment_id' => $appointment->id,
        'visit_date' => now()->toDateString(),
    ]);

    $this->actingAs($doctor)
        ->from(route('appointments.edit', $appointment))
        ->put(route('appointments.update', $appointment), [
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:00',
            'status' => 'completed',
        ])
        ->assertSessionHasErrors('patient_id');

    expect($appointment->fresh()->patient_id)->not->toBe($otherPatient->id);
});

it('resets the reminder flag when an appointment is moved', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:00:00',
        'status' => 'scheduled',
        'reminder_sent_at' => now(),
    ]);

    $this->actingAs($doctor)->put(route('appointments.update', $appointment), [
        'patient_id' => $appointment->patient_id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-11',
        'appointment_time' => '09:00',
        'status' => 'scheduled',
    ])->assertSessionHasNoErrors();

    expect($appointment->fresh()->reminder_sent_at)->toBeNull();
});

it('gives a moved queue ticket a fresh number on the new day', function () {
    $doctor = User::factory()->doctor()->queueMode()->create();
    $receptionist = User::factory()->receptionist()->create();

    // Two tickets already exist on the destination day
    foreach ([Patient::factory()->create(), Patient::factory()->create()] as $p) {
        app(App\Services\AppointmentSchedulingService::class)->createQueueAppointment([
            'patient_id' => $p->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-11',
        ]);
    }

    // A ticket on a different day, being moved onto 06-11
    $moving = app(App\Services\AppointmentSchedulingService::class)->createQueueAppointment([
        'patient_id' => Patient::factory()->create()->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
    ]);

    $this->actingAs($receptionist)->put(route('appointments.update', $moving), [
        'patient_id' => $moving->patient_id,
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-11',
        'status' => 'scheduled',
    ])->assertSessionHasNoErrors();

    $moving->refresh();

    // No collision with the two existing tickets on 06-11
    $numbers = Appointment::where('doctor_id', $doctor->id)
        ->whereDate('appointment_date', '2026-06-11')
        ->pluck('queue_number');

    expect($numbers)->toHaveCount(3)
        ->and($numbers->unique())->toHaveCount(3);
});

// ---- rebook idempotency --------------------------------------------------

it('does nothing when rebooking an appointment already at the earliest slot', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    // Book the earliest slot for today, then rebook it
    $slot = app(App\Services\AppointmentSchedulingService::class)->nextAvailableSlot($doctor->id, '2026-06-10');
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => $slot['time'].':00',
        'status' => 'scheduled',
    ]);

    $original = $appointment->appointment_time->format('H:i');

    $this->actingAs($receptionist)
        ->post(route('appointments.rebook-next-slot', $appointment))
        ->assertSessionHas('info');

    expect($appointment->fresh()->appointment_time->format('H:i'))->toBe($original);
});

// ---- orphaned in-progress prevention ------------------------------------

it('refuses to move an in-progress consultation to another day', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '09:00:00',
        'status' => 'in_progress',
    ]);

    $this->actingAs($doctor)
        ->from(route('appointments.edit', $appointment))
        ->put(route('appointments.update', $appointment), [
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-12',
            'appointment_time' => '09:00',
            'status' => 'in_progress',
        ])
        ->assertSessionHasErrors('appointment_date');

    expect($appointment->fresh()->appointment_date->toDateString())->toBe('2026-06-10');
});

it('auto-closes a consultation left in progress on a past day', function () {
    $doctor = User::factory()->doctor()->create();

    $unclosed = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-09',
        'status' => 'in_progress',
    ]);
    $noShow = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-09',
        'status' => 'scheduled',
    ]);

    $this->artisan('appointments:mark-no-shows')->assertSuccessful();

    expect($unclosed->fresh()->status)->toBe('completed')
        ->and($noShow->fresh()->status)->toBe('no_show');
});

// ---- dashboard stat consistency -----------------------------------------

it('excludes past still-scheduled appointments from pending counts', function () {
    $doctor = User::factory()->doctor()->create();

    // A stale scheduled appointment from the past (should not inflate stats)
    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-01',
        'status' => 'scheduled',
    ]);
    Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-12',
        'status' => 'scheduled',
    ]);

    $response = $this->actingAs($doctor)->get('/dashboard');

    // Only the future one counts as pending
    $response->assertViewHas('stats', fn ($stats) => $stats['pending_appointments'] === 1);
});
