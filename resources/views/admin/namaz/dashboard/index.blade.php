@extends('layouts.admin')
@section('title', 'Namaz Dashboard')
@section('page_title', 'Namaz Attendance Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <span class="eyebrow">Islamic & Deen Analytics</span>
        <h2 class="h4 mb-1">Namaz Attendance Dashboard</h2>
        <p class="text-muted-custom mb-0">Prayer performance metrics, Jamat compliance rate, and Kaza distribution.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @can('view namaz attendance')
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.namaz.attendance.index', ['user_id' => $selectedUser?->id]) }}">
                <i class="bi bi-calendar-check me-1"></i>Open Attendance Table
            </a>
        @endcan
        @can('view namaz settings')
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.namaz.settings.index') }}">
                <i class="bi bi-clock me-1"></i>Namaz Settings
            </a>
        @endcan
    </div>
</div>

<section class="admin-card mb-4" data-ajax-crud data-refresh-target="#namaz-dashboard-results">
    <div class="admin-card-head">
        <div>
            <h2>
                @if($selectedUser)
                    {{ $selectedUser->name }} — Performance Analytics
                @else
                    All Muslim People — Collective Performance
                @endif
            </h2>
            <p class="text-muted-custom small mb-0 mt-1">
                Showing statistics from <strong>{{ date('d M, Y', strtotime($startDate)) }}</strong> to <strong>{{ date('d M, Y', strtotime($endDate)) }}</strong>
            </p>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="admin-list-toolbar">
        <form class="admin-search financial-filter d-flex flex-wrap gap-2 align-items-center w-100" method="GET" action="{{ route('admin.namaz.dashboard.index') }}" data-live-search data-live-search-target="#namaz-dashboard-results">
            <div style="min-width: 220px;">
                <select class="form-select filter-select" name="user_id" data-auto-submit>
                    <option value="">All Muslim People ({{ $stats['total_muslim_users'] }})</option>
                    @foreach($muslimUsers as $user)
                        <option value="{{ $user->id }}" @selected($selectedUser && $selectedUser->id === $user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex align-items-center gap-2">
                <input class="form-control filter-date" type="date" name="start_date" value="{{ $startDate }}" title="Start Date">
                <span class="text-muted-custom small">to</span>
                <input class="form-control filter-date" type="date" name="end_date" value="{{ $endDate }}" title="End Date">
            </div>

            <div class="d-flex flex-wrap gap-1 ms-md-auto">
                <a class="btn btn-sm {{ $filter === 'today' ? 'btn-accent' : 'btn-outline-theme' }}" href="{{ route('admin.namaz.dashboard.index', array_filter(['user_id' => $selectedUser?->id, 'filter' => 'today'])) }}">Today</a>
                <a class="btn btn-sm {{ $filter === 'week' ? 'btn-accent' : 'btn-outline-theme' }}" href="{{ route('admin.namaz.dashboard.index', array_filter(['user_id' => $selectedUser?->id, 'filter' => 'week'])) }}">This Week</a>
                <a class="btn btn-sm {{ $filter === 'month' ? 'btn-accent' : 'btn-outline-theme' }}" href="{{ route('admin.namaz.dashboard.index', array_filter(['user_id' => $selectedUser?->id, 'filter' => 'month'])) }}">This Month</a>
                <a class="btn btn-sm {{ $filter === 'all' ? 'btn-accent' : 'btn-outline-theme' }}" href="{{ route('admin.namaz.dashboard.index', array_filter(['user_id' => $selectedUser?->id, 'filter' => 'all'])) }}">All Time</a>
            </div>
        </form>
    </div>

    {{-- Dashboard Results Area --}}
    <div id="namaz-dashboard-results" class="admin-list-results">
        @if($stats['person_info'])
            <div class="p-3 mb-4 rounded-3 border" style="background: var(--surface-2); border-color: var(--line) !important;">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <span class="text-muted-custom small d-block">Muslim Person</span>
                        <h4 class="mb-0">{{ $stats['person_info']['name'] }}</h4>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted-custom small d-block">Namaz Start Date</span>
                        <strong>{{ $stats['person_info']['namaz_start_date'] ?: 'Not Started' }}</strong>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <span class="text-muted-custom small d-block">Total Tracked Days</span>
                        <strong>{{ number_format($stats['person_info']['total_tracked_days']) }} Days</strong>
                    </div>
                    <div class="col-md-2 text-md-end">
                        <span class="badge bg-success-subtle text-success fs-6">
                            {{ $stats['overall']['jamat_percentage'] }}% Jamat
                        </span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Overall Summary Stat Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-2 col-lg-4">
                <article class="admin-card stat-card h-100">
                    <div class="stat-icon purple"><i class="bi bi-moon-stars"></i></div>
                    <div>
                        <strong>{{ number_format($stats['overall']['total_namaz']) }}</strong>
                        <span>Total Namaz</span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-2 col-lg-4">
                <article class="admin-card stat-card h-100">
                    <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <strong class="text-success">{{ number_format($stats['overall']['jamat']) }}</strong>
                        <span>Total Jamat ({{ $stats['overall']['jamat_percentage'] }}%)</span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-2 col-lg-4">
                <article class="admin-card stat-card h-100">
                    <div class="stat-icon blue"><i class="bi bi-person-fill"></i></div>
                    <div>
                        <strong class="text-info">{{ number_format($stats['overall']['without_jamat']) }}</strong>
                        <span>Without Jamat</span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-2 col-lg-4">
                <article class="admin-card stat-card h-100">
                    <div class="stat-icon orange"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <strong class="text-warning">{{ number_format($stats['overall']['kaza']) }}</strong>
                        <span>Total Kaza</span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-2 col-lg-4">
                <article class="admin-card stat-card h-100">
                    <div class="stat-icon red"><i class="bi bi-x-circle-fill"></i></div>
                    <div>
                        <strong class="text-danger">{{ number_format($stats['overall']['absent']) }}</strong>
                        <span>Total Absent</span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-2 col-lg-4">
                <article class="admin-card stat-card h-100">
                    <div class="stat-icon" style="background: rgba(140,150,170,.14); color: var(--muted);"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <strong>{{ number_format($stats['overall']['pending']) }}</strong>
                        <span>Pending (Future)</span>
                    </div>
                </article>
            </div>
        </div>

        {{-- Per-Prayer Breakdown Section --}}
        <div class="mb-2">
            <h3 class="h5 mb-3">Per-Prayer Attendance Breakdown</h3>
        </div>

        <div class="row g-3">
            @foreach($prayers as $pKey)
                @php
                    $pData = $stats['per_prayer'][$pKey];
                @endphp
                <div class="col-md-6 col-xl-4">
                    <div class="p-3 rounded-3 border h-100" style="background: var(--surface-2); border-color: var(--line) !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="h6 mb-0 d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary">{{ $pData['label'] }}</span>
                            </h4>
                            <span class="badge bg-success-subtle text-success">
                                {{ $pData['jamat_percentage'] }}% Jamat
                            </span>
                        </div>

                        <div class="row g-2 text-center mb-3">
                            <div class="col-3">
                                <div class="p-2 rounded bg-surface border" style="border-color: var(--line) !important;">
                                    <small class="text-success d-block fw-bold">Jamat</small>
                                    <strong class="fs-6 text-success">{{ $pData['jamat'] }}</strong>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 rounded bg-surface border" style="border-color: var(--line) !important;">
                                    <small class="text-info d-block fw-bold">Alone</small>
                                    <strong class="fs-6 text-info">{{ $pData['without_jamat'] }}</strong>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 rounded bg-surface border" style="border-color: var(--line) !important;">
                                    <small class="text-warning d-block fw-bold">Kaza</small>
                                    <strong class="fs-6 text-warning">{{ $pData['kaza'] }}</strong>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="p-2 rounded bg-surface border" style="border-color: var(--line) !important;">
                                    <small class="text-danger d-block fw-bold">Absent</small>
                                    <strong class="fs-6 text-danger">{{ $pData['absent'] }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between text-muted-custom small">
                            <span>Total Completed: <strong>{{ $pData['total'] }}</strong></span>
                            @if($pData['pending'] > 0)
                                <span>Future Pending: <strong>{{ $pData['pending'] }}</strong></span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

