<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can register to a team through an invitation', function () {
    $team = Team::factory()->create();

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@test.com',
    ]);

    $response = $this->post(route('teams.invitations.accept', ['team' => $team->id, 'invitation' => $invitation->uuid]), [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $response->assertSessionHas('success', 'You have been added to '.$team->name);

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@test.com',
    ]);

    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('it throws an error when an auth user tries to access an invitation that is not for them', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@test.com',
    ]);

    $response = $this->actingAs($user)->get(route('teams.invitations.accept', ['team' => $team->id, 'invitation' => $invitation->uuid]));

    $response->assertRedirect(route('dashboard'));

});

it('adds an auth user to a team when they accept an invitation', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();

    $invitation = TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => $user->email,
    ]);

    $response = $this->actingAs($user)->put(route('teams.invitations.accept.auth', ['team' => $team->id, 'invitation' => $invitation->uuid]));

    $response->assertRedirect(route('dashboard'));

    $response->assertSessionHas('success', 'You have been added to '.$team->name);

});
