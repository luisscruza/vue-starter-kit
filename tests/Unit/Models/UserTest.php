<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('to array', function () {
    $user = User::factory()->create();

    expect(array_keys($user->toArray()))->toBe([
        'name',
        'email',
        'email_verified_at',
        'updated_at',
        'created_at',
        'id',
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
