<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;

it('renders human translation labels as strings in both supported locales', function (string $locale) {
    app()->setLocale($locale);
    foreach (['Patients', 'Prescriptions', 'Appointments', 'Statistics', 'Dashboard'] as $label) {
        expect(__($label))->toBeString();
    }
})->with(['en', 'fr']);

it('honors appointment mode as a standalone listing filter', function () {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue']);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'scheduled']);
    $this->actingAs($doctor)->get('/appointments?appointment_mode=queue')->assertOk()
        ->assertViewHas('hasSearchQuery', true)
        ->assertViewHas('appointments', fn ($items) => $items->count() === 1 && $items->first()->appointment_mode === 'queue');
});

it('honors explicit false working-day settings without requiring closed-day times', function () {
    $doctor = User::factory()->doctor()->create();
    $this->actingAs($doctor)->postJson('/settings/working-hours', ['monday_working' => false])
        ->assertSessionHasNoErrors();
    expect(Setting::getWorkingHours('monday')['is_working'])->toBeFalse();
});

it('rejects structured patient selectors on every clinical creation screen', function (string $url) {
    $doctor = User::factory()->doctor()->create();
    Patient::factory()->create();
    $this->actingAs($doctor)->getJson($url)->assertUnprocessable();
})->with(['/appointments/create?patient_id[]=1', '/medical-records/create?patient_id[]=1',
    '/medical-records/create?appointment_id[]=1', '/prescriptions/create?patient_id[]=1']);

it('finishes the current consultation and promotes an arrived patient when saving its medical record', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-12 10:00'));
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        Setting::set($day.'_working', true, 'boolean');
        Setting::set($day.'_start', '09:00', 'time');
        Setting::set($day.'_end', '17:00', 'time');
    }
    $doctor = User::factory()->doctor()->create();
    $current = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(),
        'appointment_time' => '09:00', 'status' => 'in_progress', 'checked_in_at' => now()]);
    $next = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(),
        'appointment_time' => '10:00', 'status' => 'scheduled', 'checked_in_at' => now()]);
    $this->actingAs($doctor)->post('/medical-records', ['patient_id' => $current->patient_id,
        'appointment_id' => $current->id, 'visit_date' => today()->toDateString(), 'diagnosis' => 'Audit'])
        ->assertSessionHasNoErrors();
    expect($current->fresh()->status)->toBe('in_progress')->and($next->fresh()->status)->toBe('scheduled');
    $this->post(route('appointments.mark-current-done'), ['appointment_id' => $current->id])->assertSessionHasNoErrors();
    expect($current->fresh()->status)->toBe('completed')->and($next->fresh()->status)->toBe('in_progress');
});

it('does not silently move a prescribed medical record to a different patient', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $other = Patient::factory()->create();
    $record = MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'visit_date' => today()]);
    Prescription::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'medical_record_id' => $record->id, 'prescribed_date' => today()]);
    $this->actingAs($doctor)->putJson('/medical-records/'.$record->id, ['patient_id' => $other->id,
        'visit_date' => today()->toDateString()])->assertUnprocessable();
    expect($record->fresh()->patient_id)->toBe($patient->id);
});

it('rejects moving an active scheduled appointment into the past', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-12 12:00'));
    Setting::set('monday_working', true, 'boolean');
    Setting::set('monday_start', '09:00', 'time');
    Setting::set('monday_end', '17:00', 'time');
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'appointment_time' => '15:00']);
    $this->actingAs($doctor)->put('/appointments/'.$appointment->id, [
        'patient_id' => $appointment->patient_id, 'doctor_id' => $doctor->id, 'appointment_date' => today()->toDateString(),
        'appointment_time' => '09:00', 'status' => 'scheduled',
    ])->assertSessionHasErrors('appointment_time');
    expect($appointment->fresh()->appointment_time->format('H:i'))->toBe('15:00');
});

it('rejects moving a queue ticket onto a closed day', function () {
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'queue']);
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue']);
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        Setting::set($day.'_working', false, 'boolean');
    }
    $this->actingAs($doctor)->put('/appointments/'.$appointment->id, [
        'patient_id' => $appointment->patient_id, 'doctor_id' => $doctor->id,
        'appointment_date' => today()->addDay()->toDateString(), 'status' => 'scheduled',
    ])->assertSessionHasErrors('appointment_date');
});

it('does not block editing cancelled appointment notes merely because its former slot was reused', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'cancelled', 'appointment_date' => today()->addDay(), 'appointment_time' => '10:00']);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today()->addDay(), 'appointment_time' => '10:00']);
    $this->actingAs($doctor)->put('/appointments/'.$appointment->id, [
        'patient_id' => $appointment->patient_id, 'doctor_id' => $doctor->id,
        'appointment_date' => today()->addDay()->toDateString(), 'appointment_time' => '10:00', 'status' => 'cancelled', 'notes' => 'Corrected reason',
    ])->assertSessionHasNoErrors();
    expect($appointment->fresh()->notes)->toBe('Corrected reason');
});

it('rejects reopening a queue ticket once its daily capacity has been filled', function () {
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'queue', 'queue_daily_limit' => 1]);
    $cancelled = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue', 'status' => 'cancelled', 'appointment_date' => today()->addDay()]);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue', 'status' => 'scheduled', 'appointment_date' => today()->addDay()]);
    $this->actingAs($doctor)->patch('/appointments/'.$cancelled->id.'/status', ['status' => 'scheduled']);
    expect($cancelled->fresh()->status)->toBe('cancelled');
});

