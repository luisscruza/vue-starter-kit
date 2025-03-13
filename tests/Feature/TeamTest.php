<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('create team page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.create'));

    $response->assertStatus(200);
});

test('guests cannot create teams', function () {
    $response = $this->post(route('teams.store'), [
        'name' => 'Test Team',
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseMissing('teams', [
        'name' => 'Test Team',
    ]);
});

test('users can create teams', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $response
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Team created successfully.');

    $team = Team::where('name', 'Test Team')->first();

    $this->assertNotNull($team);
    $this->assertTrue($user->ownedTeams->contains($team));
    $this->assertTrue($user->team_id === $team->id);

    // Test roles and permissions
    $adminRole = $team->roles()->where('name', 'admin')->first();
    $memberRole = $team->roles()->where('name', 'member')->first();

    $this->assertNotNull($adminRole);
    $this->assertNotNull($memberRole);

    $this->assertTrue($adminRole->hasPermissionTo('view-team'));
    $this->assertTrue($adminRole->hasPermissionTo('edit-team'));
    $this->assertTrue($adminRole->hasPermissionTo('delete-team'));
    $this->assertTrue($memberRole->hasPermissionTo('view-team'));
});

test('team name is required', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => '',
        ]);

    $response
        ->assertSessionHasErrors('name')
        ->assertStatus(302);
});

test('team name cannot exceed 255 characters', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => str_repeat('a', 256),
        ]);

    $response
        ->assertSessionHasErrors('name')
        ->assertStatus(302);
});

test('users can view their team', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $team = Team::where('name', 'Test Team')->first();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.settings', $team));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('teams/View')
        ->has('team')
        ->where('team.name', 'Test Team')
    );
});

test('users cannot view teams they do not belong to', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create a team with otherUser
    $response = $this
        ->actingAs($otherUser)
        ->post(route('teams.store'), [
            'name' => 'Other Team',
        ]);

    $team = Team::where('name', 'Other Team')->first();

    // Attempt to view the team as user
    $response = $this
        ->actingAs($user)
        ->get(route('teams.settings', $team));

    $response->assertStatus(403);
});

test('a request without a user will redirect to login', function () {
    $response = $this->post(route('teams.store'), [
        'name' => 'Test Team',
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseMissing('teams', [
        'name' => 'Test Team',
    ]);
});

test('team view shows correct team information', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $team = Team::where('name', 'Test Team')->first();

    $response = $this
        ->actingAs($user)
        ->get(route('teams.settings', $team));

    $response->assertInertia(fn ($page) => $page
        ->component('teams/View')
        ->has('team', fn ($team) => $team
            ->has('id')
            ->has('name')
            ->has('created_at')
            ->has('updated_at')
            ->where('name', 'Test Team')
        )
    );
});

test('guests cannot view teams', function () {
    $team = Team::factory()->create();

    $response = $this->get(route('teams.settings', $team));

    $response->assertRedirect('/login');
});

test('null user in request returns appropriate response', function () {
    // Test web response
    $response = $this
        ->post(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseMissing('teams', [
        'name' => 'Test Team',
    ]);

    // Test API response
    $response = $this
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('teams.store'), [
            'name' => 'Test Team',
        ]);

    $response->assertStatus(401);
    $this->assertDatabaseMissing('teams', [
        'name' => 'Test Team',
    ]);
});
