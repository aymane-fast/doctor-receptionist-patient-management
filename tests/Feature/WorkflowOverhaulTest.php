<?php

use App\Models\Appointment;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Setting;
use App\Models\User;
use App\Services\AppointmentSchedulingService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Carbon::setTestNow('2026-10-12 10:00:00');
    $this->doctor = User::factory()->doctor()->create();
    $this->patient = Patient::factory()->create();
    $this->actingAs($this->doctor);
});
afterEach(fn () => Carbon::setTestNow());

function overhaulRecord(User $doctor, Patient $patient, array $extra = []): MedicalRecord
{
    return MedicalRecord::create($extra + ['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'visit_date' => today()]);
}

it('keeps the active appointment and exposes linked clinical forms after saving', function () {
    $active = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'status' => 'in_progress']);
    $next = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $this->get(route('doctor.current'))->assertOk()->assertSee(__('workflow.save_first'));
    $this->post(route('medical-records.store'), ['patient_id' => $this->patient->id, 'appointment_id' => $active->id,
        'visit_date' => today()->toDateString(), 'temperature' => 37.2, 'return_to_workspace' => 1])->assertRedirect(route('doctor.current'));
    $record = $active->fresh()->medicalRecord;
    expect($active->fresh()->status)->toBe('in_progress')->and($next->fresh()->status)->toBe('scheduled')
        ->and($record->temperature_unit)->toBe('C');
    $this->get(route('doctor.current'))->assertOk()->assertSee(route('clinical.orders.store', $this->patient))
        ->assertSee(route('prescriptions.store'))->assertSee(route('clinical.documents.store', $this->patient));
    $this->get(route('medical-records.create', ['appointment_id' => $active->id]))->assertRedirect(route('medical-records.edit', $record));
});

it('rejects inconsistent patient and appointment context instead of silently changing it', function () {
    $visit = Appointment::factory()->create(['doctor_id' => $this->doctor->id]);
    $this->post(route('medical-records.store'), ['patient_id' => $this->patient->id, 'appointment_id' => $visit->id,
        'visit_date' => today()->toDateString()])->assertSessionHasErrors('patient_id');
    expect(MedicalRecord::count())->toBe(0);
});

it('rejects out of range Celsius values for new records', function () {
    $this->post(route('medical-records.store'), ['patient_id' => $this->patient->id, 'visit_date' => today()->toDateString(), 'temperature' => 98.6])
        ->assertSessionHasErrors('temperature');
});

it('preserves historical temperature values until their unit is explicitly confirmed', function () {
    $record = overhaulRecord($this->doctor, $this->patient, ['temperature' => 98.6]);
    $this->get(route('medical-records.show', $record))->assertOk()->assertSee(__('workflow.legacy_unit'));
    $data = ['patient_id' => $this->patient->id, 'visit_date' => today()->toDateString(), 'temperature' => 98.6];
    $this->put(route('medical-records.update', $record), $data)->assertSessionHasNoErrors();
    expect($record->fresh()->temperature_unit)->toBeNull()->and((float) $record->fresh()->temperature)->toBe(98.6);
    $this->put(route('medical-records.update', $record), $data + ['temperature_unit' => 'F'])->assertSessionHasNoErrors();
    expect($record->fresh()->temperature_unit)->toBe('F');
    $this->put(route('medical-records.update', $record), $data + ['temperature_unit' => 'C'])->assertSessionHasErrors('temperature');
});

it('selects the requested consultation when creating a prescription', function () {
    $record = overhaulRecord($this->doctor, $this->patient);
    $this->get(route('prescriptions.create', ['medical_record_id' => $record->id]))->assertOk()
        ->assertViewHas('selectedRecord', fn ($selected) => $selected->id === $record->id)
        ->assertViewHas('selectedPatient', fn ($selected) => $selected->id === $this->patient->id);
});

it('refuses a prescription with a record belonging to another patient', function () {
    $record = overhaulRecord($this->doctor, Patient::factory()->create());
    $this->post(route('prescriptions.store'), ['patient_id' => $this->patient->id, 'medical_record_id' => $record->id,
        'prescribed_date' => today()->toDateString(), 'items' => [['medication_name' => 'Manual medicine', 'dosage' => 'Entered by doctor', 'frequency' => 'As prescribed']]])
        ->assertSessionHasErrors('patient_id');
    expect(Prescription::count())->toBe(0);
});

