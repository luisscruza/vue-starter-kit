<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeleteTeamInvitationAction;
use App\Actions\StoreTeamInvitationAction;
use App\Http\Requests\StoreTeamInvitationRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class TeamInvitationController
{
    /**
     * Store a newly created team invitation.
     */
    public function store(
        StoreTeamInvitationRequest $request,
        Team $team,
        StoreTeamInvitationAction $action
    ): RedirectResponse {
        try {
            $action->handle($request->validated(), $team);

            return back()->with('success', 'Invitation sent successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified team invitation.
     */
    public function destroy(
        Team $team,
        TeamInvitation $invitation,
        DeleteTeamInvitationAction $action
    ): RedirectResponse {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $action->handle($team, $invitation, $user);

            return back()->with('success', 'Invitation cancelled successfully.');
        } catch (\Illuminate\Auth\Access\AuthorizationException) {
            abort(403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            abort(404);
        }
    }

    /**
     * Show the specified team invitation.
     *
     * @param  Team  $team  The team that owns the invitation
     * @param  TeamInvitation  $invitation  The invitation to display
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException When invitation doesn't belong to team
     */
    public function show(Team $team, TeamInvitation $invitation): Response|RedirectResponse
    {
        $invitation->loadMissing('team')->whereHas('team', function ($query) use ($team): void {
            $query->where('id', $team->id);
        })->firstOrFail();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user && $invitation->email !== $user->email) {
            return redirect()->route('dashboard')->with('error', 'You are not allowed to accept this invitation.');
        }

        return Inertia::render('teams/Invitations/Show', [
            'team' => $team,
            'invitation' => $invitation,
        ]);
    }
}
