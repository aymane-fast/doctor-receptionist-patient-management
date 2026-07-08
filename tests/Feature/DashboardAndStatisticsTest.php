<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 10:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('renders the doctor dashboard', function () {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->count(2)->create(['doctor_id' => $doctor->id]);

    $this->actingAs($doctor)->get('/dashboard')->assertOk();
});

it('renders the receptionist dashboard', function () {
    $receptionist = User::factory()->receptionist()->create();
    Appointment::factory()->count(2)->create();

    $this->actingAs($receptionist)->get('/dashboard')->assertOk();
});

it('filters recent patients by age range portably', function () {
    $receptionist = User::factory()->receptionist()->create();
    Patient::factory()->create(['first_name' => 'Younger', 'birth_date' => now()->subYears(20)->toDateString()]);
    Patient::factory()->create(['first_name' => 'Older', 'birth_date' => now()->subYears(60)->toDateString()]);

    $this->actingAs($receptionist)
        ->get('/dashboard?age_from=40')
        ->assertOk()
        ->assertSee('Older')
        ->assertDontSee('Younger');

    $this->actingAs($receptionist)
        ->get('/dashboard?age_to=30')
        ->assertOk()
        ->assertSee('Younger')
        ->assertDontSee('Older');
});

it('initializes the first patient of the day once', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_time' => '09:00:00',
    ]);

    $this->actingAs($doctor)->post('/dashboard/initialize-first-patient');

    expect($appointment->fresh()->status)->toBe('in_progress');

    // A second call must not queue up another current patient
    $this->actingAs($doctor)->post('/dashboard/initialize-first-patient')
        ->assertSessionHas('error');
});

it('blocks receptionists from statistics', function () {
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($receptionist)->get('/statistics')->assertForbidden();
    $this->actingAs($receptionist)->get('/api/statistics')->assertForbidden();
});

it('renders statistics for doctors', function () {
    $doctor = User::factory()->doctor()->create();
    Patient::factory()->count(3)->create();
    Appointment::factory()->count(3)->create([
        'doctor_id' => $doctor->id,
        'status' => 'completed',
    ]);
    MedicalRecord::create([
        'patient_id' => Patient::first()->id,
        'doctor_id' => $doctor->id,
        'visit_date' => now()->toDateString(),
    ]);

    $this->actingAs($doctor)->get('/statistics')->assertOk();
});

it('returns statistics JSON with expected shape', function () {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->count(2)->create(['doctor_id' => $doctor->id]);

    $this->actingAs($doctor)->getJson('/api/statistics')
        ->assertOk()
        ->assertJsonStructure([
            'total_patients',
            'total_appointments',
            'trend_data',
            'trend_labels',
            'hourly_distribution',
            'age_groups',
            'monthly_revenue',
        ]);
});

it('returns trend data for all timeframes', function () {
    $doctor = User::factory()->doctor()->create();
    Appointment::factory()->create(['doctor_id' => $doctor->id]);

    foreach (['week', 'month', 'year'] as $timeframe) {
        $this->actingAs($doctor)
            ->getJson('/api/statistics/trend?timeframe=' . $timeframe)
            ->assertOk()
            ->assertJsonStructure(['labels', 'data']);
    }
});

it('shows the doctor current-patient workspace', function () {
    $doctor = User::factory()->doctor()->create();
    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'status' => 'in_progress',
    ]);

    $this->actingAs($doctor)
        ->get('/doctor/current')
        ->assertOk()
        ->assertSee($appointment->patient->first_name);
});