it('saves a prescription to the active record and returns to the workspace', function () {
    $visit = Appointment::factory()->create(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'status' => 'in_progress']);
    $record = overhaulRecord($this->doctor, $this->patient, ['appointment_id' => $visit->id]);
    $this->post(route('prescriptions.store'), ['patient_id' => $this->patient->id, 'medical_record_id' => $record->id,
        'prescribed_date' => today()->toDateString(), 'return_to_workspace' => 1,
        'items' => [['medication_name' => 'Manual medicine', 'dosage' => 'Entered by doctor', 'frequency' => 'As prescribed']]])
        ->assertRedirect(route('doctor.current'));
    expect(Prescription::first()->medical_record_id)->toBe($record->id)->and($visit->fresh()->status)->toBe('in_progress');
});

it('creates custom examination requests in the intended consultation', function (string $type) {
    $record = overhaulRecord($this->doctor, $this->patient);
    $this->post(route('clinical.orders.store', $this->patient), ['medical_record_id' => $record->id,
        'order_type' => $type, 'test_name' => 'Custom examination', 'notes' => 'Doctor instructions'])->assertSessionHasNoErrors();
    $order = Order::first();
    expect($order->patient_id)->toBe($this->patient->id)->and($order->doctor_id)->toBe($this->doctor->id)
        ->and($order->medical_record_id)->toBe($record->id)->and($order->status)->toBe('requested');
    $this->patch(route('clinical.orders.update', $order), ['status' => 'completed'])->assertSessionHasNoErrors();
    $this->get(route('clinical.orders.print', $order))->assertOk()->assertSee('Custom examination')->assertSee($this->patient->full_name);
})->with(['lab', 'scan']);

it('rejects examination requests attached to the wrong patient or doctor', function (bool $otherDoctor) {
    $record = overhaulRecord($otherDoctor ? User::factory()->doctor()->create() : $this->doctor,
        $otherDoctor ? $this->patient : Patient::factory()->create());
    $this->post(route('clinical.orders.store', $this->patient), ['medical_record_id' => $record->id,
        'order_type' => 'lab', 'test_name' => 'CBC'])->assertNotFound();
    expect(Order::count())->toBe(0);
})->with([true, false]);

it('stores an examination result privately with the correct inherited consultation', function () {
    Storage::fake('local');
    $record = overhaulRecord($this->doctor, $this->patient);
    $order = Order::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'medical_record_id' => $record->id,
        'order_type' => 'lab', 'test_name' => 'CBC', 'requested_date' => today(), 'status' => 'requested']);
    $this->post(route('clinical.documents.store', $this->patient), ['title' => 'Lab report', 'order_id' => $order->id,
        'document' => UploadedFile::fake()->create('result.pdf', 32, 'application/pdf')])->assertSessionHasNoErrors();
    $document = MedicalDocument::first();
    expect($document->medical_record_id)->toBe($record->id)->and($document->order_id)->toBe($order->id);
    Storage::disk('local')->assertExists($document->path);
    $this->get(route('clinical.documents.view', $document))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'");
    $this->get(route('clinical.documents.view', ['document' => $document, 'download' => 1]))
        ->assertOk()->assertDownload('document-'.$document->id.'.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
    $this->actingAs(User::factory()->doctor()->create())->get(route('clinical.documents.view', $document))->assertForbidden();
    $this->actingAs(User::factory()->receptionist()->create())->get(route('clinical.documents.view', $document))->assertForbidden();
    $this->get(route('clinical.documents.view', ['document' => $document, 'download' => 1]))->assertForbidden();
    $this->actingAs($this->doctor);
    Storage::disk('local')->delete($document->path);
    $this->get(route('clinical.documents.view', $document))->assertNotFound();
});

it('rejects oversized or active-content document uploads', function (string $name, string $mime, int $size) {
    Storage::fake('local');
    $this->post(route('clinical.documents.store', $this->patient), ['title' => 'Invalid report',
        'document' => UploadedFile::fake()->create($name, $size, $mime)])->assertSessionHasErrors('document');
    expect(MedicalDocument::count())->toBe(0)->and(Storage::disk('local')->allFiles())->toBe([]);
})->with([['large.pdf', 'application/pdf', 10241], ['unsafe.html', 'text/html', 10], ['unsafe.svg', 'image/svg+xml', 10]]);

