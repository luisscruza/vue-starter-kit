<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTeams;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasTeams, Impersonate, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'team_id',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * The attributes that should be appended to the model.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar',
    ];

    /**
     * Get the URL of the user's avatar.
     *
     * @return string
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar;
    }

    /**
     * Get the URL of the user's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        if (str($this->avatar_url)->startsWith('http')) {
            return $this->avatar_url;
        }

        return Storage::url($this->avatar_url);
    }

    /**
     * Determine if the user can access the admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    /**
     * Determine if the user can impersonate another user.
     */
    public function canImpersonate(): bool
    {
        return $this->is_admin;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
