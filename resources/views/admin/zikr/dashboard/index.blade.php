@extends('layouts.admin')
@section('title', 'Zikr & Tasbeeh')
@section('page_title', 'Daily Zikr Tracking')

@push('styles')
<style>
    /* Zikr Card UI Styles */
    .zikr-item-card {
        background: linear-gradient(180deg, #0b1526 0%, #070d18 100%);
        border: 1px solid #162a45;
        border-radius: 20px;
        width: 100%;
        padding: 18px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.65);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .zikr-item-card:hover {
        border-color: rgba(0, 229, 255, 0.3);
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.75), 0 0 20px rgba(0, 229, 255, 0.05);
    }

    /* Dua Content Container */
    .text-container {
        background: #08111e;
        border: 1px solid #142842;
        border-radius: 16px;
        padding: 16px 18px 14px 18px;
        text-align: right;
        margin-bottom: 12px;
        width: 100%;
    }

    .arabic-text {
        font-family: 'Scheherazade New', 'Amiri Quran', 'Amiri', 'PDMS_Saleem_QuranFont', '_PDMS_Saleem_Quran', 'Traditional Arabic', serif !important;
        font-feature-settings: "liga" 1, "cv01" 1;
        color: #f97316;
        font-size: var(--zikr-arabic-size, 24px) !important;
        line-height: 1.8;
        direction: rtl;
        text-align: right;
        margin-bottom: 4px;
        font-weight: 700;
    }

    /* Premium Center Divider */
    .islamic-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 8px 0;
        position: relative;
        direction: ltr;
    }

    .islamic-divider::before,
    .islamic-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(249, 115, 22, 0.5), transparent);
    }

    .divider-icon {
        color: #f97316;
        padding: 0 10px;
        font-size: 0.75rem;
        display: flex;
        align-items: center;
        gap: 4px;
        text-shadow: 0 0 8px rgba(249, 115, 22, 0.6);
        letter-spacing: 2px;
    }

    .urdu-text {
        font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Urdu Typesetting', serif !important;
        color: #94a3b8;
        font-size: var(--zikr-urdu-size, 16px) !important;
        line-height: 2.3;
        direction: rtl;
        text-align: right;
        margin: 0;
        width: 100%;
    }

    /* Progress Container */
    .progress-container {
        height: 6px;
        background: #070e1a;
        border: 1px solid #14263f;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        transition: width 0.4s ease;
    }

    .progress-bar-custom.amber {
        background: linear-gradient(90deg, #d97706 0%, #f59e0b 100%);
        box-shadow: 0 0 10px rgba(245, 158, 11, 0.6);
    }

    .progress-bar-custom.emerald {
        background: linear-gradient(90deg, #059669 0%, #10b981 100%);
        box-shadow: 0 0 10px rgba(16, 185, 129, 0.6);
    }

    .progress-bar-custom.cyan {
        background: linear-gradient(90deg, #00bcd4 0%, #00e5ff 100%);
        box-shadow: 0 0 10px rgba(0, 229, 255, 0.6);
    }

    /* Bottom Footer Strip */
    .card-footer-strip {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 10px 2px 0 2px;
        direction: ltr;
        flex-wrap: wrap;
    }

    @media (min-width: 1400px) {
        .card-footer-strip {
            justify-content: space-between;
        }
    }

    /* Badges Group (Centered) */
    .badge-info-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge-remaining {
        background: rgba(245, 158, 11, 0.12);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.35);
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.8rem;
        white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .badge-remaining.extra {
        background: rgba(0, 188, 212, 0.12);
        color: #00e5ff;
        border-color: rgba(0, 188, 212, 0.35);
    }

    .badge-remaining.completed-badge {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.35);
    }

    .badge-completed {
        background: #091424;
        color: #cbd5e1;
        border: 1px solid #182c48;
        font-weight: 500;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.8rem;
        white-space: nowrap;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Actions Group (Centered) */
    .badge-actions-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-icon-btn {
        background: #08111e;
        border: 1px solid #182c48;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-plus-icon {
        color: #38bdf8;
        border-color: rgba(56, 189, 248, 0.35);
    }
    .btn-plus-icon:hover {
        background: rgba(56, 189, 248, 0.15);
        border-color: #38bdf8;
        color: #38bdf8;
    }

    .btn-speed-icon {
        color: #38bdf8;
    }
    .btn-speed-icon:hover {
        background: rgba(56, 189, 248, 0.12);
        border-color: #38bdf8;
        color: #38bdf8;
    }

    .btn-reset-icon {
        color: #fbbf24;
        border-color: #182c48;
    }
    .btn-reset-icon:hover {
        background: rgba(245, 158, 11, 0.15);
        border-color: #fbbf24;
        color: #fbbf24;
    }

    .action-btn-top.green {
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.35);
    }
    .action-btn-top.green:hover {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
        border-color: #10b981;
        box-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
    }

    .action-btn-top.danger {
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.35);
    }
    .action-btn-top.danger:hover {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border-color: #ef4444;
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
    {{-- Reset All Tasbeehs Trigger (Icon 1) --}}
    <button class="action-btn-top danger" type="button" data-bs-toggle="modal" data-bs-target="#resetAllTasbeehsModal" title="Reset All Tasbeehs to 0 (Start Date Today)">
        <i class="bi bi-arrow-counterclockwise"></i>
    </button>

    {{-- Mark All Complete for Today Trigger (Icon 2) --}}
    <button class="action-btn-top green" type="button" data-bs-toggle="modal" data-bs-target="#completeAllTasbeehsModal" title="Mark All Tasbeehs Complete for Today">
        <i class="bi bi-check2-all"></i>
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

@if(! $selectedUser)
    <div class="admin-card p-5 text-center text-muted-custom">
        <i class="bi bi-people fs-1 text-accent"></i>
        <h5 class="mt-3 mb-1">No Muslim Users Found</h5>
        <p class="mb-0">Please assign the <strong>Muslim</strong> role to a user in User Management to track Zikr & Tasbeeh.</p>
    </div>
@else

    {{-- Top Overall Statistics Cards (5 Cards Including Lifetime Permanent Zikr) --}}
    <div class="row g-3 mb-4">
        {{-- Lifetime All-Time Total Card --}}
        <div class="col-12 col-sm-6 col-xl">
            <div class="zikr-stat-card p-3 rounded-4 border h-100 position-relative" style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.12) 0%, #08111e 100%); border-color: rgba(249, 115, 22, 0.35);">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small fw-bold text-uppercase" style="color: #f97316;">
                        <i class="bi bi-infinity me-1"></i>Lifetime Total
                    </span>
                    <button class="btn btn-link p-0 text-secondary" data-bs-toggle="modal" data-bs-target="#resetLifetimeModal" title="Reset Lifetime Total Counter" style="line-height: 1; font-size: 0.85rem; color: #94a3b8 !important;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <strong class="fs-2 text-white d-block my-1 font-monospace" style="color: #f97316 !important;">{{ number_format($summary['lifetime_total']) }}</strong>
                <small class="text-muted-custom d-block">All-Time Permanent Count</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Daily Target</span>
                <strong class="fs-2 text-white d-block my-1 font-monospace">{{ number_format($summary['overall_today_required']) }}</strong>
                <small class="text-muted-custom d-block">Across {{ $summary['total_active_tasbeehs'] }} Tasbeehs</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Total Required</span>
                <strong class="fs-2 text-info d-block my-1 font-monospace">{{ number_format($summary['overall_total_required']) }}</strong>
                <small class="text-muted-custom d-block">Active cycle till today</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Total Completed</span>
                <strong class="fs-2 text-success d-block my-1 font-monospace">{{ number_format($summary['overall_total_completed']) }}</strong>
                <small class="text-success d-block fw-semibold">{{ $summary['overall_percentage'] }}% Completed</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                @if($summary['overall_extra'] > 0)
                    <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Extra Zikr</span>
                    <strong class="fs-2 text-info d-block my-1 font-monospace">+{{ number_format($summary['overall_extra']) }}</strong>
                    <small class="text-info d-block fw-semibold">Ahead of schedule</small>
                @else
                    <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Remaining Backlog</span>
                    <strong class="fs-2 {{ $summary['overall_backlog'] > 0 ? 'text-warning' : 'text-success' }} d-block my-1 font-monospace">
                        {{ number_format($summary['overall_backlog']) }}
                    </strong>
                    <small class="{{ $summary['overall_backlog'] > 0 ? 'text-warning' : 'text-success' }} d-block fw-semibold">
                        {{ $summary['overall_backlog'] > 0 ? 'Pending to complete' : 'All completed!' }}
                    </small>
                @endif
            </div>
        </div>
    </div>

    {{-- Tasbeeh Items Grid --}}
    <div class="row g-3 g-md-4" id="tasbeeh-list-container">
        @forelse($summary['tasbeehs'] as $item)
            <div class="col-12 col-lg-6 d-flex" id="tasbeeh-card-{{ $item['tasbeeh_id'] }}">
                <div class="zikr-item-card w-100 d-flex flex-column justify-content-between position-relative">
                    <div>
                        {{-- Top Header with Title and Target Info --}}
                        <div class="mb-3">
                            <h5 class="mb-1 fw-bold text-white fs-6 text-truncate">{{ $item['title'] }}</h5>
                            <div class="text-secondary" style="font-size: 0.78rem; line-height: 1.4;">
                                <span>Target: <strong class="text-info font-monospace">{{ number_format($item['daily_target']) }}</strong>/day</span>
                                <span class="mx-1 opacity-50">•</span>
                                <span>Started: <strong class="text-info">{{ $item['formatted_start_date'] }}</strong></span>
                                <span class="opacity-75">({{ $item['active_days'] }} {{ Str::plural('day', $item['active_days']) }})</span>
                            </div>
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
                        <input class="form-control form-control-lg text-center font-monospace fw-bold" type="number" name="count" id="quickAddCountInput" min="-2000000000" max="2000000000" placeholder="e.g. 100 or -33" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
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

@include('admin.zikr.partials.settings-modal')
@include('admin.zikr.partials.bulk-actions-modals')
@endsection
