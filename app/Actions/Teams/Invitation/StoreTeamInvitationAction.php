<?php

declare(strict_types=1);

namespace App\Actions\Teams\Invitation;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Notifications\TeamInvitation as TeamInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;

final class StoreTeamInvitationAction
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
    public function handle(array $attributes, Team $team): TeamInvitation
    {
        return DB::transaction(function () use ($attributes, $team) {
            /** @var Role $role */
            $role = $team->roles()->where('name', $attributes['role'])->first();

            if (! $role) {
                throw new InvalidArgumentException('Invalid role selected.');
            }

            $invitation = $team->invitations()->create([
                'uuid' => Str::uuid(),
                'email' => $attributes['email'],
                'role_id' => $role->id,
            ]);

            // Send invitation email
            Notification::route('mail', $attributes['email'])
                ->notify(new TeamInvitationNotification($team, $invitation));

            return $invitation;
        });
    }
}
