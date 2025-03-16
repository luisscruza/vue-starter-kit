<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

test('to array', function () {
    $user = User::factory()->create();

    expect(array_keys($user->toArray()))->toBe([
        'name',
        'email',
        'email_verified_at',
        'avatar_url',
        'updated_at',
        'created_at',
        'id',
        'avatar',
    ]);
});

it('has teams', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    Role::create(['name' => 'admin']);

    $user->teams()->attach($team, ['role_id' => 1]);
    expect($user->teams)->toBeCollection();
    expect($user->teams->first())->toBeInstanceOf(Team::class);
    expect($user->teams->first()->id)->toBe($team->id);
});

it('it returns null if avatar url is null', function () {
    $user = User::factory()->create(['avatar_url' => null]);

    expect($user->avatar)->toBeNull();
});

it('it returns the avatar url if it starts with http', function () {
    $user = User::factory()->create(['avatar_url' => 'http://test.com/avatar.png']);

    expect($user->avatar)->toBe('http://test.com/avatar.png');
});

it('has avatar url', function () {
    $user = User::factory()->create();

    expect($user->avatar_url)->toBeString();
    expect($user->avatar)->toBeString();
});

it('user can access panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->canAccessPanel(app(Panel::class)))->toBeFalse();

    $user->update(['is_admin' => true]);

    expect($user->canAccessPanel(app(Panel::class)))->toBeTrue();
});

it('user cannot impersonate', function () {
    $user = User::factory()->create(['is_admin' => false]);

    expect($user->canImpersonate())->toBeFalse();

    $user->update(['is_admin' => true]);

    expect($user->canImpersonate())->toBeTrue();
});

it('has a filament avatar url', function () {
    $user = User::factory()->create(['avatar_url' => 'test.png']);

    expect($user->getFilamentAvatarUrl())->toBeString();
    expect($user->getFilamentAvatarUrl())->toBe(Storage::url('test.png'));
});
