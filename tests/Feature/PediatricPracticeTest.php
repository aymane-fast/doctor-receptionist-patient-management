<?php

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\PatientGuardian;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;
use App\Services\PatientProfileService;
use App\Services\PatientSearchService;
use Illuminate\Support\Facades\Mail;

function childPayload(): array
{
    return [
        'first_name' => 'Baby', 'last_name' => 'Family', 'birth_date' => today()->toDateString(),
        'guardians' => [
            ['name' => 'Alex Family', 'relationship' => 'Parent', 'phone' => '0600000001', 'email' => 'alex@example.test'],
            ['name' => 'Sam Family', 'relationship' => 'Guardian', 'phone' => '0600000002', 'email' => 'sam@example.test'],
        ],
        'primary_guardian' => 1,
    ];
}

beforeEach(function () {
    Setting::set('clinic_specialty', 'pediatrics');
});

it('launches a pediatric demo with only a practice choice and default staff', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-09-17 10:00:00'));
    Setting::setSetupComplete(false);
    $this->post(route('setup.store'), ['environment_mode' => 'demo', 'launch_demo' => 1, 'clinic_specialty' => 'pediatrics'])
        ->assertRedirect(route('dashboard'));
    expect(Setting::get('clinic_specialty'))->toBe('pediatrics')
        ->and(Patient::count())->toBe(36)
        ->and(PatientGuardian::count())->toBe(36)
        ->and(Patient::where('is_pediatric', false)->count())->toBe(0);
    $this->assertDatabaseHas('users', ['email' => 'doctor@example.com', 'role' => 'doctor']);
    $this->assertDatabaseHas('users', ['email' => 'receptionist@example.com', 'role' => 'receptionist']);
    expect(Appointment::whereDate('appointment_date', today())->where('status', 'in_progress')->count())->toBe(1);
    $this->travelBack();
});

it('offers just the first four practice types in new setup', function () {
    Setting::setSetupComplete(false);
    $this->get(route('setup.show'))->assertOk()->assertSee('General Practice')->assertSee('Pediatrics')
        ->assertSee('Dentistry')->assertSee('Cardiology')->assertDontSee('Dermatology');
});

it('localizes pediatric contact and age terminology in French', function () {
    app()->setLocale('fr');
    $patient = app(PatientProfileService::class)->save(childPayload());
    expect(__('Child information'))->toBe('Informations sur l’enfant')->and($patient->age_display)->toBe('0 jours');
    app()->setLocale('en');
});

it('rejects invalid demo practices without partial setup', function () {
    Setting::setSetupComplete(false);
    $this->post(route('setup.store'), ['environment_mode' => 'demo', 'clinic_specialty' => 'unknown'])
        ->assertSessionHasErrors('clinic_specialty');
    expect(User::count())->toBe(0)->and(Patient::count())->toBe(0);
});

it('registers newborn siblings without national IDs and with shared guardian contacts', function () {
    $this->actingAs(User::factory()->receptionist()->create());
    foreach (['Baby', 'Twin'] as $name) {
        $this->post(route('patients.store'), array_replace(childPayload(), ['first_name' => $name]))
            ->assertSessionHasNoErrors()->assertRedirect();
    }
    $patient = Patient::firstOrFail();
    expect(Patient::count())->toBe(2)->and($patient->is_pediatric)->toBeTrue()
        ->and($patient->id_card_number)->toBeNull()->and($patient->age_display)->toBe('0 days old')
        ->and($patient->phone)->toBe('0600000002')->and($patient->email)->toBe('sam@example.test')
        ->and($patient->primary_guardian->name)->toBe('Sam Family');
    $this->get(route('patients.show', $patient))->assertOk()->assertSee('Primary guardian')->assertSee('Baby Family');
    $this->get(route('patients.edit', $patient))->assertOk()->assertSee('Parent / guardian contacts');
});

it('rejects incomplete and malformed pediatric inputs without saving anything', function (array $changes, string $error) {
    $this->actingAs(User::factory()->receptionist()->create())
        ->post(route('patients.store'), array_replace(childPayload(), $changes))->assertSessionHasErrors($error);
    expect(Patient::count())->toBe(0)->and(PatientGuardian::count())->toBe(0);
})->with([
    'missing DOB' => [['birth_date' => null], 'birth_date'],
    'future DOB' => [['birth_date' => '2999-01-01'], 'birth_date'],
    'no guardians' => [['guardians' => []], 'guardians'],
    'malformed guardians' => [['guardians' => 'bad'], 'guardians'],
    'non-list guardians' => [['guardians' => ['other' => ['name' => 'Parent', 'relationship' => 'Parent', 'phone' => '123']]], 'guardians'],
    'missing name' => [['guardians' => [['relationship' => 'Parent', 'phone' => '123']]], 'guardians.0.name'],
    'invalid email' => [['guardians' => [['name' => 'Parent', 'relationship' => 'Parent', 'phone' => '123', 'email' => 'bad']]], 'guardians.0.email'],
    'invalid primary' => [['primary_guardian' => 4], 'primary_guardian'],
    'too many contacts' => [['guardians' => array_fill(0, 6, ['name' => 'Parent', 'relationship' => 'Parent', 'phone' => '123'])], 'guardians'],
]);

