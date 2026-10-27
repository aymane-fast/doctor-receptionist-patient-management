<?php

use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;

it('rejects unusable patient contacts on creation and correction for both roles', function ($role) {
    $this->actingAs(User::factory()->create(['role' => $role]));
    $data = ['first_name' => 'Synthetic', 'last_name' => 'Contact', 'id_card_number' => 'AUDIT-CONTACT', 'phone' => '123'];
    $this->post(route('patients.store'), $data)->assertSessionHasErrors('phone');
    $this->assertDatabaseMissing('patients', ['id_card_number' => 'AUDIT-CONTACT']);
    $data['phone'] = '+212 (600) 000-025';
    $this->post(route('patients.store'), $data)->assertSessionHasNoErrors();
    $patient = Patient::where('id_card_number', 'AUDIT-CONTACT')->firstOrFail();
    $this->put(route('patients.update', $patient), array_merge($data, ['phone' => 'not a phone', 'emergency_contact_phone' => '12']))
        ->assertSessionHasErrors(['phone', 'emergency_contact_phone']);
    expect($patient->fresh()->phone)->toBe('+212 (600) 000-025');
})->with(['doctor', 'receptionist']);

it('requires usable guardian contacts without requiring a child phone', function () {
    Setting::set('clinic_specialty', 'pediatrics', 'string');
    $this->actingAs(User::factory()->doctor()->create());
    $data = ['first_name' => 'Child', 'last_name' => 'Audit', 'birth_date' => '2020-02-29',
        'primary_guardian' => 0, 'guardians' => [['name' => 'Parent', 'relationship' => 'Mother', 'phone' => '123']]];
    $this->post(route('patients.store'), $data)->assertSessionHasErrors('guardians.0.phone');
    $data['guardians'][0]['phone'] = '06 00 00 00 25';
    $this->post(route('patients.store'), $data)->assertSessionHasNoErrors();
    $this->assertDatabaseHas('patients', ['first_name' => 'Child', 'phone' => '06 00 00 00 25']);
});
