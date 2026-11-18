<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-user-id" content="{{ auth()->id() }}">
    <title>Admin Workspace - Amanullah</title>
    
    <link rel="icon" type="image/png" href="{{ asset('assets/images/amanullah.png') }}">
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('portfolio-theme') || 'dark';
    </script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}?v={{ file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time() }}" rel="stylesheet">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/admin.jsx'])
</head>
<body class="admin-body">
    <div id="admin-root"></div>

    {{-- Mobile Bottom Navigation Bar (PWA Mobile View) --}}
    <nav class="pwa-bottom-nav d-lg-none" aria-label="Mobile Navigation">
        <a href="{{ route('admin.namaz.attendance.index') }}" class="pwa-nav-item">
            <i class="bi bi-moon-stars"></i>
            <span>Namaz</span>
        </a>
        <a href="{{ route('admin.zikr.index') }}" class="pwa-nav-item">
            <i class="bi bi-gem"></i>
            <span>Zikr</span>
        </a>
        <a href="{{ route('admin.date-of-births.index') }}" class="pwa-nav-item">
            <i class="bi bi-calendar-heart"></i>
            <span>DOB</span>
        </a>
        <a href="{{ route('admin.duas.index') }}" class="pwa-nav-item">
            <i class="bi bi-book-half"></i>
            <span>Duas</span>
        </a>
        <a href="{{ route('admin.programs.index') }}" class="pwa-nav-item">
            <i class="bi bi-calendar2-event"></i>
            <span>Programs</span>
        </a>
    </nav>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