it('refuses to link a document to an examination from a different consultation', function () {
    Storage::fake('local');
    $record = overhaulRecord($this->doctor, $this->patient);
    $other = overhaulRecord($this->doctor, $this->patient);
    $order = Order::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'medical_record_id' => $other->id,
        'order_type' => 'scan', 'test_name' => 'X-ray', 'requested_date' => today(), 'status' => 'requested']);
    $this->post(route('clinical.documents.store', $this->patient), ['title' => 'Wrong visit', 'medical_record_id' => $record->id,
        'order_id' => $order->id, 'document' => UploadedFile::fake()->create('report.pdf', 10, 'application/pdf')])->assertSessionHasErrors('order_id');
    expect(MedicalDocument::count())->toBe(0)->and(Storage::disk('local')->allFiles())->toBe([]);
});

it('prevents reparenting a consultation with examination requests', function () {
    $record = overhaulRecord($this->doctor, $this->patient);
    Order::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'medical_record_id' => $record->id,
        'order_type' => 'lab', 'test_name' => 'CBC', 'requested_date' => today(), 'status' => 'requested']);
    $this->put(route('medical-records.update', $record), ['patient_id' => Patient::factory()->create()->id,
        'visit_date' => today()->toDateString()])->assertSessionHasErrors('patient_id');
    expect($record->fresh()->patient_id)->toBe($this->patient->id);
});

it('keeps clinical endpoints unavailable to receptionists', function () {
    $record = overhaulRecord($this->doctor, $this->patient);
    $order = Order::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'medical_record_id' => $record->id,
        'order_type' => 'lab', 'test_name' => 'CBC', 'requested_date' => today(), 'status' => 'requested']);
    $this->actingAs(User::factory()->receptionist()->create());
    $this->post(route('clinical.orders.store', $this->patient), [])->assertForbidden();
    $this->post(route('clinical.documents.store', $this->patient), [])->assertForbidden();
    $this->get(route('clinical.orders.print', $order))->assertForbidden();
    $this->patch(route('clinical.orders.update', $order), ['status' => 'completed'])->assertForbidden();
    $this->getJson(route('clinical.medications.search', ['q' => 'test']))->assertForbidden();
});

it('suggests only the signed in doctors vocabulary and never dosage', function () {
    foreach ([$this->doctor, User::factory()->doctor()->create()] as $index => $doctor) {
        $rx = Prescription::create(['doctor_id' => $doctor->id, 'patient_id' => $this->patient->id, 'prescribed_date' => today()]);
        $rx->items()->create(['medication_name' => $index ? 'Example other' : 'Example own', 'dosage' => 'PRIVATE DOSE', 'frequency' => 'Manual']);
    }
    $this->getJson(route('clinical.medications.search', ['q' => 'Example']))->assertOk()->assertExactJson(['Example own']);
    $this->getJson(route('clinical.medications.search', ['q' => 'x']))->assertUnprocessable();
});

