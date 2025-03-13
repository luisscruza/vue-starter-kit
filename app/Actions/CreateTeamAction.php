<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateTeamAction
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
     *
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, User $user): Team
    {
        return DB::transaction(function () use ($attributes, $user) {
            $team = $user->ownedTeams()->create([
                'name' => $attributes['name'],
            ]);

            $this->createRoles($team);

            $user->teams()->attach($team->id, ['role_id' => $team->roles()->where('name', 'admin')->first()?->id]);

            $user->setCurrentTeam($team);

            return $team;
        });
    }

    /**
     * Create the roles for the team.
     */
    private function createRoles(Team $team): void
    {
        $team->roles()->create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $team->roles()->create([
            'name' => 'member',
            'guard_name' => 'web',
        ]);

        $team->roles()->where('name', 'admin')->first()?->givePermissionTo('view-team');
        $team->roles()->where('name', 'admin')->first()?->givePermissionTo('edit-team');
        $team->roles()->where('name', 'admin')->first()?->givePermissionTo('delete-team');
        $team->roles()->where('name', 'member')->first()?->givePermissionTo('view-team');

    }
}
