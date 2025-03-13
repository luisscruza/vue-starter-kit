<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

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
            ->has('owner', fn ($owner) => $owner
                ->has('id')
                ->has('name')
                ->has('email')
                ->where('id', $user->id)
            )
            ->has('members')
            ->has('invitations')
            ->where('currentUserRole', 'admin')
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

test('team owner can update team name', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->put(route('teams.update', $team), [
            'name' => 'Updated Team Name',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Team updated successfully.');

    $this->assertDatabaseHas('teams', [
        'id' => $team->id,
        'name' => 'Updated Team Name',
    ]);
});

test('non-admin team members cannot update team', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    $memberRole = Role::create(['name' => 'member', 'team_id' => $team->id]);
    $team->users()->attach($member->id, ['role_id' => $memberRole->id]);

    $response = $this
        ->actingAs($member)
        ->put(route('teams.update', $team), [
            'name' => 'Updated Team Name',
        ]);

    $response->assertStatus(403);
});

test('team owner can invite new members', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Create roles first
    $adminRole = Role::create(['name' => 'admin', 'team_id' => $team->id]);
    $memberRole = Role::create(['name' => 'member', 'team_id' => $team->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'test@example.com',
            'role' => 'member',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Invitation sent successfully.');

    $this->assertDatabaseHas('team_invitations', [
        'team_id' => $team->id,
        'email' => 'test@example.com',
        'role_id' => $memberRole->id,
    ]);
});

test('cannot invite same email twice', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    TeamInvitation::factory()->create([
        'team_id' => $team->id,
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'test@example.com',
            'role' => 'member',
        ]);

    $response->assertSessionHasErrors('email');
});

test('team owner can cancel invitations', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $invitation = TeamInvitation::factory()->create(['team_id' => $team->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('teams.invitations.destroy', [
            'team' => $team->id,
            'invitation' => $invitation->id,
        ]));

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Invitation cancelled successfully.');

    $this->assertDatabaseMissing('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('team owner can remove members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    $memberRole = Role::create(['name' => 'member', 'team_id' => $team->id]);
    $team->users()->attach($member->id, ['role_id' => $memberRole->id]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('teams.members.destroy', [
            'team' => $team->id,
            'user' => $member->id,
        ]));

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Team member removed successfully.');

    $this->assertDatabaseMissing('team_user', [
        'team_id' => $team->id,
        'user_id' => $member->id,
    ]);
});

test('cannot remove team owner', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    $adminRole = Role::create(['name' => 'admin', 'team_id' => $team->id]);
    $team->users()->attach($admin->id, ['role_id' => $adminRole->id]);

    $response = $this
        ->actingAs($admin)
        ->delete(route('teams.members.destroy', [
            'team' => $team->id,
            'user' => $owner->id,
        ]));

    $response->assertStatus(403);
});

test('cannot invite members with invalid role', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    // Create roles first
    $adminRole = Role::create(['name' => 'admin', 'team_id' => $team->id]);
    $memberRole = Role::create(['name' => 'member', 'team_id' => $team->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'test@example.com',
            'role' => 'invalid-role',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHasErrors(['role' => 'The selected role is invalid.']);

    $this->assertDatabaseMissing('team_invitations', [
        'team_id' => $team->id,
        'email' => 'test@example.com',
    ]);
});

test('non-admin team members cannot manage invitations', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    $memberRole = Role::create(['name' => 'member', 'team_id' => $team->id]);
    $team->users()->attach($member->id, ['role_id' => $memberRole->id]);

    // Try to create invitation
    $response = $this
        ->actingAs($member)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'test@example.com',
            'role' => 'member',
        ]);

    $response->assertStatus(403);

    // Try to cancel invitation
    $invitation = TeamInvitation::factory()->create(['team_id' => $team->id]);

    $response = $this
        ->actingAs($member)
        ->delete(route('teams.invitations.destroy', [
            'team' => $team->id,
            'invitation' => $invitation->id,
        ]));

    $response->assertStatus(403);
});

