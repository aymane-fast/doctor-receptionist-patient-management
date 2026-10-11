<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-06-10 09:30:00');
    $this->doctor = User::factory()->doctor()->create();
    $this->current = Appointment::factory()->create([
        'doctor_id' => $this->doctor->id, 'status' => 'in_progress',
        'appointment_time' => '09:00:00', 'checked_in_at' => now(),
    ]);
    $this->next = Appointment::factory()->create([
        'doctor_id' => $this->doctor->id, 'appointment_time' => '10:00:00',
    ]);
    $this->actingAs($this->doctor);
});

afterEach(fn () => Carbon::setTestNow());

it('asks before changing either patient from all start entry points', function ($method, $route, $payload) {
    $this->{$method}(route($route, $this->next), $payload)
        ->assertRedirect(route('appointments.switch-current', $this->next));
    expect($this->current->fresh()->status)->toBe('in_progress')
        ->and($this->next->fresh()->status)->toBe('scheduled');
    $this->get(route('appointments.switch-current', $this->next))->assertOk()
        ->assertSee($this->current->patient->full_name)->assertSee($this->next->patient->full_name)
        ->assertSee('previous_appointment_id')->assertSee('Do nothing');
})->with([
    ['post', 'appointments.set-current', []],
    ['patch', 'appointments.update-status', ['status' => 'in_progress']],
]);

it('resolves the previous appointment explicitly and records the transition', function ($action, $status) {
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => $action, 'previous_appointment_id' => $this->current->id,
    ])->assertRedirect(route('appointments.show', $this->next));
    expect($this->current->fresh()->status)->toBe($status)
        ->and($this->next->fresh()->status)->toBe('in_progress')
        ->and(Appointment::where('doctor_id', $this->doctor->id)->where('status', 'in_progress')->count())->toBe(1);
    $this->assertDatabaseHas('appointment_status_logs', [
        'appointment_id' => $this->current->id, 'from_status' => 'in_progress', 'to_status' => $status,
    ]);
    expect($this->current->fresh()->checked_in_at !== null)->toBe(in_array($action, ['completed', 'waiting']));
})->with([['completed', 'completed'], ['waiting', 'scheduled'], ['cancelled', 'cancelled'], ['no_show', 'no_show']]);

it('reconfirms a stale outgoing patient instead of changing a different consultation', function () {
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'completed', 'previous_appointment_id' => $this->next->id,
    ])->assertRedirect(route('appointments.switch-current', $this->next));
    expect($this->current->fresh()->status)->toBe('in_progress');
});

it('does not change patients for an invalid action or incomplete reschedule', function ($payload) {
    $this->post(route('appointments.set-current', $this->next), $payload + [
        'previous_appointment_id' => $this->current->id,
    ])->assertSessionHasErrors();
    expect($this->current->fresh()->status)->toBe('in_progress')
        ->and($this->next->fresh()->status)->toBe('scheduled');
})->with([[['previous_action' => 'delete']], [['previous_action' => 'reschedule']]]);

it('reschedules the previous patient before starting the next one', function () {
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'reschedule', 'previous_appointment_id' => $this->current->id,
        'reschedule_date' => '2026-06-11', 'reschedule_time' => '11:00',
    ])->assertSessionHasNoErrors()->assertSessionMissing('error');
    expect($this->current->fresh()->status)->toBe('scheduled')
        ->and($this->current->fresh()->appointment_date->toDateString())->toBe('2026-06-11')
        ->and($this->current->fresh()->checked_in_at)->toBeNull()
        ->and($this->next->fresh()->status)->toBe('in_progress');
});

it('rolls back both patients when the rescheduled slot is unavailable', function () {
    Appointment::factory()->create([
        'doctor_id' => $this->doctor->id, 'appointment_date' => '2026-06-11', 'appointment_time' => '11:00:00',
    ]);
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'reschedule', 'previous_appointment_id' => $this->current->id,
        'reschedule_date' => '2026-06-11', 'reschedule_time' => '11:00',
    ])->assertSessionHas('error');
    expect($this->current->fresh()->status)->toBe('in_progress')
        ->and($this->next->fresh()->status)->toBe('scheduled');
});

it('refuses to mark a clinically documented patient absent', function () {
    MedicalRecord::create([
        'patient_id' => $this->current->patient_id, 'doctor_id' => $this->doctor->id,
        'appointment_id' => $this->current->id, 'visit_date' => today()->toDateString(),
    ]);
    $this->get(route('appointments.switch-current', $this->next))->assertDontSee('value="no_show"', false);
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'no_show', 'previous_appointment_id' => $this->current->id,
    ])->assertSessionHas('error');
    expect($this->current->fresh()->status)->toBe('in_progress');
});

