<?php

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\DataExportService;

it('retains export selections after a localized validation failure', function (string $locale) {
    $doctor = User::factory()->doctor()->create();
    $response = $this->actingAs($doctor)->withSession(['locale' => $locale])->from('/exports')->post('/exports', [
        'data_types' => ['patients', 'appointments'], 'start_date' => today()->addDay()->toDateString(),
    ])->assertSessionHasErrors('start_date');
    expect(session('errors')->first('start_date'))->toBe(trans('exports.start_future', [], $locale));
    $page = $this->get('/exports')->assertOk();
    $dom = new DOMDocument;
    @$dom->loadHTML($page->getContent());
    $checked = (new DOMXPath($dom))->query('//input[@name="data_types[]" and @checked]');
    $values = [];
    foreach ($checked as $input) $values[] = $input->getAttribute('value');
    expect($values)->toEqualCanonicalizing(['patients', 'appointments']);
})->with(['en', 'fr']);

it('exports only a completed past or present appointment as the last visit', function () {
    $patient = Patient::factory()->create();
    $doctor = User::factory()->doctor()->create();
    foreach ([['completed', -10], ['no_show', -5], ['scheduled', 5], ['completed', 10]] as [$status, $days]) {
        Appointment::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctor->id,
            'status' => $status, 'appointment_date' => today()->addDays($days)]);
    }
    $patients = app(DataExportService::class)->exportData(['patients'])['data']['patients'];
    $index = array_search(__('Last Visit'), $patients['headers'], true);
    expect($patients['data'][0][$index])->toBe(today()->subDays(10)->toDateString());
    Appointment::where('patient_id', $patient->id)->where('status', 'completed')->delete();
    $patients = app(DataExportService::class)->exportData(['patients'])['data']['patients'];
    expect($patients['data'][0][$index])->toBe('');
});
