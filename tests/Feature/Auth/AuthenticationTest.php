<?php

declare(strict_types=1);
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});
test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});
test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
test('users get locked out after too many login attempts', function () {
    $user = User::factory()->create();

    // Attempt to login 6 times (5 is the limit)
    foreach (range(0, 5) as $attempt) {
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        if ($attempt < 5) {
            $response->assertStatus(302); // Regular failed login redirect
            $this->assertGuest();
        } else {
            // On the 6th attempt, we should get redirected back with errors
            $response->assertStatus(302);
        }
    }

    // Verify that even with correct credentials, we're still locked out
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('rate limiter is cleared after successful login', function () {
    $user = User::factory()->create();

    // Make 4 failed attempts (not enough to trigger lockout)
    foreach (range(1, 4) as $_) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
    }

    // Successfully login
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    // Logout
    $this->post('/logout');

    // Try to login again - should work because rate limiter was cleared
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
