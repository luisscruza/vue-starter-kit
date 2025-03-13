<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

trait HasTeams
{
    /**
     * Get the current team of the user.
     *
     * @return BelongsTo<Team, $this>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Set the current team of the user.
     */
    public function setCurrentTeam(Team $team): void
    {
        if ($this->belongsToTeam($team)) {
            $this->team_id = $team->id;
            $this->save();
        }
    }

    /**
     * Check if the user owns the given team.
     */
    public function ownsTeam(Team $team): bool
    {
        return $this->id === $team->{$this->getForeignKey()};
    }

    /**
     * Retrieve all teams the user owns or belongs to
     *
     * @return Collection<int, Team>
     */
    public function allTeams(): Collection
    {
        /** @var Collection<int, Team> */
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Retrieve all teams the user owns.
     *
     * @return HasMany<Team, $this>
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class)->withoutGlobalScopes();
    }

    /**
     * Retrieve all teams the user belongs to.
     *
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id')
            ->withoutGlobalScopes()
            ->withPivot('role_id')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Check if the user belongs to the specified team.
     */
    public function belongsToTeam(Team $team): bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        return (bool) $this->teams()->where('team_id', $team->id)->exists();
    }

    /**
     * Retrieve the user's role in a team.
     */
    public function teamRole(Team $team): ?Role
    {
        if (! $this->belongsToTeam($team)) {
            return null;
        }

        /** @var (Team&\Illuminate\Database\Eloquent\Model)|null $teamMember */
        $teamMember = $this->teams()->find($team->id);

        /** @var ?object{role_id: ?int} $membership */
        $membership = $teamMember?->membership; // @phpstan-ignore-line

        /** @var ?int $roleId */
        $roleId = $membership?->role_id;

        return $roleId !== null ? $team->getRole($roleId) : null;
    }

    /**
     * Check if the user has the specified role on the team.
     *
     * @param  string|array<string>  $roles
     */
    public function hasTeamRole(Team $team, string|array $roles, bool $require = false): bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        $userRole = $this->teamRole($team)?->name;

        $roles = (array) $roles;

        return $require
            ? array_diff($roles, [$userRole]) === []
            : in_array($userRole, $roles, true);
    }

    /**
     * Get the user's permissions for the given team.
     *
     * @param  string|null  $scope  Scope of permissions to get (ex. 'role', 'group'), by default getting all permissions
     * @return array<string>
     */
    public function teamPermissions(Team $team, ?string $scope = null): array
    {
        if ($this->ownsTeam($team)) {
            return ['*'];
        }

        $permissions = [];

        if ($scope === null || $scope === '' || $scope === '0' || $scope === 'role') {
            /** @var array<string> */
            $rolePermissions = $this->teamRole($team)?->permissions?->pluck('name')?->toArray() ?? [];
            $permissions = array_merge($permissions, $rolePermissions);
        }

        return array_unique($permissions);
    }

    /**
     * Determine if the user has the given permission on the given team.
     *
     * @param  string|array<string>  $permissions
     */
    public function hasTeamPermission(Team $team, string|array $permissions, bool $require = false, ?string $scope = null): bool
    {
        if ($this->ownsTeam($team)) {
            return true;
        }

        $permissions = (array) $permissions;

        if ($permissions === []) {
            return false;
        }

        $userPermissions = $this->teamPermissions($team, $scope);

        foreach ($permissions as $permission) {

            $hasPermission = $this->checkPermissionWildcard($userPermissions, $permission);

            if ($hasPermission && ! $require) {
                return true;
            }

            if (! $hasPermission && $require) {
                return false;
            }
        }

        return $require;
    }

    /**
     * Check for wildcard permissions.
     *
     * @param  array<string>  $userPermissions
     */
    private function checkPermissionWildcard(array $userPermissions, string $permission): bool
    {
        // Generate all possible wildcards from the permission segments
        $segments = collect(explode('.', $permission));
        $codes = $segments->map(fn ($item, $key): string => $segments->take($key + 1)->implode('.').($key + 1 === $segments->count() ? '' : '.*'));

        return array_intersect($codes->all(), $userPermissions) !== [];
    }
}