it('requires explicit confirmation for overlap and respects the exact selected slot', function () {
    Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $data = ['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'appointment_date' => today()->toDateString(), 'appointment_time' => '11:00'];
    $this->post(route('appointments.store'), $data)->assertSessionHasErrors('appointment_time');
    $this->post(route('appointments.store'), $data + ['scheduling_override' => 1, 'override_reason' => 'Intentional double booking'])->assertSessionHasNoErrors();
    $visit = Appointment::where('patient_id', $this->patient->id)->first();
    expect($visit->appointment_date->toDateString())->toBe('2026-10-12')->and($visit->appointment_time->format('H:i'))->toBe('11:00')
        ->and($visit->scheduling_override)->toBeTrue()->and($visit->override_by)->toBe($this->doctor->id);
    $this->get(route('dashboard'))->assertOk()->assertSee($this->patient->full_name);
});

it('allows authorized walk in exceptions outside hours without changing the slot', function (string $time) {
    $receptionist = User::factory()->receptionist()->create();
    $this->actingAs($receptionist)->post(route('appointments.store'), ['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id,
        'appointment_date' => today()->toDateString(), 'appointment_time' => $time, 'scheduling_override' => 1])->assertSessionHasNoErrors();
    $visit = Appointment::first();
    expect($visit->appointment_time->format('H:i'))->toBe($time)->and($visit->override_by)->toBe($receptionist->id);
})->with(['08:30', '18:00']);

it('does not allow overriding a past date or invalid time', function () {
    $data = ['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id, 'appointment_date' => today()->subDay()->toDateString(),
        'appointment_time' => '11:00', 'scheduling_override' => 1];
    $this->post(route('appointments.store'), $data)->assertSessionHasErrors('appointment_date');
    $data['appointment_date'] = today()->toDateString();
    $data['appointment_time'] = '99:99';
    $this->post(route('appointments.store'), $data)->assertSessionHasErrors('appointment_time');
    expect(Appointment::count())->toBe(0);
});

it('reorders scheduled operational priority without modifying booked times', function () {
    $first = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $second = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:30:00']);
    $scheduler = app(AppointmentSchedulingService::class);
    expect($scheduler->moveQueueAppointment($second, 'up'))->toBeTrue();
    expect($first->fresh()->appointment_time->format('H:i'))->toBe('11:00')->and($second->fresh()->appointment_time->format('H:i'))->toBe('11:30');
    $this->get(route('dashboard'))->assertViewHas('nextAppointment', fn ($next) => $next->id === $second->id);
    expect($scheduler->promoteNextPatient($this->doctor->id, today())->id)->toBe($second->id);
});

it('does not mark an unchecked scheduled patient as waiting in the header', function () {
    $visit = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $this->get(route('waitlist.index'))->assertOk()->assertSee(__('workflow.appointment_openings'))
        ->assertDontSee('topbar-chip-label">'.__('workflow.waiting_room'), false);
    $visit->update(['checked_in_at' => now()]);
    $this->get(route('waitlist.index'))->assertOk()->assertSee('topbar-chip-label">'.__('workflow.waiting_room'), false);
});

it('recovers validation input only in the form it came from', function () {
    $visit = Appointment::factory()->create(['patient_id' => $this->patient->id, 'doctor_id' => $this->doctor->id, 'status' => 'in_progress']);
    overhaulRecord($this->doctor, $this->patient, ['appointment_id' => $visit->id, 'notes' => 'Saved clinical notes']);
    $response = $this->withSession(['_old_input' => ['workspace_form' => 'prescription', 'notes' => 'Prescription-only notes']])
        ->get(route('doctor.current'))->assertOk();
    $response->assertSee('rows="2" maxlength="16000">Saved clinical notes</textarea>', false)
        ->assertSee('rows="2">Prescription-only notes</textarea>', false);
});

it('has complete matching French and English workflow dictionaries', function () {
    $en = require resource_path('lang/en/workflow.php');
    $fr = require resource_path('lang/fr/workflow.php');
    expect(array_keys($fr))->toBe(array_keys($en));
    foreach (['en', 'fr'] as $language) {
        Setting::setLanguage($language);
        $this->withSession(['locale' => $language])->get(route('doctor.current'))->assertOk()->assertDontSee('workflow.workspace_help')
            ->assertSee(__('auth.doctor'));
    }
});

it('returns a doctor directly to the workspace after the switch confirmation', function () {
    $current = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'status' => 'in_progress']);
    $next = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $url = route('appointments.switch-current', ['appointment' => $next, 'return_to_workspace' => 1]);
    $this->post(route('appointments.set-current', $next), ['return_to_workspace' => 1])->assertRedirect($url);
    $this->get($url)->assertOk()->assertSee('name="return_to_workspace" value="1"', false);
    $this->post(route('appointments.set-current', $next), ['return_to_workspace' => 1, 'previous_action' => 'completed',
        'previous_appointment_id' => $current->id])->assertRedirect(route('doctor.current'));
    expect($current->fresh()->status)->toBe('completed')->and($next->fresh()->status)->toBe('in_progress');
});

it('preserves the original refusal and both appointments on a failed switch', function () {
    $current = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'status' => 'in_progress']);
    overhaulRecord($this->doctor, $current->patient, ['appointment_id' => $current->id]);
    $next = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $this->post(route('appointments.set-current', $next), ['previous_action' => 'no_show', 'previous_appointment_id' => $current->id])
        ->assertRedirect(route('appointments.show', $next))->assertSessionHas('error', __('appointments.switch_no_show_record'));
    expect($current->fresh()->status)->toBe('in_progress')->and($next->fresh()->status)->toBe('scheduled');
});

it('allows explicitly confirmed edit overlap while keeping the requested time', function () {
    $existing = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00']);
    $visit = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '12:00:00']);
    $data = ['doctor_id' => $this->doctor->id, 'patient_id' => $visit->patient_id, 'appointment_date' => today()->toDateString(),
        'appointment_time' => '11:00', 'status' => 'scheduled'];
    $this->put(route('appointments.update', $visit), $data)->assertSessionHasErrors('appointment_time');
    $this->put(route('appointments.update', $visit), $data + ['scheduling_override' => 1])->assertSessionHasNoErrors();
    expect($visit->fresh()->appointment_time->format('H:i'))->toBe('11:00')->and($visit->fresh()->scheduling_override)->toBeTrue()
        ->and($existing->fresh()->status)->toBe('scheduled');
});

