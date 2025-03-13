<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TeamSwitcherController
{
    /**
     * Switch the current team.
     */
    public function store(Request $request, Team $team): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->belongsToTeam($team)) {
            return redirect()->back()->with('error', 'You do not have access to this team.');
        }

        $user->setCurrentTeam($team);

        return redirect()->back()->with('success', 'Team switched successfully');
    }
}
