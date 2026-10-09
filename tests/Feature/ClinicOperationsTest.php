<?php

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\AppointmentStatusLog;
use App\Models\Patient;
use App\Models\User;
use App\Models\WaitlistEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-06-10 09:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

// ---- Audit trail ---------------------------------------------------------

it('logs creation and every status change with the acting user', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create();

    $this->actingAs($receptionist)->post(route('appointments.check-in', $appointment));
    $this->actingAs($receptionist)->post(route('appointments.set-current', $appointment));
    $this->actingAs($receptionist)->patch(route('appointments.update-status', $appointment), ['status' => 'completed']);

    $logs = AppointmentStatusLog::where('appointment_id', $appointment->id)->orderBy('id')->get();

    // creation + in_progress + completed (check-in does not change status)
    expect($logs)->toHaveCount(3)
        ->and($logs[0]->from_status)->toBeNull()
        ->and($logs[0]->to_status)->toBe('scheduled')
        ->and($logs[1]->from_status)->toBe('scheduled')
        ->and($logs[1]->to_status)->toBe('in_progress')
        ->and($logs[1]->changed_by)->toBe($receptionist->id)
        ->and($logs[2]->to_status)->toBe('completed');
});

it('shows the status history on the appointment page', function () {
    $receptionist = User::factory()->receptionist()->create();
    $appointment = Appointment::factory()->create();

    $this->actingAs($receptionist)->post(route('appointments.cancel', $appointment));

    $this->actingAs($receptionist)
        ->get(route('appointments.show', $appointment))
        ->assertOk()
        ->assertSee(__('appointments.status_history'));
});

it('attributes system status changes to nobody', function () {
    Appointment::factory()->create([
        'appointment_date' => '2026-06-09',
        'status' => 'scheduled',
    ]);

    $this->artisan('appointments:mark-no-shows')->assertSuccessful();

    $log = AppointmentStatusLog::where('to_status', 'no_show')->first();

    expect($log)->not->toBeNull()
        ->and($log->changed_by)->toBeNull();
});

// ---- Reminders -----------------------------------------------------------

it('emails patients about tomorrow appointments exactly once', function () {
    Mail::fake();

    $withEmail = Appointment::factory()->create([
        'appointment_date' => '2026-06-11',
        'status' => 'scheduled',
    ]);
    $withEmail->patient->update(['email' => 'patient@example.com']);

    $noEmail = Appointment::factory()->create([
        'appointment_date' => '2026-06-11',
        'status' => 'scheduled',
    ]);
    $noEmail->patient->update(['email' => null]);

    $cancelled = Appointment::factory()->create([
        'appointment_date' => '2026-06-11',
        'status' => 'cancelled',
    ]);
    $cancelled->patient->update(['email' => 'cancelled@example.com']);

    $this->artisan('appointments:send-reminders')->assertSuccessful();

    Mail::assertSent(AppointmentReminderMail::class, 1);
    Mail::assertSent(AppointmentReminderMail::class, fn ($mail) => $mail->hasTo('patient@example.com'));

    expect($withEmail->fresh()->reminder_sent_at)->not->toBeNull();

    // Second run sends nothing new
    $this->artisan('appointments:send-reminders')->assertSuccessful();
    Mail::assertSent(AppointmentReminderMail::class, 1);
});

// ---- Rebook to next free slot ---------------------------------------------

it('rebooks a scheduled appointment to the next free slot', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    $appointment = Appointment::factory()->create([
        'doctor_id' => $doctor->id,
        'appointment_date' => '2026-06-10',
        'appointment_time' => '10:00:00',
    ]);

    $this->actingAs($receptionist)
        ->post(route('appointments.rebook-next-slot', $appointment))
        ->assertSessionHas('success');

    $appointment->refresh();

    // Moved to the first free slot after now (09:00 + 30min duration rounding)
    expect($appointment->status)->toBe('scheduled')
        ->and($appointment->appointment_time->format('H:i'))->not->toBe('10:00');
});

