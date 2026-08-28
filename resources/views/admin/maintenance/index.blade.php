@extends('layouts.admin')
@section('title', 'System Maintenance & Artisan Commands')
@section('page_title', 'Maintenance')
@section('content')
<div class="d-flex flex-column gap-4">
    {{-- System Status Banner --}}
    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2><i class="bi bi-terminal me-2 text-accent"></i>System Diagnostics & Maintenance</h2>
                <p class="text-muted-custom small mb-0 mt-1">Execute safe Artisan management commands and clear framework caches directly from the admin dashboard.</p>
            </div>
            <div class="responsive-actions">
                @can('run maintenance')
                    <form method="POST" action="{{ route('admin.maintenance.run') }}" data-confirm="Are you sure you want to run optimize:clear to reset all application caches?">
                        @csrf
                        <input type="hidden" name="command" value="optimize_clear">
                        <button type="submit" class="btn btn-accent btn-sm">
                            <i class="bi bi-stars me-1"></i>Clear All Caches (Optimize)
                        </button>
                    </form>
                @endcan
            </div>
        </div>
        <div class="admin-card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card p-3 rounded-3 border">
                        <span class="text-muted-custom small d-block">PHP Version</span>
                        <strong class="fs-6">{{ $systemInfo['php_version'] }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card p-3 rounded-3 border">
                        <span class="text-muted-custom small d-block">Laravel</span>
                        <strong class="fs-6">v{{ $systemInfo['laravel_version'] }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card p-3 rounded-3 border">
                        <span class="text-muted-custom small d-block">Environment</span>
                        <strong class="fs-6 text-capitalize">{{ $systemInfo['app_env'] }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card p-3 rounded-3 border">
                        <span class="text-muted-custom small d-block">Debug Mode</span>
                        <strong class="fs-6">{{ $systemInfo['app_debug'] }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card p-3 rounded-3 border">
                        <span class="text-muted-custom small d-block">Database</span>
                        <strong class="fs-6 text-uppercase">{{ $systemInfo['db_connection'] }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="stat-card p-3 rounded-3 border">
                        <span class="text-muted-custom small d-block">Cache Driver</span>
                        <strong class="fs-6 text-capitalize">{{ $systemInfo['cache_driver'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Console / Terminal Output if a command was just executed --}}
    @if(session('artisan_output'))
        <section class="admin-card border-accent">
            <div class="admin-card-head bg-dark text-light border-bottom border-secondary d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-accent px-2 py-1">Terminal Output</span>
                    <code class="text-light fs-6"># {{ session('artisan_command') }}</code>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="navigator.clipboard.writeText(document.getElementById('terminalOutputText').innerText); this.innerHTML='<i class=\'bi bi-check2\'></i> Copied'; setTimeout(() => this.innerHTML='<i class=\'bi bi-clipboard\'></i> Copy', 2000);">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
            <div class="admin-card-body p-0">
                <pre class="m-0 p-3 bg-dark text-light font-monospace rounded-bottom" id="terminalOutputText" style="white-space: pre-wrap; word-break: break-word; max-height: 320px; overflow-y: auto; font-size: .85rem; line-height: 1.6;">{{ session('artisan_output') }}</pre>
            </div>
        </section>
    @endif

    {{-- Artisan Commands Grid --}}
    <div class="row g-4">
        @foreach($commands as $key => $item)
            <div class="col-md-6 col-xl-4">
                <div class="admin-card h-100 d-flex flex-column justify-content-between maintenance-card">
                    <div class="admin-card-body pb-2">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="maintenance-cmd-icon {{ $item['color'] }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <h3 class="fs-6 fw-bold mb-0 text-truncate">{{ $item['name'] }}</h3>
                                    <span class="maintenance-pill">{{ $item['badge'] }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted-custom small mb-3">{{ $item['description'] }}</p>
                        <div class="maintenance-code-box mb-3">
                            <code>{{ $item['command'] }}</code>
                        </div>
                    </div>
                    <div class="admin-card-head pt-0 border-0">
                        @can('run maintenance')
                            <form method="POST" action="{{ route('admin.maintenance.run') }}" class="w-100" data-confirm="Run '{{ $item['command'] }}' now?">
                                @csrf
                                <input type="hidden" name="command" value="{{ $key }}">
                                <button type="submit" class="btn btn-outline-theme btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-play-fill fs-6"></i> Run Command
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-outline-theme btn-sm w-100 disabled" disabled>
                                <i class="bi bi-lock me-1"></i> Permission Required
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
