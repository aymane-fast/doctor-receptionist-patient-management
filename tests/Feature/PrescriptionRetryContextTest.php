<?php

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;

it('keeps explicit patient and consultation choices after a rejected prescription mismatch', function (string $entry) {
    $doctor = User::factory()->doctor()->create();
    $recordPatient = Patient::factory()->create();
    $chosenPatient = Patient::factory()->create();
    $record = MedicalRecord::create(['patient_id' => $recordPatient->id, 'doctor_id' => $doctor->id, 'visit_date' => today()]);
    $origin = $entry === 'linked' ? route('prescriptions.create', ['patient_id' => $recordPatient->id, 'medical_record_id' => $record->id]) : route('prescriptions.create');
    $this->actingAs($doctor)->from($origin)->post(route('prescriptions.store'), [
        'patient_id' => $chosenPatient->id, 'medical_record_id' => $record->id, 'prescribed_date' => today()->toDateString(),
        'items' => [['medication_name' => 'Synthetic retained medicine', 'dosage' => 'Test dose', 'frequency' => 'Test frequency']],
    ])->assertSessionHasErrors('patient_id');
    $this->get($origin)->assertOk()
        ->assertViewHas('selectedPatient', fn ($patient) => $patient->id === $chosenPatient->id)
        ->assertViewHas('selectedRecord', fn ($selected) => $selected->id === $record->id)
        ->assertViewHas('medicalRecords', fn ($records) => $records->contains('id', $record->id))
        ->assertSee('Synthetic retained medicine');
    $this->assertDatabaseCount('prescriptions', 0);
})->with(['plain', 'linked']);

it('does not restore a cleared patient from the original consultation link after rejection', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $record = MedicalRecord::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'visit_date' => today()]);
    $origin = route('prescriptions.create', ['patient_id' => $patient->id, 'medical_record_id' => $record->id]);
    $this->actingAs($doctor)->from($origin)->post(route('prescriptions.store'), [
        'patient_id' => null, 'medical_record_id' => $record->id, 'prescribed_date' => today()->toDateString(),
        'items' => [['medication_name' => 'Retained medicine', 'dosage' => 'Test dose', 'frequency' => 'Test frequency']],
    ])->assertSessionHasErrors('patient_id');
    $this->get($origin)->assertOk()->assertViewHas('selectedPatient', null)->assertSee('Retained medicine');
    $this->assertDatabaseCount('prescriptions', 0);
});

it('still prefills the record patient on an initial consultation link', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $record = MedicalRecord::create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'visit_date' => today()]);
    $this->actingAs($doctor)->get(route('prescriptions.create', ['medical_record_id' => $record->id]))
        ->assertOk()->assertViewHas('selectedPatient', fn ($selected) => $selected->id === $patient->id);
});