it('refuses to rebook queue tickets or finished appointments', function () {
    $receptionist = User::factory()->receptionist()->create();

    $queueTicket = Appointment::factory()->create(['appointment_mode' => 'queue']);
    $completed = Appointment::factory()->create(['status' => 'completed']);

    $this->actingAs($receptionist)
        ->post(route('appointments.rebook-next-slot', $queueTicket))
        ->assertSessionHas('error');

    $this->actingAs($receptionist)
        ->post(route('appointments.rebook-next-slot', $completed))
        ->assertSessionHas('error');
});

// ---- Waitlist --------------------------------------------------------------

it('adds a patient to the waitlist and blocks duplicates', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($receptionist)->post(route('waitlist.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'reason' => 'Prefers mornings',
    ])->assertRedirect(route('waitlist.index'));

    expect(WaitlistEntry::waiting()->count())->toBe(1);

    $this->actingAs($receptionist)->from(route('waitlist.index'))->post(route('waitlist.store'), [
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
    ])->assertSessionHasErrors('patient_id');

    expect(WaitlistEntry::waiting()->count())->toBe(1);
});

it('books a waitlisted patient into the next free slot in one click', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $entry = WaitlistEntry::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'reason' => 'Back pain',
    ]);

    $this->actingAs($receptionist)
        ->post(route('waitlist.book', $entry))
        ->assertSessionHas('success');

    $appointment = Appointment::where('patient_id', $patient->id)->first();

    expect($appointment)->not->toBeNull()
        ->and($appointment->status)->toBe('scheduled')
        ->and($appointment->reason)->toBe('Back pain')
        ->and($entry->fresh()->status)->toBe('booked');
});

it('books a waitlisted patient into a queue-mode doctor queue', function () {
    $doctor = User::factory()->doctor()->queueMode()->create();
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $entry = WaitlistEntry::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
    ]);

    $this->actingAs($receptionist)->post(route('waitlist.book', $entry));

    $appointment = Appointment::where('patient_id', $patient->id)->first();

    expect($appointment->appointment_mode)->toBe('queue')
        ->and($appointment->queue_number)->toBe(1)
        ->and($entry->fresh()->status)->toBe('booked');
});

it('scopes doctors to their own waitlist', function () {
    $doctorA = User::factory()->doctor()->create();
    $doctorB = User::factory()->doctor()->create();

    $entry = WaitlistEntry::create([
        'patient_id' => Patient::factory()->create()->id,
        'doctor_id' => $doctorA->id,
    ]);

    $this->actingAs($doctorB)->post(route('waitlist.book', $entry))->assertForbidden();
    $this->actingAs($doctorB)->post(route('waitlist.cancel', $entry))->assertForbidden();

    $this->actingAs($doctorB)
        ->get(route('waitlist.index'))
        ->assertOk()
        ->assertDontSee($entry->patient->first_name);
});

it('mentions the waitlist when cancelling frees a slot', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    WaitlistEntry::create([
        'patient_id' => Patient::factory()->create()->id,
        'doctor_id' => $doctor->id,
    ]);

    $appointment = Appointment::factory()->create(['doctor_id' => $doctor->id]);

    $response = $this->actingAs($receptionist)->post(route('appointments.cancel', $appointment));

    expect(session('success'))->toContain('waitlist');
});

// ---- Per-doctor statistics --------------------------------------------------

it('reports per-doctor performance in statistics', function () {
    $doctor = User::factory()->doctor()->create(['name' => 'Perf Doctor']);
    Appointment::factory()->count(2)->create(['doctor_id' => $doctor->id, 'status' => 'completed']);
    Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'no_show']);

    $response = $this->actingAs($doctor)->getJson('/api/statistics');

    $response->assertOk();

    $performance = collect($response->json('doctor_performance'))
        ->firstWhere('name', $doctor->professional_name);

    expect($performance)->not->toBeNull()
        ->and($performance['completed'])->toBe(2)
        ->and($performance['no_show'])->toBe(1)
        ->and($performance['total'])->toBe(3)
        ->and($performance['no_show_rate'])->toEqual(33.3);

    $this->actingAs($doctor)->get('/statistics')->assertOk()->assertSee('Perf Doctor');
});
