<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Teams\Invitation\AddMemberToTeamAction;
use App\Actions\Teams\Invitation\RegisterUserToTeamAction;
use App\Http\Requests\StoreTeamInvitationRegisterRequest;
use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class TeamInvitationRegisterController
{
    /**
     * Stores the user in the team.
     */
    public function store(StoreTeamInvitationRegisterRequest $request, Team $team, TeamInvitation $invitation, RegisterUserToTeamAction $registerUserToTeamAction): RedirectResponse
    {
        $user = $registerUserToTeamAction->handle($request->validated(), $team, $invitation);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'You have been added to '.$team->name);
    }

    /**
     * Stores the user in the team.
     */
    public function update(Request $request, Team $team, TeamInvitation $invitation, AddMemberToTeamAction $addMemberToTeamAction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $addMemberToTeamAction->handle($team, $invitation, $user);

        return redirect()->route('dashboard')->with('success', 'You have been added to '.$team->name);
    }
}
