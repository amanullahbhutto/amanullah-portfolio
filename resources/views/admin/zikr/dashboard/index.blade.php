@extends('layouts.admin')
@section('title', 'Zikr & Tasbeeh')
@section('page_title', 'Daily Zikr Tracking')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-end gap-2 mb-3">
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

    {{-- Top Overall Statistics Cards (2 cards per row on mobile, compact height) --}}
    <div class="row g-2 g-md-3 mb-4">
        {{-- Lifetime All-Time Total Card --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 position-relative d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, rgba(249, 115, 22, 0.14) 0%, #08111e 100%); border-color: rgba(249, 115, 22, 0.4); min-height: 104px;">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small fw-bold text-uppercase text-truncate" style="color: #f97316; font-size: 0.7rem;">
                        <i class="bi bi-infinity me-1"></i>Lifetime Total
                    </span>
                    <button class="btn btn-link p-0 text-secondary" data-bs-toggle="modal" data-bs-target="#resetLifetimeModal" title="Reset Lifetime Total Counter" style="line-height: 1; font-size: 0.8rem; color: #94a3b8 !important;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#94a3b8'">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <strong class="fs-3 fs-md-2 text-white d-block font-monospace my-0" id="top-stat-lifetime-total" style="color: #f97316 !important; line-height: 1.2;">{{ number_format($summary['lifetime_total']) }}</strong>
                <small class="text-muted-custom d-block text-truncate" style="font-size: 0.72rem;">All-Time Permanent</small>
            </div>
        </div>

        {{-- Daily Target --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Daily Target</span>
                <strong class="fs-3 fs-md-2 text-white d-block font-monospace my-0" id="top-stat-daily-target" style="line-height: 1.2;">{{ number_format($summary['overall_today_required']) }}</strong>
                <small class="text-muted-custom d-block text-truncate" style="font-size: 0.72rem;">{{ $summary['total_active_tasbeehs'] }} Tasbeehs</small>
            </div>
        </div>

        {{-- Total Required --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Total Required</span>
                <strong class="fs-3 fs-md-2 text-info d-block font-monospace my-0" id="top-stat-total-required" style="line-height: 1.2;">{{ number_format($summary['overall_total_required']) }}</strong>
                <small class="text-muted-custom d-block text-truncate" style="font-size: 0.72rem;">Active cycle till today</small>
            </div>
        </div>

        {{-- Total Completed --}}
        <div class="col-6 col-md-4 col-xl">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" style="background: #08111e; border-color: #142845; min-height: 104px;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Total Completed</span>
                <strong class="fs-3 fs-md-2 text-success d-block font-monospace my-0" id="top-stat-total-completed" style="line-height: 1.2;">{{ number_format($summary['overall_total_completed']) }}</strong>
                <small class="text-success d-block fw-semibold text-truncate" id="top-stat-overall-percentage" style="font-size: 0.72rem;">{{ $summary['overall_percentage'] }}% Completed</small>
            </div>
        </div>

        {{-- Extra Zikr / Backlog --}}
        <div class="col-12 col-md-4 col-xl">
            <div class="zikr-stat-card p-2 p-sm-3 rounded-4 border h-100 d-flex flex-column justify-content-between" id="top-stat-backlog-container" style="background: #08111e; border-color: #142845; min-height: 104px;">
                @if($summary['overall_extra'] > 0)
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Extra Zikr</span>
                    <strong class="fs-3 fs-md-2 text-info d-block font-monospace my-0" style="line-height: 1.2;">+{{ number_format($summary['overall_extra']) }}</strong>
                    <small class="text-info d-block fw-semibold text-truncate" style="font-size: 0.72rem;">Ahead of schedule</small>
                @else
                    <span class="text-muted-custom small fw-bold text-uppercase d-block text-truncate" style="font-size: 0.7rem;">Remaining Backlog</span>
                    <strong class="fs-3 fs-md-2 {{ $summary['overall_backlog'] > 0 ? 'text-warning' : 'text-success' }} d-block font-monospace my-0" style="line-height: 1.2;">
                        {{ number_format($summary['overall_backlog']) }}
                    </strong>
                    <small class="{{ $summary['overall_backlog'] > 0 ? 'text-warning' : 'text-success' }} d-block fw-semibold text-truncate" style="font-size: 0.72rem;">
                        {{ $summary['overall_backlog'] > 0 ? 'Behind schedule' : 'On track' }}
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
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h3 class="fs-5 fw-bold text-white mb-0 text-truncate me-2">{{ $item['title'] }}</h3>
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
                            <button
                                class="action-icon-btn btn-complete-icon"
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if ('caches' in window && navigator.onLine) {
            caches.keys().then(function (names) {
                const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
                if (!pwaCacheName) return;
                caches.open(pwaCacheName).then(function (cache) {
                    document.querySelectorAll('a[href*="/admin/zikr/tasbeeh/"]').forEach(function (link) {
                        const href = link.getAttribute('href');
                        if (href) {
                            fetch(href, { credentials: 'same-origin' }).then(function (res) {
                                if (res && res.ok) {
                                    cache.put(href, res);
                                }
                            }).catch(function () {});
                        }
                    });
                });
            }).catch(function () {});
        }
    });
</script>
@endpush
