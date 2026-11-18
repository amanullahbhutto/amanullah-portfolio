<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $profile?->full_name ?? 'Amanullah' }} - PHP &amp; Laravel Web Developer</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/amanullah.png') }}">

    <meta name="theme-color" content="{{ $pwaSettings?->theme_color ?? '#070d18' }}">
    <meta name="description" content="Amanullah is a PHP and Laravel developer in Karachi building responsive, secure, and maintainable web applications.">
    <script>document.documentElement.dataset.theme=localStorage.getItem('portfolio-theme')||'dark';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}?v={{ file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time() }}" rel="stylesheet">

    <!-- High-priority inline Quranic Font & Ayah Symbol styles to guarantee exact rendering on live server -->
    <style>
        @font-face {
            font-family: 'PDMS Saleem Quran';
            src: url('{{ asset("assets/fonts/pdms-saleem-quranfont.ttf") }}') format('truetype'),
                 url('{{ asset("assets/fonts/indopak-quran.woff2") }}') format('woff2');
            font-weight: 400 700;
            font-style: normal;
            font-display: swap;
        }
        .arabic-text,
        .font-arabic,
        .desc-arabic-text,
        .dua-content-box .arabic-text {
            font-family: 'PDMS Saleem Quran', '_PDMS_Saleem_Quran', 'PDMS_Saleem_QuranFont', 'IndoPak Quran', 'Al Qalam Quran Majeed', 'Amiri Quran', 'Scheherazade New', serif !important;
            color: #f97316 !important;
        }
        .ayah-symbol,
        .arabic-text .ayah-symbol,
        .font-arabic .ayah-symbol,
        .desc-arabic-text .ayah-symbol,
        .dua-content-box .ayah-symbol {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            max-width: 22px !important;
            border-radius: 50% !important;
            border: 1.5px solid rgba(249, 115, 22, 0.85) !important;
            background: rgba(249, 115, 22, 0.08) !important;
            font-size: 13px !important;
            font-family: 'Amiri', 'Noto Naskh Arabic', sans-serif !important;
            font-weight: 700 !important;
            color: #f97316 !important;
            line-height: 1 !important;
            margin: 0 5px !important;
            vertical-align: 3px !important;
            unicode-bidi: isolate !important;
            box-shadow: 0 0 5px rgba(249, 115, 22, 0.2) !important;
            box-sizing: border-box !important;
        }
        [data-theme="light"] .ayah-symbol,
        [data-theme="light"] .arabic-text .ayah-symbol,
        [data-theme="light"] .font-arabic .ayah-symbol,
        [data-theme="light"] .desc-arabic-text .ayah-symbol,
        [data-theme="light"] .dua-content-box .ayah-symbol {
            border-color: rgba(234, 88, 12, 0.85) !important;
            color: #ea580c !important;
            background: rgba(234, 88, 12, 0.08) !important;
            box-shadow: none !important;
        }
    </style>

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/main.jsx'])
</head>
<body class="public-body">
    <div id="root"></div>

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/aos/aos.js') }}"></script>

    {{-- Ensure public visitors have no active PWA install prompt in browser URL bar --}}
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function (registrations) {
                for (let r of registrations) {
                    r.unregister();
                }
            });
        }
    </script>
</body>
</html>