it('keeps guardians editable after a practice switch and updates the reminder contact', function () {
    $patient = app(PatientProfileService::class)->save(childPayload());
    Setting::set('clinic_specialty', 'general_practice');
    $this->actingAs(User::factory()->receptionist()->create());
    $this->get(route('patients.edit', $patient))->assertOk()->assertSee('Parent / guardian contacts');
    $payload = childPayload();
    $payload['guardians'] = [$payload['guardians'][0]];
    $payload['primary_guardian'] = 0;
    $this->put(route('patients.update', $patient), $payload)->assertSessionHasNoErrors()->assertRedirect();
    expect($patient->fresh()->guardians)->toHaveCount(1)
        ->and($patient->fresh()->email)->toBe('alex@example.test')
        ->and($patient->fresh()->is_pediatric)->toBeTrue();
});

it('searches both guardians and keeps sibling identities distinct', function () {
    app(PatientProfileService::class)->save(childPayload());
    app(PatientProfileService::class)->save(array_replace(childPayload(), ['first_name' => 'Twin']));
    foreach (['Alex', '0600000001', 'sam@example.test'] as $query) {
        $results = PatientSearchService::search($query);
        expect($results)->toHaveCount(2)->and($results[0]['name'])->toContain(today()->toDateString())
            ->and($results[0]['display'])->toContain(today()->toDateString());
    }
    expect(PatientSearchService::search('Family')->pluck('name')->unique())->toHaveCount(2);
});

it('does not change saved guardians or clinical history when an edit fails validation', function () {
    $patient = app(PatientProfileService::class)->save(childPayload() + ['allergies' => 'Peanuts', 'chronic_conditions' => 'Asthma']);
    $payload = childPayload();
    $payload['guardians'][0]['email'] = 'invalid-email';
    $payload['allergies'] = 'Changed';
    $this->actingAs(User::factory()->receptionist()->create())->put(route('patients.update', $patient), $payload)
        ->assertSessionHasErrors('guardians.0.email');
    expect($patient->fresh()->allergies)->toBe('Peanuts')->and($patient->fresh()->guardians)->toHaveCount(2)
        ->and($patient->fresh()->email)->toBe('sam@example.test');
});

it('shows child birth date and guardian in all preselected clinical forms', function () {
    $patient = app(PatientProfileService::class)->save(childPayload());
    $this->actingAs(User::factory()->doctor()->create());
    foreach (['appointments.create', 'medical-records.create', 'prescriptions.create'] as $route) {
        $this->get(route($route, ['patient_id' => $patient->id]))->assertOk()->assertSee($patient->selection_label);
    }
});

it('rolls child and guardians back together when walk-in booking fails', function () {
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        Setting::set($day.'_working', false, 'boolean');
    }
    $this->actingAs(User::factory()->receptionist()->create())->post(route('patients.store'), childPayload() + [
        'book_today' => 1, 'appointment_date' => today()->toDateString(),
    ])->assertSessionHasErrors('book_today');
    expect(Patient::count())->toBe(0)->and(PatientGuardian::count())->toBe(0);
});

it('uses the shared appointment consultation and prescription workflow for a child', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = app(PatientProfileService::class)->save(childPayload());
    $appointment = Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'appointment_date' => today(), 'status' => 'scheduled']);
    $this->actingAs($doctor)->post(route('appointments.set-current', $appointment))->assertRedirect();
    expect($appointment->fresh()->status)->toBe('in_progress');
    $this->get(route('doctor.current'))->assertOk()->assertSee('Sam Family')->assertSee('0 days old');
    $this->post(route('medical-records.store'), ['patient_id' => $patient->id, 'visit_date' => today()->toDateString(), 'symptoms' => 'Routine review', 'diagnosis' => 'Wellness review', 'specialty_data' => ['feeding_notes' => 'Feeding history reviewed.']])->assertSessionHasNoErrors();
    $record = MedicalRecord::firstOrFail();
    expect($record->patient_id)->toBe($patient->id)->and($record->specialty_data['feeding_notes'])->toBe('Feeding history reviewed.');
    $this->get(route('medical-records.show', $record))->assertOk()->assertSee('Sam Family');
    $this->post(route('prescriptions.store'), ['patient_id' => $patient->id, 'prescribed_date' => today()->toDateString(), 'items' => [['medication_name' => 'Test medication', 'dosage' => 'As prescribed', 'frequency' => 'As prescribed', 'duration_days' => 1]]])->assertSessionHasNoErrors();
    $prescription = Prescription::firstOrFail();
    $this->get(route('prescriptions.print', $prescription))->assertOk()->assertSee('Baby Family')->assertSee('Sam Family')->assertSee('0 days old');
    $this->actingAs(User::factory()->receptionist()->create())->get(route('medical-records.show', $record))->assertForbidden();
    $this->get(route('prescriptions.print', $prescription))->assertForbidden();
    $this->get(route('patients.ai-summary', $patient))->assertForbidden();
});