it('does not bypass queue capacity by reopening through the full edit form', function () {
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'queue', 'queue_daily_limit' => 1]);
    $cancelled = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue', 'status' => 'cancelled', 'appointment_date' => today()->addDay()]);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue', 'status' => 'scheduled', 'appointment_date' => today()->addDay()]);
    $this->actingAs($doctor)->put('/appointments/'.$cancelled->id, [
        'patient_id' => $cancelled->patient_id, 'doctor_id' => $doctor->id,
        'appointment_date' => today()->addDay()->toDateString(), 'status' => 'scheduled',
    ])->assertSessionHasErrors('appointment_date');
    expect($cancelled->fresh()->status)->toBe('cancelled');
});

it('advances the waiting room when completing a current appointment using the status button', function () {
    $doctor = User::factory()->doctor()->create();
    $current = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'status' => 'in_progress']);
    $next = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'status' => 'scheduled', 'checked_in_at' => now()]);
    $this->actingAs($doctor)->patch('/appointments/'.$current->id.'/status', ['status' => 'completed']);
    expect($next->fresh()->status)->toBe('in_progress');
});

it('persists all consultation fields exposed by the existing forms', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $this->actingAs($doctor)->post('/medical-records', ['patient_id' => $patient->id,
        'visit_date' => today()->toDateString(), 'visit_type' => 'emergency',
        'history_present_illness' => 'Persistent history', 'physical_examination' => 'Persistent exam'])
        ->assertSessionHasNoErrors();
    $record = MedicalRecord::sole();
    expect($record->visit_type)->toBe('emergency')->and($record->history_present_illness)->toBe('Persistent history')
        ->and($record->physical_examination)->toBe('Persistent exam');
    $this->actingAs($doctor)->put('/medical-records/'.$record->id, ['patient_id' => $patient->id,
        'visit_date' => today()->toDateString(), 'visit_type' => 'follow-up',
        'history_present_illness' => 'Revised history', 'physical_examination' => 'Revised exam'])->assertSessionHasNoErrors();
    expect($record->fresh()->visit_type)->toBe('follow-up')->and($record->fresh()->physical_examination)->toBe('Revised exam');
    $this->actingAs($doctor)->get('/medical-records/'.$record->id.'/edit')->assertOk()
        ->assertSee('value="'.today()->toDateString().'"', false);
});

it('patient-history links filter clinical lists to the selected patient', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $other = Patient::factory()->create();
    foreach ([$patient, $other] as $person) {
        MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $person->id, 'visit_date' => today()]);
        Prescription::create(['doctor_id' => $doctor->id, 'patient_id' => $person->id, 'prescribed_date' => today()]);
    }
    $this->actingAs($doctor)->get('/medical-records?patient_id='.$patient->id)->assertOk()
        ->assertViewHas('medicalRecords', fn ($items) => $items->count() === 1 && $items->first()->patient_id === $patient->id);
    $this->actingAs($doctor)->get('/prescriptions?patient_id='.$patient->id)->assertOk()
        ->assertViewHas('prescriptions', fn ($items) => $items->count() === 1 && $items->first()->patient_id === $patient->id);
});

it('rejects array-shaped foreign keys before they reach persistence', function (string $url, string $field) {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id]);
    $record = MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'visit_date' => today()]);
    $data = ['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'appointment_date' => today()->addDay()->toDateString(),
        'appointment_time' => '10:00', 'visit_date' => today()->toDateString(), 'prescribed_date' => today()->toDateString(),
        'items' => [['medication_name' => 'Audit', 'dosage' => '1', 'frequency' => 'Daily']]];
    $data[$field] = [$field === 'medical_record_id' ? $record->id : ($field === 'appointment_id' ? $appointment->id : $patient->id)];
    $response = $this->actingAs($doctor)->postJson($url, $data);
    $this->assertSame(422, $response->status(), $url.' '.$field.' should be a scalar ID');
})->with([
    ['/appointments', 'patient_id'], ['/medical-records', 'patient_id'], ['/medical-records', 'appointment_id'],
    ['/prescriptions', 'patient_id'], ['/prescriptions', 'medical_record_id'], ['/waitlist', 'patient_id'],
]);

it('validates receptionist doctor selectors instead of treating arrays or staff as doctors', function (string $url) {
    $receptionist = User::factory()->receptionist()->create();
    $this->actingAs($receptionist)->getJson($url)->assertUnprocessable();
})->with(['/appointments/queue?doctor_id[]=1', '/api/appointments/next-available-slot?doctor_id[]=1',
    '/appointments/queue?doctor_id=1', '/api/appointments/next-available-slot?doctor_id=1']);

it('handles optional AI provider success and failure without external traffic', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    config(['services.groq.api_key' => 'audit-only-fake-key']);
    \Illuminate\Support\Facades\Http::preventStrayRequests();
    \Illuminate\Support\Facades\Http::fake(['api.groq.com/*' => \Illuminate\Support\Facades\Http::sequence()
        ->push(['choices' => [['message' => ['content' => 'Audit summary']]]], 200)
        ->push(['error' => 'Provider unavailable'], 503)
        ->push(['choices' => []], 200)]);
    $this->actingAs($doctor)->getJson('/patients/'.$patient->id.'/ai-summary')->assertOk()->assertJson(['success' => true, 'summary' => 'Audit summary']);
    $this->getJson('/patients/'.$patient->id.'/ai-summary')->assertOk()->assertJson(['success' => false]);
    $this->getJson('/patients/'.$patient->id.'/ai-summary')->assertOk()->assertJson(['success' => false]);
});
