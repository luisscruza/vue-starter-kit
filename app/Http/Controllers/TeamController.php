<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateTeamAction;
use App\Http\Requests\CreateTeamRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class TeamController
{
    /**
     * Show the create team page.
     */
    public function create(): Response
    {
        return Inertia::render('teams/Create');
    }

    /**
     * Store a new team.
     */
    public function store(CreateTeamRequest $request, CreateTeamAction $createTeamAction): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $createTeamAction->handle($request->validated(), $user);

        return redirect()->route('dashboard')
            ->with('success', 'Team created successfully.');
    }
}
