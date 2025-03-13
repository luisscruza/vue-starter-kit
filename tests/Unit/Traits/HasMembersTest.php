<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

test('team has an owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    expect($team->owner())->toBeInstanceOf(BelongsTo::class);
    expect($team->owner->id)->toBe($user->id);
});

test('team can have users', function () {
    $team = Team::factory()->create();
    $user = User::factory()->create();
    $role = Role::create(['name' => 'member']);

    $team->users()->attach($user, ['role_id' => $role->id]);

    expect($team->users())->toBeInstanceOf(BelongsToMany::class);
    expect($team->users)->toHaveCount(1);
    expect($team->users->first())->toBeInstanceOf(User::class);
    expect($team->users->first()->id)->toBe($user->id);
    expect($team->users->first()->membership->role_id)->toBe($role->id);
});

test('team can have roles', function () {
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor', 'team_id' => $team->id]);

    expect($team->roles())->toBeInstanceOf(HasMany::class);
    expect($team->roles)->toHaveCount(1);
    expect($team->roles->first())->toBeInstanceOf(Role::class);
    expect($team->roles->first()->name)->toBe('editor');
});

test('team can get role by id', function () {
    $team = Team::factory()->create();
    $role = Role::create(['name' => 'editor']);

    expect($team->getRole($role->id))->toBeInstanceOf(Role::class);
    expect($team->getRole($role->id)->name)->toBe('editor');
    expect($team->getRole(null))->toBeNull();
    expect($team->getRole(999))->toBeNull();
});

test('team can have invitations', function () {
    $team = Team::factory()->create();
    $invitation = TeamInvitation::factory()->create(['team_id' => $team->id]);

    expect($team->invitations())->toBeInstanceOf(HasMany::class);
    expect($team->invitations)->toHaveCount(1);
    expect($team->invitations->first())->toBeInstanceOf(TeamInvitation::class);
    expect($team->invitations->first()->id)->toBe($invitation->id);
});

test('team can get all users including owner', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $role = Role::create(['name' => 'member']);

    $team->users()->attach($member, ['role_id' => $role->id]);

    $allUsers = $team->allUsers();
    expect($allUsers)->toHaveCount(2);
    expect($allUsers->pluck('id'))->toContain($owner->id, $member->id);
});

test('team can check if it has a user', function () {
    $owner = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);
    $member = User::factory()->create();
    $nonMember = User::factory()->create();
    $role = Role::create(['name' => 'member']);

    $team->users()->attach($member, ['role_id' => $role->id]);

    expect($team->hasUser($owner))->toBeTrue();
    expect($team->hasUser($member))->toBeTrue();
    expect($team->hasUser($nonMember))->toBeFalse();
});
