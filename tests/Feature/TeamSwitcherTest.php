<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('users can switch between their teams', function () {
    $user = User::factory()->create();
    $role = Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    // Create first team
    $firstTeam = Team::factory()->create(['user_id' => $user->id]);
    $user->teams()->attach($firstTeam, ['role_id' => $role->id]);
    $user->setCurrentTeam($firstTeam);

    // Create second team
    $secondTeam = Team::factory()->create(['user_id' => $user->id]);
    $role = Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $user->teams()->attach($secondTeam, ['role_id' => $role->id]);

    // Switch to second team
    $response = $this
        ->actingAs($user)
        ->put(route('teams.switch', $secondTeam));

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Team switched successfully');

    $this->assertTrue($user->fresh()->team_id === $secondTeam->id);
});

test('users cannot switch to teams they do not belong to', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Create team owned by other user
    $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);

    $response = $this
        ->actingAs($user)
        ->put(route('teams.switch', $otherTeam));

    $response
        ->assertRedirect()
        ->assertSessionHas('error', 'You do not have access to this team.');

    $this->assertNotEquals($user->fresh()->team_id, $otherTeam->id);
});

test('guests cannot switch teams', function () {
    $team = Team::factory()->create();

    $response = $this->put(route('teams.switch', $team));

    $response->assertRedirect('/login');
});

test('switching teams with null user returns error', function () {
    $team = Team::factory()->create();

    // Test web response
    $response = $this->put(route('teams.switch', $team));

    $response->assertRedirect('/login');

    // Test API response
    $response = $this
        ->withHeaders(['Accept' => 'application/json'])
        ->put(route('teams.switch', $team));

    $response->assertStatus(401);
});

test('users stay on current team after failed switch attempt', function () {
    $user = User::factory()->create();

    $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);

    // Create and set current team
    $currentTeam = Team::factory()->create(['user_id' => $user->id]);
    $user->teams()->attach($currentTeam, ['role_id' => $role->id]);
    $user->setCurrentTeam($currentTeam);

    // Create another team that user doesn't belong to
    $otherTeam = Team::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('teams.switch', $otherTeam));

    $response
        ->assertRedirect()
        ->assertSessionHas('error', 'You do not have access to this team.');

    $this->assertTrue($user->fresh()->team_id === $currentTeam->id);
});
