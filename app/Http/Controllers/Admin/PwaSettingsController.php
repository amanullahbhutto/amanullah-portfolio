<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PwaSetting;
use App\Services\PwaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class PwaSettingsController extends Controller
{
    protected PwaService $pwaService;

    public function __construct(PwaService $pwaService)
    {
        $this->pwaService = $pwaService;
    }

    public function index(): View
    {
        $this->authorizeAdmin();
        $settings = $this->pwaService->getSettings();

        return view('admin.pwa.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
            'app_name' => 'required|string|max:255',
            'short_name' => 'required|string|max:64',
            'description' => 'nullable|string|max:1000',
            'theme_color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'background_color' => ['required', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'display_mode' => 'required|in:standalone,fullscreen,minimal-ui,browser',
            'orientation' => 'required|in:portrait-primary,portrait,landscape,any',
            'start_url' => 'required|string|max:255',
            'install_button_text' => 'required|string|max:64',
            'offline_message' => 'nullable|string|max:1000',
            'disabled_message' => 'nullable|string|max:1000',
            'maintenance_message' => 'nullable|string|max:1000',
            'offline_mode_enabled' => 'nullable|boolean',
            'auto_sync_enabled' => 'nullable|boolean',
            'max_offline_days' => 'required|integer|min:1|max:365',
            'app_version' => 'required|string|max:32',
            'icon_192_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'icon_512_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'icon_maskable_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'splash_image_file' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'app_logo_file' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
        ]);

        $settings = PwaSetting::first() ?? new PwaSetting();

        $settings->is_active = $request->boolean('is_active');
        $settings->app_name = $validated['app_name'];
        $settings->short_name = $validated['short_name'];
        $settings->description = $validated['description'] ?? null;
        $settings->theme_color = $validated['theme_color'];
        $settings->background_color = $validated['background_color'];
        $settings->display_mode = $validated['display_mode'];
        $settings->orientation = $validated['orientation'];
        $settings->start_url = $validated['start_url'];
        $settings->install_button_text = $validated['install_button_text'];
        $settings->offline_message = $validated['offline_message'] ?? null;
        $settings->disabled_message = $validated['disabled_message'] ?? null;
        $settings->maintenance_message = $validated['maintenance_message'] ?? null;
        $settings->offline_mode_enabled = $request->boolean('offline_mode_enabled');
        $settings->auto_sync_enabled = $request->boolean('auto_sync_enabled');
        $settings->max_offline_days = (int) $validated['max_offline_days'];

        // Automatic version increment if unchanged but files or name changed
        $settings->app_version = $validated['app_version'];

        $uploadDir = public_path('assets/pwa-icons');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        // Process File Uploads
        if ($request->hasFile('icon_192_file')) {
            $filename = 'icon-192x192-' . time() . '.' . $request->file('icon_192_file')->getClientOriginalExtension();
            $request->file('icon_192_file')->move($uploadDir, $filename);
            $settings->icon_192 = 'assets/pwa-icons/' . $filename;
        }

        if ($request->hasFile('icon_512_file')) {
            $filename = 'icon-512x512-' . time() . '.' . $request->file('icon_512_file')->getClientOriginalExtension();
            $request->file('icon_512_file')->move($uploadDir, $filename);
            $settings->icon_512 = 'assets/pwa-icons/' . $filename;
        }

        if ($request->hasFile('icon_maskable_file')) {
            $filename = 'icon-maskable-' . time() . '.' . $request->file('icon_maskable_file')->getClientOriginalExtension();
            $request->file('icon_maskable_file')->move($uploadDir, $filename);
            $settings->icon_maskable = 'assets/pwa-icons/' . $filename;
        }

        if ($request->hasFile('splash_image_file')) {
            $filename = 'splash-' . time() . '.' . $request->file('splash_image_file')->getClientOriginalExtension();
            $request->file('splash_image_file')->move($uploadDir, $filename);
            $settings->splash_image = 'assets/pwa-icons/' . $filename;
        }

        if ($request->hasFile('app_logo_file')) {
            $filename = 'pwa-logo-' . time() . '.' . $request->file('app_logo_file')->getClientOriginalExtension();
            $request->file('app_logo_file')->move($uploadDir, $filename);
            $settings->app_logo = 'assets/pwa-icons/' . $filename;
        }

        $settings->save();
        PwaSetting::clearCache();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Mobile Application Settings updated successfully!',
                'settings' => $settings,
            ]);
        }

        return redirect()->route('admin.pwa.settings')->with('success', 'Mobile Application Settings updated successfully!');
    }

    public function toggleActive(): JsonResponse
    {
        $this->authorizeAdmin();

        $settings = PwaSetting::first() ?? PwaSetting::createDefault();
        $settings->is_active = !$settings->is_active;
        $settings->save();
        PwaSetting::clearCache();

        $statusText = $settings->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'is_active' => $settings->is_active,
            'message' => "Mobile Application has been {$statusText} successfully.",
        ]);
    }

    protected function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'admin']) || $user->can('manage pwa settings')) {
            return;
        }

        abort(403, 'Unauthorized access to Mobile Application Settings.');
    }
}

