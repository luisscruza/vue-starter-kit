<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Team;
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
        $user = auth()->user();

        if (! $team->users->contains($user)) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('teams/View', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
            ],
        ]);
    }
}
