<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account • Amanullah Portfolio</title>
    
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
                        <i class="bi bi-person-plus-fill"></i> New User
                    </span>
                    <h1 class="auth-compact-title">Create account</h1>
                    <p class="auth-compact-subtitle">Enter your details to register your account.</p>
                </div>

                <form method="POST" action="{{ route('register.store') }}" id="registerForm">
                    @csrf

                    {{-- Full Name Field --}}
                    <div class="auth-input-group-compact">
                        <label for="name">Full Name</label>
                        <div class="auth-input-wrapper-compact">
                            <i class="bi bi-person auth-input-icon-compact"></i>
                            <input 
                                class="auth-input-control-compact no-right-icon" 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autofocus 
                                autocomplete="name" 
                                placeholder="Amanullah Bhutto"
                            >
                        </div>
                    </div>

                    {{-- Email Address Field --}}
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
                                autocomplete="email" 
                                placeholder="name@example.com"
                            >
                        </div>
                    </div>

                    {{-- Password Field with Eye Toggle --}}
                    <div class="auth-input-group-compact">
                        <label for="password">Password (Min. 8 Chars)</label>
                        <div class="auth-input-wrapper-compact">
                            <i class="bi bi-shield-lock auth-input-icon-compact"></i>
                            <input 
                                class="auth-input-control-compact" 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="new-password" 
                                placeholder="Enter strong password"
                            >
                            <button type="button" class="auth-password-toggle-compact" id="toggleRegisterPasswordBtn" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="toggleRegisterPasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Dynamic Password Strength Meter --}}
                    <div class="password-strength-compact" id="strengthContainer" style="display: none;">
                        <div class="password-strength-bars-compact">
                            <div class="strength-bar-segment-compact" id="seg1"></div>
                            <div class="strength-bar-segment-compact" id="seg2"></div>
                            <div class="strength-bar-segment-compact" id="seg3"></div>
                            <div class="strength-bar-segment-compact" id="seg4"></div>
                        </div>
                        <div class="strength-text-compact">
                            <span>Strength: <strong id="strengthLabel">Weak</strong></span>
                            <span id="strengthHint">Min 8 characters</span>
                        </div>
                    </div>

                    {{-- Confirm Password Field with Eye Toggle --}}
                    <div class="auth-input-group-compact">
                        <label for="password_confirmation">Confirm Password</label>
                        <div class="auth-input-wrapper-compact">
                            <i class="bi bi-lock-check auth-input-icon-compact"></i>
                            <input 
                                class="auth-input-control-compact" 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                required 
                                autocomplete="new-password" 
                                placeholder="Re-enter password"
                            >
                            <button type="button" class="auth-password-toggle-compact" id="toggleConfirmPasswordBtn" tabindex="-1" aria-label="Toggle confirm password visibility">
                                <i class="bi bi-eye" id="toggleConfirmPasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button class="btn-auth-submit-compact mt-2" type="submit" id="btnSubmitRegister">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="registerSpinner" role="status" aria-hidden="true"></span>
                        <span id="registerBtnText">Create User Account</span>
                        <i class="bi bi-arrow-right" id="registerBtnIcon"></i>
                    </button>
                </form>

                {{-- Switch to Login --}}
                <div class="auth-switch-box-compact">
                    Already registered? 
                    <a href="{{ route('login') }}">Sign In <i class="bi bi-arrow-right ms-1"></i></a>
                </div>

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
            // Password Show/Hide Toggle for Main Password
            const togglePasswordBtn = document.getElementById('toggleRegisterPasswordBtn');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('toggleRegisterPasswordIcon');

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

            // Password Show/Hide Toggle for Confirm Password
            const toggleConfirmBtn = document.getElementById('toggleConfirmPasswordBtn');
            const confirmInput = document.getElementById('password_confirmation');
            const toggleConfirmIcon = document.getElementById('toggleConfirmPasswordIcon');

            if (toggleConfirmBtn && confirmInput && toggleConfirmIcon) {
                toggleConfirmBtn.addEventListener('click', function () {
                    if (confirmInput.type === 'password') {
                        confirmInput.type = 'text';
                        toggleConfirmIcon.className = 'bi bi-eye-slash';
                    } else {
                        confirmInput.type = 'password';
                        toggleConfirmIcon.className = 'bi bi-eye';
                    }
                });
            }

            // Interactive Password Strength Meter
            const strengthContainer = document.getElementById('strengthContainer');
            const seg1 = document.getElementById('seg1');
            const seg2 = document.getElementById('seg2');
            const seg3 = document.getElementById('seg3');
            const seg4 = document.getElementById('seg4');
            const strengthLabel = document.getElementById('strengthLabel');
            const strengthHint = document.getElementById('strengthHint');

            if (passwordInput && strengthContainer) {
                passwordInput.addEventListener('input', function () {
                    const val = passwordInput.value;
                    if (!val) {
                        strengthContainer.style.display = 'none';
                        return;
                    }

                    strengthContainer.style.display = 'block';

                    let score = 0;
                    if (val.length >= 8) score++;
                    if (/[0-9]/.test(val)) score++;
                    if (/[A-Z]/.test(val) || /[a-z]/.test(val)) score++;
                    if (/[^A-Za-z0-9]/.test(val) && val.length >= 10) score++;

                    // Reset classes
                    [seg1, seg2, seg3, seg4].forEach(s => {
                        s.className = 'strength-bar-segment-compact';
                    });

                    if (score === 1 || val.length < 8) {
                        seg1.classList.add('weak');
                        strengthLabel.textContent = 'Weak';
                        strengthLabel.style.color = '#ef4444';
                        strengthHint.textContent = 'Min 8 characters';
                    } else if (score === 2) {
                        seg1.classList.add('fair');
                        seg2.classList.add('fair');
                        strengthLabel.textContent = 'Fair';
                        strengthLabel.style.color = '#f59e0b';
                        strengthHint.textContent = 'Add numbers / symbols';
                    } else if (score === 3) {
                        seg1.classList.add('good');
                        seg2.classList.add('good');
                        seg3.classList.add('good');
                        strengthLabel.textContent = 'Good';
                        strengthLabel.style.color = '#3b82f6';
                        strengthHint.textContent = 'Strong password!';
                    } else if (score >= 4) {
                        seg1.classList.add('strong');
                        seg2.classList.add('strong');
                        seg3.classList.add('strong');
                        seg4.classList.add('strong');
                        strengthLabel.textContent = 'Very Strong';
                        strengthLabel.style.color = '#10b981';
                        strengthHint.textContent = 'Excellent!';
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
            const registerForm = document.getElementById('registerForm');
            const btnSubmit = document.getElementById('btnSubmitRegister');
            const spinner = document.getElementById('registerSpinner');
            const btnText = document.getElementById('registerBtnText');
            const btnIcon = document.getElementById('registerBtnIcon');

            if (registerForm && btnSubmit) {
                registerForm.addEventListener('submit', function () {
                    btnSubmit.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');
                    if (btnIcon) btnIcon.classList.add('d-none');
                    if (btnText) btnText.textContent = 'Creating account...';
                });
            }
        });
    </script>
</body>
</html>
