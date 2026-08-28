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
        $muslimRole = Role::firstOrCreate(['name' => 'Muslim', 'guard_name' => 'web']);

        $permissions = [
            'view zikr',
            'zikr.view',
            'create zikr',
            'zikr.create',
            'update zikr',
            'zikr.update',
            'reset zikr',
            'zikr.reset',
            'manage tasbeeh',
            'tasbeeh.manage',
            'delete tasbeeh',
            'tasbeeh.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Assign user zikr permissions to Muslim role
        $muslimPermissions = [
            'view zikr',
            'zikr.view',
            'create zikr',
            'zikr.create',
            'update zikr',
            'zikr.update',
            'reset zikr',
            'zikr.reset',
        ];
        $muslimRole->givePermissionTo($muslimPermissions);

        // Assign all permissions to Admins
        $adminRoles = ['Super Admin', 'Admin', 'admin'];
        foreach ($adminRoles as $roleName) {
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
            'view zikr',
            'zikr.view',
            'create zikr',
            'zikr.create',
            'update zikr',
            'zikr.update',
            'reset zikr',
            'zikr.reset',
            'manage tasbeeh',
            'tasbeeh.manage',
            'delete tasbeeh',
            'tasbeeh.delete',
        ];

        foreach ($permissions as $perm) {
            $p = Permission::where('name', $perm)->where('guard_name', 'web')->first();
            if ($p) {
                $p->delete();
            }
        }
    }
};

