<?php

declare(strict_types=1);

namespace App\Actions\Teams\Invitation;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class RegisterUserToTeamAction
{
    /**
     * Create a new class instance.
     */
    public function __construct(public AddMemberToTeamAction $addMemberToTeamAction) {}

    /**
     * Execute the action.     *
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, Team $team, TeamInvitation $invitation): User
    {
        return DB::transaction(function () use ($attributes, $team, $invitation) {
            $password = is_string($attributes['password']) ? $attributes['password'] : '';

            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => Hash::make($password),
            ]);

            $this->addMemberToTeamAction->handle($team, $invitation, $user);

            return $user;
        });
    }
}
