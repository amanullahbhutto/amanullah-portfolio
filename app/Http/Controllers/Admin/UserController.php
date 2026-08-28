<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view user', ['only' => ['index', 'show']]);
        $this->middleware('permission:create user', ['only' => ['create', 'store']]);
        $this->middleware('permission:update user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete user', ['only' => ['destroy']]);
    }

    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['roles' => Role::query()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', 'min:6'],
            'roles.*' => [Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))],
        ]);

        $rolesToSync = [];
        if (! empty($validated['roles'])) {
            $rolesToSync = $validated['roles'];
        } elseif (! empty($validated['role'])) {
            $rolesToSync = [$validated['role']];
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($rolesToSync)) {
            $user->syncRoles($rolesToSync);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['user' => $user->load('roles.permissions')]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', ['user' => $user, 'roles' => Role::query()->orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'roles' => ['nullable', 'array'],
            'role' => ['nullable', 'string', Rule::exists('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))],
        ]);

        $rolesToSync = [];
        if (! empty($validated['roles'])) {
            $rolesToSync = $validated['roles'];
        } elseif (! empty($validated['role'])) {
            $rolesToSync = [$validated['role']];
        }

        if ($user->is($request->user())) {
            $hasAdminRole = in_array('Super Admin', $rolesToSync, true) || in_array('admin', $rolesToSync, true);
            if (! $hasAdminRole && ($user->hasRole('Super Admin') || $user->hasRole('admin'))) {
                return back()->withErrors(['role' => 'You cannot remove your own administrator/super admin role.']);
            }
        }

        $userData = ['name' => $validated['name'], 'email' => $validated['email']];
        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->syncRoles($rolesToSync);

        return redirect()->route('admin.users.index')->with('success', 'User access updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $isOnlySuperAdmin = $user->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1;
        $isOnlyAdmin = $user->hasRole('admin') && User::role('admin')->count() <= 1 && User::role('Super Admin')->count() === 0;

        if ($isOnlySuperAdmin || $isOnlyAdmin) {
            return back()->withErrors(['user' => 'At least one active Super Admin / Administrator account is required.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
