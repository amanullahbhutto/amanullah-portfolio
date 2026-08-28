<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view role', ['only' => ['index', 'show']]);
        $this->middleware('permission:create role', ['only' => ['create', 'store']]);
        $this->middleware('permission:update role', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete role', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['permissions', 'users'])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', ['role' => new Role, 'permissions' => $this->groupedPermissions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $role = Role::query()->create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions'] ?? []);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role): View
    {
        abort_unless($role->guard_name === 'web', 404);

        return view('admin.roles.show', ['role' => $role->load(['permissions', 'users'])]);
    }

    public function edit(Role $role): View
    {
        abort_unless($role->guard_name === 'web', 404);

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        $validated = $this->validatedData($request, $role);
        $permissions = collect($validated['permissions'] ?? [])->unique()->values();

        if (in_array(strtolower($role->name), ['super admin', 'admin'], true) && strtolower($validated['name']) !== strtolower($role->name)) {
            return back()->withErrors(['name' => 'The Super Admin and Admin role names cannot be renamed.'])->withInput();
        }

        if ($this->wouldLockAdminAccess($request, $role, $permissions)) {
            return back()
                ->withErrors(['permissions' => 'View dashboard, view user, view role, and view permission must stay enabled for the active admin role.'])
                ->withInput();
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($permissions->all());
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        if (in_array(strtolower($role->name), ['super admin', 'admin'], true)) {
            return back()->withErrors(['role' => 'The Super Admin and Admin roles are essential system roles and cannot be deleted.']);
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'There are users assigned to this role. Move users to another role before deleting.']);
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    private function validatedData(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9 _-]+$/',
                Rule::unique('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
        ]);
    }

    private function groupedPermissions()
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission): string {
                $name = $permission->name;
                if (str_contains($name, '.')) {
                    $mod = str($name)->before('.')->replace('_', ' ')->title()->toString();
                    return Str::plural($mod);
                }
                if (str_starts_with($name, 'view ') || str_starts_with($name, 'create ') || str_starts_with($name, 'update ') || str_starts_with($name, 'delete ') || str_starts_with($name, 'run ') || str_starts_with($name, 'confirm ')) {
                    $mod = str($name)->after(' ')->replace('_', ' ')->title()->toString();
                    return Str::plural($mod);
                }
                return 'General';
            });
    }

    private function wouldLockAdminAccess(Request $request, Role $role, $permissions): bool
    {
        if (! $request->user()->roles->contains('id', $role->id)) {
            return false;
        }

        // If user is Super Admin, they have bypass via Gate::before so will never be locked out
        if ($request->user()->hasRole('Super Admin')) {
            return false;
        }

        $required = ['view dashboard', 'view user', 'view role', 'view permission'];

        return $permissions->intersect($required)->count() !== count($required);
    }
}
