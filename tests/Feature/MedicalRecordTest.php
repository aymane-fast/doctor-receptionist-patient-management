<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;

it('blocks receptionists from medical records', function () {
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->get('/medical-records')->assertForbidden();
    $this->actingAs($receptionist)->get('/medical-records/create')->assertForbidden();
    $this->actingAs($receptionist)->post('/medical-records', [])->assertForbidden();
});

it('saves a medical record without prematurely completing the linked appointment', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'status' => 'in_progress',
    ]);

    $this->actingAs($doctor)->post('/medical-records', [
        'patient_id' => $appointment->patient_id,
        'appointment_id' => $appointment->id,
        'visit_date' => now()->toDateString(),
        'symptoms' => 'Cough',
        'diagnosis' => 'Cold',
        'treatment' => 'Rest',
    ])->assertSessionHasNoErrors();

    $record = MedicalRecord::first();

    expect($record)->not->toBeNull()
        ->and($record->doctor_id)->toBe($doctor->id)
        ->and($record->patient_id)->toBe($appointment->patient_id)
        ->and($appointment->fresh()->status)->toBe('in_progress');
});

it('rejects a future visit date', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)->from('/medical-records/create')->post('/medical-records', [
        'patient_id' => $patient->id,
        'visit_date' => now()->addDays(3)->toDateString(),
    ])->assertSessionHasErrors('visit_date');
});

it('prevents a doctor from using another doctors appointment', function () {
    $doctorA = User::factory()->doctor()->create();
    $doctorB = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create(['doctor_id' => $doctorA->id]);

    $this->actingAs($doctorB)->post('/medical-records', [
        'patient_id' => $appointment->patient_id,
        'appointment_id' => $appointment->id,
        'visit_date' => now()->toDateString(),
    ])->assertNotFound();
});

it('hides other doctors records', function () {
    $doctorA = User::factory()->doctor()->create();
    $doctorB = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $record = MedicalRecord::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctorA->id,
        'visit_date' => now()->toDateString(),
        'diagnosis' => 'Private',
    ]);

    $this->actingAs($doctorB)->get(route('medical-records.show', $record))->assertForbidden();
    $this->actingAs($doctorB)->get(route('medical-records.edit', $record))->assertForbidden();
    $this->actingAs($doctorB)->delete(route('medical-records.destroy', $record))->assertForbidden();
    $this->actingAs($doctorA)->get(route('medical-records.show', $record))->assertOk();
});

it('updates a medical record', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $record = MedicalRecord::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'visit_date' => now()->toDateString(),
        'diagnosis' => 'Initial',
    ]);

    $this->actingAs($doctor)->put(route('medical-records.update', $record), [
        'patient_id' => $patient->id,
        'visit_date' => now()->toDateString(),
        'diagnosis' => 'Revised diagnosis',
    ])->assertRedirect(route('medical-records.show', $record));

    expect($record->fresh()->diagnosis)->toBe('Revised diagnosis');
});
