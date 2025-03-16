<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTeamMemberRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class TeamMemberController
{
    /**
     * Remove the specified user from the team.
     */
    public function destroy(Team $team, User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasTeamPermission($team, 'edit-team')) {
            abort(403);
        }

        if ($team->owner && $team->owner->is($user)) {
            return back()->with('error', 'Team owner cannot be removed.');
        }

        $team->users()->detach($user->id);

        if ($user->currentTeam?->is($team)) {
            $user->forceFill([
                'team_id' => null,
            ])->save();
        }

        return back()->with('success', 'Team member removed successfully.');
    }

    /**
     * Update the member.
     */
    public function update(UpdateTeamMemberRequest $request, Team $team, User $user): RedirectResponse
    {
        $team->users()->updateExistingPivot($user->id, $request->validated());

        return back()->with('success', 'Team member updated successfully.');
    }
}
