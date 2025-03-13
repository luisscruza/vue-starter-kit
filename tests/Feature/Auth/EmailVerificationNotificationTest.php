<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

test('verification notification can be sent to unverified user', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertSessionHas('status', 'verification-link-sent');
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('verification notification is not sent to verified user', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertRedirect(route('dashboard'));
    Notification::assertNotSentTo($user, VerifyEmail::class);
});

test('verification notification is rate limited', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    // Send 6 requests (rate limit is 6 per minute)
    for ($i = 0; $i < 6; $i++) {
        $this->actingAs($user)->post(route('verification.send'));
    }

    // The 7th request should be rate limited
    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertStatus(429); // Too Many Requests
    Notification::assertSentTo($user, VerifyEmail::class, 6);
});

test('guests cannot send verification notification', function () {
    $response = $this->post(route('verification.send'));

    $response->assertRedirect(route('login'));
});
