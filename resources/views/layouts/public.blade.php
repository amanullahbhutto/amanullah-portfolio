<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($profile?->full_name ?? 'Amanullah'))</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/amanullah.png') }}">

    {{-- Progressive Web App (PWA) Meta & Icons --}}
    @php
        $pwaSettings = \App\Models\PwaSetting::getSettings();
    @endphp
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="{{ $pwaSettings->theme_color ?? '#070d18' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaSettings->short_name ?? 'Amanullah' }}">
    <link rel="apple-touch-icon" href="{{ $pwaSettings->icon_192_url }}">

    <meta name="description" content="@yield('meta_description', 'Amanullah is a PHP and Laravel developer in Karachi building responsive, secure, and maintainable web applications.')">
    <script>document.documentElement.dataset.theme=localStorage.getItem('portfolio-theme')||'dark';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="public-body">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header" id="siteHeader">
        <nav class="navbar navbar-expand-lg" aria-label="Main navigation">
            <div class="container">
                <a class="navbar-brand brand-mark" href="{{ route('home') }}" aria-label="Amanullah home">
                    <span class="brand-symbol">A</span><span>AMANULLAH<span class="brand-dot">.</span></span>
                </a>
                <div class="d-flex align-items-center gap-2 order-lg-3">
                    <button type="button" class="btn btn-outline-theme btn-sm d-none" data-pwa-install-btn title="Install App">
                        <i class="bi bi-download me-1"></i><span>{{ $pwaSettings->install_button_text ?? 'Install App' }}</span>
                    </button>
                    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch colour theme" title="Day / night mode">
                        <i class="bi bi-sun-fill theme-icon-light"></i>
                        <i class="bi bi-moon-stars-fill theme-icon-dark"></i>
                    </button>
                    <a href="{{ route('contact.create') }}" class="btn btn-accent d-none d-sm-inline-flex">Hire me <i class="bi bi-arrow-up-right ms-2"></i></a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav mx-auto align-items-lg-center">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">Projects</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('posts.*') ? 'active' : '' }}" href="{{ route('posts.index') }}">Blog Posts</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}" href="{{ route('services.index') }}">Services</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Me</a></li>
                        <li class="nav-item d-sm-none"><a class="nav-link {{ request()->routeIs('contact.*') ? 'active' : '' }}" href="{{ route('contact.create') }}">Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content" style="overflow-x: hidden;">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-main row g-5 align-items-start">
                <div class="col-lg-5">
                    <a class="brand-mark footer-brand" href="{{ route('home') }}"><span class="brand-symbol">A</span><span>AMANULLAH<span class="brand-dot">.</span></span></a>
                    <p class="footer-summary">{{ $profile?->short_bio ?? 'PHP and Laravel developer building responsive and maintainable web applications.' }}</p>
                    <div class="social-links">
                        @if($profile?->github_url)<a href="{{ $profile->github_url }}" target="_blank" rel="noopener" aria-label="GitHub"><i class="bi bi-github"></i></a>@endif
                        @if($profile?->linkedin_url)<a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>@endif
                        @if($profile?->whatsapp_url)<a class="whatsapp-link" href="{{ $profile->whatsapp_url }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>@endif
                        <a class="gmail-link" href="{{ $profile?->gmail_compose_url ?? 'https://mail.google.com/mail/?view=cm&fs=1&to=aman.ullah.csc%40gmail.com' }}" target="_blank" rel="noopener" aria-label="Email"><i class="bi bi-envelope"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2 offset-lg-1">
                    <h2 class="footer-title">Explore</h2>
                    <ul class="footer-links">
                        <li><a href="{{ route('projects.index') }}">Projects</a></li>
                        <li><a href="{{ route('services.index') }}">Services</a></li>
                        <li><a href="{{ route('posts.index') }}">Blog</a></li>
                        <li><a href="{{ route('about') }}">About</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4">
                    <h2 class="footer-title">Contact</h2>
                    <ul class="footer-links footer-contact">
                        <li><a class="gmail-link" href="{{ $profile?->gmail_compose_url ?? 'https://mail.google.com/mail/?view=cm&fs=1&to=aman.ullah.csc%40gmail.com' }}" target="_blank" rel="noopener">{{ $profile?->email ?? 'aman.ullah.csc@gmail.com' }}</a></li>
                        @if($profile?->phone)<li><a class="whatsapp-link" href="{{ $profile->whatsapp_url }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>{{ $profile->phone }}</a></li>@endif
                        <li>{{ $profile?->city ?? 'Karachi' }}, {{ $profile?->country ?? 'Pakistan' }}</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
                <span>&copy; {{ date('Y') }} {{ $profile?->full_name ?? 'Amanullah' }}. All rights reserved.</span>
                {{-- <a href="{{ route('login') }}" class="admin-link"><i class="bi bi-shield-lock me-1"></i>Admin</a> --}}
            </div>
        </div>
    </footer>

    <button class="back-to-top" type="button" data-back-to-top aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>
    
    {{-- iOS Safari Installation Instructions Modal --}}
    @include('admin.pwa.partials.ios-modal')

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    
    {{-- PWA Installer & Service Worker Registration --}}
    <script src="{{ asset('assets/js/pwa/pwa-installer.js') }}?v={{ file_exists(public_path('assets/js/pwa/pwa-installer.js')) ? filemtime(public_path('assets/js/pwa/pwa-installer.js')) : time() }}"></script>
    @stack('scripts')
</body>
</html>
