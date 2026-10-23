<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Services\DataExportService;
use App\Services\SpecialtyService;

function specialtySetupPayload(): array
{
    $payload = [
        'environment_mode' => 'demo',
        'clinic_name' => 'Little Stars Clinic',
        'clinic_phone' => '+212600000099',
        'clinic_address' => '9 Care Avenue, Casablanca',
        'clinic_specialty' => 'pediatrics',
        'appointment_mode' => 'queue',
        'appointment_duration' => 20,
        'queue_daily_limit' => 25,
        'owner_doctor' => [
            'name' => 'Dr. Little',
            'email' => 'little@clinic.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ],
    ];

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
        $payload["{$day}_working"] = '1';
        $payload["{$day}_start"] = '09:00';
        $payload["{$day}_end"] = '17:00';
    }

    return $payload;
}

it('uses the chosen specialty for demo setup and seeded clinic data', function () {
    Setting::setSetupComplete(false);

    $this->post(route('setup.store'), specialtySetupPayload())
        ->assertRedirect(route('dashboard'));

    $patient = Patient::firstOrFail();

    expect(Setting::get('clinic_specialty'))->toBe('pediatrics')
        ->and($patient->age)->toBeLessThanOrEqual(17)
        ->and($patient->specialty_data)->toHaveKey('guardian_name')
        ->and(Appointment::pluck('reason')->every(fn (string $reason) => in_array($reason, config('specialties.pediatrics.appointment_reasons'), true)))->toBeTrue();
});

it('stores only configured optional patient details and renders the matching form', function () {
    Setting::set('clinic_specialty', 'dentistry', 'string');
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->get(route('patients.create'))
        ->assertOk()
        ->assertSee('Last dental visit')
        ->assertDontSee('Cardiac history');

    $this->actingAs($receptionist)->post(route('patients.store'), [
        'first_name' => 'Dental',
        'last_name' => 'Patient',
        'phone' => '+212600000098',
        'id_card_number' => 'DENTAL098',
        'specialty_data' => [
            'last_dental_visit' => '2026-08-01',
            'oral_hygiene_habits' => 'Brushes twice daily.',
            'unapproved_key' => 'Must not be stored',
        ],
    ]);

    expect(Patient::where('id_card_number', 'DENTAL098')->firstOrFail()->specialty_data)->toBe([
        'last_dental_visit' => '2026-08-01',
        'oral_hygiene_habits' => 'Brushes twice daily.',
    ]);
});

it('stores optional specialty clinical details without changing core record workflow', function () {
    Setting::set('clinic_specialty', 'cardiology', 'string');
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)->post(route('medical-records.store'), [
        'patient_id' => $patient->id,
        'visit_date' => now()->toDateString(),
        'symptoms' => 'Palpitations',
        'diagnosis' => 'Assessment pending',
        'specialty_data' => [
            'ecg_summary' => 'Normal sinus rhythm.',
            'cardiac_risk_assessment' => 'Low current risk.',
        ],
    ])->assertRedirect();

    expect(MedicalRecord::firstOrFail()->specialty_data)->toBe([
        'ecg_summary' => 'Normal sinus rhythm.',
        'cardiac_risk_assessment' => 'Low current risk.',
    ]);
});

it('keeps specialty details readable after the clinic changes profile', function () {
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create([
        'specialty_data' => ['last_dental_visit' => '2026-08-01'],
    ]);

    Setting::set('clinic_specialty', 'cardiology', 'string');

    $this->actingAs($receptionist)->get(route('patients.show', $patient))
        ->assertOk()
        ->assertSee('Last dental visit')
        ->assertSee('2026-08-01');
});

it('lets a doctor change specialty and optionally apply its defaults to their account', function () {
    $doctor = User::factory()->doctor()->create([
        'appointment_mode' => 'queue',
        'queue_daily_limit' => 10,
    ]);

    $this->actingAs($doctor)->post(route('settings.specialty'), [
        'clinic_specialty' => 'dentistry',
        'apply_specialty_defaults' => '1',
    ])->assertRedirect(route('settings.index'));

    expect(Setting::get('clinic_specialty'))->toBe('dentistry')
        ->and(Setting::getAppointmentDuration())->toBe(30)
        ->and($doctor->fresh()->appointment_mode)->toBe('scheduled')
        ->and($doctor->fresh()->queue_daily_limit)->toBeNull();
});

it('uses the specialty in secondary workflows and clinical exports', function () {
    Setting::set('clinic_specialty', 'dentistry', 'string');
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create([
        'specialty_data' => ['last_dental_visit' => '2026-08-01'],
    ]);

    MedicalRecord::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'visit_date' => now()->toDateString(),
        'specialty_data' => ['tooth_reference' => 'Upper right molar'],
    ]);

    $this->actingAs($doctor)->get(route('prescriptions.create'))
        ->assertOk()
        ->assertSee('specialty-medication-suggestions')
        ->assertDontSee('value="Ibuprofen"', false);

    $specialties = app(SpecialtyService::class);
    $export = app(DataExportService::class)->exportData(['patients', 'medical_records']);
    $receptionist = User::factory()->receptionist()->create();
    $entry = WaitlistEntry::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
    ]);

    $this->actingAs($receptionist)->post(route('waitlist.book', $entry))
        ->assertSessionHas('success');

    expect($specialties->defaultFollowUpReason())->toBe('Restoration follow-up')
        ->and($export['info']['clinic_specialty'])->toBe('Dentistry')
        ->and($export['data']['patients']['headers'])->toContain(__('Specialty Details'))
        ->and($export['data']['medical_records']['headers'])->toContain(__('Specialty Details'))
        ->and(implode(' ', $export['data']['patients']['data'][0]))->toContain('Last dental visit: 2026-08-01')
        ->and(implode(' ', $export['data']['medical_records']['data'][0]))->toContain('Tooth or area reference: Upper right molar')
        ->and(WaitlistEntry::find($entry->id)->status)->toBe('booked')
        ->and(Appointment::where('patient_id', $patient->id)->firstOrFail()->reason)->toBe('Dental check-up');
});