it('paginates complete clinical history without leaking other doctors requests', function () {
    for ($i = 1; $i <= 31; $i++) {
        Order::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patient->id,
            'order_type' => 'lab', 'test_name' => 'Own request '.$i, 'requested_date' => today(), 'status' => 'requested']);
    }
    $other = Order::create(['doctor_id' => User::factory()->doctor()->create()->id, 'patient_id' => $this->patient->id,
        'order_type' => 'lab', 'test_name' => 'Other doctor secret', 'requested_date' => today(), 'status' => 'requested']);
    $this->get(route('clinical.history', $this->patient))->assertOk()->assertDontSee('Other doctor secret')
        ->assertViewHas('orders', fn ($orders) => $orders->total() === 31 && $orders->count() === 20);
    $this->get(route('clinical.history', ['patient' => $this->patient, 'orders_page' => 2]))->assertOk()->assertSee('Own request 1');
    $this->get(route('clinical.orders.print', $other))->assertForbidden();
    $this->actingAs(User::factory()->receptionist()->create())->get(route('clinical.history', $this->patient))->assertForbidden();
});

it('uses explicit scheduled priority consistently even across arrival groups', function () {
    $arrived = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '11:00:00', 'checked_in_at' => now()]);
    $other = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'appointment_time' => '10:30:00']);
    $scheduler = app(AppointmentSchedulingService::class);
    $this->get(route('dashboard'))->assertViewHas('nextAppointment', fn ($next) => $next->id === $arrived->id);
    expect($scheduler->moveQueueAppointment($other, 'up'))->toBeTrue();
    $this->get(route('dashboard'))->assertViewHas('nextAppointment', fn ($next) => $next->id === $other->id);
    $this->get(route('appointments.queue'))->assertViewHas('queueAppointments', fn ($visits) => $visits->first()->id === $other->id);
    $this->actingAs(User::factory()->receptionist()->create())->get(route('dashboard'))
        ->assertViewHas('nextByDoctor', fn ($next) => $next[$this->doctor->id]->id === $other->id);
    expect($scheduler->promoteNextPatient($this->doctor->id)->id)->toBe($other->id);
});

it('does not carry attendance or scheduled priority onto a different day', function () {
    $visit = Appointment::factory()->create(['doctor_id' => $this->doctor->id, 'checked_in_at' => now(), 'queue_position' => 1]);
    $data = ['doctor_id' => $this->doctor->id, 'patient_id' => $visit->patient_id, 'appointment_date' => today()->addDay()->toDateString(),
        'appointment_time' => '11:00', 'status' => 'scheduled'];
    $this->put(route('appointments.update', $visit), $data)->assertSessionHasNoErrors();
    expect($visit->fresh()->checked_in_at)->toBeNull()->and($visit->fresh()->queue_position)->toBeNull();
    $data['appointment_date'] = today()->subDay()->toDateString();
    $data['scheduling_override'] = 1;
    $this->put(route('appointments.update', $visit), $data)->assertSessionHasErrors('appointment_date');
});

it('keeps clinically documented appointments assigned to their original doctor', function () {
    $visit = Appointment::factory()->create(['doctor_id' => $this->doctor->id]);
    overhaulRecord($this->doctor, $visit->patient, ['appointment_id' => $visit->id]);
    $newDoctor = User::factory()->doctor()->create();
    $this->actingAs(User::factory()->receptionist()->create())->put(route('appointments.update', $visit), [
        'doctor_id' => $newDoctor->id, 'patient_id' => $visit->patient_id, 'appointment_date' => today()->toDateString(),
        'appointment_time' => '11:00', 'status' => 'scheduled'])->assertSessionHasErrors('doctor_id');
    expect($visit->fresh()->doctor_id)->toBe($this->doctor->id);
});

it('recovers a malformed setup staff list without a wizard rendering error', function () {
    Setting::setSetupComplete(false);
    $this->withSession(['_old_input' => ['doctors' => 'invalid', 'receptionists' => [null]]])->get(route('setup.show'))
        ->assertOk()->assertSee('setup_owner_doctor_name');
});

it('localizes newly suggested follow up reasons without altering clinical history', function () {
    app()->setLocale('fr');
    expect(app(\App\Services\SpecialtyService::class)->defaultFollowUpReason('pediatrics'))->toBe(__('Pediatric follow-up'));
});
