<?php

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Setting;
use App\Models\User;
use App\Services\SpecialtyService;

it('confirms the actual follow-up date in the selected language', function () {
    $doctor = User::factory()->doctor()->create();
    $visit = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'in_progress']);
    $this->actingAs($doctor)->withSession(['locale' => 'fr'])->post(route('appointments.create-follow-up', $visit->patient), [
        'current_appointment_id' => $visit->id, 'follow_up_number' => 7, 'follow_up_unit' => 'days',
    ])->assertSessionHasNoErrors()->assertRedirect(route('doctor.current'));
    $booked = Appointment::where('id', '!=', $visit->id)->firstOrFail();
    $date = \Carbon\Carbon::parse($booked->appointment_date->toDateString().' '.$booked->appointment_time->format('H:i'))
        ->locale('fr')->isoFormat('LLL');
    expect(session('success'))->toBe('Le rendez-vous de suivi est prévu le '.$date.'.');
});

it('offers four supported practice choices and preserves an installed legacy profile', function () {
    $doctor = User::factory()->doctor()->create();
    $service = app(SpecialtyService::class);
    expect(array_keys($service->selectableProfiles()))->toEqualCanonicalizing(['general_practice', 'pediatrics', 'dentistry', 'cardiology']);
    $this->actingAs($doctor)->post(route('settings.specialty'), ['clinic_specialty' => 'psychiatry'])
        ->assertSessionHasErrors('clinic_specialty');
    Setting::set('clinic_specialty', 'psychiatry', 'string');
    expect($service->selectableProfiles())->toHaveKey('psychiatry');
    $this->post(route('settings.specialty'), ['clinic_specialty' => 'psychiatry'])->assertSessionHasNoErrors();
    $this->get(route('settings.index'))->assertOk()->assertSee(__('clinical_ui.practice_legacy'));
});

it('saves consultation notes and opens prescriptions without requiring a diagnosis or finishing the visit', function () {
    $doctor = User::factory()->doctor()->create();
    $visit = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'in_progress']);
    $this->actingAs($doctor)->get(route('doctor.current'))->assertOk()
        ->assertSee('id="prepare-prescription"', false)->assertSee('form="active-consultation-form"', false);
    $payload = ['patient_id' => $visit->patient_id, 'appointment_id' => $visit->id,
        'visit_date' => today()->toDateString(), 'symptoms' => 'Draft observations',
        'workspace_form' => 'consultation', 'workspace_next' => 'prescription', 'return_to_workspace' => 1];
    $this->post(route('medical-records.store'), $payload)->assertRedirect(route('doctor.current').'#prescription');
    expect($visit->fresh()->status)->toBe('in_progress')
        ->and($visit->fresh()->medicalRecord->diagnosis)->toBeNull()
        ->and($visit->fresh()->medicalRecord->symptoms)->toBe('Draft observations');
    $this->get(route('doctor.current'))->assertOk()->assertDontSee('id="prepare-prescription"', false)
        ->assertSee(route('prescriptions.store'));
});

it('keeps invalid prescription handoff notes recoverable without creating a record', function () {
    $doctor = User::factory()->doctor()->create();
    $visit = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'in_progress']);
    $this->actingAs($doctor)->from(route('doctor.current'))->post(route('medical-records.store'), [
        'patient_id' => $visit->patient_id, 'appointment_id' => $visit->id,
        'visit_date' => today()->toDateString(), 'temperature' => 100,
        'symptoms' => 'Keep these notes', 'workspace_form' => 'consultation',
        'workspace_next' => 'prescription', 'return_to_workspace' => 1,
    ])->assertSessionHasErrors('temperature')->assertRedirect(route('doctor.current'));
    expect(MedicalRecord::count())->toBe(0);
    $this->get(route('doctor.current'))->assertOk()->assertSee('Keep these notes')
        ->assertSee('data-initial-tab="consultation"', false);
});

it('exposes scheduled rescheduling consistently to both roles without offering quick rebooking for queue tickets', function ($role) {
    $doctor = User::factory()->doctor()->create();
    $actor = $role === 'doctor' ? $doctor : User::factory()->receptionist()->create();
    $visit = Appointment::factory()->create(['doctor_id' => $doctor->id]);
    $ticket = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_mode' => 'queue', 'queue_number' => 1]);
    foreach ([route('dashboard'), route('appointments.index', ['show_today' => 1]), route('appointments.queue'), route('appointments.show', $visit)] as $url) {
        $this->actingAs($actor)->get($url)->assertOk()->assertSee(route('appointments.reschedule', $visit))
            ->assertSee(route('appointments.rebook-next-slot', $visit))
            ->assertDontSee(route('appointments.rebook-next-slot', $ticket));
    }
})->with(['doctor', 'receptionist']);

it('keeps appointment row actions accessible and limits status actions to permitted transitions', function () {
    $doctor = User::factory()->doctor()->create();
    $cancelled = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'cancelled']);
    $future = Appointment::factory()->create(['doctor_id' => $doctor->id, 'appointment_date' => today()->addDay()]);
    $response = $this->actingAs($doctor)->get(route('appointments.index', ['search' => $cancelled->patient->first_name]));
    $response->assertOk()->assertSee('popovertarget="status-dropdown-'.$cancelled->id.'"', false);
    $document = new DOMDocument();
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $statuses = $xpath->query('//*[@id="status-dropdown-'.$cancelled->id.'"]//input[@name="status"]');
    expect(array_map(fn ($node) => $node->getAttribute('value'), iterator_to_array($statuses)))->toBe(['scheduled']);
    $response = $this->get(route('appointments.index', ['search' => $future->patient->first_name]));
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $statuses = $xpath->query('//*[@id="status-dropdown-'.$future->id.'"]//input[@name="status"]');
    expect(array_map(fn ($node) => $node->getAttribute('value'), iterator_to_array($statuses)))
        ->not->toContain('in_progress', 'no_show');
});
