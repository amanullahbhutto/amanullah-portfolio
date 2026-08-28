<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_has_full_access_via_gate_before(): void
    {
        $superAdmin = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($superAdmin);
        $this->assertTrue($superAdmin->hasRole('Super Admin'));

        // Check that Gate::before allows super admin on any permission
        $this->actingAs($superAdmin);
        $this->assertTrue($superAdmin->can('any.nonexistent.permission'));
        $this->assertTrue($superAdmin->can('view dashboard'));

        // Super Admin can access admin pages
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.roles.index'))->assertOk();
        $this->get(route('admin.permissions.index'))->assertOk();
    }

    public function test_super_admin_can_create_and_manage_roles(): void
    {
        $superAdmin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($superAdmin);

        // Create new role with permissions
        $response = $this->post(route('admin.roles.store'), [
            'name' => 'Store Manager',
            'permissions' => ['products.view', 'sales.view'],
        ]);
        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Store Manager']);

        $role = Role::where('name', 'Store Manager')->first();
        $this->assertTrue($role->hasPermissionTo('products.view'));
        $this->assertTrue($role->hasPermissionTo('sales.view'));

        // Edit role permissions
        $this->put(route('admin.roles.update', $role), [
            'name' => 'Store Manager',
            'permissions' => ['products.view', 'sales.view', 'sales.create'],
        ]);
        $this->assertTrue($role->fresh()->hasPermissionTo('sales.create'));

        // Delete role
        $this->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['name' => 'Store Manager']);
    }

    public function test_super_admin_can_create_user_and_assign_roles(): void
    {
        $superAdmin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($superAdmin);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Jane Cashier',
            'email' => 'cashier@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['Cashier'],
        ]);
        $response->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'cashier@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('Cashier'));

        // Update user role to Manager
        $this->put(route('admin.users.update', $newUser), [
            'name' => 'Jane Cashier',
            'email' => 'cashier@example.com',
            'roles' => ['Manager'],
        ]);
        $this->assertTrue($newUser->fresh()->hasRole('Manager'));
        $this->assertFalse($newUser->fresh()->hasRole('Cashier'));
    }

    public function test_user_can_be_created_with_six_digit_numeric_password(): void
    {
        $superAdmin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($superAdmin);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Numeric Pass User',
            'email' => 'numeric@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'role' => 'User',
        ]);
        $response->assertRedirect(route('admin.users.index'));

        $newUser = User::where('email', 'numeric@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('123456', $newUser->password));
    }

    public function test_super_admin_can_manage_permissions(): void
    {
        $superAdmin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($superAdmin);

        // Create permission
        $response = $this->post(route('admin.permissions.store'), [
            'name' => 'discounts.apply',
        ]);
        $response->assertRedirect(route('admin.permissions.index'));
        $this->assertDatabaseHas('permissions', ['name' => 'discounts.apply']);

        $permission = Permission::where('name', 'discounts.apply')->first();

        // Update permission
        $this->put(route('admin.permissions.update', $permission), [
            'name' => 'discounts.manage',
        ]);
        $this->assertDatabaseHas('permissions', ['name' => 'discounts.manage']);

        // Delete permission
        $this->delete(route('admin.permissions.destroy', $permission->fresh()))
            ->assertRedirect(route('admin.permissions.index'));
        $this->assertDatabaseMissing('permissions', ['name' => 'discounts.manage']);
    }

    public function test_unauthorized_user_receives_403_access_denied(): void
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);
        $regularUser->assignRole('User');

        $this->actingAs($regularUser);

        // Attempting to access admin users page without permissions returns 403
        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(403);
        $response->assertSee('Access Denied');
    }

    public function test_modular_crud_permissions_enforce_access_on_finance_and_programs(): void
    {
        $staffUser = User::create([
            'name' => 'Finance Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('password123'),
        ]);
        // Grant only view investors and view programs permissions
        $staffUser->givePermissionTo(['view dashboard', 'view investors', 'view programs', 'create program']);

        $this->actingAs($staffUser);

        // Allowed actions
        $this->get(route('admin.investors.index'))->assertOk();
        $this->get(route('admin.programs.index'))->assertOk();

        // Create program is permitted
        $response = $this->post(route('admin.programs.store'), [
            'name' => 'Annual Youth Summit',
            'status' => 'active',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('programs', ['name' => 'Annual Youth Summit']);

        // Forbidden actions (e.g. creating investor without create investor permission)
        $investorResponse = $this->post(route('admin.investors.store'), [
            'name' => 'New Angel Investor',
            'profit_share_percentage' => 10,
            'status' => 'active',
        ]);
        $investorResponse->assertStatus(403);

        // Forbidden action (viewing investments without view investments permission)
        $this->get(route('admin.investments.index'))->assertStatus(403);
    }

    public function test_newly_registered_user_gets_user_role_and_cannot_access_admin_until_promoted(): void
    {
        // 1. User registers via public /register
        $response = $this->post(route('register.store'), [
            'name' => 'Public Visitor',
            'email' => 'visitor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $response->assertRedirect(route('home'));

        $newUser = User::where('email', 'visitor@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('User'));
        $this->assertFalse($newUser->hasRole('Super Admin'));
        $this->assertFalse($newUser->hasRole('Admin'));
        $this->assertFalse($newUser->hasRole('Manager'));

        // 2. Newly registered user tries to access /admin -> 403 Forbidden
        $this->actingAs($newUser);
        $this->get(route('admin.dashboard'))->assertStatus(403);

        // 3. Super Admin promotes this user to Manager
        $superAdmin = User::where('email', 'admin@gmail.com')->first();
        $this->actingAs($superAdmin);

        $this->put(route('admin.users.update', $newUser), [
            'name' => 'Public Visitor',
            'email' => 'visitor@example.com',
            'roles' => ['Manager'],
        ]);
        $this->assertTrue($newUser->fresh()->hasRole('Manager'));

        // 4. Now the promoted user CAN access admin dashboard
        $this->actingAs($newUser->fresh());
        $this->get(route('admin.dashboard'))->assertOk();
    }
}


