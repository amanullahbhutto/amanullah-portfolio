@extends('layouts.admin')

@section('title', 'Mobile Application Settings')

@section('content')
<section class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1 class="page-title mb-1">
                <i class="bi bi-phone me-2 text-accent"></i>Mobile Application Settings
            </h1>
            <p class="text-muted-custom mb-0">Manage Progressive Web App (PWA) installation, icons, branding, offline synchronization and active state.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="/manifest.json" target="_blank" class="btn btn-outline-theme btn-sm" title="View Dynamic Manifest">
                <i class="bi bi-filetype-json me-1"></i>Manifest.json
            </a>
            <button type="button" class="btn btn-accent btn-sm" data-pwa-install-btn>
                <i class="bi bi-download me-1"></i>Test Install
            </button>
        </div>
    </div>
</section>

{{-- App Active / Deactivate Status Card --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="p-3 p-md-4 rounded-4 border d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="background: #08111e; border-color: {{ $settings->is_active ? '#10b981' : '#ef4444' }} !important;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 52px; height: 52px; border-radius: 16px; background: {{ $settings->is_active ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }}; border: 1px solid {{ $settings->is_active ? 'rgba(16, 185, 129, 0.35)' : 'rgba(239, 68, 68, 0.35)' }}; display: flex; align-items: center; justify-content: center; color: {{ $settings->is_active ? '#10b981' : '#ef4444' }};">
                    <i class="bi {{ $settings->is_active ? 'bi-check-circle-fill' : 'bi-slash-circle-fill' }} fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="mb-0 text-white fs-6 fw-bold">Mobile Application Status:</h4>
                        <span class="badge {{ $settings->is_active ? 'bg-success' : 'bg-danger' }} px-2 py-1">
                            {{ $settings->is_active ? 'ACTIVE & INSTALLABLE' : 'DEACTIVATED' }}
                        </span>
                    </div>
                    <p class="text-muted-custom small mb-0">
                        @if($settings->is_active)
                            App is currently active. Users can install on Android/iOS, use offline mode, and sync records with the database.
                        @else
                            App is deactivated. Install buttons are hidden, background sync is paused, and open apps show the disabled banner.
                        @endif
                    </p>
                </div>
            </div>
            <div>
                <button type="button" class="btn {{ $settings->is_active ? 'btn-outline-danger' : 'btn-success' }} btn-sm px-4 fw-bold" id="btnToggleAppActive" data-toggle-url="{{ route('admin.pwa.toggle-active') }}">
                    <i class="bi {{ $settings->is_active ? 'bi-power' : 'bi-check2' }} me-1"></i>
                    {{ $settings->is_active ? 'Deactivate Mobile App' : 'Activate Mobile App' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Prominent Download & Install Mobile App Card in Body --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="p-4 rounded-4 border position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(8, 17, 30, 0.95) 0%, rgba(12, 22, 38, 0.95) 100%); border-color: rgba(56, 189, 248, 0.35) !important; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $settings->icon_192_url }}" alt="App Icon" style="width: 64px; height: 64px; border-radius: 18px; border: 2px solid rgba(56, 189, 248, 0.4); padding: 2px; box-shadow: 0 8px 20px rgba(0,0,0,0.5);">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h3 class="mb-0 text-white fs-5 fw-bold">{{ $settings->app_name }}</h3>
                            <span class="badge bg-info text-dark fw-bold">v{{ $settings->app_version }}</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2"><i class="bi bi-shield-check me-1"></i>PWA Ready</span>
                        </div>
                        <p class="text-secondary small mb-0" style="line-height: 1.5;">
                            Is website ko apne Mobile (Android / iPhone) ya Computer (Desktop) par direct install karein. Yeh full-screen mobile app ki tarah offline chalegi.
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-accent px-4 py-2 fw-bold d-inline-flex align-items-center gap-2" data-pwa-install-btn style="border-radius: 12px; font-size: 0.95rem; box-shadow: 0 0 20px rgba(255, 107, 44, 0.35);">
                        <i class="bi bi-download fs-6"></i>
                        <span>Download & Install Mobile App</span>
                    </button>
                    <a href="{{ route('pwa.manifest') }}" target="_blank" class="btn btn-outline-theme px-3 py-2 btn-sm" style="border-radius: 12px;" title="View Web App Manifest">
                        <i class="bi bi-filetype-json me-1"></i>Manifest
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.pwa.settings.update') }}" enctype="multipart/form-data" id="pwaSettingsForm">
    @csrf
    @method('PUT')

    <input type="hidden" name="is_active" id="inputIsActive" value="{{ $settings->is_active ? '1' : '0' }}">

    <div class="row g-4">
        {{-- Section 1: General Information --}}
        <div class="col-12 col-xl-6">
            <div class="p-4 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-info"></i>General Application Info
                </h5>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Application Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="app_name" class="form-control" value="{{ old('app_name', $settings->app_name) }}" required placeholder="e.g. Amanullah Portfolio & Management" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    <small class="text-muted-custom">Displayed on app splash screens and installation dialogs.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Application Short Name <span class="text-danger">*</span></label>
                    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $settings->short_name) }}" required placeholder="e.g. Amanullah" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    <small class="text-muted-custom">Displayed on mobile home screens under the app icon (max 12-15 chars).</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Application Description</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Brief description of application..." style="background: #0c1626; border-color: #1c2c44; color: #fff;">{{ old('description', $settings->description) }}</textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-white small fw-bold">Display Mode</label>
                        <select name="display_mode" class="form-select" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                            <option value="standalone" {{ $settings->display_mode === 'standalone' ? 'selected' : '' }}>Standalone (App like)</option>
                            <option value="fullscreen" {{ $settings->display_mode === 'fullscreen' ? 'selected' : '' }}>Fullscreen</option>
                            <option value="minimal-ui" {{ $settings->display_mode === 'minimal-ui' ? 'selected' : '' }}>Minimal UI</option>
                            <option value="browser" {{ $settings->display_mode === 'browser' ? 'selected' : '' }}>Browser</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-white small fw-bold">Orientation</label>
                        <select name="orientation" class="form-select" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                            <option value="portrait-primary" {{ $settings->orientation === 'portrait-primary' ? 'selected' : '' }}>Portrait Primary</option>
                            <option value="portrait" {{ $settings->orientation === 'portrait' ? 'selected' : '' }}>Portrait</option>
                            <option value="landscape" {{ $settings->orientation === 'landscape' ? 'selected' : '' }}>Landscape</option>
                            <option value="any" {{ $settings->orientation === 'any' ? 'selected' : '' }}>Any</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Start URL</label>
                    <input type="text" name="start_url" class="form-control" value="{{ old('start_url', $settings->start_url) }}" required placeholder="/admin/dashboard" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                </div>
            </div>
        </div>

        {{-- Section 2: Colors & Versioning --}}
        <div class="col-12 col-xl-6">
            <div class="p-4 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-palette text-accent"></i>Theme Colors & Versioning
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label text-white small fw-bold">Theme Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color" id="themeColorPicker" value="{{ $settings->theme_color ?? '#070d18' }}" style="background: #0c1626; border-color: #1c2c44;">
                            <input type="text" name="theme_color" id="themeColorInput" class="form-control font-monospace" value="{{ old('theme_color', $settings->theme_color ?? '#070d18') }}" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        </div>
                        <small class="text-muted-custom">Status bar and navigation bar color.</small>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-white small fw-bold">Background Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" class="form-control form-control-color" id="bgColorPicker" value="{{ $settings->background_color ?? '#070d18' }}" style="background: #0c1626; border-color: #1c2c44;">
                            <input type="text" name="background_color" id="bgColorInput" class="form-control font-monospace" value="{{ old('background_color', $settings->background_color ?? '#070d18') }}" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        </div>
                        <small class="text-muted-custom">Splash screen background color.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Application Version <span class="text-danger">*</span></label>
                    <input type="text" name="app_version" class="form-control font-monospace" value="{{ old('app_version', $settings->app_version) }}" required placeholder="1.0.0" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    <small class="text-muted-custom">Changing version number automatically invalidates and refreshes Service Worker cache for all users.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Install Button Text</label>
                    <input type="text" name="install_button_text" class="form-control" value="{{ old('install_button_text', $settings->install_button_text) }}" required placeholder="Install Mobile App" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="offline_mode_enabled" id="offlineModeCheck" value="1" {{ $settings->offline_mode_enabled ? 'checked' : '' }}>
                            <label class="form-check-label text-white small fw-bold" for="offlineModeCheck">Enable Offline Mode</label>
                        </div>
                        <small class="text-muted-custom d-block">Allows IndexedDB offline records.</small>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="auto_sync_enabled" id="autoSyncCheck" value="1" {{ $settings->auto_sync_enabled ? 'checked' : '' }}>
                            <label class="form-check-label text-white small fw-bold" for="autoSyncCheck">Enable Auto-Sync</label>
                        </div>
                        <small class="text-muted-custom d-block">Syncs outbox upon reconnecting.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white small fw-bold">Max Offline Duration (Days)</label>
                    <input type="number" name="max_offline_days" class="form-control" value="{{ old('max_offline_days', $settings->max_offline_days) }}" min="1" max="365" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                </div>
            </div>
        </div>

        {{-- Section 3: Branding & Icon Uploads --}}
        <div class="col-12">
            <div class="p-4 rounded-4 border" style="background: #08111e; border-color: #142845;">
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-images text-success"></i>Application Branding & Icons
                </h5>
                <p class="text-muted-custom small mb-4">Upload PNG/WEBP icons. Standard dimensions are required for Android, iOS and Desktop standalone modes.</p>

                <div class="row g-4">
                    {{-- 192x192 Icon --}}
                    <div class="col-12 col-md-6 col-lg-3 text-center">
                        <label class="form-label text-white small fw-bold d-block mb-2">192×192 Standard Icon</label>
                        <div class="mb-3 p-3 rounded-3 d-inline-block" style="background: #0c1626; border: 1px solid #1c2c44;">
                            <img src="{{ $settings->icon_192_url }}?v={{ time() }}" id="preview_icon_192" alt="192x192 Icon" style="width: 80px; height: 80px; object-fit: contain; border-radius: 16px;">
                        </div>
                        <input type="file" name="icon_192_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this, 'preview_icon_192')" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        <small class="text-muted-custom d-block mt-1">Required for mobile home screen</small>
                    </div>

                    {{-- 512x512 Icon --}}
                    <div class="col-12 col-md-6 col-lg-3 text-center">
                        <label class="form-label text-white small fw-bold d-block mb-2">512×512 High-Res Icon</label>
                        <div class="mb-3 p-3 rounded-3 d-inline-block" style="background: #0c1626; border: 1px solid #1c2c44;">
                            <img src="{{ $settings->icon_512_url }}?v={{ time() }}" id="preview_icon_512" alt="512x512 Icon" style="width: 80px; height: 80px; object-fit: contain; border-radius: 16px;">
                        </div>
                        <input type="file" name="icon_512_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this, 'preview_icon_512')" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        <small class="text-muted-custom d-block mt-1">Required for splash screens & app stores</small>
                    </div>

                    {{-- Maskable Icon --}}
                    <div class="col-12 col-md-6 col-lg-3 text-center">
                        <label class="form-label text-white small fw-bold d-block mb-2">512×512 Maskable Icon</label>
                        <div class="mb-3 p-3 rounded-3 d-inline-block" style="background: #0c1626; border: 1px solid #1c2c44;">
                            <img src="{{ $settings->icon_maskable_url }}?v={{ time() }}" id="preview_icon_maskable" alt="Maskable Icon" style="width: 80px; height: 80px; object-fit: contain; border-radius: 50%;">
                        </div>
                        <input type="file" name="icon_maskable_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this, 'preview_icon_maskable')" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        <small class="text-muted-custom d-block mt-1">Adaptive shape icon on Android devices</small>
                    </div>

                    {{-- Splash / Logo --}}
                    <div class="col-12 col-md-6 col-lg-3 text-center">
                        <label class="form-label text-white small fw-bold d-block mb-2">Splash Screen Image</label>
                        <div class="mb-3 p-3 rounded-3 d-inline-block" style="background: #0c1626; border: 1px solid #1c2c44;">
                            <img src="{{ $settings->splash_image_url }}?v={{ time() }}" id="preview_splash_image" alt="Splash Image" style="width: 80px; height: 80px; object-fit: contain; border-radius: 16px;">
                        </div>
                        <input type="file" name="splash_image_file" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp" onchange="previewImage(this, 'preview_splash_image')" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        <small class="text-muted-custom d-block mt-1">Centered on startup splash screen</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: System Messages & Banners --}}
        <div class="col-12">
            <div class="p-4 rounded-4 border" style="background: #08111e; border-color: #142845;">
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-quote text-warning"></i>System Messages & Notifications
                </h5>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-white small fw-bold">Offline Banner Message</label>
                        <textarea name="offline_message" rows="3" class="form-control" style="background: #0c1626; border-color: #1c2c44; color: #fff;">{{ old('offline_message', $settings->offline_message) }}</textarea>
                        <small class="text-muted-custom">Shown when internet connection is lost.</small>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label text-white small fw-bold">App Disabled Message</label>
                        <textarea name="disabled_message" rows="3" class="form-control" style="background: #0c1626; border-color: #1c2c44; color: #fff;">{{ old('disabled_message', $settings->disabled_message) }}</textarea>
                        <small class="text-muted-custom">Shown when admin deactivates the app.</small>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label text-white small fw-bold">Maintenance Message</label>
                        <textarea name="maintenance_message" rows="3" class="form-control" style="background: #0c1626; border-color: #1c2c44; color: #fff;">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>
                        <small class="text-muted-custom">Shown during server updates.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit Footer --}}
    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-end gap-2">
        <button type="submit" class="btn btn-accent btn-lg px-5 fw-bold" id="btnSavePwaSettings">
            <i class="bi bi-check2-circle me-1"></i>Save Application Settings
        </button>
    </div>
