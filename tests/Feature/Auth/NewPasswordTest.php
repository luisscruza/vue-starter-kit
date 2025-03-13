<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('reset password screen can be rendered', function () {
    $response = $this->get('/reset-password/token');

    $response->assertInertia(fn ($page) => $page
        ->component('auth/ResetPassword')
        ->has('token')
        ->has('email')
    );
});

test('password can be reset with valid token', function () {
    Event::fake();

    $user = User::factory()->create();

    $token = Password::createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    Event::assertDispatched(PasswordReset::class);
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', trans('passwords.reset'));
});

test('password reset fails with invalid token', function () {
    $user = User::factory()->create();

    $response = $this->from('/reset-password/invalid-token')->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('password reset fails with invalid email', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->from('/reset-password/'.$token)->post('/reset-password', [
        'token' => $token,
        'email' => 'wrong@email.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('password reset requires valid password', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = $this->from('/reset-password/'.$token)->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new',  // Too short
        'password_confirmation' => 'new',
    ]);

    $response->assertSessionHasErrors('password');
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
