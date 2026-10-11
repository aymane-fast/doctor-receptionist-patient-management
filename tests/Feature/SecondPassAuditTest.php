<?php

use App\Models\{Appointment, MedicalRecord, Patient, Setting, User};
use App\Services\{AppointmentSchedulingService, PatientSearchService};
use Illuminate\Support\Facades\Http;

it('rejects clinical values that exceed database storage bounds', function (string $field, mixed $value) {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor)->postJson('/medical-records', [
        'patient_id' => $patient->id, 'visit_date' => today()->toDateString(), $field => $value,
    ])->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    ['weight', 1000], ['height', 1000], ['temperature', 1000], ['heart_rate', 2147483648],
    ['blood_pressure', str_repeat('x', 256)], ['notes', str_repeat('é', 16001)],
]);

it('rejects overflowing prescription duration without partial creation', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor)->postJson('/prescriptions', [
        'patient_id' => $patient->id, 'prescribed_date' => today()->toDateString(),
        'items' => [['medication_name' => 'Test', 'dosage' => '1', 'frequency' => 'Daily', 'duration_days' => 2147483648]],
    ])->assertUnprocessable()->assertJsonValidationErrors('items.0.duration_days');
    $this->assertDatabaseCount('prescriptions', 0);
});

it('treats whitespace-only autocomplete as an empty query', function () {
    Patient::factory()->count(3)->create();
    expect(PatientSearchService::search('  '))->toHaveCount(0);
});

it('allows an omitted optional follow-up reason', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'status' => 'in_progress']);
    $this->actingAs($doctor)->post(route('appointments.create-follow-up', $appointment->patient_id), [
        'current_appointment_id' => $appointment->id, 'follow_up_number' => 1, 'follow_up_unit' => 'weeks',
    ])->assertSessionHasNoErrors()->assertSessionHas('success');
    expect(Appointment::count())->toBe(2);
});

it('does not create two consultation records for one linked visit', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today()]);
    MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id, 'visit_date' => today()]);
    $this->actingAs($doctor)->postJson('/medical-records', [
        'patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id, 'visit_date' => today()->toDateString(),
    ])->assertUnprocessable()->assertJsonValidationErrors('appointment_id');
    expect(MedicalRecord::count())->toBe(1);
});

it('does not reopen appointments in the past through a status action', function (string $mode) {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => $mode, 'appointment_date' => today()->subDay(), 'status' => 'cancelled']);
    $this->actingAs($doctor)->patch('/appointments/'.$appointment->id.'/status', ['status' => 'scheduled'])->assertSessionHas('error');
    expect($appointment->fresh()->status)->toBe('cancelled');
})->with(['scheduled', 'queue']);

it('does not reopen a duplicate active same-day patient visit', function (string $mode) {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => $mode, 'appointment_date' => today()->addDay(), 'appointment_time' => '10:00', 'status' => 'cancelled']);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $appointment->patient_id, 'appointment_mode' => $mode, 'appointment_date' => today()->addDay(), 'appointment_time' => '11:00']);
    $this->actingAs($doctor)->patch('/appointments/'.$appointment->id.'/status', ['status' => 'scheduled'])->assertSessionHas('error');
    expect($appointment->fresh()->status)->toBe('cancelled');
})->with(['scheduled', 'queue']);

it('does not reopen queue tickets onto a closed day', function () {
    $doctor = User::factory()->doctor()->create();
    $date = today()->addDay();Setting::set(strtolower($date->format('l')).'_working', false, 'boolean');
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue', 'appointment_date' => $date, 'status' => 'cancelled']);
    $this->actingAs($doctor)->patch('/appointments/'.$appointment->id.'/status', ['status' => 'scheduled'])->assertSessionHas('error');
    expect($appointment->fresh()->status)->toBe('cancelled');
});

it('handles an AI transport connection exception as a safe failure', function () {
    $doctor = User::factory()->doctor()->create();$patient = Patient::factory()->create();
    config(['services.groq.api_key' => 'audit-not-real']);
    Http::preventStrayRequests();Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('Audit network failure'));
    $this->actingAs($doctor)->getJson('/patients/'.$patient->id.'/ai-summary')->assertOk()->assertJson(['success' => false]);
});

