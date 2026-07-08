<?php

use App\Models\User;

it('shows the login page to guests', function () {
    $this->get('/login')->assertOk();
});

it('logs in with valid credentials and regenerates the session', function () {
    $user = User::factory()->doctor()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->doctor()->create();

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirect('/login')->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('throttles repeated failed login attempts', function () {
    $user = User::factory()->doctor()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

it('logs out and invalidates the session', function () {
    $user = User::factory()->doctor()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});

it('redirects guests to login for protected pages', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/patients')->assertRedirect('/login');
    $this->get('/appointments')->assertRedirect('/login');
});

it('only lets doctors open the register page', function () {
    $doctor = User::factory()->doctor()->create();
    $receptionist = User::factory()->receptionist()->create();

    $this->actingAs($doctor)->get('/register')->assertOk();
    $this->actingAs($receptionist)->get('/register')->assertForbidden();
});

it('lets a doctor create a staff account', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->post('/register', [
        'name' => 'New Receptionist',
        'email' => 'newstaff@clinic.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'receptionist',
    ])->assertRedirect(route('dashboard'));

    expect(User::where('email', 'newstaff@clinic.test')->value('role'))->toBe('receptionist');
});

it('rejects registration with an invalid role', function () {
    $doctor = User::factory()->doctor()->create();

    $this->actingAs($doctor)->from('/register')->post('/register', [
        'name' => 'Hacker',
        'email' => 'hacker@clinic.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ])->assertSessionHasErrors('role');
});
