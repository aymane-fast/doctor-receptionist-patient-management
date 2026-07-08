<?php

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;

it('blocks receptionists from prescriptions', function () {
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->get('/prescriptions')->assertForbidden();
    $this->actingAs($receptionist)->post('/prescriptions', [])->assertForbidden();
});

it('creates a prescription with medication items', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)->post('/prescriptions', [
        'patient_id' => $patient->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            ['medication_name' => 'Amoxicillin', 'dosage' => '500mg', 'frequency' => 'Three times daily', 'duration_days' => 7],
            ['medication_name' => 'Paracetamol', 'dosage' => '1g', 'frequency' => 'As needed'],
        ],
        'notes' => 'Take with food',
    ])->assertSessionHasNoErrors();

    $prescription = Prescription::with('items')->first();

    expect($prescription)->not->toBeNull()
        ->and($prescription->items)->toHaveCount(2)
        ->and($prescription->medication_name)->toBeNull()
        ->and($prescription->items[0]->medication_name)->toBe('Amoxicillin');
});

it('requires at least one medication item', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)->from('/prescriptions/create')->post('/prescriptions', [
        'patient_id' => $patient->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [],
    ])->assertSessionHasErrors('items');
});

it('updates a prescription and replaces its items', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $prescription = Prescription::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);
    $prescription->items()->create([
        'medication_name' => 'Old Med',
        'dosage' => '5mg',
        'frequency' => 'Once daily',
    ]);

    $this->actingAs($doctor)->put(route('prescriptions.update', $prescription), [
        'patient_id' => $patient->id,
        'prescribed_date' => now()->toDateString(),
        'items' => [
            ['medication_name' => 'New Med A', 'dosage' => '10mg', 'frequency' => 'Twice daily', 'duration_days' => 14],
            ['medication_name' => 'New Med B', 'dosage' => '20mg', 'frequency' => 'Once daily'],
        ],
        'notes' => 'Updated regimen',
    ])->assertRedirect(route('prescriptions.show', $prescription));

    $prescription->refresh()->load('items');

    expect($prescription->items)->toHaveCount(2)
        ->and($prescription->items->pluck('medication_name')->all())->toBe(['New Med A', 'New Med B'])
        ->and($prescription->notes)->toBe('Updated regimen');
});

it('hides other doctors prescriptions', function () {
    $doctorA = User::factory()->doctor()->create();
    $doctorB = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $prescription = Prescription::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctorA->id,
        'prescribed_date' => now()->toDateString(),
    ]);

    $this->actingAs($doctorB)->get(route('prescriptions.show', $prescription))->assertForbidden();
    $this->actingAs($doctorB)->get(route('prescriptions.edit', $prescription))->assertForbidden();
    $this->actingAs($doctorB)->delete(route('prescriptions.destroy', $prescription))->assertForbidden();
});

it('renders the print view', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $prescription = Prescription::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'prescribed_date' => now()->toDateString(),
    ]);
    $prescription->items()->create([
        'medication_name' => 'Printable Med',
        'dosage' => '5mg',
        'frequency' => 'Once daily',
    ]);

    $this->actingAs($doctor)
        ->get(route('prescriptions.print', $prescription))
        ->assertOk()
        ->assertSee('Printable Med');
});

it('renders the edit form for legacy single-medication prescriptions', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $prescription = Prescription::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'prescribed_date' => now()->toDateString(),
        'medication_name' => 'Legacy Med',
        'dosage' => '10mg',
        'frequency' => 'Twice daily',
        'duration_days' => 30,
    ]);

    $this->actingAs($doctor)
        ->get(route('prescriptions.edit', $prescription))
        ->assertOk()
        ->assertSee('Legacy Med');
});