it('uses pediatric production setup without sample patients', function () {
    Setting::setSetupComplete(false);
    $payload = specialtySetupPayload();
    $payload['environment_mode'] = 'production';
    $payload['queue_daily_limit'] = null;
    $this->post(route('setup.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    expect(Setting::get('clinic_specialty'))->toBe('pediatrics')->and(Patient::count())->toBe(0);
    $this->get(route('patients.create'))->assertOk()->assertSee('Child information');
});

it('books and completes children through both shared scheduling modes', function (string $mode) {
    $this->travelTo(\Carbon\Carbon::parse('2026-09-17 09:00:00'));
    $doctor = User::factory()->doctor()->create(['appointment_mode' => $mode]);
    $patient = app(PatientProfileService::class)->save(childPayload());
    $this->actingAs(User::factory()->receptionist()->create())->post(route('appointments.store'), [
        'patient_id' => $patient->id, 'doctor_id' => $doctor->id, 'appointment_date' => today()->toDateString(),
        'appointment_time' => '10:00', 'reason' => 'Child wellness visit',
    ])->assertSessionHasNoErrors();
    $appointment = Appointment::firstOrFail();
    expect($appointment->appointment_mode)->toBe($mode);
    $this->get(route('appointments.show', $appointment))->assertOk()->assertSee('Sam Family');
    $this->post(route('appointments.set-current', $appointment))->assertRedirect();
    $this->actingAs($doctor)->get(route('doctor.current'))->assertOk()->assertSee('Baby Family');
    $this->patch(route('appointments.update-status', $appointment), ['status' => 'completed']);
    expect($appointment->fresh()->status)->toBe('completed')->and($appointment->fresh()->patient_id)->toBe($patient->id);
    $this->travelBack();
})->with(['scheduled', 'queue']);

it('exports all guardians with an explicit primary contact', function () {
    app(PatientProfileService::class)->save(childPayload());
    $result = app(\App\Services\DataExportService::class)->exportData(['patients']);
    $patients = $result['data']['patients'];
    expect($patients['data'][0])->toHaveCount(count($patients['headers']));
    expect(implode(' ', $patients['data'][0]))->toContain('Primary: Sam Family', 'Alex Family');
});

it('backfills legacy guardian notes without erasing clinical data', function () {
    $migration = require database_path('migrations/2026_09_17_000001_add_patient_guardians.php');
    $migration->down();
    $id = \DB::table('patients')->insertGetId([
        'first_name' => 'Legacy', 'last_name' => 'Child', 'birth_date' => '2025-01-01',
        'phone' => '0600000010', 'id_card_number' => 'OLD10', 'allergies' => 'Peanuts',
        'specialty_data' => json_encode(['guardian_name' => 'Legacy Parent', 'guardian_relationship' => 'Parent', 'immunization_status' => 'Unknown']),
    ]);
    $migration->up();
    $patient = Patient::findOrFail($id);
    expect($patient->is_pediatric)->toBeTrue()->and($patient->primary_guardian->name)->toBe('Legacy Parent')
        ->and($patient->allergies)->toBe('Peanuts')->and($patient->specialty_data['immunization_status'])->toBe('Unknown');
});

it('can safely resume when its table was created before migration history was recorded', function () {
    $migration = require database_path('migrations/2026_09_17_000001_add_patient_guardians.php');
    $migration->down();

    \Illuminate\Support\Facades\Schema::create('patient_guardians', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->string('relationship', 100);
        $table->string('phone');
        $table->string('email')->nullable();
        $table->boolean('is_primary')->default(false);
        $table->timestamps();
    });

    $migration->up();

    expect(\Illuminate\Support\Facades\Schema::hasColumn('patients', 'is_pediatric'))->toBeTrue()
        ->and(\Illuminate\Support\Facades\Schema::hasTable('patient_guardians'))->toBeTrue();
});

it('sends reminders only to the primary guardian and names the child', function () {
    Mail::fake();
    $patient = app(PatientProfileService::class)->save(childPayload());
    $appointment = Appointment::factory()->create(['patient_id' => $patient->id, 'appointment_date' => today()->addDay(), 'status' => 'scheduled']);
    $this->artisan('appointments:send-reminders')->assertSuccessful();
    Mail::assertSent(AppointmentReminderMail::class, fn ($mail) => $mail->hasTo('sam@example.test') && ! $mail->hasTo('alex@example.test'));
    $html = (new AppointmentReminderMail($appointment))->render();
    expect($html)->toContain('Sam Family', 'Baby Family');
});
