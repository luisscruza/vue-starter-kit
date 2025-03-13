<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

trait HasMembers
{
    /**
     * Get the owner of the team.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all users associated with the team.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
            ->withPivot('role_id')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get all roles associated with the team.
     *
     * @return HasMany<Role, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class, 'team_id', 'id');
    }

    /**
     * Get the role for the team.
     */
    public function getRole(?int $roleId): ?Role
    {
        if ($roleId === null) {
            return null;
        }

        return Role::find($roleId);
    }

    /**
     * Get all pending invitations for the team.
     *
     * @return HasMany<TeamInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class, 'team_id', 'id');
    }

    /**
     * Retrieve all users in the team, including the owner.
     *
     * @return Collection<int, User>
     */
    public function allUsers(): Collection
    {
        return collect([$this->owner])
            ->filter()
            ->merge($this->users);
    }

    /**
     * Check if the team includes a given user.
     */
    public function hasUser(User $user): bool
    {
        if ($user->id === $this->user_id) {
            return true;
        }

        return $this->users->contains($user);
    }
}
