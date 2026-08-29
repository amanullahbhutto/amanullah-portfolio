<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline | {{ $settings->app_name ?? 'Amanullah Portfolio' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #070d18;
            color: #f1f5f9;
            font-family: system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .offline-card {
            background: #08111e;
            border: 1px solid #142845;
            border-radius: 24px;
            padding: 40px 30px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
        }
        .offline-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.35);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #f59e0b;
            margin-bottom: 24px;
        }
        .btn-theme {
            background: #38bdf8;
            color: #070d18;
            font-weight: 600;
            border-radius: 12px;
            padding: 10px 24px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-theme:hover {
            background: #0ea5e9;
            color: #070d18;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.4);
        }
        .btn-outline {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #18283e;
            border-radius: 12px;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border-color: #38bdf8;
        }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">
            <i class="bi bi-cloud-slash fs-1"></i>
        </div>
        <h3 class="fw-bold text-white mb-2">You're Offline</h3>
        <p class="text-secondary small mb-4" style="line-height: 1.6;">
            {{ $settings->offline_message ?? 'Aap internet se disconnected hain. Aapka data locally IndexedDB mein save ho raha hai aur online aate hi sync ho jayega.' }}
        </p>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center mb-4">
            <button type="button" class="btn-theme justify-content-center" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Try Again
            </button>
            <a href="/admin/zikr" class="btn-outline justify-content-center">
                <i class="bi bi-gem"></i> Tasbeeh Counter
            </a>
        </div>

        <div class="pt-3 border-top border-secondary border-opacity-25 text-muted small" style="font-size: 0.8rem;">
            <span><i class="bi bi-shield-check text-success me-1"></i> Offline Mode Active</span>
            <span class="mx-2">•</span>
            <span>Auto Sync Ready</span>
        </div>
    </div>
</body>
</html>

