<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;

it('combines patient selection and inclusive date ranges without leaking another doctors records', function ($kind, $field, $model, $viewKey) {
    $doctor = User::factory()->doctor()->create();
    $otherDoctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create(['first_name' => 'Nadia', 'last_name' => 'Filter']);
    $otherPatient = Patient::factory()->create();
    $data = ['patient_id' => $patient->id, 'doctor_id' => $doctor->id];
    $first = $model::create($data + [$field => '2026-10-01']);
    $last = $model::create($data + [$field => '2026-10-12']);
    $model::create($data + [$field => '2026-09-30']);
    $model::create(['patient_id' => $patient->id, 'doctor_id' => $otherDoctor->id, $field => '2026-10-05']);
    $model::create(['patient_id' => $otherPatient->id, 'doctor_id' => $doctor->id, $field => '2026-10-05']);
    $this->actingAs($doctor)->get(route($kind.'.index', ['patient_id' => $patient->id, 'search' => 'Nadia',
        'from_date' => '2026-10-01', 'to_date' => '2026-10-12']))->assertOk()
        ->assertViewHas($viewKey, fn ($rows) => $rows->pluck('id')->all() === [$last->id, $first->id]);
    $this->get(route($kind.'.index', ['from_date' => '2026-10-12', 'to_date' => '2026-10-01']))->assertSessionHasErrors('to_date');
    $this->get(route($kind.'.index', ['from_date' => 'not-a-date']))->assertSessionHasErrors('from_date');
    $this->get(route($kind.'.index', ['patient_id' => $patient->id, 'to_date' => '2026-09-29']))->assertOk()
        ->assertViewHas($viewKey, fn ($rows) => $rows->isEmpty());
})->with([
    ['medical-records', 'visit_date', MedicalRecord::class, 'medicalRecords'],
    ['prescriptions', 'prescribed_date', Prescription::class, 'prescriptions'],
]);

it('keeps active patients from both booking modes visible after a settings change', function ($role) {
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'scheduled']);
    $actor = $role === 'doctor' ? $doctor : User::factory()->receptionist()->create();
    $scheduled = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'appointment_mode' => 'scheduled', 'status' => 'scheduled']);
    $queue = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'appointment_mode' => 'queue', 'queue_number' => 1, 'queue_position' => 1, 'status' => 'scheduled']);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today(), 'status' => 'completed']);
    Appointment::factory()->create(['appointment_date' => today(), 'status' => 'scheduled']);
    $this->actingAs($actor);
    foreach (['scheduled', 'queue'] as $mode) {
        $doctor->update(['appointment_mode' => $mode]);
        $this->get(route('appointments.queue', ['doctor_id' => $doctor->id]))->assertOk()
            ->assertViewHas('queueAppointments', fn ($rows) => $rows->pluck('id')->sort()->values()->all() === collect([$scheduled->id, $queue->id])->sort()->values()->all());
        $this->get(route('appointments.index', ['doctor_id' => $doctor->id, 'show_today' => 1]))->assertOk()
            ->assertViewHas('appointments', fn ($rows) => $rows->contains('id', $scheduled->id) && $rows->contains('id', $queue->id));
    }
    expect($scheduled->fresh()->appointment_mode)->toBe('scheduled')
        ->and($queue->fresh()->appointment_mode)->toBe('queue')->and($queue->fresh()->queue_number)->toBe(1);
})->with(['doctor', 'receptionist']);

it('renders the tabbed workspace and recovered notes in both languages', function ($locale) {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'in_progress']);
    $errors = new \Illuminate\Support\ViewErrorBag;
    $errors->put('default', new \Illuminate\Support\MessageBag(['temperature' => 'Please check this value.']));
    $this->actingAs($doctor)->withSession(['locale' => $locale, 'errors' => $errors,
        '_old_input' => ['workspace_form' => 'consultation', 'symptoms' => 'Recovered draft notes'],
    ])->get(route('doctor.current'))->assertOk()
        ->assertSee('data-initial-tab="consultation"', false)->assertSee('Recovered draft notes')
        ->assertSee('data-workspace-tab="history"', false)
        ->assertSee('data-workspace-tab="prescription"', false)
        ->assertSee('data-workspace-tab="examinations"', false)
        ->assertSee('data-workspace-tab="documents"', false)
        ->assertSee(__('clinical_ui.finish_visit'))->assertDontSee('clinical_ui.');
    $english = require resource_path('lang/en/clinical_ui.php');
    $french = require resource_path('lang/fr/clinical_ui.php');
    expect(array_keys($french))->toBe(array_keys($english));
})->with(['en', 'fr']);

it('keeps follow-up validation feedback and entered values inside the open dialog', function () {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'in_progress']);
    $errors = new \Illuminate\Support\ViewErrorBag;
    $errors->put('default', new \Illuminate\Support\MessageBag(['follow_up_number' => 'Follow-up needs correction.']));
    $response = $this->actingAs($doctor)->withSession(['errors' => $errors,
        '_old_input' => ['workspace_form' => 'follow-up', 'follow_up_number' => 4, 'follow_up_unit' => 'weeks'],
    ])->get(route('doctor.current'))->assertOk()->assertSee('data-initial-tab="follow-up"', false);
    $dom = new \DOMDocument;
    @$dom->loadHTML($response->getContent());
    $xpath = new \DOMXPath($dom);
    expect($xpath->evaluate('string(//dialog[@id="follow-up-dialog"]//*[@role="alert"])'))->toContain('Follow-up needs correction.')
        ->and($xpath->evaluate('string(//input[@id="follow_up_number"]/@value)'))->toBe('4')
        ->and($xpath->evaluate('string(//select[@id="follow_up_unit"]/option[@selected]/@value)'))->toBe('weeks');
});
