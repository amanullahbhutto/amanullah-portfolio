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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}?v={{ file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time() }}" rel="stylesheet">

    <style>
        :root {
            --accent: #ff6b2c;
            --accent-dark: #e95417;
            --accent-soft: rgba(255, 107, 44, .13);
            --bg: #0b0c0f;
            --bg-alt: #111318;
            --surface: #17191f;
            --surface-2: #20232a;
            --text: #f6f7fb;
            --muted: #a7abb6;
            --line: rgba(255, 255, 255, .09);
            --shadow: 0 24px 80px rgba(0, 0, 0, .3);
        }

        [data-theme="light"] {
            --bg: #f7f8fb;
            --bg-alt: #ffffff;
            --surface: #ffffff;
            --surface-2: #f0f2f6;
            --text: #15171d;
            --muted: #626978;
            --line: rgba(21, 23, 29, .1);
            --shadow: 0 24px 80px rgba(30, 37, 50, .1);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            min-height: 100vh;
            min-height: 100dvh;
            transition: background 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        .auth-centered-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 16px;
            position: relative;
            z-index: 1;
        }

        .auth-bg-ambient {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .auth-bg-ambient::before {
            content: '';
            position: absolute;
            top: 15%;
            left: 50%;
            transform: translateX(-50%);
            width: min(600px, 90vw);
            height: 400px;
            background: radial-gradient(circle, rgba(255, 107, 44, 0.14) 0%, rgba(56, 189, 248, 0.08) 50%, transparent 70%);
            filter: blur(80px);
        }

        .auth-centered-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: auto 0;
        }

        .auth-compact-header {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .auth-brand-mini {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text);
        }

        .auth-brand-mini .brand-mini-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #ff6b2c 0%, #e95417 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 1.05rem;
            color: #fff;
            box-shadow: 0 4px 14px rgba(255, 107, 44, 0.35);
        }

        .auth-brand-mini .brand-mini-text {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        .auth-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auth-btn-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--muted);
            padding: 5px 11px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: var(--surface);
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .auth-btn-pill:hover {
            color: var(--accent);
            border-color: var(--accent);
            background: var(--surface-2);
        }

        .auth-theme-pill-btn {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: var(--surface);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .auth-theme-pill-btn:hover {
            color: var(--accent);
            border-color: var(--accent);
            background: var(--surface-2);
        }

        .auth-compact-card {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 24px 26px 20px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .auth-compact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2.5px;
            background: linear-gradient(90deg, #ff6b2c 0%, #f59e0b 50%, #06b6d4 100%);
        }

        .auth-compact-card-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .auth-compact-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: 14px;
            background: var(--accent-soft);
            border: 1px solid rgba(255, 107, 44, 0.25);
            color: var(--accent);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
        }

        .auth-compact-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text);
            margin: 0 0 2px 0;
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        .auth-compact-subtitle {
            font-size: 0.76rem;
            color: var(--muted);
            margin: 0;
        }

        .auth-input-group-compact {
            position: relative;
            margin-bottom: 12px;
        }

        .auth-input-group-compact label {
            display: block;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 4px;
        }

        .auth-input-wrapper-compact {
            position: relative;
            display: flex;
            align-items: center;
        }

        .auth-input-icon-compact {
            position: absolute;
            left: 12px;
            color: var(--muted);
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 2;
        }

        .auth-input-control-compact {
            width: 100%;
            height: 42px;
            padding: 6px 38px 6px 38px;
            border-radius: 11px;
            border: 1px solid var(--line);
            background: var(--bg-alt);
            color: var(--text);
            font-size: 0.84rem;
            font-family: inherit;
            transition: all 0.2s ease;
            outline: none;
        }

        .auth-input-control-compact.no-right-icon {
            padding-right: 12px;
        }

        .auth-input-control-compact::placeholder {
            color: var(--muted);
            opacity: 0.55;
            font-size: 0.8rem;
        }

        .auth-input-control-compact:focus {
            border-color: var(--accent);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(255, 107, 44, 0.15);
        }

        .auth-input-wrapper-compact:focus-within .auth-input-icon-compact {
            color: var(--accent);
        }

        .auth-password-toggle-compact {
            position: absolute;
            right: 8px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            color: var(--muted);
            font-size: 0.95rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.18s ease;
            z-index: 3;
        }

        .auth-password-toggle-compact:hover {
            color: var(--accent);
            background: var(--accent-soft);
        }

        .auth-extra-row-compact {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2px;
            margin-bottom: 14px;
            font-size: 0.74rem;
        }

        .auth-custom-checkbox-compact {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            user-select: none;
            color: var(--muted);
            font-size: 0.74rem;
        }

        .auth-custom-checkbox-compact input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid var(--line);
            background: var(--bg-alt);
            cursor: pointer;
            position: relative;
            transition: all 0.18s ease;
            flex-shrink: 0;
        }

        .auth-custom-checkbox-compact input[type="checkbox"]:checked {
            background: var(--accent);
            border-color: var(--accent);
        }

        .auth-custom-checkbox-compact input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 4.5px;
            top: 1.5px;
            width: 5px;
            height: 9px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .btn-auth-submit-compact {
            width: 100%;
            height: 42px;
            border-radius: 11px;
            font-size: 0.88rem;
            font-weight: 700;
            border: none;
            background: linear-gradient(135deg, #ff6b2c 0%, #e95417 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 6px 18px rgba(255, 107, 44, 0.3);
            cursor: pointer;
            transition: all 0.22s ease;
        }

        .btn-auth-submit-compact:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(255, 107, 44, 0.4);
            color: #fff;
        }

        .btn-auth-submit-compact:active {
            transform: translateY(0);
        }

        .auth-switch-box-compact {
            margin-top: 13px;
            padding-top: 11px;
            border-top: 1px solid var(--line);
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
        }

        .auth-switch-box-compact a {
            color: var(--accent);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.18s ease;
        }

        .auth-switch-box-compact a:hover {
            text-decoration: underline;
            color: #ff8c5a;
        }

        .auth-security-footer-compact {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 0.68rem;
            color: var(--muted);
            opacity: 0.75;
            margin-top: 10px;
        }

        .text-accent { color: var(--accent) !important; }
        .text-danger { color: #ef4444 !important; }
        .d-none { display: none !important; }
        .spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }

        @media (max-width: 480px) {
            .auth-compact-card {
                padding: 20px 18px 16px;
                border-radius: 16px;
            }
            .auth-compact-title {
                font-size: 1.28rem;
            }
        }
    </style>
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
