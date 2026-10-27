<?php

use App\Models\Appointment;
use App\Models\User;

it('preserves a no-show when either role opens and saves an ordinary correction', function (string $role) {
    $doctor = User::factory()->doctor()->create();
    $actor = $role === 'doctor' ? $doctor : User::factory()->receptionist()->create();
    $visit = Appointment::factory()->create([
        'doctor_id' => $doctor->id, 'status' => 'no_show',
        'appointment_mode' => 'scheduled', 'appointment_date' => today()->subDay(),
        'appointment_time' => '10:00', 'checked_in_at' => null,
    ]);
    $response = $this->actingAs($actor)->get(route('appointments.edit', $visit))->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($response->getContent());
    $selected = (new DOMXPath($dom))->query('//select[@name="status"]/option[@selected]');
    expect($selected->length)->toBe(1);
    $status = $selected->item(0)->getAttribute('value');
    expect($status)->toBe('no_show');

    $this->put(route('appointments.update', $visit), [
        'patient_id' => $visit->patient_id, 'doctor_id' => $doctor->id,
        'appointment_date' => $visit->appointment_date->toDateString(),
        'appointment_time' => '10:00', 'status' => $status,
        'notes' => 'Corrected contact instructions only',
    ])->assertSessionHasNoErrors();
    expect($visit->fresh()->status)->toBe('no_show')
        ->and($visit->fresh()->notes)->toBe('Corrected contact instructions only')
        ->and($visit->fresh()->checked_in_at)->toBeNull();
})->with(['doctor', 'receptionist']);
