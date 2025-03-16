<?php

declare(strict_types=1);

namespace App\Actions\Teams\Invitation;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteTeamInvitationAction
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
    public function handle(Team $team, TeamInvitation $invitation, User $user): void
    {
        if (! $user->hasTeamPermission($team, 'edit-team')) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Unauthorized action.');
        }

        if ($invitation->team_id !== $team->id) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        DB::transaction(function () use ($invitation): void {
            $invitation->delete();
        });
    }
}
