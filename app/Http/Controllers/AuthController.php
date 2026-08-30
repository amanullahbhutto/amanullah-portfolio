<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->can('view dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user->can('view dashboard')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }

    public function register(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->can('view dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        abort_unless(config('portfolio.allow_registration'), 404);

        return view('auth.register');
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        abort_unless(config('portfolio.allow_registration'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::query()->create($validated);
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $user->syncRoles([$userRole]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->can('view dashboard')) {
            return redirect()->route('admin.dashboard')->with('success', 'Your account has been created.');
        }

        return redirect()->route('home')->with('success', 'Your account has been created successfully. Welcome! (Admin access requires role assignment by a Super Admin).');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
