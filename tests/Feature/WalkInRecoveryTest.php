<?php

use App\Models\Setting;
use App\Models\User;

it('keeps walk-in booking intent and selected time after a patient validation error', function (string $role) {
    Setting::set('clinic_specialty', 'pediatrics', 'string');
    $this->actingAs(User::factory()->create(['role' => $role]))->from('/patients/create')->post('/patients', [
        'first_name' => 'Newborn', 'last_name' => 'Audit', 'birth_date' => today()->toDateString(),
        'primary_guardian' => 0, 'guardians' => [['name' => 'Synthetic Parent', 'relationship' => 'Mother', 'phone' => '123']],
        'book_today' => 1, 'appointment_date' => today()->toDateString(), 'appointment_time' => '14:30',
        'appointment_reason' => 'Keep this booking intention',
    ])->assertSessionHasErrors('guardians.0.phone');
    $page = $this->get('/patients/create')->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($page->getContent());
    $xpath = new DOMXPath($dom);
    expect($xpath->query('//input[@id="book_today" and @checked]')->length)->toBe(1)
        ->and($dom->getElementById('appointment_time')->getAttribute('value'))->toBe('14:30')
        ->and($dom->getElementById('appointment_reason')->getAttribute('value'))->toBe('Keep this booking intention');
    $this->assertDatabaseMissing('patients', ['first_name' => 'Newborn', 'last_name' => 'Audit']);
})->with(['doctor', 'receptionist']);

it('uses the selected walk-in doctor mode and queues registration without a fixed time', function (string $role) {
    $this->travelTo(\Carbon\Carbon::parse('2026-06-10 10:00:00'));
    $doctor = User::factory()->doctor()->queueMode()->create();
    $actor = $role === 'doctor' ? $doctor : User::factory()->receptionist()->create();
    $this->actingAs($actor)->get('/patients/create')->assertOk();
    $this->post('/patients', [
        'first_name' => 'Queuewalkin', 'last_name' => 'Audit', 'id_card_number' => 'QUEUE-WALKIN',
        'phone' => '+212600000066', 'book_today' => 1, 'appointment_date' => today()->toDateString(),
    ])->assertSessionHasNoErrors();
    $visit = \App\Models\Appointment::firstOrFail();
    expect($visit->doctor_id)->toBe($doctor->id)
        ->and($visit->appointment_mode)->toBe('queue')
        ->and($visit->queue_number)->toBe(1)
        ->and($visit->checked_in_at)->not->toBeNull();
})->with(['doctor', 'receptionist']);