</form>

@include('admin.pwa.partials.ios-modal')
@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Theme Color Picker synchronization
    const themePicker = document.getElementById('themeColorPicker');
    const themeInput = document.getElementById('themeColorInput');
    if (themePicker && themeInput) {
        themePicker.addEventListener('input', () => themeInput.value = themePicker.value);
        themeInput.addEventListener('input', () => {
            if (/^#[0-9A-Fa-f]{6}$/.test(themeInput.value)) themePicker.value = themeInput.value;
        });
    }

    // Background Color Picker synchronization
    const bgPicker = document.getElementById('bgColorPicker');
    const bgInput = document.getElementById('bgColorInput');
    if (bgPicker && bgInput) {
        bgPicker.addEventListener('input', () => bgInput.value = bgPicker.value);
        bgInput.addEventListener('input', () => {
            if (/^#[0-9A-Fa-f]{6}$/.test(bgInput.value)) bgPicker.value = bgInput.value;
        });
    }

    // Toggle Active Status AJAX
    const toggleBtn = document.getElementById('btnToggleAppActive');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', async function () {
            const url = toggleBtn.getAttribute('data-toggle-url');
            toggleBtn.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();
                if (data.success) {
                    if (window.App && typeof window.App.showToast === 'function') {
                        window.App.showToast('success', data.message);
                    }
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    alert(data.message || 'Failed to toggle app status.');
                }
            } catch (e) {
                console.error(e);
                alert('An unexpected error occurred.');
            } finally {
                toggleBtn.disabled = false;
            }
        });
    }
});
</script>
@endpush

