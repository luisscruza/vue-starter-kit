<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('a user can own a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    expect($user->ownedTeams)->toHaveCount(1);
    expect($user->ownedTeams->first())->toBeInstanceOf(Team::class);
    expect($user->ownedTeams->first()->id)->toBe($team->id);
});

test('a user can belong to a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    Role::create(['name' => 'admin']);

    $user->teams()->attach($team, ['role_id' => 1]);
    expect($user->teams)->toHaveCount(1);
    expect($user->teams->first())->toBeInstanceOf(Team::class);
    expect($user->teams->first()->id)->toBe($team->id);
});

test('a user can have multiple teams', function () {
    $user = User::factory()->create();
    $team1 = Team::factory()->create();
    $team2 = Team::factory()->create(['user_id' => $user->id]);

    Role::create(['name' => 'admin']);

    $user->teams()->attach($team1, ['role_id' => 1]);
    $user->teams()->attach($team2, ['role_id' => 1]);

    expect($user->allTeams())->toHaveCount(2);
    expect($user->allTeams()->pluck('id'))->toContain($team1->id, $team2->id);
    expect($user->ownedTeams()->get())->toHaveCount(1);
    expect($user->teams()->get())->toHaveCount(2);
});

test('a user owns a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    expect($user->ownsTeam($team))->toBeTrue();
});

test('a user belongs to a team', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    Role::create(['name' => 'admin']);

    $user->teams()->attach($team, ['role_id' => 1]);

    expect($user->belongsToTeam($team))->toBeTrue();
});

test('team relationship has correct configuration', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    Role::create(['name' => 'admin']);
    $user->teams()->attach($team, ['role_id' => 1]);

    $relationship = $user->teams();

    expect($relationship)->toBeInstanceOf(BelongsToMany::class);
    expect($relationship->getTable())->toBe('team_user');
    expect($relationship->getForeignPivotKeyName())->toBe('user_id');
    expect($relationship->getRelatedPivotKeyName())->toBe('team_id');

    expect($relationship->getQuery()->getQuery()->joins[0]->table)->toBe('team_user');

    // Refresh the user model to get the relationship with pivot data
    $user->refresh();
    $teamMember = $user->teams->first();
    expect($teamMember->membership)->toBeObject();
    expect($teamMember->membership->role_id)->toBe(1);
});

test('team owner has access regardless of role', function () {
    $user = User::factory()->create();
    $ownedTeam = Team::factory()->create(['user_id' => $user->id]);

    // Test team role for owner
    expect($user->teamRole($ownedTeam))->toBeNull();

    // Test team permissions for owner
    expect($user->teamPermissions($ownedTeam))->toBe(['*']);

    // Test hasTeamPermission for owner
    expect($user->hasTeamPermission($ownedTeam, 'any.permission'))->toBeTrue();
    expect($user->hasTeamPermission($ownedTeam, ['any.permission', 'another.permission'], true))->toBeTrue();

    // Test hasTeamRole for owner
    expect($user->hasTeamRole($ownedTeam, 'any-role'))->toBeTrue();
    expect($user->hasTeamRole($ownedTeam, ['role1', 'role2'], true))->toBeTrue();
});

test('team member can have a role', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor']);

    $user->teams()->attach($team, ['role_id' => $role->id]);

    expect($user->teamRole($team))->toBeInstanceOf(Role::class);
    expect($user->teamRole($team)->name)->toBe('editor');
});

test('team member role checks work correctly', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor']);

    $user->teams()->attach($team, ['role_id' => $role->id]);

    // Single role check
    expect($user->hasTeamRole($team, 'editor'))->toBeTrue();
    expect($user->hasTeamRole($team, 'admin'))->toBeFalse();

    // Multiple roles check - any role
    expect($user->hasTeamRole($team, ['editor', 'admin']))->toBeTrue();
    expect($user->hasTeamRole($team, ['admin', 'viewer']))->toBeFalse();

    // Multiple roles check - require all
    expect($user->hasTeamRole($team, ['editor'], true))->toBeTrue();
    expect($user->hasTeamRole($team, ['editor', 'admin'], true))->toBeFalse();
});

test('team member can have permissions through role', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor']);

    // Create permissions first
    Permission::create(['name' => 'edit']);
    Permission::create(['name' => 'view']);

    $role->givePermissionTo(['edit', 'view']);
    $user->teams()->attach($team, ['role_id' => $role->id]);

    $permissions = $user->teamPermissions($team);
    expect($permissions)->toBeArray();
    expect($permissions)->toContain('edit', 'view');
});

test('team permission checks work correctly', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor']);

    // Create permissions first
    Permission::create(['name' => 'edit.posts']);
    Permission::create(['name' => 'view.posts']);
    Permission::create(['name' => 'delete.posts']);
    Permission::create(['name' => 'publish.posts']);
    Permission::create(['name' => 'edit.*']);  // Add wildcard permission
    Permission::create(['name' => 'view.*']);  // Add wildcard permission

    $role->givePermissionTo(['edit.*', 'view.*']);  // Assign wildcard permissions
    $user->teams()->attach($team, ['role_id' => $role->id]);

    // Single permission check
    expect($user->hasTeamPermission($team, 'edit.posts'))->toBeTrue();
    expect($user->hasTeamPermission($team, 'delete.posts'))->toBeFalse();

    // Multiple permissions check - any permission
    expect($user->hasTeamPermission($team, ['edit.posts', 'delete.posts']))->toBeTrue();
    expect($user->hasTeamPermission($team, ['delete.posts', 'publish.posts']))->toBeFalse();

    // Multiple permissions check - require all
    expect($user->hasTeamPermission($team, ['edit.posts', 'view.posts'], true))->toBeTrue();
    expect($user->hasTeamPermission($team, ['edit.posts', 'delete.posts'], true))->toBeFalse();

    // Wildcard permission check
    expect($user->hasTeamPermission($team, 'edit.*'))->toBeTrue();
    expect($user->hasTeamPermission($team, ['edit.*', 'view.*'], true))->toBeTrue();
});

test('empty permission check returns false', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor']);

    $user->teams()->attach($team, ['role_id' => $role->id]);

    expect($user->hasTeamPermission($team, []))->toBeFalse();
});

test('non-member has no permissions', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();

    expect($user->teamRole($team))->toBeNull();
    expect($user->teamPermissions($team))->toBe([]);
    expect($user->hasTeamPermission($team, 'any.permission'))->toBeFalse();
    expect($user->hasTeamRole($team, 'any-role'))->toBeFalse();
});
