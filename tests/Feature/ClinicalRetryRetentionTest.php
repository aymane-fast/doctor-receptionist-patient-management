<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\UploadedFile;

it('keeps the selected examination type after rejection in either clinical interface', function (string $screen) {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $visit = Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'status' => 'in_progress']);
    $record = MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'appointment_id' => $visit->id, 'visit_date' => today()]);
    $origin = $screen === 'workspace' ? route('doctor.current') : route('clinical.history', ['patient' => $patient, 'medical_record_id' => $record->id]);
    $this->actingAs($doctor)->from($origin)->post(route('clinical.orders.store', $patient), [
        'workspace_form' => 'examinations', 'medical_record_id' => $record->id,
        'order_type' => 'scan', 'test_name' => '   ', 'notes' => 'Retain this imaging instruction',
    ])->assertSessionHasErrors('test_name');
    $page = $this->get($origin)->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($page->getContent());
    expect((new DOMXPath($dom))->query('//select[@id="order_type"]/option[@value="scan" and @selected]')->length)->toBe(1);
    $page->assertSee('Retain this imaging instruction');
    $this->assertDatabaseCount('orders', 0);
})->with(['workspace', 'history']);

it('keeps the related examination after a rejected document upload in either clinical interface', function (string $screen) {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();
    $visit = Appointment::factory()->create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'status' => 'in_progress']);
    $record = MedicalRecord::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id,
        'appointment_id' => $visit->id, 'visit_date' => today()]);
    $order = Order::create(['doctor_id' => $doctor->id, 'patient_id' => $patient->id, 'medical_record_id' => $record->id,
        'order_type' => 'scan', 'test_name' => 'Synthetic imaging', 'requested_date' => today(), 'status' => 'requested']);
    $origin = $screen === 'workspace' ? route('doctor.current') : route('clinical.history', ['patient' => $patient, 'medical_record_id' => $record->id]);
    $this->actingAs($doctor)->from($origin)->post(route('clinical.documents.store', $patient), [
        'workspace_form' => 'documents', 'medical_record_id' => $record->id, 'order_id' => $order->id,
        'title' => 'Retain this report title', 'document' => UploadedFile::fake()->create('invalid.txt', 1, 'text/plain'),
    ])->assertSessionHasErrors('document');
    $page = $this->get($origin)->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($page->getContent());
    $selected = (new DOMXPath($dom))->query('//select[@id="document_order"]/option[@selected]');
    expect($selected->length)->toBe(1)->and($selected->item(0)->getAttribute('value'))->toBe((string) $order->id);
    $page->assertSee('Retain this report title');
    $this->assertDatabaseCount('medical_documents', 0);
})->with(['workspace', 'history']);
