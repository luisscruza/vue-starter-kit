<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;

test('to array', function () {
    $team = Team::factory()->create();

    expect(array_keys($team->toArray()))->toBe([
        'name',
        'user_id',
        'updated_at',
        'created_at',
        'id',
    ]);
});

it('belongs to user', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    expect($team->owner)->toBeInstanceOf(User::class);
    expect($team->owner->id)->toBe($user->id);
});
