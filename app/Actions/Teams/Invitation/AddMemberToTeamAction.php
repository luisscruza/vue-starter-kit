<?php

declare(strict_types=1);

namespace App\Actions\Teams\Invitation;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class AddMemberToTeamAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the action.
     */
    public function handle(Team $team, TeamInvitation $invitation, User $user): bool
    {
        return DB::transaction(function () use ($team, $invitation, $user): true {
            $user->teams()->attach($team->id, ['role_id' => $invitation->role_id]);

            $user->setCurrentTeam($team);

            $invitation->delete();

            return true;
        });
    }
}
