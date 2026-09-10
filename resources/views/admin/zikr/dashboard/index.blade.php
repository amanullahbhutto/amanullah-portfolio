@extends('layouts.admin')
@section('title', 'Zikr & Tasbeeh')
@section('page_title', 'Daily Zikr Tracking')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between gap-3 mb-4">
    {{-- Active Zikr Journey / Duration Tracker (Days, Months, Years) --}}
    <div class="d-flex align-items-center justify-content-center">
        @if($selectedUser && isset($summary['journey_duration']))
            <div class="zikr-journey-pill d-inline-flex align-items-center px-3 py-2 rounded-4 border mx-auto mx-md-0" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.12) 0%, rgba(8, 17, 30, 0.95) 100%); border-color: rgba(6, 182, 212, 0.35); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);">
                <div class="d-flex align-items-center justify-content-center me-2 rounded-circle flex-shrink-0" style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.2); color: #06b6d4; font-size: 1rem;">
                    <i class="bi bi-calendar2-range"></i>
                </div>
                <div class="d-flex flex-column justify-content-center" style="line-height: 1.25;">
                    <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-sm-start">
                        <span class="small fw-bold text-uppercase" style="color: #38bdf8; font-size: 0.68rem; letter-spacing: 0.5px;">
                            Zikr Journey:
                        </span>
                        <strong class="text-white font-monospace" style="font-size: 0.85rem;" id="top-journey-duration-text">
                            {{ $summary['journey_duration']['formatted_full'] }}
                        </strong>
                        <span class="badge rounded-pill text-info font-monospace ms-1" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.3); font-size: 0.65rem; padding: 2px 7px;" id="top-journey-total-days-badge">
                            {{ $summary['journey_duration']['total_days'] }} {{ $summary['journey_duration']['total_days'] === 1 ? 'Day' : 'Days' }} Total
                        </span>
                    </div>
                    <small class="text-muted-custom" style="font-size: 0.68rem;" id="top-journey-start-text">
                        Started: <span style="color: #cbd5e1;">{{ $summary['journey_duration']['start_date_formatted'] }}</span>
                    </small>
                </div>
            </div>
        @endif
    </div>

    {{-- Top Action Buttons --}}
    <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
        {{-- Reset All Tasbeehs Trigger (Icon 1) --}}
        <button class="action-btn-top danger" type="button" data-bs-toggle="modal" data-bs-target="#resetAllTasbeehsModal" title="Reset All Tasbeehs to 0 (Start Date Today)">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>

        {{-- Mark All Complete for Today Trigger (Icon 2) --}}
        <button class="action-btn-top green" type="button" data-bs-toggle="modal" data-bs-target="#completeAllTasbeehsModal" title="Mark All Tasbeehs Complete for Today">
            <i class="bi bi-check2-all"></i>
        </button>

        {{-- Global Stats Visibility Eye Trigger --}}
        <button class="action-btn-top purple" type="button" id="toggleAllStatsEyeBtn" title="Toggle Stats Visibility (Show/Hide Numbers)">
            <i class="bi bi-eye" id="toggleAllStatsEyeIcon"></i>
        </button>

        {{-- Display Settings Modal Trigger --}}
        <button class="action-btn-top" type="button" data-bs-toggle="modal" data-bs-target="#zikrSettingsModal" title="Display Settings (Font Size & Visibility)">
            <i class="bi bi-gear-fill"></i>
        </button>

        @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->can('manage tasbeeh'))
            {{-- Manage Tasbeehs --}}
            <a class="action-btn-top cyan" href="{{ route('admin.tasbeehs.index') }}" title="Manage Tasbeeh Master Definitions">
                <i class="bi bi-card-checklist"></i>
            </a>
        @endif
    </div>
</div>

@if(! $selectedUser)
    <div class="admin-card p-5 text-center text-muted-custom">
        <i class="bi bi-people fs-1 text-accent"></i>
        <h5 class="mt-3 mb-1">No Muslim Users Found</h5>
        <p class="mb-0">Please assign the <strong>Muslim</strong> role to a user in User Management to track Zikr & Tasbeeh.</p>
    </div>
