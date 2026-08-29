<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="auth-user-id" content="{{ auth()->id() }}">
    <title>@yield('title', 'Dashboard') - Amanullah</title>
    
    {{-- Progressive Web App (PWA) Meta & Icons --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="pwa-sw-url" content="{{ route('pwa.sw') }}">
    <meta name="pwa-status-url" content="{{ route('pwa.status') }}">
    <meta name="pwa-sync-push-url" content="{{ route('pwa.sync.push') }}">
    <meta name="pwa-sync-pull-url" content="{{ route('pwa.sync.pull') }}">
    <meta name="theme-color" content="{{ $pwaSettings?->theme_color ?? '#070d18' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $pwaSettings?->short_name ?? 'Amanullah' }}">
    <link rel="apple-touch-icon" href="{{ $pwaSettings?->icon_192_url ?? asset('assets/pwa-icons/icon-192x192.png') }}">
    
    <script>document.documentElement.dataset.theme=localStorage.getItem('portfolio-theme')||'dark';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/amanullah.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Amiri:ital,wght@0,400;0,700;1,400&family=Manrope:wght@400;500;600;700;800&family=Noto+Naskh+Arabic:wght@400;500;600;700&family=Noto+Nastaliq+Urdu:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Scheherazade+New:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}?v={{ file_exists(public_path('assets/css/app.css')) ? filemtime(public_path('assets/css/app.css')) : time() }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-head">
                <a class="brand-mark" href="{{ route('admin.dashboard') }}"><span class="brand-symbol">A</span><span>AMANULLAH<span class="brand-dot">.</span></span></a>
                <button class="sidebar-close d-lg-none" type="button" data-sidebar-close aria-label="Close sidebar"><i class="bi bi-x-lg"></i></button>
            </div>
            <nav class="sidebar-nav" aria-label="Admin navigation">
                <span class="sidebar-label">Workspace</span>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i><span>Dashboard</span></a>
                
                @php
                    $canSeeNamaz = auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->canAny(['view namaz attendance', 'namaz_attendance.view', 'view namaz dashboard', 'namaz_dashboard.view', 'view namaz settings', 'namaz_settings.view']);
                    $namazMenuOpen = request()->routeIs('admin.namaz.*');
                    $canSeeZikr = auth()->user()->isMuslim() || auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->canAny(['view zikr', 'zikr.view', 'manage tasbeeh', 'tasbeeh.manage']);
                    $zikrMenuOpen = request()->routeIs('admin.zikr.*') || request()->routeIs('admin.tasbeehs.*');
                @endphp

                @if($canSeeNamaz || $canSeeZikr)
                    <span class="sidebar-label mt-3">Islamic & Deen</span>
                @endif

                @if($canSeeNamaz)
                    <details class="sidebar-group" {{ $namazMenuOpen ? 'open' : '' }}>
                        <summary class="{{ $namazMenuOpen ? 'active' : '' }}"><i class="bi bi-moon-stars"></i><span>Namaz Attendance</span><i class="bi bi-chevron-down sidebar-chevron"></i></summary>
                        <div class="sidebar-subnav">
                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->canAny(['view namaz attendance', 'namaz_attendance.view']))
                                <a href="{{ route('admin.namaz.attendance.index') }}" class="{{ request()->routeIs('admin.namaz.attendance.*') ? 'active' : '' }}"><i class="bi bi-calendar-check"></i><span>Attendance</span></a>
                            @endif
                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->canAny(['view namaz dashboard', 'namaz_dashboard.view']))
                                <a href="{{ route('admin.namaz.dashboard.index') }}" class="{{ request()->routeIs('admin.namaz.dashboard.*') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
                            @endif
                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->canAny(['view namaz settings', 'namaz_settings.view']))
                                <a href="{{ route('admin.namaz.settings.index') }}" class="{{ request()->routeIs('admin.namaz.settings.*') ? 'active' : '' }}"><i class="bi bi-clock"></i><span>Namaz Settings</span></a>
                            @endif
                        </div>
                    </details>
                @endif

                @if($canSeeZikr)
                    <details class="sidebar-group" {{ $zikrMenuOpen ? 'open' : '' }}>
                        <summary class="{{ $zikrMenuOpen ? 'active' : '' }}"><i class="bi bi-gem"></i><span>Zikr / Tasbeeh</span><i class="bi bi-chevron-down sidebar-chevron"></i></summary>
                        <div class="sidebar-subnav">
                            <a href="{{ route('admin.zikr.index') }}" class="{{ request()->routeIs('admin.zikr.*') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Zikr Dashboard</span></a>
                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->canAny(['manage tasbeeh', 'tasbeeh.manage']))
                                <a href="{{ route('admin.tasbeehs.index') }}" class="{{ request()->routeIs('admin.tasbeehs.*') ? 'active' : '' }}"><i class="bi bi-card-checklist"></i><span>Manage Tasbeehs</span></a>
                            @endif
                        </div>
                    </details>
                @endif
                @can('view profile')<a href="{{ route('admin.profile.edit') }}" class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}"><i class="bi bi-person-badge"></i><span>Profile</span></a>@endcan
                @can('view project')<a href="{{ route('admin.content.index', 'projects') }}" class="{{ request()->is('admin/content/projects*') ? 'active' : '' }}"><i class="bi bi-kanban"></i><span>Projects</span></a>@endcan
                @can('view post')<a href="{{ route('admin.content.index', 'posts') }}" class="{{ request()->is('admin/content/posts*') ? 'active' : '' }}"><i class="bi bi-journal-richtext"></i><span>Blog Posts</span></a>@endcan
                @can('view service')<a href="{{ route('admin.content.index', 'services') }}" class="{{ request()->is('admin/content/services*') ? 'active' : '' }}"><i class="bi bi-grid"></i><span>Services</span></a>@endcan
                <span class="sidebar-label mt-3">Resume</span>
                @can('view experience')<a href="{{ route('admin.content.index', 'experiences') }}" class="{{ request()->is('admin/content/experiences*') ? 'active' : '' }}"><i class="bi bi-briefcase"></i><span>Experience</span></a>@endcan
                @can('view education')<a href="{{ route('admin.content.index', 'educations') }}" class="{{ request()->is('admin/content/educations*') ? 'active' : '' }}"><i class="bi bi-mortarboard"></i><span>Education</span></a>@endcan
                @can('view skill')<a href="{{ route('admin.content.index', 'skills') }}" class="{{ request()->is('admin/content/skills*') ? 'active' : '' }}"><i class="bi bi-bar-chart"></i><span>Skills</span></a>@endcan
                @can('view date of birth')
                    <a href="{{ route('admin.date-of-births.index') }}" class="{{ request()->routeIs('admin.date-of-births.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-heart"></i><span>Date of Birth</span>
                    </a>
                @endcan
                <span class="sidebar-label mt-3">Management</span>
                @can('view message')
                    <a href="{{ route('admin.messages.index') }}" class="{{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                        <i class="bi bi-envelope"></i><span>Messages</span>
                        @if($unreadMessageCount > 0)<span class="sidebar-badge">{{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}</span>@endif
                    </a>
                @endcan
                @can('view dashboard')
                    <a href="{{ route('admin.visitors.index') }}" class="{{ request()->routeIs('admin.visitors.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i><span>Visitors</span>
                    </a>
                @endcan
                @can('view user')<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="bi bi-people"></i><span>Users</span></a>@endcan
                @can('view role')<a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"><i class="bi bi-shield-lock"></i><span>Roles</span></a>@endcan
                @can('view permission')<a href="{{ route('admin.permissions.index') }}" class="{{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"><i class="bi bi-key"></i><span>Permissions</span></a>@endcan
                @if(auth()->user()->hasAnyRole(['Super Admin', 'admin']) || auth()->user()->can('view maintenance'))<a href="{{ route('admin.maintenance.index') }}" class="{{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}"><i class="bi bi-terminal"></i><span>Maintenance</span></a>@endif
                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->can('manage pwa settings'))
                    <a href="{{ route('admin.pwa.settings') }}" class="{{ request()->routeIs('admin.pwa.settings*') ? 'active' : '' }}">
                        <i class="bi bi-phone"></i><span>Mobile App Settings</span>
                    </a>
                @endif

                
                @php
                    $canSeeKhata = auth()->user()->hasAnyRole(['Super Admin', 'admin']) || auth()->user()->can('view khata');
                    $canSeeInvestors = auth()->user()->hasAnyRole(['Super Admin', 'admin']) || auth()->user()->canAny(['view investors', 'view investments', 'view profit sharing', 'view profit payments', 'view investment withdrawals', 'view investor reports']);
                    $canSeePrograms = auth()->user()->hasAnyRole(['Super Admin', 'admin']) || auth()->user()->canAny(['view programs', 'view contributions', 'view expense categories', 'view program expenses', 'view program transactions', 'view program reports']);
                    $investorMenuOpen = request()->routeIs('admin.investor-dashboard', 'admin.investors.*', 'admin.investments.*', 'admin.profit-sharing.*', 'admin.profit-payments.*', 'admin.investment-withdrawals.*', 'admin.investor-reports.*');
                    $programMenuOpen = request()->routeIs('admin.programs.*', 'admin.program-contributions.*', 'admin.expense-categories.*', 'admin.program-expenses.*', 'admin.program-transactions.*', 'admin.program-reports.*');
                @endphp

                @if($canSeeKhata || $canSeeInvestors || $canSeePrograms)
                    <span class="sidebar-label mt-3">Finance & Khata</span>
                @endif

                @if($canSeeKhata)
                    <a href="{{ route('admin.khata.index') }}" class="{{ request()->routeIs('admin.khata.*') ? 'active' : '' }}"><i class="bi bi-journal-bookmark-fill"></i><span>Khata System</span></a>
                @endif

                @if($canSeeInvestors)
                    <details class="sidebar-group" {{ $investorMenuOpen ? 'open' : '' }}>
                        <summary class="{{ $investorMenuOpen ? 'active' : '' }}"><i class="bi bi-pie-chart"></i><span>Investors & Profits</span><i class="bi bi-chevron-down sidebar-chevron"></i></summary>
                        <div class="sidebar-subnav">
                            @can('view investors')<a href="{{ route('admin.investor-dashboard') }}" class="{{ request()->routeIs('admin.investor-dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>@endcan
                            @can('view investors')<a href="{{ route('admin.investors.index') }}" class="{{ request()->routeIs('admin.investors.*') ? 'active' : '' }}"><i class="bi bi-people"></i><span>Investors</span></a>@endcan
                            @can('view investments')<a href="{{ route('admin.investments.index') }}" class="{{ request()->routeIs('admin.investments.*') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i><span>Investments</span></a>@endcan
                            @can('view profit sharing')<a href="{{ route('admin.profit-sharing.index') }}" class="{{ request()->routeIs('admin.profit-sharing.*') ? 'active' : '' }}"><i class="bi bi-calculator"></i><span>Profit Sharing</span></a>@endcan
                            @can('view profit payments')<a href="{{ route('admin.profit-payments.index') }}" class="{{ request()->routeIs('admin.profit-payments.*') ? 'active' : '' }}"><i class="bi bi-wallet2"></i><span>Profit Payments</span></a>@endcan
                            @can('view investment withdrawals')<a href="{{ route('admin.investment-withdrawals.index') }}" class="{{ request()->routeIs('admin.investment-withdrawals.*') ? 'active' : '' }}"><i class="bi bi-arrow-up-right-circle"></i><span>Withdrawals</span></a>@endcan
                            @can('view investor reports')<a href="{{ route('admin.investor-reports.index') }}" class="{{ request()->routeIs('admin.investor-reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span></a>@endcan
                        </div>
                    </details>
                @endif

                @if($canSeePrograms)
                    <details class="sidebar-group" {{ $programMenuOpen ? 'open' : '' }}>
                        <summary class="{{ $programMenuOpen ? 'active' : '' }}"><i class="bi bi-calendar2-event"></i><span>Programs</span><i class="bi bi-chevron-down sidebar-chevron"></i></summary>
                        <div class="sidebar-subnav">
                            @can('view programs')<a href="{{ route('admin.programs.index') }}" class="{{ request()->routeIs('admin.programs.*') ? 'active' : '' }}"><i class="bi bi-card-checklist"></i><span>Programs</span></a>@endcan
                            @can('view contributions')<a href="{{ route('admin.program-contributions.index') }}" class="{{ request()->routeIs('admin.program-contributions.*') ? 'active' : '' }}"><i class="bi bi-wallet2"></i><span>Income</span></a>@endcan
                            @can('view program expenses')<a href="{{ route('admin.program-expenses.index') }}" class="{{ request()->routeIs('admin.program-expenses.*') ? 'active' : '' }}"><i class="bi bi-receipt"></i><span>Expenses</span></a>@endcan
                            @can('view expense categories')<a href="{{ route('admin.expense-categories.index') }}" class="{{ request()->routeIs('admin.expense-categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Expense Categories</span></a>@endcan
                            @can('view program transactions')<a href="{{ route('admin.program-transactions.index') }}" class="{{ request()->routeIs('admin.program-transactions.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i><span>Transactions</span></a>@endcan
                            @can('view program reports')<a href="{{ route('admin.program-reports.index') }}" class="{{ request()->routeIs('admin.program-reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i><span>Reports</span></a>@endcan
                        </div>
                    </details>
                @endif

                
            </nav>
            <div class="sidebar-user">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="min-w-0"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->roles->pluck('name')->join(', ') }}</small></div>
            </div>
        </aside>
        <div class="sidebar-overlay" data-sidebar-close></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="d-flex align-items-center gap-2 gap-sm-3 min-w-0">
                    <button class="topbar-icon d-lg-none flex-shrink-0" type="button" data-sidebar-open aria-label="Open sidebar"><i class="bi bi-list"></i></button>
                    <div class="min-w-0">
                        <h1 class="topbar-title text-nowrap text-truncate mb-0">@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>
                <div class="topbar-actions">
                    {{-- PWA Sync Status Badge --}}
                    <button type="button" class="pwa-sync-badge badge-online d-none d-sm-inline-flex" data-pwa-sync-badge data-pwa-sync-now title="Click to Sync Now">
                        <i class="bi bi-wifi me-1"></i><span>Online</span>
                    </button>

                    {{-- PWA Install Button (Responsive topbar icon) --}}
                    <button type="button" class="topbar-icon" data-pwa-install-btn title="Download / Install Mobile App" aria-label="Download / Install Mobile App">
                        <i class="bi bi-download"></i>
                    </button>

                    <a class="topbar-icon" href="{{ route('home') }}" target="_blank" title="View website" aria-label="View website"><i class="bi bi-box-arrow-up-right"></i></a>
                    @can('view message')
                        <a class="topbar-icon notification-link" href="{{ route('admin.messages.index', ['filter' => 'unread']) }}" title="Unread messages" aria-label="Unread messages">
                            <i class="bi bi-bell"></i>
                            @if($unreadMessageCount > 0)<span>{{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}</span>@endif
                        </a>
                    @endcan
                    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch colour theme" title="Day / night mode">
                        <i class="bi bi-sun-fill theme-icon-light"></i><i class="bi bi-moon-stars-fill theme-icon-dark"></i>
                    </button>
                    <div class="dropdown">
                        <button class="topbar-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span><strong class="d-none d-sm-inline">{{ auth()->user()->name }}</strong></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i>Edit profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button></form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            <main class="admin-content">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Mobile Bottom Navigation Bar (PWA Mobile View) --}}
    <nav class="pwa-bottom-nav d-lg-none" aria-label="Mobile Navigation">
        <a href="{{ route('admin.dashboard') }}" class="pwa-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.zikr.index') }}" class="pwa-nav-item {{ request()->routeIs('admin.zikr.*') ? 'active' : '' }}">
            <i class="bi bi-gem"></i>
            <span>Zikr</span>
        </a>
        @if(auth()->user()->hasAnyRole(['Super Admin', 'admin']) || auth()->user()->can('view khata'))
            <a href="{{ route('admin.khata.index') }}" class="pwa-nav-item {{ request()->routeIs('admin.khata.*') ? 'active' : '' }}">
                <i class="bi bi-journal-bookmark"></i>
                <span>Khata</span>
            </a>
        @endif
        @if(auth()->user()->hasAnyRole(['Super Admin', 'admin']) || auth()->user()->can('view programs'))
            <a href="{{ route('admin.programs.index') }}" class="pwa-nav-item {{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-event"></i>
                <span>Programs</span>
            </a>
        @endif
        <button type="button" class="pwa-nav-item pwa-nav-sync" data-pwa-sync-now title="Sync Data">
            <span class="pwa-sync-badge badge-online" data-pwa-sync-badge><i class="bi bi-wifi"></i></span>
            <span>Sync</span>
        </button>
    </nav>

    {{-- iOS Safari PWA Installation Instructions Modal --}}
    @include('admin.pwa.partials.ios-modal')

    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}?v={{ file_exists(public_path('assets/js/app.js')) ? filemtime(public_path('assets/js/app.js')) : time() }}"></script>
    
    {{-- Progressive Web App (PWA) Core Scripts --}}
    <script src="{{ asset('assets/js/pwa/pwa-db.js') }}?v={{ file_exists(public_path('assets/js/pwa/pwa-db.js')) ? filemtime(public_path('assets/js/pwa/pwa-db.js')) : time() }}"></script>
    <script src="{{ asset('assets/js/pwa/pwa-sync.js') }}?v={{ file_exists(public_path('assets/js/pwa/pwa-sync.js')) ? filemtime(public_path('assets/js/pwa/pwa-sync.js')) : time() }}"></script>
    <script src="{{ asset('assets/js/pwa/pwa-installer.js') }}?v={{ file_exists(public_path('assets/js/pwa/pwa-installer.js')) ? filemtime(public_path('assets/js/pwa/pwa-installer.js')) : time() }}"></script>
    @stack('scripts')
</body>
</html>
