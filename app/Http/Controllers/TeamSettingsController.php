<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class TeamSettingsController
{
    /**
     * Show the team settings page.
     */
    public function index(Team $team): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user || (! $team->users->contains($user) && ! $team->owner?->is($user))) {
            abort(403, 'Unauthorized');
        }

        $members = $team->users->map(fn ($member): array => [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'role' => $member->teamRole($team)?->name,
            'created_at' => $member->membership->created_at, // @phpstan-ignore-line
        ])->values();

        return Inertia::render('teams/View', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
                'owner' => [
                    'id' => $team->owner?->id,
                    'name' => $team->owner?->name,
                    'email' => $team->owner?->email,
                ],
                'members' => $members,
                'invitations' => $team->invitations->map(fn ($invitation): array => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role_id' => $invitation->role_id,
                    'created_at' => $invitation->created_at,
                ])->values(),
                'currentUserRole' => $user->teamRole($team)->name ?? ($team->owner?->is($user) ? 'owner' : null),
            ],
        ]);
    }

    /**
     * Update the team's information.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $team->update($request->validated());

        return back()->with('success', 'Team updated successfully.');
    }
}
