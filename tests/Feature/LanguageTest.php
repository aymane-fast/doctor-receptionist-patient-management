<?php

use App\Models\Setting;
use App\Models\User;

it('lets a guest switch their session language without changing the clinic default', function () {
    Setting::setLanguage('en');

    $this->get('/lang/fr')->assertRedirect();

    expect(session('locale'))->toBe('fr')
        ->and(Setting::getLanguage())->toBe('en');
});

it('persists the clinic language when a staff member switches', function () {
    Setting::setLanguage('en');
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->get('/lang/fr')->assertRedirect()->assertSessionHas('success', 'La langue a été modifiée.');

    expect(Setting::getLanguage())->toBe('fr');
});

it('rejects unsupported languages', function () {
    $this->get('/lang/xx')->assertRedirect();

    expect(session('locale'))->not->toBe('xx')
        ->and(Setting::getLanguage())->not->toBe('xx');
});

it('returns available languages as JSON', function () {
    $this->getJson('/api/languages')
        ->assertOk()
        ->assertJsonStructure(['languages', 'current']);
});
