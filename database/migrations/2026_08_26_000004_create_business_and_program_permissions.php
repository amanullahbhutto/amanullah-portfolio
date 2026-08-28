<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'view investors', 'create investor', 'update investor', 'delete investor',
        'view investments', 'create investment', 'update investment', 'delete investment',
        'view profit sharing', 'confirm profit sharing',
        'view profit payments', 'create profit payment',
        'view investment withdrawals', 'create investment withdrawal',
        'view investor reports',
        'view programs', 'create program', 'update program', 'delete program',
        'view contributions', 'create contribution', 'update contribution', 'delete contribution',
        'view expense categories', 'create expense category', 'update expense category', 'delete expense category',
        'view program expenses', 'create program expense', 'update program expense', 'delete program expense',
        'view program transactions', 'view program reports',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($this->permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::query()->whereIn('name', $this->permissions)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