@else

    {{-- Top Overall Statistics Cards (2 cards per row on mobile, 3 on tablet, 6 on desktop) --}}
    <div class="row g-2 g-md-3 mb-4" id="zikr-top-stat-cards">
        {{-- Lifetime All-Time Total Card --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 position-relative d-flex flex-column justify-content-between" data-stat-card="lifetime" style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.14) 0%, #08111e 100%); border-color: rgba(249, 115, 22, 0.4); min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="small fw-bold text-uppercase text-truncate" style="color: #f97316; font-size: 0.7rem;">
                        <i class="bi bi-infinity me-1"></i>Lifetime Total
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-link p-0 text-secondary zikr-stat-eye-btn" data-stat-target="lifetime" title="Show/Hide Lifetime Total" style="line-height: 1; font-size: 0.82rem; color: #94a3b8 !important;" onmouseover="this.style.color='#f97316'" onmouseout="this.style.color='#94a3b8'">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-link p-0 text-secondary" data-bs-toggle="modal" data-bs-target="#resetLifetimeModal" title="Reset Lifetime Total Counter" style="line-height: 1; font-size: 0.8rem; color: #94a3b8 !important;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
                <strong class="fs-3 fs-md-2 text-white d-block font-monospace my-0 zikr-stat-maskable" id="top-stat-lifetime-total" data-raw-val="{{ number_format($summary['lifetime_total']) }}" data-base-lifetime="{{ (int) $summary['lifetime_total'] }}" data-stat-card="lifetime" style="color: #f97316 !important; line-height: 1.2;" title="Click to show/hide">{{ number_format($summary['lifetime_total']) }}</strong>
                <small class="d-block text-truncate zikr-stat-maskable" id="top-stat-lifetime-duration" data-raw-subtext="<i class='bi bi-clock-history me-1'></i>{{ $summary['lifetime_duration']['formatted_full'] ?? 'Day 1' }}" data-masked-subtext="<i class='bi bi-clock-history me-1'></i>••••" data-stat-card="lifetime" style="font-size: 0.72rem; color: #fdba74;" title="Started: {{ $summary['lifetime_duration']['start_date_formatted'] ?? 'Today' }}">
                    <i class="bi bi-clock-history me-1"></i>{{ $summary['lifetime_duration']['formatted_full'] ?? 'Day 1' }}
                </small>
            </div>
        </div>

        {{-- Daily Target --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" data-stat-card="daily_target" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Daily Target</span>
                    <button type="button" class="btn btn-link p-0 text-secondary zikr-stat-eye-btn" data-stat-target="daily_target" title="Show/Hide Daily Target" style="line-height: 1; font-size: 0.82rem; color: #94a3b8 !important;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <strong class="fs-3 fs-md-2 text-white d-block font-monospace my-0 zikr-stat-maskable" id="top-stat-daily-target" data-raw-val="{{ number_format($summary['overall_today_required']) }}" data-stat-card="daily_target" style="line-height: 1.2;" title="Click to show/hide">{{ number_format($summary['overall_today_required']) }}</strong>
                <small class="text-muted-custom d-block text-truncate zikr-stat-maskable" id="top-stat-daily-subtext" data-raw-subtext="{{ $summary['total_active_tasbeehs'] }} Tasbeehs" data-masked-subtext="•••• Tasbeehs" data-stat-card="daily_target" style="font-size: 0.72rem;">{{ $summary['total_active_tasbeehs'] }} Tasbeehs</small>
            </div>
        </div>

        {{-- Total Read Today --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" data-stat-card="read_today" style="background: #08111e; border-color: rgba(16, 185, 129, 0.35); min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="small fw-bold text-uppercase d-block text-truncate" style="color: #10b981; font-size: 0.7rem;">
                        <i class="bi bi-calendar-check me-1"></i>Read Today
                    </span>
                    <button type="button" class="btn btn-link p-0 text-secondary zikr-stat-eye-btn" data-stat-target="read_today" title="Show/Hide Read Today" style="line-height: 1; font-size: 0.82rem; color: #94a3b8 !important;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <strong class="fs-3 fs-md-2 d-block font-monospace my-0 zikr-stat-maskable" id="top-stat-today-completed" data-raw-val="{{ number_format($summary['overall_today_completed']) }}" data-stat-card="read_today" style="color: #10b981 !important; line-height: 1.2;" title="Click to show/hide">{{ number_format($summary['overall_today_completed']) }}</strong>
                <small class="text-muted-custom d-block text-truncate zikr-stat-maskable" id="top-stat-today-percentage" data-raw-subtext="{{ $summary['overall_today_percentage'] }}% of daily target" data-masked-subtext="•••% of daily target" data-stat-card="read_today" style="font-size: 0.72rem;">{{ $summary['overall_today_percentage'] }}% of daily target</small>
            </div>
        </div>

        {{-- Total Required --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" data-stat-card="total_required" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Total Required</span>
                    <button type="button" class="btn btn-link p-0 text-secondary zikr-stat-eye-btn" data-stat-target="total_required" title="Show/Hide Total Required" style="line-height: 1; font-size: 0.82rem; color: #94a3b8 !important;" onmouseover="this.style.color='#06b6d4'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <strong class="fs-3 fs-md-2 text-info d-block font-monospace my-0 zikr-stat-maskable" id="top-stat-total-required" data-raw-val="{{ number_format($summary['overall_total_required']) }}" data-stat-card="total_required" style="line-height: 1.2;" title="Click to show/hide">{{ number_format($summary['overall_total_required']) }}</strong>
                <small class="text-muted-custom d-block text-truncate zikr-stat-maskable" id="top-stat-required-subtext" data-raw-subtext="Active cycle till today" data-masked-subtext="Active cycle till today" data-stat-card="total_required" style="font-size: 0.72rem;">Active cycle till today</small>
            </div>
        </div>

        {{-- Total Completed --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" data-stat-card="total_completed" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Total Completed</span>
                    <button type="button" class="btn btn-link p-0 text-secondary zikr-stat-eye-btn" data-stat-target="total_completed" title="Show/Hide Total Completed" style="line-height: 1; font-size: 0.82rem; color: #94a3b8 !important;" onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <strong class="fs-3 fs-md-2 text-success d-block font-monospace my-0 zikr-stat-maskable" id="top-stat-total-completed" data-raw-val="{{ number_format($summary['overall_total_completed']) }}" data-stat-card="total_completed" style="line-height: 1.2;" title="Click to show/hide">{{ number_format($summary['overall_total_completed']) }}</strong>
                <small class="text-success d-block fw-semibold text-truncate zikr-stat-maskable" id="top-stat-overall-percentage" data-raw-subtext="{{ $summary['overall_percentage'] }}% Completed" data-masked-subtext="•••% Completed" data-stat-card="total_completed" style="font-size: 0.72rem;">{{ $summary['overall_percentage'] }}% Completed</small>
            </div>
        </div>

        {{-- Extra Zikr / Backlog --}}
        <div class="col-6 col-md-4 col-xl-2">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" id="top-stat-backlog-container" data-stat-card="backlog" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" id="top-stat-backlog-title" style="font-size: 0.7rem;">
                        {{ $summary['overall_extra'] > 0 ? 'Extra Zikr' : 'Remaining Backlog' }}
                    </span>
                    <button type="button" class="btn btn-link p-0 text-secondary zikr-stat-eye-btn" data-stat-target="backlog" title="Show/Hide Backlog" style="line-height: 1; font-size: 0.82rem; color: #94a3b8 !important;" onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @php
                    $isExtra = $summary['overall_extra'] > 0;
                    $isBehind = $summary['overall_backlog'] > 0;
                    $colorClass = $isExtra ? 'text-info' : ($isBehind ? 'text-warning' : 'text-success');
                    $valText = $isExtra ? ('+' . number_format($summary['overall_extra'])) : number_format($summary['overall_backlog']);
                    $subText = $isExtra ? 'Ahead of schedule' : ($isBehind ? 'Behind schedule' : 'On track');
                @endphp
                <strong class="fs-3 fs-md-2 {{ $colorClass }} d-block font-monospace my-0 zikr-stat-maskable" id="top-stat-backlog-value" data-raw-val="{{ $valText }}" data-stat-card="backlog" style="line-height: 1.2;" title="Click to show/hide">
                    {{ $valText }}
                </strong>
                <small class="{{ $colorClass }} d-block fw-semibold text-truncate zikr-stat-maskable" id="top-stat-backlog-subtext" data-raw-subtext="{{ $subText }}" data-masked-subtext="{{ $subText }}" data-stat-card="backlog" style="font-size: 0.72rem;">
                    {{ $subText }}
                </small>
            </div>
        </div>
    </div>

    {{-- Tasbeeh Items Grid --}}
    <div class="row g-3 g-md-4" id="tasbeeh-list-container">
        @forelse($summary['tasbeehs'] as $item)
            <div class="col-12 col-lg-6 d-flex" id="tasbeeh-card-{{ $item['tasbeeh_id'] }}" data-daily-target="{{ $item['daily_target'] }}" data-active-days="{{ $item['active_days'] }}" data-today-completed="{{ $item['today_completed'] }}" data-total-completed="{{ $item['total_completed'] }}" data-total-required="{{ $item['total_required'] }}" data-tracking-start-date="{{ $item['tracking_start_date'] ?? '' }}" data-render-date="{{ now()->format('Y-m-d') }}">
                <div class="zikr-item-card w-100 d-flex flex-column justify-content-between position-relative">
                    <div>
                        {{-- Top Header with Sort Number Circle Badge and Today's Count Badge --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="zikr-card-seq-badge" title="Tasbeeh {{ ($item['sort_order'] ?? 0) > 0 ? $item['sort_order'] : $loop->iteration }}">
                                <span class="seq-number">{{ ($item['sort_order'] ?? 0) > 0 ? $item['sort_order'] : $loop->iteration }}</span>
                            </div>
                            
                            {{-- Small Compact Today Count Badge --}}
                            @if($item['today_completed'] >= $item['daily_target'] && $item['daily_target'] > 0)
                                <span class="badge today-status-badge rounded-pill d-inline-flex align-items-center px-2 py-0.5" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-size: 0.72rem; font-weight: 600;">
                                    <i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">{{ number_format($item['today_completed']) }}</strong>
                                </span>
                            @elseif($item['today_completed'] > 0)
                                <span class="badge today-status-badge rounded-pill d-inline-flex align-items-center px-2 py-0.5" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #38bdf8; font-size: 0.72rem; font-weight: 600;">
                                    Today: <strong class="ms-1 font-monospace">{{ number_format($item['today_completed']) }}</strong>
                                </span>
                            @else
                                <span class="badge today-status-badge rounded-pill d-inline-flex align-items-center px-2 py-0.5" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; font-size: 0.72rem; font-weight: 600;">
                                    Today: <strong class="ms-1 font-monospace">0</strong>
                                </span>
                            @endif
                        </div>

                        {{-- Target & Started Meta --}}
                        <div class="d-flex align-items-center flex-wrap gap-1 mb-2 text-muted-custom small" style="font-size: 0.8rem;">
                            <span>Target: <strong class="text-info font-monospace">{{ number_format($item['daily_target']) }}</strong>/day</span>
                            <span class="mx-1 opacity-50">•</span>
                            <span>Started: <strong class="text-info">{{ $item['formatted_start_date'] }}</strong></span>
                        </div>

                        {{-- Right-Aligned Text Area with Custom Center Divider --}}
                        <div class="text-container">
                            <!-- Arabic Text -->
                            <div class="arabic-text">
                                {{ $item['arabic_text'] }}
                            </div>

                            <!-- Sleek Glowing Islamic Center Divider -->
                            <div class="islamic-divider">
                                <div class="divider-icon">✦ ✧ ✦</div>
                            </div>

                            <!-- Urdu Translation -->
                            <div class="urdu-text">
                                {{ $item['urdu_meaning'] ?? '—' }}
                            </div>
                        </div>

                        {{-- Cycle Progress Bar --}}
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1 text-muted-custom" style="font-size: 0.76rem;">
                                <span><i class="bi bi-bar-chart-fill me-1 text-secondary"></i> Cycle Progress</span>
                                <span class="fw-bold text-white font-monospace">{{ $item['percentage'] }}%</span>
                            </div>
                            <div class="progress-container">
                                @php
                                    $barClass = 'amber';
                                    if ($item['extra'] > 0) {
                                        $barClass = 'cyan';
                                    } elseif ($item['remaining'] === 0) {
                                        $barClass = 'emerald';
                                    }
                                @endphp
                                <div class="progress-bar-custom {{ $barClass }}" style="width: {{ $item['percentage'] }}%;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Footer Strip with Badges & Action Icons --}}
                    <div class="card-footer-strip mt-2 pt-2 border-top border-secondary border-opacity-10">
                        {{-- Badges Group --}}
                        <div class="badge-info-group">
                            @if($item['extra'] > 0)
                                <span class="badge-remaining extra">+{{ number_format($item['extra']) }} Extra</span>
                            @elseif($item['remaining'] === 0)
                                <span class="badge-remaining completed-badge">Completed</span>
                            @else
                                <span class="badge-remaining">Remaining {{ number_format($item['remaining']) }}</span>
                            @endif
                            <span class="badge-completed">Completed: <strong class="text-white">{{ number_format($item['total_completed']) }}</strong> / {{ number_format($item['total_required']) }}</span>
                        </div>

                        {{-- Action Icons Group --}}
                        <div class="badge-actions-group">
                            {{-- Mark This Tasbeeh Complete for Today (Direct 1-Click Complete) --}}
                            @php
                                $isItemDoneToday = (($item['today_completed'] ?? 0) >= ($item['daily_target'] ?? 100) && ($item['daily_target'] ?? 100) > 0);
                            @endphp
                            <button
                                class="action-icon-btn btn-complete-icon {{ $isItemDoneToday ? 'is-completed active' : '' }}"
                                type="button"
                                data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                                data-tasbeeh-title="{{ $item['title'] }}"
                                data-user-id="{{ $selectedUser->id }}"
                                data-complete-url="{{ route('admin.zikr.counter.complete-today', $item['tasbeeh_id']) }}"
                                title="Complete Today for this Tasbeeh"
                            >
                                <i class="bi bi-check2-all"></i>
                            </button>

                            {{-- Quick Add Count Modal Trigger --}}
                            <button
                                class="action-icon-btn btn-plus-icon"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#quickAddModal"
                                data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                                data-tasbeeh-title="{{ $item['title'] }}"
                                data-user-id="{{ $selectedUser->id }}"
                                data-post-url="{{ route('admin.zikr.counter.manual', $item['tasbeeh_id']) }}"
                                title="Quick Add Count"
                            >
                                <i class="bi bi-plus-lg"></i>
                            </button>

                            {{-- Open Counter Screen --}}
                            <a href="{{ route('admin.zikr.counter.show', ['tasbeeh' => $item['tasbeeh_id'], 'user_id' => $selectedUser->id]) }}" class="action-icon-btn btn-speed-icon" title="Open Counter">
                                <i class="bi bi-speedometer2"></i>
                            </a>

                            {{-- View Description & Complete Details (Click icon to view all details) --}}
                            <button
                                class="action-icon-btn btn-desc-icon"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#tasbeehDescModal"
                                data-id="{{ $item['tasbeeh_id'] }}"
                                data-title="{{ $item['title'] }}"
                                data-arabic="{{ $item['arabic_text'] }}"
                                data-urdu="{{ $item['urdu_meaning'] }}"
                                data-desc="{{ $item['description'] ?? '' }}"
                                data-ref="{{ $item['reference'] ?? '' }}"
                                data-target="{{ $item['daily_target'] }}"
                                data-order="{{ ($item['sort_order'] ?? 0) > 0 ? $item['sort_order'] : $loop->iteration }}"
                                data-active="{{ ($item['is_active'] ?? true) ? '1' : '0' }}"
                                data-today-completed="{{ $item['today_completed'] ?? 0 }}"
                                data-total-completed="{{ $item['total_completed'] ?? 0 }}"
                                data-total-required="{{ $item['total_required'] ?? $item['daily_target'] }}"
                                data-percentage="{{ $item['percentage'] ?? 0 }}"
                                data-remaining="{{ $item['remaining'] ?? 0 }}"
                                data-extra="{{ $item['extra'] ?? 0 }}"
                                data-active-days="{{ $item['active_days'] ?? 1 }}"
                                data-started="{{ $item['formatted_start_date'] ?? '—' }}"
                                data-last-zikr="{{ $item['formatted_last_zikr'] ?? '—' }}"
                                data-counter-url="{{ route('admin.zikr.counter.show', ['tasbeeh' => $item['tasbeeh_id'], 'user_id' => $selectedUser->id]) }}"
                                title="View Complete Details & Description"
                            >
                                <i class="bi bi-eye-fill"></i>
                            </button>

                            {{-- Reset Tracking Cycle --}}
                            <button
                                class="action-icon-btn btn-reset-icon"
                                type="button"
                                data-bs-toggle="modal"
                                data-bs-target="#resetTasbeehModal"
                                data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                                data-tasbeeh-title="{{ $item['title'] }}"
                                data-user-id="{{ $selectedUser->id }}"
                                data-reset-url="{{ route('admin.zikr.counter.reset', $item['tasbeeh_id']) }}"
                                title="Reset Tracking Cycle"
                            >
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted-custom">
                <i class="bi bi-gem fs-1 text-accent"></i>
                <h5 class="mt-3 mb-1">No Active Tasbeehs Found</h5>
                <p class="mb-0">Please add or activate Tasbeeh in Manage Tasbeehs.</p>
            </div>
        @endforelse
    </div>
@endif

{{-- Quick Add Count Modal --}}
<div class="modal fade finance-modal" id="quickAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="quickAddForm" method="POST" action="">
                @csrf
                <input type="hidden" name="user_id" id="quickAddUserId">
                <div class="modal-header border-secondary border-opacity-25">
                    <div>
                        <h5 class="modal-title mb-0 text-white">Add Zikr Count</h5>
                        <p class="text-muted-custom small mb-0 mt-1" id="quickAddTasbeehTitle">Tasbeeh</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Quick Preset Buttons --}}
                    <label class="form-label small text-muted-custom fw-bold">Quick Presets:</label>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill" onclick="document.getElementById('quickAddCountInput').value=33">+33</button>
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill" onclick="document.getElementById('quickAddCountInput').value=100">+100</button>
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill" onclick="document.getElementById('quickAddCountInput').value=300">+300</button>
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill" onclick="document.getElementById('quickAddCountInput').value=1000">+1,000</button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-white">Enter Count <span class="text-danger">*</span></label>
                        <input class="form-control form-control-lg text-center font-monospace fw-bold" type="number" inputmode="numeric" name="count" id="quickAddCountInput" min="-2000000000" max="2000000000" placeholder="e.g. 100 or -33" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" id="quickAddSubmitBtn">
                        <i class="bi bi-check-lg me-1"></i>Add Zikr
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset Tracking Confirmation Modal --}}
<div class="modal fade finance-modal" id="resetTasbeehModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="resetTasbeehForm" method="POST" action="">
                @csrf
                <input type="hidden" name="user_id" id="resetTasbeehUserId">
                <div class="modal-header border-secondary border-opacity-25">
                    <div>
                        <h5 class="modal-title text-danger mb-0">Reset Tracking?</h5>
                        <p class="text-muted-custom small mb-0 mt-1" id="resetTasbeehTitle">Tasbeeh</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.3); color: #fbbf24;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                        <div class="small">
                            <strong>Note:</strong> Resetting will set your completed count for <strong>this Tasbeeh only</strong> to 0 and restart your tracking cycle from today. Other Tasbeehs will remain completely untouched.
                        </div>
                    </div>
                    <p class="text-muted-custom small mb-0">Are you sure you want to start a new tracking cycle?</p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="submit" id="resetTasbeehSubmitBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Yes, Reset Progress
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.zikr.partials.desc-modal')
@include('admin.zikr.partials.settings-modal')
@include('admin.zikr.partials.bulk-actions-modals')
@endsection

@push('scripts')
<script>
    (function () {
        const STORAGE_KEY = 'zikr_stat_cards_visibility_state';
        const ALL_CARDS = ['lifetime', 'daily_target', 'read_today', 'total_required', 'total_completed', 'backlog'];

        function getVisibilityMap() {
            try {
                const saved = localStorage.getItem(STORAGE_KEY);
                return saved ? JSON.parse(saved) : {};
            } catch (e) {
                return {};
            }
        }

        function isCardVisible(map, cardKey) {
            // Default to visible (true) unless explicitly set to false
            return map[cardKey] !== false;
        }

        function saveVisibilityMap(map) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(map));
            } catch (e) {}
        }

        window.toggleZikrStatVisibility = function (cardKey) {
            const map = getVisibilityMap();
            const current = isCardVisible(map, cardKey);
            map[cardKey] = !current;
            saveVisibilityMap(map);
            window.renderZikrStatCards();
        };

        window.toggleAllZikrStatsVisibility = function () {
            const map = getVisibilityMap();
            const anyHidden = ALL_CARDS.some(k => !isCardVisible(map, k));
            ALL_CARDS.forEach(k => {
                map[k] = anyHidden;
            });
            saveVisibilityMap(map);
            window.renderZikrStatCards();
        };

        window.renderZikrStatCards = function () {
            const map = getVisibilityMap();

            ALL_CARDS.forEach(key => {
                const isVisible = isCardVisible(map, key);

                // 1. Update Eye Icon & Tooltip
                const eyeBtn = document.querySelector(`.zikr-stat-eye-btn[data-stat-target="${key}"]`);
                if (eyeBtn) {
                    const icon = eyeBtn.querySelector('i');
                    if (icon) {
                        icon.className = isVisible ? 'bi bi-eye-slash' : 'bi bi-eye';
                    }
                    eyeBtn.setAttribute('title', isVisible ? 'Click to hide' : 'Click to show');
                }

                // 2. Update Main Number Element
                const valEls = document.querySelectorAll(`.zikr-stat-maskable[data-stat-card="${key}"][data-raw-val]`);
                valEls.forEach(valEl => {
                    const rawVal = valEl.dataset.rawVal !== undefined ? valEl.dataset.rawVal : valEl.textContent;
                    valEl.dataset.rawVal = rawVal;
                    if (isVisible) {
                        valEl.textContent = rawVal;
                        valEl.classList.remove('zikr-stat-masked-dots');
                    } else {
                        valEl.textContent = '••••';
                        valEl.classList.add('zikr-stat-masked-dots');
                    }
                });

                // 3. Update Subtext Element
                const subEls = document.querySelectorAll(`.zikr-stat-maskable[data-stat-card="${key}"][data-raw-subtext]`);
                subEls.forEach(subEl => {
                    const rawSubtext = subEl.dataset.rawSubtext !== undefined ? subEl.dataset.rawSubtext : subEl.innerHTML;
                    const maskedSubtext = subEl.dataset.maskedSubtext || rawSubtext;
                    subEl.dataset.rawSubtext = rawSubtext;
                    if (isVisible) {
                        subEl.innerHTML = rawSubtext;
                    } else {
                        subEl.innerHTML = maskedSubtext;
                    }
                });
            });

            // 4. Update Top Master Eye Icon
            const masterIcon = document.getElementById('toggleAllStatsEyeIcon');
            const masterBtn = document.getElementById('toggleAllStatsEyeBtn');
            if (masterIcon) {
                const anyHidden = ALL_CARDS.some(k => map[k] !== true);
                masterIcon.className = anyHidden ? 'bi bi-eye' : 'bi bi-eye-slash';
                if (masterBtn) {
                    masterBtn.setAttribute('title', anyHidden ? 'Show All Stats' : 'Hide All Stats');
                }
            }
        };

        function initZikrStatVisibility() {
            // Individual Eye Buttons
            document.querySelectorAll('.zikr-stat-eye-btn').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const cardKey = this.dataset.statTarget;
                    if (cardKey) {
                        window.toggleZikrStatVisibility(cardKey);
                    }
                });
            });

            // Click on Masked Value or Subtext directly to toggle
            document.querySelectorAll('.zikr-stat-maskable').forEach(el => {
                el.addEventListener('click', function (e) {
                    // Ignore clicks if text was selected or clicking inside links/buttons
                    if (window.getSelection && window.getSelection().toString().length > 0) return;
                    const cardKey = this.dataset.statCard;
                    if (cardKey) {
                        window.toggleZikrStatVisibility(cardKey);
                    }
                });
            });

            // Top Master Eye Button
            const masterBtn = document.getElementById('toggleAllStatsEyeBtn');
            if (masterBtn) {
                masterBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.toggleAllZikrStatsVisibility();
                });
            }

            // Initial render
            window.renderZikrStatCards();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initZikrStatVisibility);
        } else {
            initZikrStatVisibility();
        }
    })();

    document.addEventListener('DOMContentLoaded', function () {

        // ─── SW Cache Warming (online only) ───────────────────────────────────
        if ('caches' in window && navigator.onLine) {
            caches.keys().then(function (names) {
                const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
                if (!pwaCacheName) return;
                caches.open(pwaCacheName).then(function (cache) {
                    cache.add(window.location.href).catch(() => {});
                    cache.add(window.location.pathname).catch(() => {});
                    cache.add('/admin/zikr').catch(() => {});
                    cache.add('/admin/tasbeehs').catch(() => {});
                    document.querySelectorAll('a[href*="/admin/zikr/tasbeeh/"]').forEach(function (link) {
                        const href = link.getAttribute('href');
                        if (href) {
                            fetch(href, { credentials: 'same-origin' }).then(function (res) {
                                if (res && res.ok) {
                                    cache.put(href, res.clone()).catch(() => {});
                                    try {
                                        const parsedUrl = new URL(href, window.location.origin);
                                        cache.put(parsedUrl.pathname, res).catch(() => {});
                                    } catch (e) {}
                                }
                            }).catch(function () {});
                        }
                    });
                });
            }).catch(function () {});
        }



        // Strip commas/+ from formatted number strings
        function parseRawNum(str) {
            if (str === null || str === undefined) return 0;
            return parseInt(String(str).replace(/[^0-9\-]/g, ''), 10) || 0;
        }

        // ─── Live delta state (accumulates all BroadcastChannel increments) ──
        // Key = tasbeehId, value = { today: N, total: N }
        const liveDeltas = {};

        // ─── DOM Updater — applies liveDeltas on top of original server values ─
        function applyDeltasToDOM() {
            let grandTotalDelta = 0;
            let grandTodayDelta = 0;

            document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                const tId = card.id.replace('tasbeeh-card-', '');
                const delta = liveDeltas[tId];
                if (!delta) return;

                // Read original server baseline from data attributes (set once at render)
                const baseTotal   = parseInt(card.dataset.baseTotal   || card.dataset.totalCompleted   || '0', 10) || 0;
                const baseToday   = parseInt(card.dataset.baseToday   || card.dataset.todayCompleted   || '0', 10) || 0;
                const dailyTarget = parseInt(card.dataset.dailyTarget  || '100', 10) || 100;
                const totalReq    = parseInt(card.dataset.totalRequired || '0', 10) || 0;

                // Persist baseline on first touch (so repeat calls don't compound)
                if (!card.dataset.baseTotal) card.dataset.baseTotal = String(baseTotal);
                if (!card.dataset.baseToday) card.dataset.baseToday = String(baseToday);

                const newTotal = baseTotal + delta.total;
                const newToday = baseToday + delta.today;

                grandTotalDelta += delta.total;
                grandTodayDelta += delta.today;

                // Today badge
                const todayBadge = card.querySelector('.today-status-badge');
                if (todayBadge) {
                    const strong = todayBadge.querySelector('strong');
                    if (strong) strong.textContent = newToday.toLocaleString();
                    if (newToday >= dailyTarget && dailyTarget > 0) {
                        todayBadge.style.background   = 'rgba(16, 185, 129, 0.15)';
                        todayBadge.style.borderColor  = 'rgba(16, 185, 129, 0.4)';
                        todayBadge.style.color        = '#34d399';
                        // Add checkmark icon if not present
                        if (!todayBadge.querySelector('i.bi-check2')) {
                            todayBadge.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">${newToday.toLocaleString()}</strong>`;
                        }
                    } else if (newToday > 0) {
                        todayBadge.style.background   = 'rgba(6, 182, 212, 0.15)';
                        todayBadge.style.borderColor  = 'rgba(6, 182, 212, 0.4)';
                        todayBadge.style.color        = '#38bdf8';
                    }
                }

                // "Completed: X / Y" badge
                const completedBadge = card.querySelector('.badge-completed');
                if (completedBadge) {
                    const strong = completedBadge.querySelector('strong');
                    if (strong) strong.textContent = newTotal.toLocaleString();
                }

                // Remaining / Extra badge
                const remainingBadge = card.querySelector('.badge-remaining');
                if (remainingBadge) {
                    const diff = newTotal - totalReq;
                    if (diff > 0) {
                        remainingBadge.textContent = '+' + diff.toLocaleString() + ' Extra';
                        remainingBadge.className = remainingBadge.className.replace('completed-badge', '').replace('extra', '').trim() + ' extra';
                    } else if (diff === 0) {
                        remainingBadge.textContent = 'Completed';
                        remainingBadge.className = remainingBadge.className.replace('extra', '').trim() + ' completed-badge';
                    } else {
                        remainingBadge.textContent = 'Remaining ' + Math.abs(diff).toLocaleString();
                        remainingBadge.className = remainingBadge.className.replace('extra', '').replace('completed-badge', '').trim();
                    }
                }

                // Progress bar
                const progressBar = card.querySelector('.progress-bar-custom');
                if (progressBar && totalReq > 0) {
                    const pct = Math.min(100, Math.round((newTotal / totalReq) * 100));
                    progressBar.style.width = pct + '%';
                }
            });

            // ─── Top Stat Cards ───────────────────────────────────────────────

            // Read Today (overall_today_completed)
            const todayStatEl = document.getElementById('top-stat-today-completed');
            if (todayStatEl && grandTodayDelta !== 0) {
                const base = parseInt(todayStatEl.dataset.baseVal || '', 10);
                if (isNaN(parseInt(todayStatEl.dataset.baseVal, 10))) {
                    todayStatEl.dataset.baseVal = String(parseRawNum(todayStatEl.dataset.rawVal));
                }
                const newVal = (parseInt(todayStatEl.dataset.baseVal, 10) || 0) + grandTodayDelta;
                todayStatEl.dataset.rawVal = newVal.toLocaleString();
                todayStatEl.textContent = newVal.toLocaleString();
            }

            // Total Completed (overall_total_completed)
            const totalCompEl = document.getElementById('top-stat-total-completed');
            if (totalCompEl && grandTotalDelta !== 0) {
                if (!totalCompEl.dataset.baseVal) {
                    totalCompEl.dataset.baseVal = String(parseRawNum(totalCompEl.dataset.rawVal));
                }
                const newVal = (parseInt(totalCompEl.dataset.baseVal, 10) || 0) + grandTotalDelta;
                totalCompEl.dataset.rawVal = newVal.toLocaleString();
                totalCompEl.textContent = newVal.toLocaleString();
            }

            // Lifetime Total
            const lifetimeEl = document.getElementById('top-stat-lifetime-total');
            if (lifetimeEl && grandTotalDelta !== 0) {
                const baseLifetime = parseInt(lifetimeEl.dataset.baseLifetime || '0', 10) || 0;
                if (!lifetimeEl.dataset.liveBase) {
                    lifetimeEl.dataset.liveBase = String(baseLifetime);
                }
                const newLifetime = (parseInt(lifetimeEl.dataset.liveBase, 10) || 0) + grandTotalDelta;
                lifetimeEl.dataset.liveBase = String(newLifetime); // keep accumulating
                lifetimeEl.dataset.rawVal = newLifetime.toLocaleString();
                lifetimeEl.textContent = newLifetime.toLocaleString();
            }

            // Re-run visibility mask (in case numbers were hidden)
            if (typeof window.renderZikrStatCards === 'function') {
                window.renderZikrStatCards();
            }
        }

        // ─── Add a delta increment for a tasbeeh ─────────────────────────────
        function addDelta(tasbeehId, countDelta, isTodayItem = true) {
            const tId = String(tasbeehId);
            if (!liveDeltas[tId]) liveDeltas[tId] = { today: 0, total: 0 };
            liveDeltas[tId].total += countDelta;
            if (isTodayItem) liveDeltas[tId].today += countDelta;
            applyDeltasToDOM();
        }

        // ─── Extract a YYYY-MM-DD date string from an outbox item ────────────
        function getItemDate(item) {
            const p = item.payload || {};
            // 1. Prefer explicit date in payload
            if (p.date && /^\d{4}-\d{2}-\d{2}$/.test(String(p.date).trim())) {
                return p.date.trim();
            }
            // 2. Fall back to created_at timestamp
            if (item.created_at) {
                try {
                    const d = new Date(item.created_at);
                    if (!isNaN(d.getTime())) return getLocalDateStr(d);
                } catch (_) {}
            }
            // 3. Unknown date — return null (do NOT assume today)
            return null;
        }

        function getLocalDateStr(dateObj) {
            const d = (dateObj instanceof Date && !isNaN(dateObj.getTime())) ? dateObj : new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        // ─── Check if the cached page HTML is stale (from a previous day) ─────
        function applyStalenessResetIfNeeded() {
            const today = getLocalDateStr();
            document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                const renderDate = card.dataset.renderDate || '';
                if (renderDate && renderDate < today) {
                    // Cached page is from a previous day — zero the today baseline
                    card.dataset.todayCompleted = '0';
                    card.dataset.baseToday = '0';
                    card.dataset.renderDate = today;

                    // Reset the today badge visually to 0
                    const todayBadge = card.querySelector('.today-status-badge');
                    if (todayBadge) {
                        const strong = todayBadge.querySelector('strong');
                        if (strong) strong.textContent = '0';
                        todayBadge.style.background   = 'rgba(239, 68, 68, 0.12)';
                        todayBadge.style.borderColor  = 'rgba(239, 68, 68, 0.3)';
                        todayBadge.style.color        = '#f87171';
                    }
                }
            });

            // Also reset top stat "Read Today" if page is stale
            const todayStatEl = document.getElementById('top-stat-today-completed');
            if (todayStatEl) {
                const statRenderDate = todayStatEl.dataset.renderDate || '';
                if (statRenderDate && statRenderDate < today) {
                    todayStatEl.dataset.renderDate = today;
                    todayStatEl.dataset.baseVal = '0';
                    todayStatEl.dataset.rawVal  = '0';
                    todayStatEl.textContent = '0';
                }
            }
        }

        // ─── Read ALL pending outbox items and compute liveDeltas from scratch ─
        async function rebuildDeltasFromOutbox() {
            try {
                applyStalenessResetIfNeeded();

                if (!window.PwaDB || typeof window.PwaDB.getPendingOutbox !== 'function') return;
                const items = await window.PwaDB.getPendingOutbox();

                const clientToday = getLocalDateStr();

                // Reset deltas, rebuild fully from outbox
                Object.keys(liveDeltas).forEach(k => delete liveDeltas[k]);

                (items || []).forEach(item => {
                    const entity = item.entity || '';
                    const p = item.payload || {};
                    if (entity !== 'zikr_count' && entity !== 'tasbeeh_count') return;
                    const tId = p.tasbeeh_id ? String(p.tasbeeh_id) : null;
                    if (!tId) return;
                    const cnt = parseInt(p.count, 10) || 0;
                    const itemDate = getItemDate(item);  // ← uses created_at fallback, NOT today
                    if (!liveDeltas[tId]) liveDeltas[tId] = { today: 0, total: 0 };
                    liveDeltas[tId].total += cnt;
                    // Only count as "today" if itemDate is EXACTLY today
                    if (itemDate && itemDate === clientToday) liveDeltas[tId].today += cnt;
                });

                applyDeltasToDOM();
            } catch (e) {
                console.warn('rebuildDeltasFromOutbox error:', e);
            }
        }

        // ─── BroadcastChannel: live taps from counter page ───────────────────
        const clientToday = getLocalDateStr();

        function handleZikrBroadcast(data) {
            if (!data || !data.type) return;

            if (data.type === 'ZIKR_COUNT_INCREMENT') {
                const tId   = String(data.tasbeehId || '');
                const delta = parseInt(data.delta, 10) || 0;
                if (tId && delta !== 0) {
                    addDelta(tId, delta, true); // increments always today
                }
            } else if (data.type === 'ZIKR_COMPLETE_TODAY') {
                const tId = String(data.tasbeehId || '');
                if (tId) {
                    const card = document.getElementById('tasbeeh-card-' + tId);
                    if (card) {
                        const baseToday  = parseInt(card.dataset.baseToday   || card.dataset.todayCompleted   || '0', 10) || 0;
                        const dailyTarget = parseInt(card.dataset.dailyTarget || '100', 10) || 100;
                        const needed = Math.max(0, dailyTarget - baseToday - (liveDeltas[tId] ? liveDeltas[tId].today : 0));
                        if (needed > 0) addDelta(tId, needed, true);
                    }
                }
            } else if (data.type === 'ZIKR_COMPLETE_ALL') {
                document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                    const tId2 = card.id.replace('tasbeeh-card-', '');
                    const baseToday  = parseInt(card.dataset.baseToday   || card.dataset.todayCompleted   || '0', 10) || 0;
                    const dailyTarget = parseInt(card.dataset.dailyTarget || '100', 10) || 100;
                    const needed = Math.max(0, dailyTarget - baseToday - (liveDeltas[tId2] ? liveDeltas[tId2].today : 0));
                    if (needed > 0) addDelta(tId2, needed, true);
                });
            } else if (data.type === 'ZIKR_RESET_SINGLE') {
                const tId = String(data.tasbeehId || '');
                if (liveDeltas[tId]) delete liveDeltas[tId];
                // Reset card dataset so DOM re-reads 0
                const card = document.getElementById('tasbeeh-card-' + tId);
                if (card) {
                    card.dataset.baseTotal = '0';
                    card.dataset.baseToday = '0';
                    card.dataset.totalCompleted = '0';
                    card.dataset.todayCompleted = '0';
                }
                applyDeltasToDOM();
            } else if (data.type === 'ZIKR_RESET_ALL') {
                Object.keys(liveDeltas).forEach(k => delete liveDeltas[k]);
                document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                    card.dataset.baseTotal = '0'; card.dataset.baseToday = '0';
                    card.dataset.totalCompleted = '0'; card.dataset.todayCompleted = '0';
                });
                applyDeltasToDOM();
            }
        }

        // BroadcastChannel listener
        if ('BroadcastChannel' in window) {
            try {
                const zikrChannel = new BroadcastChannel('portfolio_zikr_channel');
                zikrChannel.onmessage = function (event) {
                    if (event.data && event.data.type) handleZikrBroadcast(event.data);
                };
            } catch (e) {}
        }

        // localStorage fallback for broadcast (cross-tab in Safari)
        window.addEventListener('storage', function (event) {
            if (event.key === 'pwa_zikr_live_broadcast' && event.newValue) {
                try { handleZikrBroadcast(JSON.parse(event.newValue)); } catch (e) {}
            }
        });

        // ─── On load: read outbox and apply ──────────────────────────────────
        rebuildDeltasFromOutbox();

        // ─── On pwa:sync-completed: rebuild (server data is now in DB) ────────
        window.addEventListener('pwa:sync-completed', function (e) {
            const detail = e.detail || {};
            if (detail.hasTasbeehSync || detail.syncedCount > 0) {
                // PwaSync._invalidateZikrPageCaches() will reload the page after 1.8s
                // but rebuild deltas immediately as a stopgap
                rebuildDeltasFromOutbox();
            }
        });

        // ─── On re-connecting to internet ─────────────────────────────────────
        window.addEventListener('online', function () {
            setTimeout(rebuildDeltasFromOutbox, 600);
        });

        // ─── Re-check on visibility (return from another tab) ─────────────────
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                rebuildDeltasFromOutbox();
            }
        });

        // ─── pageshow (back-forward cache restore) ────────────────────────────
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) rebuildDeltasFromOutbox();
        });

        // ─── Midnight rollover: check every 30s, reset today if date changed ──
        let _lastTrackedDay = getLocalDateStr();
        setInterval(function () {
            const today = getLocalDateStr();
            if (today !== _lastTrackedDay) {
                _lastTrackedDay = today;
                // Zero all today baselines
                Object.keys(liveDeltas).forEach(k => { liveDeltas[k].today = 0; });
                document.querySelectorAll('[id^="tasbeeh-card-"]').forEach(card => {
                    card.dataset.todayCompleted = '0';
                    card.dataset.baseToday = '0';
                    card.dataset.renderDate = today;
                    const todayBadge = card.querySelector('.today-status-badge');
                    if (todayBadge) {
                        const strong = todayBadge.querySelector('strong');
                        if (strong) strong.textContent = '0';
                        todayBadge.style.background  = 'rgba(239, 68, 68, 0.12)';
                        todayBadge.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                        todayBadge.style.color       = '#f87171';
                    }
                });
                const todayStatEl = document.getElementById('top-stat-today-completed');
                if (todayStatEl) {
                    todayStatEl.dataset.baseVal = '0';
                    todayStatEl.dataset.rawVal  = '0';
                    todayStatEl.textContent = '0';
                }
                // Re-read outbox (today's pending items only from new date)
                rebuildDeltasFromOutbox();
            }
        }, 30000);
    });
</script>
@endpush
