<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UpdateProfileAction
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
    public function handle(array $attributes, User $user): void
    {
        DB::transaction(function () use ($attributes, $user): true {
            if (isset($attributes['avatar_url']) && $attributes['avatar_url']) {
                $file = $attributes['avatar_url'];

                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $extension = $file->getClientOriginalExtension();
                    $randomString = bin2hex(random_bytes(8));
                    $filename = "avatar_user_{$user->id}_{$randomString}.{$extension}";

                    // Delete old avatar if exists
                    if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
                        Storage::disk('public')->delete($user->avatar_url);
                    }

                    // Store new avatar
                    $path = $file->storeAs('avatars', $filename, 'public');
                    $attributes['avatar_url'] = $path;
                }
            }

            $user->fill($attributes);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return true;
        });
    }
}
