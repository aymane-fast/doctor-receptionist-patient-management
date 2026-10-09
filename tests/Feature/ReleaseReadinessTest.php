<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use App\Services\DataExportService;
use Illuminate\Support\Facades\DB;

it('uses an isolated database even when deployment caches exist', function () {
    expect(app()->environment())->toBe('testing')
        ->and(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(app()->configurationIsCached())->toBeFalse()
        ->and(app()->routesAreCached())->toBeFalse();
});

function releaseWorkingWeek(): void
{
    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        Setting::set($day.'_working', true, 'boolean');
        Setting::set($day.'_start', '09:00', 'time');
        Setting::set($day.'_end', '17:00', 'time');
    }
}

function releaseWalkInPayload(string $date, string $time): array
{
    return ['first_name' => 'Audit', 'last_name' => 'Walkin', 'phone' => '0600000000',
        'id_card_number' => 'AUDIT-WALKIN', 'book_today' => '1',
        'appointment_date' => $date, 'appointment_time' => $time];
}

it('rolls back a walk-in submitted with a time already past', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-09 14:00'));
    releaseWorkingWeek();
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'scheduled']);
    $this->actingAs($doctor)->post('/patients', releaseWalkInPayload('2026-10-09', '10:00'))
        ->assertSessionHasErrors('book_today');
    expect(Patient::count())->toBe(0)->and(Appointment::count())->toBe(0);
});

it('does not mark future walk-in bookings as arrived and allows booking while closed now', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-09 20:00'));
    releaseWorkingWeek();
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'scheduled']);
    $this->actingAs($doctor)->post('/patients', releaseWalkInPayload('2026-10-10', '10:00'))
        ->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    expect(Appointment::sole()->checked_in_at)->toBeNull();
});

it('neutralizes spreadsheet formulas in every exported user-controlled cell', function () {
    $doctor = User::factory()->doctor()->create();
    Patient::factory()->create(['first_name' => '=1+1', 'last_name' => '+SUM(1,2)',
        'address' => "\t=HYPERLINK(\"https://example.invalid\")", 'phone' => '-123']);
    $response = $this->actingAs($doctor)->post('/exports', ['data_types' => ['patients']])->assertOk();
    expect($response->getContent())->toContain("'=1+1")->toContain("'+SUM")->toContain("'-123");
});

it('computes available slots without querying the appointments once per slot', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-09 08:00'));
    releaseWorkingWeek();
    $doctor = User::factory()->doctor()->create();
    DB::enableQueryLog();
    app(AppointmentSchedulingService::class)->availableSlots($doctor->id, '2026-10-10');
    $queries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'from "appointments"'));
    DB::disableQueryLog();
    expect($queries->count())->toBeLessThanOrEqual(1);
});

it('rejects malformed setup staff input without an internal server error', function () {
    Setting::setSetupComplete(false);
    $this->postJson('/setup', ['environment_mode' => 'production', 'doctors' => 'invalid',
        'receptionists' => [['email' => ['invalid']]]])->assertUnprocessable();
    expect(User::count())->toBe(0);
});

