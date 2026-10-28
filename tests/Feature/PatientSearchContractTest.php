<?php

use App\Models\Patient;
use App\Models\User;
use App\Services\PatientSearchService;

it('preserves patient search scoring for each matching branch', function ($query, $score, $lastName) {
    $patient = Patient::factory()->create([
        'first_name' => 'Alice', 'last_name' => $lastName, 'phone' => '+212600000000',
        'email' => 'test@example.org', 'id_card_number' => 'IDENT123',
    ]);
    $result = PatientSearchService::search($query);
    expect($result->pluck('id')->all())->toBe([$patient->id])
        ->and($result->first()['score'])->toBe($score);
})->with([
    ['Alice Doe', 2200, 'Doe'], ['Alice', 1400, 'Doe'], ['Doe', 1300, 'Doe'],
    ['Ali', 1200, 'Doe'], ['lic', 900, 'Doe'], ['IDENT123', 900, 'Doe'],
    ['2126', 300, 'Doe'], ['test@', 400, 'Doe'], ['Alice', 1400, 'Alice'],
]);

it('keeps clinical autocomplete response fields and compatibility parameters', function ($role, $route, $parameter) {
    $user = User::factory()->create(['role' => $role]);
    $patient = Patient::factory()->create(['first_name' => 'Synthetic', 'last_name' => 'Search Contract', 'email' => null]);
    $this->actingAs($user)->getJson(route($route, [$parameter => 'Synthetic Search Contract']))
        ->assertOk()->assertExactJson([[
            'id' => $patient->id, 'name' => $patient->full_name,
            'phone' => $patient->phone, 'email' => 'No email', 'display' => $patient->selection_label,
        ]]);
})->with([
    ['doctor', 'api.medical-records.patients.search', 'q'],
    ['receptionist', 'api.medical-records.patients.search', 'query'],
    ['doctor', 'api.prescriptions.patients.search', 'q'],
    ['receptionist', 'api.prescriptions.patients.search', 'q'],
]);
