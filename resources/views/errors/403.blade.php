<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Access Denied</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <style>
        .error-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-primary, #0c1017);
            color: var(--text-primary, #f3f4f6);
        }
        .error-card {
            max-width: 540px;
            width: 100%;
            background: var(--bg-surface, #141b26);
            border: 1px solid var(--border-color, rgba(255,255,255,0.08));
            border-radius: 1.25rem;
            padding: 3rem 2.5rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .error-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .error-code {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #ef4444;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .error-desc {
            color: var(--text-muted, #94a3b8);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="error-page-wrapper">
        <div class="error-card">
            <div class="error-badge">
                <i class="bi bi-shield-x"></i>
            </div>
            <div class="error-code">HTTP 403 Forbidden</div>
            <h1 class="error-title">Access Denied</h1>
            <p class="error-desc">
                {{ $exception->getMessage() ?: 'You do not have the required permissions to access this page or resource. Please contact your Super Administrator if you believe this is an error.' }}
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                @if(auth()->check())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-accent">
                        <i class="bi bi-grid-1x2 me-1"></i>Go to Dashboard
                    </a>
                @endif
                <a href="{{ route('home') }}" class="btn btn-outline-theme">
                    <i class="bi bi-house me-1"></i>Home Page
                </a>
            </div>
        </div>
    </div>
</body>
</html>