it('renders the full populated navigation and detail matrix for both roles', function (string $role, string $specialty) {
    releaseWorkingWeek();
    app(\App\Services\SpecialtyService::class)->setCurrent($specialty);
    $doctor = User::factory()->doctor()->create();
    $user = $role === 'doctor' ? $doctor : User::factory()->receptionist()->create();
    $patient = Patient::factory()->create(['first_name' => 'Auditpatient']);
    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'appointment_date' => today(), 'appointment_time' => '10:00', 'status' => 'scheduled']);
    $record = MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'visit_date' => today(), 'diagnosis' => 'Clinical-private-marker']);
    $prescription = Prescription::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'prescribed_date' => today(), 'medication_name' => 'Audit medication', 'dosage' => '1', 'frequency' => 'daily']);
    $shared = ['/dashboard', '/patients', '/patients?search=Auditpatient', '/patients/create',
        '/patients/'.$patient->id, '/patients/'.$patient->id.'/edit', '/appointments', '/appointments/create',
        '/appointments/'.$appointment->id, '/appointments/'.$appointment->id.'/edit',
        '/waitlist', '/settings',
        '/api/patients/search?q=Audit', '/api/working-hours', '/api/settings/working-status'];
    $clinical = ['/register', '/doctor/current', '/medical-records', '/medical-records?search=Auditpatient',
        '/medical-records/create', '/medical-records/'.$record->id, '/medical-records/'.$record->id.'/edit',
        '/prescriptions', '/prescriptions?search=Auditpatient', '/prescriptions/create',
        '/prescriptions/'.$prescription->id, '/prescriptions/'.$prescription->id.'/edit',
        '/prescriptions/'.$prescription->id.'/print', '/statistics', '/api/statistics',
        '/api/statistics/trend?timeframe=month', '/exports'];
    $this->actingAs($user);
    $this->get('/appointments/'.$appointment->id.'/reschedule')->assertRedirect('/appointments/'.$appointment->id.'/edit');
    foreach ($shared as $url) {
        $response = $this->get($url);
        $this->assertSame(200, $response->status(), $url);
        if ($role === 'receptionist') {
            $response->assertDontSee('Clinical-private-marker');
        }
    }
    foreach ($clinical as $url) {
        $response = $this->get($url);
        $role === 'doctor' ? $response->assertOk() : $response->assertForbidden();
    }
})->with([
    ['doctor', 'general_practice'], ['receptionist', 'general_practice'],
    ['doctor', 'pediatrics'], ['receptionist', 'pediatrics'],
    ['doctor', 'dentistry'], ['receptionist', 'dentistry'],
    ['doctor', 'cardiology'], ['receptionist', 'cardiology'],
]);

it('rejects structured query strings rather than returning server errors', function (string $url) {
    $doctor = User::factory()->doctor()->create();
    $this->actingAs($doctor)->getJson($url)->assertUnprocessable();
})->with([
    '/api/patients/search?q[]=Audit',
    '/api/medical-records/patients/search?q[]=Audit',
    '/api/prescriptions/patients/search?q[]=Audit',
    '/patients?search[]=Audit',
    '/medical-records?search[]=Audit',
    '/prescriptions?search[]=Audit',
    '/appointments?search[]=Audit',
    '/dashboard?patient_search[]=Audit',
]);

it('does not duplicate an active appointment when booking a queued waitlist entry', function () {
    releaseWorkingWeek();
    $doctor = User::factory()->doctor()->create(['appointment_mode' => 'queue']);
    $patient = Patient::factory()->create();
    $appointment = app(AppointmentSchedulingService::class)->createQueueAppointment([
        'doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'appointment_date' => today()->toDateString()]);
    $entry = \App\Models\WaitlistEntry::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'preferred_date' => today(), 'status' => 'waiting']);
    $this->actingAs($doctor)->post('/waitlist/'.$entry->id.'/book')->assertRedirect();
    expect(Appointment::count())->toBe(1)->and($entry->fresh()->status)->toBe('waiting');
});

it('rejects unauthenticated requests to every protected route and method', function () {
    $checked = 0;
    foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
        if (! in_array('auth', $route->gatherMiddleware(), true)) {
            continue;
        }
        $url = '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri());
        foreach (array_diff($route->methods(), ['HEAD']) as $method) {
            $response = $this->json($method, $url);
            $this->assertSame(401, $response->status(), $method.' '.$url);
            $checked++;
        }
    }
    expect($checked)->toBeGreaterThan(60);
});

it('launches a fresh default demo for each setup practice type', function (string $specialty) {
    Setting::setSetupComplete(false);
    $this->post('/setup', ['environment_mode' => 'demo', 'launch_demo' => '1',
        'clinic_specialty' => $specialty])->assertSessionHasNoErrors()->assertRedirect('/dashboard');
    expect(User::count())->toBe(2)->and(Patient::count())->toBe(36)
        ->and(Setting::get('clinic_specialty'))->toBe($specialty)
        ->and(Appointment::count())->toBeGreaterThan(0);
    $this->get('/dashboard')->assertOk();
})->with(['general_practice', 'pediatrics', 'dentistry', 'cardiology']);