it('rechecks duplicate patients inside scheduled booking service', function () {
    $doctor = User::factory()->doctor()->create();$patient = Patient::factory()->create();
    $data = ['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'appointment_date' => today()->addDay()->toDateString(), 'appointment_time' => '10:00', 'status' => 'scheduled'];
    $service = app(AppointmentSchedulingService::class);$service->createScheduledAppointment($data);
    expect(fn () => $service->createScheduledAppointment(array_merge($data, ['appointment_time' => '11:00'])))->toThrow(InvalidArgumentException::class);
    expect(Appointment::count())->toBe(1);
});

it('retains patient search filters across pagination', function () {
    $doctor = User::factory()->doctor()->create();Patient::factory()->count(16)->create(['last_name' => 'PassTwo']);
    $this->actingAs($doctor)->get('/patients?search=PassTwo')->assertOk()
        ->assertViewHas('patients', fn ($patients) => str_contains($patients->nextPageUrl(), 'search=PassTwo'));
});

it('retains every medication and selected patient after a failed prescription submission', function () {
    $doctor = User::factory()->doctor()->create();$patient = Patient::factory()->create();
    $this->actingAs($doctor)->from('/prescriptions/create')->post('/prescriptions', [
        'patient_id' => $patient->id, 'prescribed_date' => today()->addDay()->toDateString(),
        'items' => [['medication_name' => 'Retained A', 'dosage' => '5 mg', 'frequency' => 'Daily'], ['medication_name' => 'Retained B', 'dosage' => '10 mg', 'frequency' => 'Nightly']],
    ])->assertSessionHasErrors('prescribed_date');
    $this->get('/prescriptions/create')->assertOk()->assertSee('Retained A')->assertSee('Retained B')->assertSee($patient->full_name);
});

it('rejects malformed operational command dates without changing appointments', function (string $command, string $date) {
    $appointment = Appointment::factory()->create(['appointment_date' => today()->addDay()]);
    $this->artisan($command, ['--date' => $date])->assertFailed();
    expect($appointment->fresh()->status)->toBe('scheduled');
})->with([
    ['appointments:send-reminders', 'nonsense'], ['appointments:send-reminders', '2026-02-30'],
    ['appointments:mark-no-shows', 'nonsense'], ['appointments:mark-no-shows', '2099-01-01'],
]);

it('continues a reminder batch after transport failure and leaves only failed mail retryable', function () {
    $first = Appointment::factory()->create(['appointment_date' => today()->addDay()]);
    $second = Appointment::factory()->create(['appointment_date' => today()->addDay()]);
    $first->patient->update(['email' => 'failed@audit.invalid']);$second->patient->update(['email' => 'good@audit.invalid']);
    $failed = \Mockery::mock(\Illuminate\Mail\PendingMail::class);
    $failed->shouldReceive('send')->once()->andThrow(new \RuntimeException('Synthetic SMTP failure'));
    $good = \Mockery::mock(\Illuminate\Mail\PendingMail::class);$good->shouldReceive('send')->once();
    $retry = \Mockery::mock(\Illuminate\Mail\PendingMail::class);$retry->shouldReceive('send')->once();
    \Illuminate\Support\Facades\Mail::shouldReceive('to')->with('failed@audit.invalid')->twice()->andReturn($failed, $retry);
    \Illuminate\Support\Facades\Mail::shouldReceive('to')->with('good@audit.invalid')->once()->andReturn($good);
    $this->artisan('appointments:send-reminders')->assertSuccessful();
    expect($first->fresh()->reminder_sent_at)->toBeNull()->and($second->fresh()->reminder_sent_at)->not->toBeNull();
    $this->artisan('appointments:send-reminders')->assertSuccessful();
    expect($first->fresh()->reminder_sent_at)->not->toBeNull();
});

it('preserves working-hour input after invalid closing time', function () {
    $doctor = User::factory()->doctor()->create();
    $this->actingAs($doctor)->from('/settings')->post('/settings/working-hours', [
        'working_hours_form' => 1, 'monday_working' => 1, 'monday_start' => '15:00', 'monday_end' => '14:00',
    ])->assertSessionHasErrors('monday_end');
    $this->get('/settings')->assertOk()->assertSee('value="15:00"', false)->assertSee('value="14:00"', false);
});

it('can render malformed old medication inputs without crashing', function () {
    $doctor = User::factory()->doctor()->create();$patient = Patient::factory()->create();
    $this->actingAs($doctor)->from('/prescriptions/create')->post('/prescriptions', [
        'patient_id' => $patient->id, 'prescribed_date' => today()->toDateString(), 'items' => [['medication_name' => ['invalid'], 'dosage' => ['invalid'], 'frequency' => ['invalid']]],
    ])->assertSessionHasErrors();
    $this->get('/prescriptions/create')->assertOk();
});

it('keeps unrelated dashboard filters when paging appointments', function () {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->count(6)->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'status' => 'scheduled']);
    $this->actingAs($doctor)->get('/dashboard?status_filter=scheduled&record_patient_search=Other')
        ->assertOk()->assertViewHas('todayAppointments', fn ($appointments) => str_contains($appointments->nextPageUrl(), 'record_patient_search=Other') && str_contains($appointments->nextPageUrl(), 'status_filter=scheduled'));
});

it('recovers the creation screen after a malformed patient selector', function (string $resource) {
    $doctor = User::factory()->doctor()->create();$patient = Patient::factory()->create();
    $this->actingAs($doctor)->from('/'.$resource.'/create')->post('/'.$resource, [
        'patient_id' => [$patient->id], 'appointment_date' => today()->addDay()->toDateString(), 'appointment_time' => '10:00',
        'visit_date' => today()->toDateString(), 'prescribed_date' => today()->toDateString(),
        'items' => [['medication_name' => 'Test', 'dosage' => '1', 'frequency' => 'Daily']],
    ])->assertSessionHasErrors('patient_id');
    $this->get('/'.$resource.'/create')->assertOk();
})->with(['appointments', 'medical-records', 'prescriptions']);
