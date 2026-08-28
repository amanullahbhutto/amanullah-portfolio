<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure Muslim role exists
        Role::firstOrCreate(['name' => 'Muslim', 'guard_name' => 'web']);

        $permissions = [
            'view namaz attendance',
            'namaz_attendance.view',
            'create namaz attendance',
            'namaz_attendance.create',
            'update namaz attendance',
            'namaz_attendance.update',
            'delete namaz attendance',
            'namaz_attendance.delete',
            'view namaz dashboard',
            'namaz_dashboard.view',
            'view namaz settings',
            'namaz_settings.view',
            'update namaz settings',
            'namaz_settings.update',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolesToGrant = ['Super Admin', 'Admin', 'admin'];
        foreach ($rolesToGrant as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($permissions);
            }
        }
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view namaz attendance',
            'namaz_attendance.view',
            'create namaz attendance',
            'namaz_attendance.create',
            'update namaz attendance',
            'namaz_attendance.update',
            'delete namaz attendance',
            'namaz_attendance.delete',
            'view namaz dashboard',
            'namaz_dashboard.view',
            'view namaz settings',
            'namaz_settings.view',
            'update namaz settings',
            'namaz_settings.update',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};