it('does not let another doctor view or resolve this switch', function () {
    $this->actingAs(User::factory()->doctor()->create());
    $this->get(route('appointments.switch-current', $this->next))->assertForbidden();
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'completed', 'previous_appointment_id' => $this->current->id,
    ])->assertForbidden();
});

it('does not change anything when the confirmation is dismissed', function () {
    $this->get(route('appointments.switch-current', $this->next))->assertOk();
    $this->get(route('appointments.show', $this->current))->assertOk();
    expect($this->current->fresh()->status)->toBe('in_progress')
        ->and($this->next->fresh()->status)->toBe('scheduled');
});

it('does not automatically mark arrived waiting patients absent', function () {
    $arrived = Appointment::factory()->create([
        'appointment_date' => '2026-06-09', 'checked_in_at' => '2026-06-09 09:00:00',
    ]);
    $absent = Appointment::factory()->create(['appointment_date' => '2026-06-09']);
    $this->artisan('appointments:mark-no-shows')->assertSuccessful();
    expect($arrived->fresh()->status)->toBe('scheduled')
        ->and($absent->fresh()->status)->toBe('no_show');
});

it('blocks an edit-form switch without silently returning the previous patient to waiting', function () {
    $this->put(route('appointments.update', $this->next), [
        'patient_id' => $this->next->patient_id, 'doctor_id' => $this->doctor->id,
        'appointment_date' => '2026-06-10', 'appointment_time' => '10:00', 'status' => 'in_progress',
    ])->assertSessionHasErrors('status');
    expect($this->current->fresh()->status)->toBe('in_progress')
        ->and($this->next->fresh()->status)->toBe('scheduled');
});

it('allows the receptionist to resolve a switch', function () {
    $this->actingAs(User::factory()->receptionist()->create());
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'completed', 'previous_appointment_id' => $this->current->id,
    ])->assertRedirect(route('appointments.show', $this->next));
    expect($this->current->fresh()->status)->toBe('completed');
});

it('is harmless when the confirmed switch is submitted twice', function () {
    $payload = ['previous_action' => 'completed', 'previous_appointment_id' => $this->current->id];
    $this->post(route('appointments.set-current', $this->next), $payload);
    $this->post(route('appointments.set-current', $this->next), $payload);
    expect($this->current->fresh()->status)->toBe('completed')
        ->and($this->next->fresh()->status)->toBe('in_progress');
    expect($this->current->statusLogs()->where('to_status', 'completed')->count())->toBe(1);
});

it('reschedules queue patients with a new ticket and recalculated estimates', function () {
    $this->doctor->update(['appointment_mode' => 'queue', 'queue_daily_limit' => 10]);
    $this->current->update(['appointment_mode' => 'queue', 'queue_number' => 1, 'queue_position' => 1]);
    $this->next->update(['appointment_mode' => 'queue', 'queue_number' => 2, 'queue_position' => 2]);
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'reschedule', 'previous_appointment_id' => $this->current->id,
        'reschedule_date' => '2026-06-11', 'reschedule_time' => '11:00',
    ])->assertSessionMissing('error');
    expect($this->current->fresh()->appointment_date->toDateString())->toBe('2026-06-11')
        ->and($this->current->fresh()->queue_number)->toBe(1)
        ->and($this->current->fresh()->estimated_time)->not->toBeNull()
        ->and($this->next->fresh()->status)->toBe('in_progress');
});

it('leaves both patients unchanged for a past time or closed day', function ($date, $time) {
    \App\Models\Setting::set('sunday_working', false, 'boolean');
    $this->post(route('appointments.set-current', $this->next), [
        'previous_action' => 'reschedule', 'previous_appointment_id' => $this->current->id,
        'reschedule_date' => $date, 'reschedule_time' => $time,
    ])->assertSessionHas('error');
    expect($this->current->fresh()->status)->toBe('in_progress')
        ->and($this->next->fresh()->status)->toBe('scheduled');
})->with([['2026-06-10', '09:00'], ['2026-06-14', '11:00']]);

it('renders the switch in French', function () {
    $this->withSession(['locale' => 'fr'])->get(route('appointments.switch-current', $this->next))
        ->assertOk()->assertSee('Changer de patient en cours');
});
