<?php

declare(strict_types=1);
use App\Models\User;
use Illuminate\Support\Facades\Hash;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/settings/password')
        ->put('/settings/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/password');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});
test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/settings/password')
        ->put('/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect('/settings/password');
});

test('password confirmation page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/confirm-password');

    $response->assertStatus(200);
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/confirm-password', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertNotNull(session('auth.password_confirmed_at'));
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertStatus(302);
});

test('password settings page shows correct verification status for verified email user', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/password');

    $response->assertInertia(fn ($page) => $page
        ->component('settings/Password')
        ->where('mustVerifyEmail', true)
        ->where('status', null)
    );
});

test('password settings page shows status message when present', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->session(['status' => 'test-status'])
        ->get('/settings/password');

    $response->assertInertia(fn ($page) => $page
        ->component('settings/Password')
        ->where('mustVerifyEmail', true)
        ->where('status', 'test-status')
    );
});

test('password settings page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/password');

    $response->assertStatus(200);
});
