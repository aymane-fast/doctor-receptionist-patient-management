<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-06-08 08:00:00'));
    Setting::setAppointmentDuration(15);
    Setting::setSetupComplete(true);
});

afterEach(function () {
    Carbon::setTestNow();
});

function makeClinicUser(string $role = 'doctor', array $attributes = []): User
{
    return User::create(array_merge([
        'name' => ucfirst($role) . ' User',
        'email' => uniqid($role . '_') . '@example.com',
        'password' => 'password',
        'role' => $role,
    ], $attributes));
}

function makePatient(array $attributes = []): Patient
{
    return Patient::create(array_merge([
        'first_name' => 'Test',
        'last_name' => 'Patient',
        'birth_date' => '1990-01-01',
        'gender' => 'male',
        'phone' => '0600000000',
        'id_card_number' => uniqid('ID'),
    ], $attributes));
}

it('assigns queue numbers and estimated times for queue mode doctors', function () {
    $doctor = makeClinicUser('doctor', ['appointment_mode' => 'queue']);
    $receptionist = makeClinicUser('receptionist');
    $firstPatient = makePatient();
    $secondPatient = makePatient(['id_card_number' => 'IDSECOND']);

    $this->actingAs($receptionist)
        ->post(route('appointments.store'), [
            'patient_id' => $firstPatient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-08',
            'reason' => 'Walk-in',
        ])
        ->assertRedirect();

    $this->actingAs($receptionist)
        ->post(route('appointments.store'), [
            'patient_id' => $secondPatient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-08',
            'reason' => 'Walk-in',
        ])
        ->assertRedirect();

    $appointments = Appointment::where('doctor_id', $doctor->id)->orderBy('queue_number')->get();

    expect($appointments)->toHaveCount(2)
        ->and($appointments[0]->appointment_mode)->toBe('queue')
        ->and($appointments[0]->queue_number)->toBe(1)
        ->and($appointments[0]->appointment_time->format('H:i'))->toBe('09:00')
        ->and($appointments[1]->queue_number)->toBe(2)
        ->and($appointments[1]->appointment_time->format('H:i'))->toBe('09:15');
});

it('rejects overlapping scheduled appointments based on configured duration', function () {
    $doctor = makeClinicUser('doctor', ['appointment_mode' => 'scheduled']);
    $receptionist = makeClinicUser('receptionist');
    $firstPatient = makePatient();
    $secondPatient = makePatient(['id_card_number' => 'IDOVERLAP']);

    Appointment::create([
        'patient_id' => $firstPatient->id,
        'doctor_id' => $doctor->id,
        'appointment_mode' => 'scheduled',
        'appointment_date' => '2026-06-08',
        'appointment_time' => '09:00',
        'status' => 'scheduled',
    ]);

    $this->actingAs($receptionist)
        ->from(route('appointments.create'))
        ->post(route('appointments.store'), [
            'patient_id' => $secondPatient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => '2026-06-08',
            'appointment_time' => '09:10',
            'reason' => 'Overlap check',
        ])
        ->assertRedirect(route('appointments.create'))
        ->assertSessionHasErrors('appointment_time');
});
