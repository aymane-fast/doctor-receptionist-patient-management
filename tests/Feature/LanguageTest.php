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

it('renders local flag images for both staff roles and preserves language switching', function ($role) {
    $user = User::factory()->create(['role' => $role]);
    $this->actingAs($user)->get('/dashboard')->assertOk()
        ->assertSee('images/flags/us.svg')->assertSee('images/flags/fr.svg');
    $this->get('/lang/fr')->assertRedirect();
    $settings = $this->get('/settings')->assertOk()->assertSee('lang="fr"', false)
        ->assertSee('images/flags/fr.svg')->assertSee('images/flags/us.svg');
    if ($role === 'doctor') {
        $settings->assertSee('href="#clinic-settings"', false);
    } else {
        $settings->assertDontSee('href="#clinic-settings"', false);
    }
    $this->get('/lang/en')->assertRedirect();
    $this->get('/dashboard')->assertOk()->assertSee('lang="en"', false);
})->with(['doctor', 'receptionist']);