test('cannot cancel invitation from different team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $otherTeam = Team::factory()->create(['user_id' => $user->id]);

    $invitation = TeamInvitation::factory()->create(['team_id' => $otherTeam->id]);

    $response = $this
        ->actingAs($user)
        ->delete(route('teams.invitations.destroy', [
            'team' => $team->id,
            'invitation' => $invitation->id,
        ]));

    $response->assertStatus(404);

    $this->assertDatabaseHas('team_invitations', [
        'id' => $invitation->id,
    ]);
});

test('cannot invite members with non-existent team role', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $otherTeam = Team::factory()->create(['user_id' => $user->id]);

    // Create roles for the other team but not for the current team
    Role::create(['name' => 'admin', 'team_id' => $otherTeam->id]);
    Role::create(['name' => 'member', 'team_id' => $otherTeam->id]);

    $response = $this
        ->actingAs($user)
        ->post(route('teams.invitations.store', $team), [
            'email' => 'test@example.com',
            'role' => 'member',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('error', 'Invalid role selected.');

    $this->assertDatabaseMissing('team_invitations', [
        'team_id' => $team->id,
        'email' => 'test@example.com',
    ]);
});

test('removing team owner returns error message', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    $adminRole = Role::create(['name' => 'admin', 'team_id' => $team->id]);
    $team->users()->attach($admin->id, ['role_id' => $adminRole->id]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('teams.members.destroy', [
            'team' => $team->id,
            'user' => $owner->id,
        ]));

    $response
        ->assertRedirect()
        ->assertSessionHas('error', 'Team owner cannot be removed.');
});

test('removing member from their current team unsets team_id', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    $memberRole = Role::create(['name' => 'member', 'team_id' => $team->id]);
    $team->users()->attach($member->id, ['role_id' => $memberRole->id]);

    // Set this team as the member's current team
    $member->forceFill(['team_id' => $team->id])->save();
    $this->assertEquals($team->id, $member->team_id);

    $response = $this
        ->actingAs($owner)
        ->delete(route('teams.members.destroy', [
            'team' => $team->id,
            'user' => $member->id,
        ]));

    $response->assertRedirect();

    // Refresh the member model to get the updated team_id
    $member->refresh();
    $this->assertNull($member->team_id);
});

test('store team invitation request validates team parameter type', function () {
    $user = User::factory()->create();
    $invalidTeam = new stdClass(); // Create a non-Team object

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Route parameter "team" must be an instance of Team');

    $request = new App\Http\Requests\StoreTeamInvitationRequest();
    $request->setContainer(app())
        ->setRedirector(app(Illuminate\Routing\Redirector::class))
        ->setRouteResolver(function () use ($invalidTeam) {
            return tap(new Illuminate\Routing\Route('POST', 'test', []), function ($route) use ($invalidTeam) {
                $route->parameters = ['team' => $invalidTeam];
            });
        });

    // Try to access team through the private method using reflection
    $reflector = new ReflectionClass($request);
    $method = $reflector->getMethod('team');
    $method->setAccessible(true);
    $method->invoke($request);
});

test('store team invitation request accepts valid team parameter', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    $request = new App\Http\Requests\StoreTeamInvitationRequest();
    $request->setContainer(app())
        ->setRedirector(app(Illuminate\Routing\Redirector::class))
        ->setRouteResolver(function () use ($team) {
            return tap(new Illuminate\Routing\Route('POST', 'test', []), function ($route) use ($team) {
                $route->parameters = ['team' => $team];
            });
        });

    // Try to access team through the private method using reflection
    $reflector = new ReflectionClass($request);
    $method = $reflector->getMethod('team');
    $method->setAccessible(true);

    $result = $method->invoke($request);
    expect($result)->toBeInstanceOf(Team::class);
    expect($result->id)->toBe($team->id);
});
