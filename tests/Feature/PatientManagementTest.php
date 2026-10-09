<?php

use App\Models\Patient;
use App\Models\User;

it('creates a patient with only the required fields', function () {
    $receptionist = User::factory()->receptionist()->create();

    $response = $this->actingAs($receptionist)->post('/patients', [
        'first_name' => 'Walk',
        'last_name' => 'In',
        'phone' => '+212600000001',
        'id_card_number' => 'WI0001',
    ]);

    $patient = Patient::where('id_card_number', 'WI0001')->first();

    expect($patient)->not->toBeNull()
        ->and($patient->birth_date)->toBeNull()
        ->and($patient->gender)->toBeNull()
        ->and($patient->age)->toBeNull();

    $response->assertRedirect(route('patients.show', $patient));
});

it('renders profile and edit pages for a patient without birth date or gender', function () {
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create([
        'birth_date' => null,
        'gender' => null,
    ]);

    $this->actingAs($receptionist)->get(route('patients.show', $patient))->assertOk();
    $this->actingAs($receptionist)->get(route('patients.edit', $patient))->assertOk();
});

it('allows a patient with unknown birth date and gender to be updated', function () {
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create([
        'birth_date' => null,
        'gender' => null,
    ]);

    $this->actingAs($receptionist)->put(route('patients.update', $patient), [
        'first_name' => 'Updated',
        'last_name' => $patient->last_name,
        'phone' => $patient->phone,
        'id_card_number' => $patient->id_card_number,
    ])->assertRedirect(route('patients.show', $patient));

    expect($patient->fresh()->first_name)->toBe('Updated')
        ->and($patient->fresh()->birth_date)->toBeNull()
        ->and($patient->fresh()->gender)->toBeNull();
});

it('does not create a patient when a walk-in booking cannot be made', function () {
    $receptionist = User::factory()->receptionist()->create();

    foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
        \App\Models\Setting::set("{$day}_working", false, 'boolean');
    }

    $this->actingAs($receptionist)->from('/patients/create')->post('/patients', [
        'first_name' => 'No',
        'last_name' => 'Booking',
        'phone' => '+212600000099',
        'id_card_number' => 'NOBOOK001',
        'book_today' => '1',
        'appointment_date' => today()->toDateString(),
    ])->assertSessionHasErrors('book_today');

    expect(Patient::where('id_card_number', 'NOBOOK001')->exists())->toBeFalse();
});

it('rejects duplicate ID card numbers', function () {
    $receptionist = User::factory()->receptionist()->create();
    Patient::factory()->create(['id_card_number' => 'DUP001']);

    $this->actingAs($receptionist)->from('/patients/create')->post('/patients', [
        'first_name' => 'Second',
        'last_name' => 'Person',
        'phone' => '+212600000002',
        'id_card_number' => 'DUP001',
    ])->assertSessionHasErrors('id_card_number');
});

it('updates a patient', function () {
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($receptionist)->put(route('patients.update', $patient), [
        'first_name' => 'Updated',
        'last_name' => $patient->last_name,
        'birth_date' => '1990-05-05',
        'gender' => 'female',
        'phone' => $patient->phone,
        'id_card_number' => $patient->id_card_number,
    ])->assertRedirect(route('patients.show', $patient));

    expect($patient->fresh()->first_name)->toBe('Updated');
});

it('blocks receptionists from deleting patients', function () {
    $receptionist = User::factory()->receptionist()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($receptionist)
        ->delete(route('patients.destroy', $patient))
        ->assertForbidden();

    expect(Patient::find($patient->id))->not->toBeNull();
});

it('lets doctors delete patients', function () {
    $doctor = User::factory()->doctor()->create();
    $patient = Patient::factory()->create();

    $this->actingAs($doctor)
        ->delete(route('patients.destroy', $patient))
        ->assertRedirect(route('patients.index'));

    expect(Patient::find($patient->id))->toBeNull();
});

it('finds patients by reversed full name', function () {
    $receptionist = User::factory()->receptionist()->create();
    Patient::factory()->create(['first_name' => 'Amina', 'last_name' => 'Berrada']);
    Patient::factory()->create(['first_name' => 'Omar', 'last_name' => 'Alaoui']);

    $response = $this->actingAs($receptionist)->get('/patients?search=Berrada Amina');

    $response->assertOk()
        ->assertSee('Amina')
        ->assertDontSee('Omar');
});

it('finds patients by phone number', function () {
    $receptionist = User::factory()->receptionist()->create();
    Patient::factory()->create(['first_name' => 'Karim', 'phone' => '+212677889900']);

    $this->actingAs($receptionist)
        ->get('/patients?search=677889900')
        ->assertOk()
        ->assertSee('Karim');
});

it('shows an empty state until a search is performed', function () {
    $receptionist = User::factory()->receptionist()->create();
    Patient::factory()->count(3)->create();

    $this->actingAs($receptionist)->get('/patients')->assertOk();
});

it('returns patient search results as JSON for autocomplete', function () {
    $receptionist = User::factory()->receptionist()->create();
    Patient::factory()->create(['first_name' => 'Yasmine', 'last_name' => 'Chraibi']);

    $response = $this->actingAs($receptionist)->getJson('/api/patients/search?q=Yasmine');

    $response->assertOk();
    expect($response->json())->toHaveCount(1)
        ->and($response->json('0.name'))->toContain('Yasmine');
});
