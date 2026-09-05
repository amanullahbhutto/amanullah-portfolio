<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in • Amanullah Portfolio CMS</title>
    
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('portfolio-theme') || 'dark';
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
</head>
<body>
    @include('partials.flash')

    <main class="auth-centered-page">
        {{-- Ambient Glow Backdrop --}}
        <div class="auth-bg-ambient"></div>

        <div class="auth-centered-wrapper">
            {{-- Compact Top Navigation & Brand --}}
            <div class="auth-compact-header">
                <a class="auth-brand-mini" href="{{ route('home') }}">
                    <div class="brand-mini-icon">A</div>
                    <span class="brand-mini-text">AMANULLAH<span style="color: #ff6b2c;">.</span></span>
                </a>

                <div class="auth-header-actions">
                    <a class="auth-btn-pill" href="{{ route('home') }}">
                        <i class="bi bi-arrow-left"></i>
                        <span>Website</span>
                    </a>

                    <button type="button" class="auth-theme-pill-btn" data-theme-toggle title="Toggle Dark/Light Mode">
                        <i class="bi bi-moon-stars" id="authThemeIcon"></i>
                    </button>
                </div>
            </div>

            {{-- Compact Glass Card --}}
            <div class="auth-compact-card">
                <div class="auth-compact-card-header">
                    <span class="auth-compact-badge">
                        <i class="bi bi-person-check-fill"></i> Admin Portal
                    </span>
                    <h1 class="auth-compact-title">Welcome back</h1>
                    <p class="auth-compact-subtitle">Enter your credentials to access your dashboard.</p>
                </div>

                <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                    @csrf

                    {{-- Email Field --}}
                    <div class="auth-input-group-compact">
                        <label for="email">Email Address</label>
                        <div class="auth-input-wrapper-compact">
                            <i class="bi bi-envelope-at auth-input-icon-compact"></i>
                            <input 
                                class="auth-input-control-compact no-right-icon" 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                autocomplete="email" 
                                placeholder="admin@example.com"
                            >
                        </div>
                    </div>

                    {{-- Password Field with Eye Toggle --}}
                    <div class="auth-input-group-compact">
                        <label for="password">Password</label>
                        <div class="auth-input-wrapper-compact">
                            <i class="bi bi-shield-lock auth-input-icon-compact"></i>
                            <input 
                                class="auth-input-control-compact" 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password" 
                                placeholder="Enter your password"
                            >
                            <button type="button" class="auth-password-toggle-compact" id="togglePasswordBtn" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me & Security Row --}}
                    <div class="auth-extra-row-compact">
                        <label class="auth-custom-checkbox-compact">
                            <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <span>Keep me signed in</span>
                        </label>

                        <span class="text-muted-custom small d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                            <i class="bi bi-lock-fill text-accent"></i> Encrypted
                        </span>
                    </div>

                    {{-- Submit Button --}}
                    <button class="btn-auth-submit-compact" type="submit" id="btnSubmitLogin">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
                        <span id="loginBtnText">Sign in to Dashboard</span>
                        <i class="bi bi-arrow-right" id="loginBtnIcon"></i>
                    </button>
                </form>

                {{-- Switch to Register --}}
                @if(config('portfolio.allow_registration'))
                    <div class="auth-switch-box-compact">
                        Need a user account? 
                        <a href="{{ route('register') }}">Create Account <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                @endif

                {{-- Security Badge --}}
                <div class="auth-security-footer-compact">
                    <i class="bi bi-shield-fill-check text-accent"></i>
                    <span>256-bit SSL Secure Verification</span>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password Show/Hide Toggle
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
                togglePasswordBtn.addEventListener('click', function () {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        togglePasswordIcon.className = 'bi bi-eye-slash';
                    } else {
                        passwordInput.type = 'password';
                        togglePasswordIcon.className = 'bi bi-eye';
                    }
                });
            }

            // Theme Icon update based on current theme
            const themeBtn = document.querySelector('[data-theme-toggle]');
            const themeIcon = document.getElementById('authThemeIcon');

            function syncThemeIcon() {
                const currentTheme = document.documentElement.dataset.theme || 'dark';
                if (themeIcon) {
                    themeIcon.className = currentTheme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
                }
            }
            syncThemeIcon();

            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    setTimeout(syncThemeIcon, 50);
                });
            }

            // Submit Button Loading state
            const loginForm = document.getElementById('loginForm');
            const btnSubmit = document.getElementById('btnSubmitLogin');
            const spinner = document.getElementById('loginSpinner');
            const btnText = document.getElementById('loginBtnText');
            const btnIcon = document.getElementById('loginBtnIcon');

            if (loginForm && btnSubmit) {
                loginForm.addEventListener('submit', function () {
                    btnSubmit.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');
                    if (btnIcon) btnIcon.classList.add('d-none');
                    if (btnText) btnText.textContent = 'Verifying...';
                });
            }
        });
    </script>
</body>
</html>
