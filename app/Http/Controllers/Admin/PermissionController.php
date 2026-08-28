<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view permission', ['only' => ['index', 'show']]);
        $this->middleware('permission:create permission', ['only' => ['create', 'store']]);
        $this->middleware('permission:update permission', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete permission', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->withCount('roles')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('admin.permissions.create', ['permission' => new Permission]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        Permission::query()->create(['name' => $validated['name'], 'guard_name' => 'web']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission): View
    {
        abort_unless($permission->guard_name === 'web', 404);

        return view('admin.permissions.show', ['permission' => $permission->load('roles')]);
    }

    public function edit(Permission $permission): View
    {
        abort_unless($permission->guard_name === 'web', 404);

        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        abort_unless($permission->guard_name === 'web', 404);
        if ($this->isCorePermission($permission)) {
            return back()->withErrors(['permission' => 'Core admin permissions cannot be renamed.']);
        }

        $validated = $this->validatedData($request, $permission);
        $permission->update(['name' => $validated['name']]);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        abort_unless($permission->guard_name === 'web', 404);
        if ($this->isCorePermission($permission)) {
            return back()->withErrors(['permission' => 'Core admin permissions cannot be deleted.']);
        }

        if ($permission->roles()->exists()) {
            return back()->withErrors(['permission' => 'Remove this permission from roles before deleting it.']);
        }

        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }

    private function validatedData(Request $request, ?Permission $permission = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('permissions', 'name')
                    ->where(fn ($query) => $query->where('guard_name', 'web'))
                    ->ignore($permission?->id),
            ],
        ]);
    }

    private function isCorePermission(Permission $permission): bool
    {
        return in_array($permission->name, ['view dashboard', 'view user', 'view role', 'view permission'], true);
    }
}
