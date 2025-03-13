<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'view-team',
            'edit-team',
            'delete-team',
            'view-user',
            'edit-user',
            'delete-user',
            'view-role',
            'edit-role',
            'delete-role',
            'view-permission',
            'edit-permission',
            'delete-permission',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

    }
};
