@extends('layouts.admin')
@section('title', $tasbeeh->title . ' — Live Counter')
@section('page_title', 'Live Zikr Counter')

@push('styles')
<style>
.btn-tasbeeh-lock-fixed {
    position: fixed;
    top: calc(82px + 14px);
    right: max(14px, env(safe-area-inset-right, 14px));
    z-index: 2500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 9999px;
    background: rgba(8, 17, 30, 0.92);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.20);
    color: #e2e8f0;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.5);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}
@media (max-width: 991px) {
    .btn-tasbeeh-lock-fixed {
        top: calc(62px + 12px);
        right: max(12px, env(safe-area-inset-right, 12px));
        padding: 6px 12px;
        font-size: 0.78rem;
    }
}
.btn-tasbeeh-lock-fixed:hover {
    background: rgba(14, 28, 48, 0.98);
    border-color: rgba(255, 255, 255, 0.35);
    color: #fff;
    transform: translateY(-1px);
}
.btn-tasbeeh-lock-fixed.is-locked {
    background: rgba(245, 158, 11, 0.22);
    border-color: rgba(245, 158, 11, 0.65);
    color: #fbbf24;
    box-shadow: 0 0 22px rgba(245, 158, 11, 0.38);
}
.btn-tasbeeh-lock-fixed:active {
    transform: scale(0.96);
}
.btn-tasbeeh-lock-fixed.lock-hint-active {
    background: rgba(14, 116, 144, 0.35) !important;
    border-color: #38bdf8 !important;
    color: #38bdf8 !important;
    box-shadow: 0 0 20px rgba(56, 189, 248, 0.65) !important;
    transform: scale(1.05);
    animation: lockHintPulse 0.35s ease infinite alternate;
}
@keyframes lockHintPulse {
    from { transform: scale(1.02); }
    to { transform: scale(1.08); }
}
body.tasbeeh-locked-mode .btn-tasbeeh-lock-fixed {
    top: max(14px, env(safe-area-inset-top, 14px));
    z-index: 2500;
    display: inline-flex !important;
    visibility: visible !important;
    opacity: 1 !important;
    pointer-events: auto !important;
}
body.tasbeeh-locked-mode #backToZikrBtn,
body.tasbeeh-locked-mode #tasbeehControlsBtn,
body.tasbeeh-locked-mode .card-top-bar .btn-menu-dots,
body.tasbeeh-locked-mode [data-bs-target="#controlsModal"] {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    width: 0 !important;
    height: 0 !important;
    min-height: 0 !important;
    max-height: 0 !important;
    min-width: 0 !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
}
.flash-toast-viewport {
    pointer-events: none !important;
}
.vibration-intensity-group .btn-outline-info {
    border-color: rgba(56, 189, 248, 0.35);
    color: #93c5fd;
    background: rgba(15, 23, 42, 0.6);
    transition: all 0.2s ease;
}
.vibration-intensity-group .btn-check:checked + .btn-outline-info {
    background: #0284c7 !important;
    border-color: #38bdf8 !important;
    color: #ffffff !important;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="live-counter-page-wrapper" id="liveCounterPage" style="cursor: pointer; min-height: calc(100vh - 120px); width: 100%; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation;">
    {{-- Fixed Top-Right Focus / Lock Mode Button (Away from card tap surface) --}}
    <button class="btn-tasbeeh-lock-fixed" id="tasbeehLockToggleBtn" type="button" onclick="event.stopPropagation()" title="Double click to Lock (Only Tasbeeh)">
        <i class="bi bi-unlock fs-5" id="tasbeehLockIcon"></i>
        <span class="d-inline" id="tasbeehLockText">Lock</span>
    </button>

    {{-- Main Tasbeeh Card with Tap Anywhere Detection --}}
    <div
        class="tasbeeh-card"
        id="tasbeehContainer"
        data-increment-url="{{ route('admin.zikr.counter.increment', $tasbeeh) }}"
        data-user-id="{{ $user->id }}"
        data-total-required="{{ $stats['total_required'] }}"
        data-total-completed="{{ $stats['total_completed'] }}"
        data-today-completed="{{ $stats['today_completed'] }}"
        data-daily-target="{{ $stats['daily_target'] }}"
        data-tracking-start-date="{{ $stats['tracking_start_date'] }}"
        data-render-date="{{ now()->format('Y-m-d') }}"
        data-base-today-completed="{{ $stats['today_completed'] }}"
        data-base-today="{{ $stats['today_completed'] }}"
        data-base-total-completed="{{ $stats['total_completed'] }}"
        data-base-total="{{ $stats['total_completed'] }}"
    >
        {{-- Card Top Header Bar --}}
        <div class="card-top-bar">
            <div class="d-flex align-items-center gap-2 min-w-0">
                @php
                    $from = request('from');
                    $backRoute = $from === 'tasbeehs'
                        ? route('admin.tasbeehs.index', ['user_id' => $user->id])
                        : route('admin.zikr.index', ['user_id' => $user->id]);
                    $backUrl = $backRoute . '#tasbeeh-card-' . $tasbeeh->id;
                @endphp
                <a href="{{ $backUrl }}" id="backToZikrBtn" class="btn-menu-dots text-decoration-none flex-shrink-0" onclick="event.stopPropagation()" title="Back to Dashboard">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <span class="fw-semibold text-white text-truncate" style="font-size: 0.9rem;">{{ $tasbeeh->title }}</span>
                <span class="badge rounded-pill d-none tasbeeh-lock-status-pill ms-1" id="tasbeehLockStatusPill" style="background: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.5); color: #fbbf24; font-size: 0.72rem; font-weight: 600;">
                    <i class="bi bi-lock-fill me-1"></i>Locked
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($stats['today_completed'] >= $stats['daily_target'] && $stats['daily_target'] > 0)
                    <span class="badge rounded-pill px-2 py-0.5" id="liveTodayBadge" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-size: 0.72rem; font-weight: 600;">
                        <i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace" id="liveTodayVal">{{ $stats['today_completed'] }}</strong>
                    </span>
                @elseif($stats['today_completed'] > 0)
                    <span class="badge rounded-pill px-2 py-0.5" id="liveTodayBadge" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #38bdf8; font-size: 0.72rem; font-weight: 600;">
                        Today: <strong class="ms-1 font-monospace" id="liveTodayVal">{{ $stats['today_completed'] }}</strong>
                    </span>
                @else
                    <span class="badge rounded-pill px-2 py-0.5" id="liveTodayBadge" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; font-size: 0.72rem; font-weight: 600;">
                        Today: <strong class="ms-1 font-monospace" id="liveTodayVal">0</strong>
                    </span>
                @endif
                <button class="btn-menu-dots flex-shrink-0" type="button" id="tasbeehControlsBtn" onclick="event.stopPropagation()" data-bs-toggle="modal" data-bs-target="#controlsModal" title="Controls & Quick Add">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                </button>
            </div>
        </div>

        {{-- Dua Section - Arabic & Urdu --}}
        <div class="dua-content-box">
            <div class="arabic-text">
                {{ $tasbeeh->arabic_text }}
            </div>

            <div class="islamic-divider">
                <div class="divider-icon">✦ ✧ ✦</div>
            </div>

            @if($tasbeeh->urdu_meaning)
                <div class="urdu-text">
                    {{ $tasbeeh->urdu_meaning }}
                </div>
            @endif
        </div>

        {{-- Islamic Mehrab Arch with Main Counter --}}
        <div class="mehrab-arch" id="mehrabArchBox">
            {{-- Main Counter Display Number without comma --}}
            <div class="counter-number" id="mainCountDisplay">{{ $stats['total_completed'] }}</div>

            {{-- Bottom Stats Row Inside Arch --}}
            <div class="bottom-stats-row">
                <div class="stat-col">
                    <small>Target</small>
                    <strong class="text-white" id="reqVal">{{ $stats['total_required'] }}</strong>
                </div>
                <div class="stat-col">
                    <small>Completed</small>
                    <strong class="text-success" id="completedVal">{{ $stats['total_completed'] }}</strong>
                </div>
                <div class="stat-col">
                    <small id="remainingLabel">{{ $stats['extra'] > 0 ? 'Extra' : 'Remaining' }}</small>
                    <strong class="{{ $stats['extra'] > 0 ? 'text-info' : ($stats['remaining'] > 0 ? 'text-warning' : 'text-success') }}" id="remainingVal">
                        {{ $stats['extra'] > 0 ? ('+' . $stats['extra']) : $stats['remaining'] }}
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Controls & Actions Modal --}}
<div class="modal fade finance-modal" id="controlsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content modal-content-custom p-3">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                <h6 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-sliders2 text-warning"></i>
                    <span>Quick Controls</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Quick Presets --}}
            <label class="d-block text-secondary mb-2" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Quick Add Presets</label>
            <div class="preset-grid">
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(33)">+33</button>
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(100)">+100</button>
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(300)">+300</button>
                <button type="button" class="btn btn-pill" onclick="applyQuickAmount(1000)">+1,000</button>
            </div>

            {{-- Custom Count Form --}}
            <form id="counterManualForm" method="POST" action="{{ route('admin.zikr.counter.manual', $tasbeeh) }}" class="d-flex gap-2 mb-3">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="number" inputmode="numeric" name="count" id="customInputCount" class="form-control input-dark font-monospace fw-bold" placeholder="Custom (e.g. 50 or -33)" required>
                <button type="submit" class="btn btn-action-add text-nowrap d-flex align-items-center gap-1">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            </form>

            {{-- Vibration & Sound Feedback Settings --}}
            <div class="counter-feedback-box p-3 rounded-3 mb-3" style="background: #0c1728; border: 1px solid #162a45;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-white d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-warning" style="font-size: 0.9rem;"></i>
                        <span>Alert & Feedback Settings</span>
                    </span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none small text-info" id="btnTestSound" style="font-size: 0.75rem;">
                        <i class="bi bi-volume-up me-1"></i>Test Alert
                    </button>
                </div>

                {{-- Vibration Settings Section --}}
                <div class="mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-phone-vibrate text-info" style="font-size: 0.85rem;"></i>
                            <span class="small fw-bold text-info" style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">Vibration</span>
                        </div>
                    </div>

                    {{-- 3 Vibration Options: Short, Normal, Strong --}}
                    <div class="mb-3 bg-dark bg-opacity-50 p-2 rounded-2 border border-secondary border-opacity-25">
                        <label class="text-secondary small d-flex align-items-center justify-content-between mb-1" style="font-size: 0.74rem;">
                            <span>Vibration Size:</span>
                        </label>
                        <div class="btn-group w-100 vibration-intensity-group" role="group" aria-label="Vibration Size">
                            <input type="radio" class="btn-check" name="vibrateIntensity" id="vibeShort" value="short" autocomplete="off">
                            <label class="btn btn-outline-info btn-sm py-1 px-1" for="vibeShort" style="font-size: 0.75rem;">
                                Short
                            </label>

                            <input type="radio" class="btn-check" name="vibrateIntensity" id="vibeNormal" value="normal" autocomplete="off" checked>
                            <label class="btn btn-outline-info btn-sm py-1 px-1" for="vibeNormal" style="font-size: 0.75rem;">
                                Normal
                            </label>

                            <input type="radio" class="btn-check" name="vibrateIntensity" id="vibeStrong" value="strong" autocomplete="off">
                            <label class="btn btn-outline-info btn-sm py-1 px-1" for="vibeStrong" style="font-size: 0.75rem;">
                                Strong
                            </label>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 ps-1">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgVibrateTap" style="font-size: 0.82rem; cursor: pointer;">
                                Every Tap Vibrate
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgVibrateTap" style="cursor: pointer;">
                        </div>
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgVibrateDailyTask" style="font-size: 0.82rem; cursor: pointer;">
                                Daily Target Complete
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgVibrateDailyTask" checked style="cursor: pointer;">
                        </div>
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgVibrate33" style="font-size: 0.82rem; cursor: pointer;">
                                Every 33 Count
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgVibrate33" checked style="cursor: pointer;">
                        </div>
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgVibrate100" style="font-size: 0.82rem; cursor: pointer;">
                                Every 100 Count
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgVibrate100" checked style="cursor: pointer;">
                        </div>
                    </div>
                </div>

                {{-- Sound Settings Section --}}
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-volume-up-fill text-warning" style="font-size: 0.85rem;"></i>
                        <span class="small fw-bold text-warning" style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">Sound Alert</span>
                    </div>
                    <div class="d-flex flex-column gap-2 ps-1">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgSoundDailyTask" style="font-size: 0.82rem; cursor: pointer;">
                                Daily Target Complete
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgSoundDailyTask" checked style="cursor: pointer;">
                        </div>
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgSound33" style="font-size: 0.82rem; cursor: pointer;">
                                Every 33 Count
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgSound33" checked style="cursor: pointer;">
                        </div>
                        <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                            <label class="form-check-label text-white small" for="cfgSound100" style="font-size: 0.82rem; cursor: pointer;">
                                Every 100 Count
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="cfgSound100" checked style="cursor: pointer;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Extra Navigation & Management Links --}}
            <div class="d-flex flex-wrap gap-2 pt-2 border-top border-secondary border-opacity-25">
                <button type="button" class="btn btn-outline-theme btn-sm flex-fill" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#zikrSettingsModal">
                    <i class="bi bi-gear-fill me-1 text-info"></i> Display Settings
                </button>
                <button type="button" class="btn btn-outline-theme btn-sm flex-fill" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#changeStartDateModal">
                    <i class="bi bi-calendar-event me-1 text-info"></i> Start Date
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm flex-fill" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#resetTasbeehModal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Cycle
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Change Start Date Modal --}}
<div class="modal fade finance-modal" id="changeStartDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="changeStartDateForm" method="POST" action="{{ route('admin.zikr.counter.start-date', $tasbeeh) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title mb-0 text-white">Change Tracking Start Date</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted-custom small mb-3">
                        Total required count will be calculated from this date to today inclusive.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-white">Tracking Start Date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="tracking_start_date" value="{{ $stats['tracking_start_date'] }}" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit">
                        <span data-submit-label>Save Start Date</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset Tracking Modal --}}
<div class="modal fade finance-modal" id="resetTasbeehModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="resetDetailForm" method="POST" action="{{ route('admin.zikr.counter.reset', $tasbeeh) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header border-secondary border-opacity-25">
                    <div>
                        <h5 class="modal-title text-danger mb-0">Reset Tracking?</h5>
                        <p class="text-muted-custom small mb-0 mt-1">{{ $tasbeeh->title }}</p>
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
                    <button class="btn btn-danger" type="submit">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Yes, Reset Progress
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.zikr.partials.settings-modal')

@push('scripts')
<script>
    (function () {
        const container = document.getElementById('tasbeehContainer');
        if (!container) return;

        function getLocalDateStr(dateObj = null) {
            const d = (dateObj instanceof Date && !isNaN(dateObj.getTime())) ? dateObj : new Date();
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        const pageRenderDate = container.dataset.renderDate || document.querySelector('meta[name="page-rendered-date"]')?.getAttribute('content');
        let clientToday = getLocalDateStr();
        let lastActiveDate = null;
        try {
            lastActiveDate = localStorage.getItem('pwa_zikr_active_date');
        } catch (_) {}

        let isPastDayRender = Boolean(
            (pageRenderDate && pageRenderDate < clientToday) ||
            (lastActiveDate && lastActiveDate < clientToday) ||
            (!pageRenderDate && !navigator.onLine)
        );
        if (isPastDayRender) {
            container.dataset.renderDate = clientToday;
        }

        const dailyTarget = parseInt(container.dataset.dailyTarget || '100', 10) || 100;
        let baseTotalCompleted = parseInt(container.dataset.totalCompleted, 10) || 0;
        let baseTodayCompleted = isPastDayRender ? 0 : (parseInt(container.dataset.todayCompleted || '0', 10) || 0);
        let totalCompleted = baseTotalCompleted;
        let todayCompleted = baseTodayCompleted;
        let totalRequired = parseInt(container.dataset.totalRequired, 10) || 0;

        const maxBeads = 33;
        let pendingBatch = 0;
        let batchTimer = null;
        let isSyncing = false;

        const mainCountEl = document.getElementById('mainCountDisplay');
        const completedValEl = document.getElementById('completedVal');
        const reqValEl = document.getElementById('reqVal');
        const remainingValEl = document.getElementById('remainingVal');
        const remainingLabelEl = document.getElementById('remainingLabel');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const liveTodayValEl = document.getElementById('liveTodayVal');
        const liveTodayBadgeEl = document.getElementById('liveTodayBadge');

        // =========================================================================
        // Feedback (Vibration & Sound) Preferences Management
        // =========================================================================
        const FEEDBACK_PREFS_KEY = 'zikr_counter_feedback_prefs';
        const defaultFeedbackPrefs = {
            vibrateIntensity: 'normal', // 'short', 'normal', 'strong'
            vibrateTap: false,
            vibrateDailyTask: true,
            vibrate33: true,
            vibrate100: true,
            soundDailyTask: true,
            sound33: true,
            sound100: true
        };

        function getFeedbackPrefs() {
            try {
                const stored = localStorage.getItem(FEEDBACK_PREFS_KEY);
                if (stored) {
                    return Object.assign({}, defaultFeedbackPrefs, JSON.parse(stored));
                }
            } catch (_) {}
            return Object.assign({}, defaultFeedbackPrefs);
        }

        function saveFeedbackPrefs(prefs) {
            try {
                localStorage.setItem(FEEDBACK_PREFS_KEY, JSON.stringify(prefs));
            } catch (_) {}
        }

        let feedbackPrefs = getFeedbackPrefs();

        function getVibrationDuration(type) {
            const intensity = feedbackPrefs.vibrateIntensity || 'normal';
            if (intensity === 'short') { // Chhota (Short)
                if (type === 'tap') return 25;
                if (type === '33') return 40;
                if (type === '100') return 70;
                if (type === 'daily_task') return 100;
                return 35; // preview
            } else if (intensity === 'strong') { // Bara (Strong)
                if (type === 'tap') return 75;
                if (type === '33') return 120;
                if (type === '100') return 180;
                if (type === 'daily_task') return 240;
                return 150; // preview
            } else { // Normal
                if (type === 'tap') return 45;
                if (type === '33') return 65;
                if (type === '100') return 100;
                if (type === 'daily_task') return 140;
                return 75; // preview
            }
        }

        function triggerVibration(type) {
            if (!window.navigator || typeof window.navigator.vibrate !== 'function') return;
            const ms = getVibrationDuration(type);
            try {
                window.navigator.vibrate(ms);
            } catch (_) {}
        }

        const cfgVibrateTap = document.getElementById('cfgVibrateTap');
        const cfgVibrateDailyTask = document.getElementById('cfgVibrateDailyTask');
        const cfgVibrate33 = document.getElementById('cfgVibrate33');
        const cfgVibrate100 = document.getElementById('cfgVibrate100');
        const cfgSoundDailyTask = document.getElementById('cfgSoundDailyTask');
        const cfgSound33 = document.getElementById('cfgSound33');
        const cfgSound100 = document.getElementById('cfgSound100');
        const vibeIntensityRadios = document.querySelectorAll('input[name="vibrateIntensity"]');

        function syncFeedbackCheckboxes() {
            if (cfgVibrateTap) cfgVibrateTap.checked = Boolean(feedbackPrefs.vibrateTap);
            if (cfgVibrateDailyTask) cfgVibrateDailyTask.checked = Boolean(feedbackPrefs.vibrateDailyTask);
            if (cfgVibrate33) cfgVibrate33.checked = Boolean(feedbackPrefs.vibrate33);
            if (cfgVibrate100) cfgVibrate100.checked = Boolean(feedbackPrefs.vibrate100);
            if (cfgSoundDailyTask) cfgSoundDailyTask.checked = Boolean(feedbackPrefs.soundDailyTask);
            if (cfgSound33) cfgSound33.checked = Boolean(feedbackPrefs.sound33);
            if (cfgSound100) cfgSound100.checked = Boolean(feedbackPrefs.sound100);

            const activeIntensity = feedbackPrefs.vibrateIntensity || 'normal';
            vibeIntensityRadios.forEach(radio => {
                radio.checked = (radio.value === activeIntensity);
            });
        }
        syncFeedbackCheckboxes();

        vibeIntensityRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.checked) {
                    feedbackPrefs.vibrateIntensity = radio.value;
                    saveFeedbackPrefs(feedbackPrefs);
                    triggerVibration('preview');
                }
            });
        });

        [cfgVibrateTap, cfgVibrateDailyTask, cfgVibrate33, cfgVibrate100, cfgSoundDailyTask, cfgSound33, cfgSound100].forEach(cb => {
            if (!cb) return;
            cb.addEventListener('change', () => {
                feedbackPrefs = {
                    vibrateIntensity: feedbackPrefs.vibrateIntensity || 'normal',
                    vibrateTap: cfgVibrateTap ? cfgVibrateTap.checked : false,
                    vibrateDailyTask: cfgVibrateDailyTask ? cfgVibrateDailyTask.checked : true,
                    vibrate33: cfgVibrate33 ? cfgVibrate33.checked : true,
                    vibrate100: cfgVibrate100 ? cfgVibrate100.checked : true,
                    soundDailyTask: cfgSoundDailyTask ? cfgSoundDailyTask.checked : true,
                    sound33: cfgSound33 ? cfgSound33.checked : true,
                    sound100: cfgSound100 ? cfgSound100.checked : true
                };
                saveFeedbackPrefs(feedbackPrefs);
            });
        });

        // Web Audio API Sound Chimes (Zero-latency, soothing tones without external files)
        let counterAudioCtx = null;
        function getCounterAudioContext() {
            if (!counterAudioCtx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) counterAudioCtx = new AudioCtx();
            }
            if (counterAudioCtx && counterAudioCtx.state === 'suspended') {
                counterAudioCtx.resume().catch(() => {});
            }
            return counterAudioCtx;
        }

        // Unlock Web Audio Context on first touch or pointer gesture for iOS Safari & Android
        const unlockCounterAudio = () => {
            try {
                const ctx = getCounterAudioContext();
                if (ctx && ctx.state === 'suspended') {
                    ctx.resume().catch(() => {});
                }
            } catch (_) {}
        };
        document.addEventListener('pointerdown', unlockCounterAudio, { passive: true });
        document.addEventListener('click', unlockCounterAudio, { passive: true });

        function playCounterSound(type) {
            try {
                const ctx = getCounterAudioContext();
                if (!ctx) return;

                const runSound = () => {
                    const now = ctx.currentTime;
                    if (type === '33') {
                        // Crisp, clear, gentle bell chime (880 Hz - A5) with warm harmonics
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(880, now);
                        gain.gain.setValueAtTime(0.32, now);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start(now);
                        osc.stop(now + 0.45);
                    } else if (type === '100') {
                        // Harmonious dual ascending chime (880 Hz -> 1318.5 Hz - E6)
                        [880, 1318.51].forEach((freq, i) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            const start = now + (i * 0.12);
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(freq, start);
                            gain.gain.setValueAtTime(0.35, start);
                            gain.gain.exponentialRampToValueAtTime(0.001, start + 0.45);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(start);
                            osc.stop(start + 0.45);
                        });
                    } else if (type === 'daily_task') {
                        // Celebratory 4-note ascending chime: C5 (523Hz) -> E5 (659Hz) -> G5 (784Hz) -> C6 (1046.5Hz)
                        // Uses 'triangle' wave for loud, crystal-clear bell ring on phone speakers
                        [523.25, 659.25, 783.99, 1046.50].forEach((freq, i) => {
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            const start = now + (i * 0.11);
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(freq, start);
                            gain.gain.setValueAtTime(0.35, start);
                            gain.gain.exponentialRampToValueAtTime(0.001, start + 0.45);
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.start(start);
                            osc.stop(start + 0.45);
                        });
                    }
                };

                if (ctx.state === 'suspended') {
                    ctx.resume().then(() => runSound()).catch(() => runSound());
                } else {
                    runSound();
                }
            } catch (_) {}
        }

        const btnTestSound = document.getElementById('btnTestSound');
        if (btnTestSound) {
            btnTestSound.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                playCounterSound('daily_task');
                if (feedbackPrefs.vibrateDailyTask) {
                    triggerVibration('daily_task');
                } else {
                    triggerVibration('preview');
                }
            });
        }

        function recalculateRequiredForDate(targetDateStr) {
            const startStr = container.dataset.trackingStartDate;
            if (startStr) {
                const start = new Date(startStr + 'T00:00:00');
                const current = new Date(targetDateStr + 'T00:00:00');
                const diffDays = Math.max(1, Math.floor((current - start) / 86400000) + 1);
                totalRequired = diffDays * dailyTarget;
                if (reqValEl) reqValEl.innerText = String(totalRequired);
            }
        }

        if (isPastDayRender) {
            recalculateRequiredForDate(clientToday);
            updateTodayDisplay();
        }

        function updateTodayDisplay() {
            if (todayCompleted < 0) todayCompleted = 0;

            if (liveTodayValEl) {
                liveTodayValEl.innerText = String(todayCompleted);
            }

            if (liveTodayBadgeEl) {
                if (todayCompleted >= dailyTarget && dailyTarget > 0) {
                    liveTodayBadgeEl.style.background = 'rgba(16, 185, 129, 0.15)';
                    liveTodayBadgeEl.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                    liveTodayBadgeEl.style.color = '#34d399';
                    liveTodayBadgeEl.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace" id="liveTodayVal">${todayCompleted}</strong>`;
                } else if (todayCompleted > 0) {
                    liveTodayBadgeEl.style.background = 'rgba(6, 182, 212, 0.15)';
                    liveTodayBadgeEl.style.borderColor = 'rgba(6, 182, 212, 0.4)';
                    liveTodayBadgeEl.style.color = '#38bdf8';
                    liveTodayBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace" id="liveTodayVal">${todayCompleted}</strong>`;
                } else {
                    liveTodayBadgeEl.style.background = 'rgba(239, 68, 68, 0.12)';
                    liveTodayBadgeEl.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                    liveTodayBadgeEl.style.color = '#f87171';
                    liveTodayBadgeEl.innerHTML = `Today: <strong class="ms-1 font-monospace" id="liveTodayVal">0</strong>`;
                }
            }
        }

        // Update Screen Elements
        function updateDisplay(shouldAnimate = false) {
            if (totalCompleted < 0) totalCompleted = 0;

            const formattedNum = String(totalCompleted);

            if (mainCountEl) {
                mainCountEl.innerText = formattedNum;

                // Dynamic responsive font sizing based on digit length
                const len = formattedNum.length;
                if (len <= 4) {
                    mainCountEl.style.fontSize = 'clamp(1.85rem, 4.4vh, 2.35rem)';
                } else if (len <= 6) {
                    mainCountEl.style.fontSize = 'clamp(1.5rem, 3.6vh, 1.9rem)';
                } else if (len <= 9) {
                    mainCountEl.style.fontSize = 'clamp(1.2rem, 3.0vh, 1.55rem)';
                } else {
                    mainCountEl.style.fontSize = 'clamp(0.95rem, 2.4vh, 1.25rem)';
                }

                if (shouldAnimate) {
                    mainCountEl.classList.remove('number-bump');
                    void mainCountEl.offsetWidth;
                    mainCountEl.classList.add('number-bump');
                }
            }

            if (completedValEl) completedValEl.innerText = String(totalCompleted);
            if (reqValEl) reqValEl.innerText = String(totalRequired);

            const diff = totalCompleted - totalRequired;
            if (remainingValEl && remainingLabelEl) {
                if (diff > 0) {
                    remainingLabelEl.innerText = 'Extra';
                    remainingValEl.innerText = '+' + String(diff);
                    remainingValEl.className = 'text-info';
                } else {
                    const rem = Math.max(0, totalRequired - totalCompleted);
                    remainingLabelEl.innerText = 'Remaining';
                    remainingValEl.innerText = String(rem);
                    remainingValEl.className = rem > 0 ? 'text-warning' : 'text-success';
                }
            }

            updateTodayDisplay();
        }

        // Batch AJAX Sync to backend
        let inFlightBatch = 0;

        function flushBatch(forceOffline = false) {
            if (pendingBatch === 0 || isSyncing) return;

            isSyncing = true;
            inFlightBatch = pendingBatch;
            pendingBatch = 0;

            const tasbeehId = '{{ $tasbeeh->id }}';

            // If offline, save directly to IndexedDB outbox without duplicate broadcasting
            if (!navigator.onLine || forceOffline) {
                if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                    window.PwaSync.saveZikrCount(tasbeehId, inFlightBatch, null, false);
                }
                inFlightBatch = 0;
                isSyncing = false;
                return;
            }

            const formData = new FormData();
            formData.append('count', inFlightBatch);
            formData.append('user_id', container.dataset.userId);
            formData.append('_token', csrfToken);

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3500);

            fetch(container.dataset.incrementUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
                signal: controller.signal,
            })
                .then(async (res) => {
                    clearTimeout(timeoutId);
                    const payload = await res.json().catch(() => ({}));
                    if (!res.ok) throw Object.assign(new Error('Increment failed'), { payload });
                    return payload;
                })
                .then((payload) => {
                    if (payload.stats) {
                        baseTotalCompleted = Number(payload.stats.total_completed);
                        if (payload.stats.today_completed !== undefined) {
                            baseTodayCompleted = Number(payload.stats.today_completed);
                        }
                        totalCompleted = baseTotalCompleted + pendingBatch;
                        todayCompleted = baseTodayCompleted + pendingBatch;
                        updateDisplay(false);
                    }
                    inFlightBatch = 0;
                })
                .catch((err) => {
                    clearTimeout(timeoutId);
                    console.warn('Increment network sync failed, saving to offline outbox:', err);
                    if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                        window.PwaSync.saveZikrCount(tasbeehId, inFlightBatch, null, false);
                    }
                    inFlightBatch = 0;
                })
                .finally(() => {
                    isSyncing = false;
                    if (pendingBatch > 0) {
                        clearTimeout(batchTimer);
                        batchTimer = setTimeout(() => flushBatch(), 400);
                    }
                });
        }

        // Helper to spawn a gentle animated glowing ripple on mobile tap
        function createTouchRipple(e) {
            try {
                const ripple = document.createElement('div');
                ripple.className = 'zikr-touch-ripple';
                let x = e.clientX;
                let y = e.clientY;
                if ((x === undefined || y === undefined) && e.touches && e.touches[0]) {
                    x = e.touches[0].clientX;
                    y = e.touches[0].clientY;
                }
                if (x === undefined || y === undefined) {
                    const archBox = document.getElementById('mehrabArchBox') || container;
                    const rect = archBox.getBoundingClientRect();
                    x = rect.left + rect.width / 2;
                    y = rect.top + rect.height / 2;
                }
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                document.body.appendChild(ripple);
                setTimeout(() => {
                    if (ripple.parentNode) ripple.parentNode.removeChild(ripple);
                }, 380);
            } catch (_) {}
        }

        // Focus / Lock Mode State (Only Tasbeeh Active)
        let isTasbeehLocked = false;
        const lockToggleBtn = document.getElementById('tasbeehLockToggleBtn');
        const lockIcon = document.getElementById('tasbeehLockIcon');
        const lockStatusPill = document.getElementById('tasbeehLockStatusPill');

        function toggleTasbeehLock(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            isTasbeehLocked = !isTasbeehLocked;

            if (isTasbeehLocked) {
                document.body.classList.add('tasbeeh-locked-mode');
                const topbar = document.querySelector('.admin-topbar');
                if (topbar) topbar.style.setProperty('display', 'none', 'important');
                const sidebar = document.getElementById('adminSidebar');
                if (sidebar) sidebar.style.setProperty('display', 'none', 'important');
                const bottomNav = document.querySelector('.pwa-bottom-nav');
                if (bottomNav) bottomNav.style.setProperty('display', 'none', 'important');

                // Hide Back Button and 3-dot Controls Button when locked
                const backBtn = document.getElementById('backToZikrBtn');
                if (backBtn) backBtn.style.setProperty('display', 'none', 'important');
                const controlsBtn = document.getElementById('tasbeehControlsBtn') || document.querySelector('[data-bs-target="#controlsModal"]');
                if (controlsBtn) controlsBtn.style.setProperty('display', 'none', 'important');

                // Close controls modal if currently open
                const modalEl = document.getElementById('controlsModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modalInst = bootstrap.Modal.getInstance(modalEl);
                    if (modalInst) modalInst.hide();
                }

                if (lockToggleBtn) {
                    lockToggleBtn.classList.add('is-locked');
                    lockToggleBtn.setAttribute('title', 'Tasbeeh Locked (Double click to Unlock)');
                }
                const lockTextEl = document.getElementById('tasbeehLockText');
                if (lockTextEl) lockTextEl.textContent = 'Unlock';
                if (lockIcon) {
                    lockIcon.className = 'bi bi-lock-fill fs-5 text-warning';
                }
                if (lockStatusPill) {
                    lockStatusPill.classList.remove('d-none');
                }
                try {
                    history.pushState({ tasbeehLocked: true }, document.title, window.location.href);
                } catch (_) {}

                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('warning', 'Tasbeeh Lock Active: Sirf Tasbeeh chalegi. Unlock ke liye lock par double click karein.');
                }
            } else {
                document.body.classList.remove('tasbeeh-locked-mode');
                const topbar = document.querySelector('.admin-topbar');
                if (topbar) topbar.style.removeProperty('display');
                const sidebar = document.getElementById('adminSidebar');
                if (sidebar) sidebar.style.removeProperty('display');
                const bottomNav = document.querySelector('.pwa-bottom-nav');
                if (bottomNav) bottomNav.style.removeProperty('display');

                // Restore Back Button and 3-dot Controls Button when unlocked
                const backBtn = document.getElementById('backToZikrBtn');
                if (backBtn) backBtn.style.removeProperty('display');
                const controlsBtn = document.getElementById('tasbeehControlsBtn') || document.querySelector('[data-bs-target="#controlsModal"]');
                if (controlsBtn) controlsBtn.style.removeProperty('display');

                if (lockToggleBtn) {
                    lockToggleBtn.classList.remove('is-locked');
                    lockToggleBtn.setAttribute('title', 'Lock Mode (Double click to Lock)');
                }
                const lockTextEl = document.getElementById('tasbeehLockText');
                if (lockTextEl) lockTextEl.textContent = 'Lock';
                if (lockIcon) {
                    lockIcon.className = 'bi bi-unlock fs-5 text-white';
                }
                if (lockStatusPill) {
                    lockStatusPill.classList.add('d-none');
                }
                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('info', 'Tasbeeh Unlocked: Tamam controls dobara active hain.');
                }
            }
        }

        // Double click / double tap handler for the lock button
        let lastLockTapTime = 0;
        let lockHintTimeout = null;

        function triggerLockDoubleTapAction(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const now = Date.now();
            const timeSinceLast = now - lastLockTapTime;

            if (timeSinceLast > 50 && timeSinceLast < 800) {
                // Confirmed second tap / double click!
                clearTimeout(lockHintTimeout);
                lastLockTapTime = 0;
                if (lockToggleBtn) lockToggleBtn.classList.remove('lock-hint-active');
                toggleTasbeehLock(e);
            } else {
                // First tap / click: NO blocking toast popup
                lastLockTapTime = now;
                if (lockToggleBtn) {
                    lockToggleBtn.classList.add('lock-hint-active');
                }
                const lockTextEl = document.getElementById('tasbeehLockText');
                if (lockTextEl) lockTextEl.textContent = 'Tap Again!';

                clearTimeout(lockHintTimeout);
                lockHintTimeout = setTimeout(() => {
                    if (lockToggleBtn) lockToggleBtn.classList.remove('lock-hint-active');
                    if (lockTextEl) lockTextEl.textContent = isTasbeehLocked ? 'Unlock' : 'Lock';
                    lastLockTapTime = 0;
                }, 800);
            }
        }

        if (lockToggleBtn) {
            lockToggleBtn.addEventListener('pointerdown', function (e) {
                if (e.pointerType === 'touch') {
                    e.preventDefault();
                    e.stopPropagation();
                    triggerLockDoubleTapAction(e);
                }
            });
            lockToggleBtn.addEventListener('click', function (e) {
                if (e.pointerType !== 'touch') {
                    triggerLockDoubleTapAction(e);
                }
            });
        }

        // Prevent navigation while locked
        const backBtn = document.getElementById('backToZikrBtn');
        if (backBtn) {
            backBtn.addEventListener('click', function (e) {
                if (isTasbeehLocked) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        }

        window.addEventListener('popstate', function (e) {
            if (isTasbeehLocked) {
                try {
                    history.pushState({ tasbeehLocked: true }, document.title, window.location.href);
                } catch (_) {}
                if (window.App && typeof window.App.showToast === 'function') {
                    window.App.showToast('warning', 'Tasbeeh lock hai. Unlock karne ke liye lock button par double click karein.');
                }
            }
        });

        // Ultra-responsive Tap Anywhere Handler for Mobile & Desktop with Haptics & Ripples
        let lastTapTimestamp = 0;
        function isTapNearLockBtn(e) {
            if (e && e.target && e.target.closest && e.target.closest('#tasbeehLockToggleBtn, .btn-tasbeeh-lock-fixed')) {
                return true;
            }
            if (!lockToggleBtn) return false;
            let cx = e ? e.clientX : undefined;
            let cy = e ? e.clientY : undefined;
            if ((cx === undefined || cy === undefined) && e && e.touches && e.touches[0]) {
                cx = e.touches[0].clientX;
                cy = e.touches[0].clientY;
            }
            if (cx !== undefined && cy !== undefined) {
                const rect = lockToggleBtn.getBoundingClientRect();
                if (cx >= rect.left - 14 && cx <= rect.right + 14 && cy >= rect.top - 14 && cy <= rect.bottom + 14) {
                    return true;
                }
            }
            return false;
        }

        function handleScreenTap(e) {
            // If clicking the lock toggle button itself or near it, do not count tasbeeh
            if (isTapNearLockBtn(e)) {
                return;
            }

            // If not locked, ignore clicks on buttons/links/inputs/modals/controls/forms
            if (!isTasbeehLocked) {
                if (e && e.target && e.target.closest && e.target.closest('button, a, input, select, textarea, .modal, .modal-backdrop, [data-bs-toggle], .btn-menu-dots, .btn-close, .dropdown-menu, label, form, .preset-grid')) {
                    return;
                }
            }

            const now = Date.now();
            if (now - lastTapTimestamp < 75) {
                return;
            }
            lastTapTimestamp = now;

            // 24-hour / midnight rollover check before tap calculation
            const currentDayNow = getLocalDateStr();
            const currentRenderDate = container.dataset.renderDate || document.querySelector('meta[name="page-rendered-date"]')?.getAttribute('content');
            if (currentDayNow !== lastTrackedDay || (currentRenderDate && currentRenderDate < currentDayNow)) {
                lastTrackedDay = currentDayNow;
                baseTodayCompleted = 0;
                todayCompleted = 0;
                container.dataset.renderDate = currentDayNow;
                recalculateRequiredForDate(currentDayNow);
            }

            // Visual touch ripple
            if (e) createTouchRipple(e);

            const prevToday = todayCompleted;
            totalCompleted += 1;
            todayCompleted += 1;
            pendingBatch += 1;
            updateDisplay(true);

            // Daily Target Completion & Milestone Alerts (Triggers on 1st time AND every repeated cycle / baar baar)
            const isDailyTargetHit = (dailyTarget > 0 && todayCompleted > 0 && Math.floor(todayCompleted / dailyTarget) > Math.floor(Math.max(0, prevToday) / dailyTarget));
            const isMilestone100 = (todayCompleted > 0 && todayCompleted % 100 === 0) || (totalCompleted > 0 && totalCompleted % 100 === 0);
            const isMilestone33 = (todayCompleted > 0 && todayCompleted % 33 === 0) || (totalCompleted > 0 && totalCompleted % 33 === 0);

            // Single vibration and sound: only ONE event triggers per tap to prevent double vibration
            if (isDailyTargetHit) {
                if (feedbackPrefs.vibrateDailyTask) {
                    triggerVibration('daily_task');
                }
                if (feedbackPrefs.soundDailyTask) {
                    playCounterSound('daily_task');
                }
            } else if (isMilestone100) {
                if (feedbackPrefs.vibrate100) {
                    triggerVibration('100');
                }
                if (feedbackPrefs.sound100) {
                    playCounterSound('100');
                }
            } else if (isMilestone33) {
                if (feedbackPrefs.vibrate33) {
                    triggerVibration('33');
                }
                if (feedbackPrefs.sound33) {
                    playCounterSound('33');
                }
            } else if (feedbackPrefs.vibrateTap) {
                triggerVibration('tap');
            }

            // Broadcast real-time tap to other tabs/pages immediately
            if (window.PwaSync && typeof window.PwaSync.broadcastZikrCountUpdate === 'function') {
                window.PwaSync.broadcastZikrCountUpdate('{{ $tasbeeh->id }}', 1);
            }

            clearTimeout(batchTimer);
            batchTimer = setTimeout(() => flushBatch(), 400);
        }

        // Fast pointerdown for touchscreens (0ms latency), click for desktop mouse
        let lastTouchHandled = 0;
        document.addEventListener('pointerdown', function (e) {
            if (e.pointerType === 'touch') {
                if (isTapNearLockBtn(e)) {
                    return;
                }
                if (!isTasbeehLocked) {
                    if (e.target && e.target.closest && e.target.closest('button, a, input, select, textarea, .modal, .modal-backdrop, [data-bs-toggle], .btn-menu-dots, .btn-close, .dropdown-menu, label, form, .preset-grid')) {
                        return;
                    }
                }
                lastTouchHandled = Date.now();
                handleScreenTap(e);
            }
        }, { passive: true });

        document.addEventListener('click', function (e) {
            if (isTapNearLockBtn(e)) {
                return;
            }
            if (Date.now() - lastTouchHandled < 650) {
                return; // Prevent duplicate execution from synthesized click
            }
            handleScreenTap(e);
        });
        window.handleLiveCardTap = handleScreenTap;

        // Quick Preset amount handler
        window.applyQuickAmount = function (amount) {
            const input = document.getElementById('customInputCount');
            if (input) input.value = amount;
            const form = document.getElementById('counterManualForm');
            if (form) form.requestSubmit();
        };

        // Manual form AJAX submission
        const manualForm = document.getElementById('counterManualForm');
        if (manualForm) {
            manualForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const input = document.getElementById('customInputCount');
                const val = parseInt(input.value, 10);
                if (isNaN(val)) return;

                const submitBtn = manualForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                const prevToday = todayCompleted;

                if (!navigator.onLine) {
                    if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                        window.PwaSync.saveZikrCount('{{ $tasbeeh->id }}', val);
                    }
                    totalCompleted += val;
                    todayCompleted += val;
                    updateDisplay();
                    if (dailyTarget > 0 && todayCompleted > 0 && Math.floor(todayCompleted / dailyTarget) > Math.floor(Math.max(0, prevToday) / dailyTarget)) {
                        if (feedbackPrefs.vibrateDailyTask) {
                            triggerVibration('daily_task');
                        }
                        if (feedbackPrefs.soundDailyTask) {
                            playCounterSound('daily_task');
                        }
                    }
                    const modalEl = document.getElementById('controlsModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    input.value = '';
                    if (submitBtn) submitBtn.disabled = false;
                    if (window.App && typeof window.App.showToast === 'function') {
                        window.App.showToast('info', 'Zikr count saved offline. Will sync once reconnected.');
                    }
                    return;
                }

                fetch(manualForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(manualForm),
                })
                    .then(async (res) => {
                        const payload = await res.json().catch(() => ({}));
                        if (!res.ok) throw Object.assign(new Error('Manual add failed'), { payload });
                        return payload;
                    })
                    .then((payload) => {
                        if (payload.stats) {
                            baseTotalCompleted = Number(payload.stats.total_completed);
                            if (payload.stats.today_completed !== undefined) {
                                baseTodayCompleted = Number(payload.stats.today_completed);
                            }
                            totalCompleted = baseTotalCompleted;
                            todayCompleted = baseTodayCompleted;
                            updateDisplay();
                            if (dailyTarget > 0 && todayCompleted > 0 && Math.floor(todayCompleted / dailyTarget) > Math.floor(Math.max(0, prevToday) / dailyTarget)) {
                                if (feedbackPrefs.vibrateDailyTask) {
                                    triggerVibration('daily_task');
                                }
                                if (feedbackPrefs.soundDailyTask) {
                                    playCounterSound('daily_task');
                                }
                            }
                        }
                        if (window.PwaSync && typeof window.PwaSync.broadcastZikrCountUpdate === 'function') {
                            window.PwaSync.broadcastZikrCountUpdate('{{ $tasbeeh->id }}', val);
                        }
                        const modalEl = document.getElementById('controlsModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        input.value = '';
                        if (typeof showFlashToast === 'function') {
                            showFlashToast(payload.message || 'Zikr count updated successfully.');
                        }
                    })
                    .catch((err) => {
                        if (!navigator.onLine && window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                            window.PwaSync.saveZikrCount('{{ $tasbeeh->id }}', val);
                            totalCompleted += val;
                            todayCompleted += val;
                            updateDisplay();
                            if (dailyTarget > 0 && todayCompleted > 0 && Math.floor(todayCompleted / dailyTarget) > Math.floor(Math.max(0, prevToday) / dailyTarget)) {
                                if (feedbackPrefs.vibrateDailyTask) {
                                    triggerVibration('daily_task');
                                }
                                if (feedbackPrefs.soundDailyTask) {
                                    playCounterSound('daily_task');
                                }
                            }
                        } else {
                            alert(err?.payload?.message || 'Could not update zikr count.');
                        }
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            });
        }

        // Immediate flush on mobile navigation/pagehide/visibilitychange
        const flushImmediate = () => {
            if (pendingBatch > 0) {
                const countToSave = pendingBatch;
                pendingBatch = 0;
                if (window.PwaSync && typeof window.PwaSync.saveZikrCount === 'function') {
                    window.PwaSync.saveZikrCount('{{ $tasbeeh->id }}', countToSave, null, false);
                }
            }
        };

        // Invalidate /admin/zikr and /admin/tasbeehs from SW cache when leaving counter page
        // This forces a fresh server fetch on next visit so updated counts appear
        const invalidateListPageCaches = () => {
            try {
                if ('caches' in window) {
                    caches.keys().then(names => {
                        const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
                        if (!pwaCacheName) return;
                        caches.open(pwaCacheName).then(cache => {
                            const pats = ['/admin/zikr', '/admin/tasbeehs'];
                            cache.keys().then(reqs => {
                                reqs.forEach(req => {
                                    const url = req.url || '';
                                    const isListPage = pats.some(pat => {
                                        try {
                                            const parsed = new URL(url);
                                            return parsed.pathname === pat ||
                                                   parsed.pathname.startsWith(pat + '?') ||
                                                   (parsed.pathname.startsWith(pat + '/') && !url.includes('/admin/zikr/tasbeeh/'));
                                        } catch (_) { return false; }
                                    });
                                    if (isListPage) {
                                        cache.delete(req).catch(() => {});
                                    }
                                });
                            }).catch(() => {});
                        }).catch(() => {});
                    }).catch(() => {});
                }
            } catch (_) {}
        };

        window.addEventListener('pagehide', () => { flushImmediate(); invalidateListPageCaches(); });
        window.addEventListener('beforeunload', () => {
            flushImmediate();
            invalidateListPageCaches();
            if (navigator.sendBeacon && pendingBatch > 0) {
                const formData = new FormData();
                formData.append('count', pendingBatch);
                formData.append('user_id', container.dataset.userId);
                formData.append('_token', csrfToken);
                navigator.sendBeacon(container.dataset.incrementUrl, formData);
            }
        });
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') flushImmediate();
        });
        document.querySelectorAll('a, button[data-bs-dismiss], [data-bs-toggle]').forEach(el => {
            el.addEventListener('click', flushImmediate);
        });

        // Initial bead render & display update
        updateDisplay();

        // Absolute reconciliation of pending offline counts for this tasbeeh
        const reconcilePending = async (syncedData = null) => {
            try {
                const currentTasbeehId = '{{ $tasbeeh->id }}';
                clientToday = getLocalDateStr();
                let lastActiveDate = null;
                try {
                    lastActiveDate = localStorage.getItem('pwa_zikr_active_date');
                } catch (_) {}

                const currentRenderDate = container.dataset.renderDate || pageRenderDate;
                const isPastDay = Boolean(
                    (currentRenderDate && currentRenderDate < clientToday) ||
                    (lastActiveDate && lastActiveDate < clientToday) ||
                    (!currentRenderDate && !navigator.onLine)
                );

                if (isPastDay) {
                    recalculateRequiredForDate(clientToday);
                    baseTodayCompleted = 0;
                    container.dataset.renderDate = clientToday;
                }

                // 1. If syncedData is provided, update baseline
                if (syncedData && syncedData.zikr_summary && Array.isArray(syncedData.zikr_summary.tasbeehs)) {
                    const syncDate = (syncedData.server_time || '').substring(0, 10);
                    const isSyncToday = !syncDate || syncDate === clientToday;
                    const match = syncedData.zikr_summary.tasbeehs.find(t => String(t.tasbeeh_id) === currentTasbeehId);
                    if (match) {
                        baseTotalCompleted = Number(match.total_completed || 0);
                        baseTodayCompleted = isSyncToday ? Number(match.today_completed || 0) : 0;
                    }
                } else if (isPastDay) {
                    baseTodayCompleted = 0;
                }

                // 2. Read pending outbox items
                let items = [];
                if (window.PwaDB && typeof window.PwaDB.getPendingOutbox === 'function') {
                    items = await window.PwaDB.getPendingOutbox();
                }

                const getItemLocalDate = (item) => {
                    const p = item.payload || {};
                    if (p.date && typeof p.date === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(p.date.trim())) {
                        return p.date.trim();
                    }
                    if (item.created_at) {
                        try {
                            const parsed = new Date(item.created_at);
                            if (!isNaN(parsed.getTime())) {
                                return getLocalDateStr(parsed);
                            }
                        } catch (_) {}
                    }
                    return null;
                };

                let pendingCountToday = 0;
                let pendingCountTotal = 0;
                let isCompletedToday = false;
                let isReset = false;

                (items || []).forEach(item => {
                    const entity = item.entity || '';
                    const p = item.payload || {};
                    const tId = p.tasbeeh_id ? String(p.tasbeeh_id) : null;
                    const itemDate = getItemLocalDate(item);
                    const isTodayItem = Boolean(itemDate && itemDate === clientToday);

                    if ((entity === 'tasbeeh_count' || entity === 'zikr_count') && tId === currentTasbeehId) {
                        const cnt = (parseInt(p.count, 10) || 0);
                        pendingCountTotal += cnt;
                        if (isTodayItem) {
                            pendingCountToday += cnt;
                        }
                    } else if (entity === 'tasbeeh_complete_today' && tId === currentTasbeehId) {
                        if (isTodayItem) isCompletedToday = true;
                    } else if (entity === 'zikr_complete_all') {
                        if (isTodayItem) isCompletedToday = true;
                    } else if (entity === 'tasbeeh_reset_single' && tId === currentTasbeehId) {
                        isReset = true;
                    } else if (entity === 'zikr_reset_all') {
                        isReset = true;
                    }
                });

                if (isReset) {
                    totalCompleted = 0 + pendingBatch;
                    todayCompleted = 0 + pendingBatch;
                } else if (isCompletedToday) {
                    const neededForToday = Math.max(dailyTarget - baseTodayCompleted, 0);
                    todayCompleted = Math.max(baseTodayCompleted, dailyTarget) + pendingCountToday + pendingBatch;
                    totalCompleted = baseTotalCompleted + neededForToday + pendingCountTotal + pendingBatch;
                } else {
                    totalCompleted = baseTotalCompleted + pendingCountTotal + pendingBatch;
                    todayCompleted = baseTodayCompleted + pendingCountToday + pendingBatch;
                }

                if (todayCompleted < 0) todayCompleted = 0;
                if (totalCompleted < 0) totalCompleted = 0;

                updateDisplay();
                try {
                    localStorage.setItem('pwa_zikr_active_date', clientToday);
                } catch (_) {}
            } catch (e) {
                console.warn('reconcilePending error:', e);
            }
        };

        // Periodic 24-hour / midnight rollover check
        let lastTrackedDay = getLocalDateStr();
        const checkMidnightRollover = () => {
            const currentDay = getLocalDateStr();
            const currentRenderDate = container.dataset.renderDate || document.querySelector('meta[name="page-rendered-date"]')?.getAttribute('content');
            if (currentDay !== lastTrackedDay || (currentRenderDate && currentRenderDate < currentDay)) {
                lastTrackedDay = currentDay;
                container.dataset.renderDate = currentDay;
                baseTodayCompleted = 0;
                todayCompleted = 0;
                pendingBatch = 0;
                recalculateRequiredForDate(currentDay);
                reconcilePending();
            }
        };
        setInterval(checkMidnightRollover, 5000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') checkMidnightRollover();
        });
        window.addEventListener('focus', checkMidnightRollover);
        window.addEventListener('pageshow', checkMidnightRollover);

        reconcilePending();
        window.addEventListener('load', () => reconcilePending());
        window.addEventListener('pageshow', () => reconcilePending());
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') reconcilePending();
        });
        window.addEventListener('pwa:sync-completed', (e) => reconcilePending(e.detail?.data || null));

        function handleCounterBroadcast(data) {
            if (!data || !data.type) return;
            const myClientId = window.PWA_CLIENT_ID || (window.PwaSync && window.PwaSync.clientId);
            if (data.clientId && myClientId && data.clientId === myClientId) {
                return; // Never process broadcast triggered by this exact same tab
            }
            const currentTasbeehId = '{{ $tasbeeh->id }}';

            // Check day rollover before handling broadcast
            const currentDayNow = getLocalDateStr();
            const currentRenderDate = container.dataset.renderDate || document.querySelector('meta[name="page-rendered-date"]')?.getAttribute('content');
            if (currentDayNow !== lastTrackedDay || (currentRenderDate && currentRenderDate < currentDayNow)) {
                lastTrackedDay = currentDayNow;
                baseTodayCompleted = 0;
                todayCompleted = 0;
                container.dataset.renderDate = currentDayNow;
                recalculateRequiredForDate(currentDayNow);
            }

            if (data.type === 'ZIKR_COUNT_INCREMENT' && String(data.tasbeehId) === currentTasbeehId) {
                const delta = parseInt(data.delta, 10) || 0;
                if (delta !== 0) {
                    totalCompleted += delta;
                    todayCompleted += delta;
                    updateDisplay(true);
                }
            } else if (data.type === 'ZIKR_COMPLETE_TODAY' && String(data.tasbeehId) === currentTasbeehId) {
                const countToAdd = dailyTarget > 0 ? dailyTarget : 100;
                totalCompleted += countToAdd;
                todayCompleted += countToAdd;
                updateDisplay(true);
            } else if (data.type === 'ZIKR_COMPLETE_ALL') {
                const countToAdd = dailyTarget > 0 ? dailyTarget : 100;
                totalCompleted += countToAdd;
                todayCompleted += countToAdd;
                updateDisplay(true);
            } else if ((data.type === 'ZIKR_RESET_SINGLE' && String(data.tasbeehId) === currentTasbeehId) || data.type === 'ZIKR_RESET_ALL') {
                totalCompleted = 0;
                todayCompleted = 0;
                updateDisplay();
            }
        }

        // Listen for BroadcastChannel & storage events
        if ('BroadcastChannel' in window) {
            try {
                const zikrChannel = new BroadcastChannel('portfolio_zikr_channel');
                zikrChannel.onmessage = (event) => {
                    if (event.data && event.data.type) {
                        handleCounterBroadcast(event.data);
                    }
                };
            } catch (e) {}
        }

        window.addEventListener('storage', (event) => {
            if (event.key === 'pwa_zikr_live_broadcast' && event.newValue) {
                try {
                    const parsed = JSON.parse(event.newValue);
                    handleCounterBroadcast(parsed);
                } catch (e) {}
            }
        });

        // Cache this counter page dynamically into Service Worker Cache
        if ('caches' in window) {
            caches.keys().then(names => {
                const pwaCacheName = names.find(n => n.startsWith('portfolio-pwa-v'));
                if (pwaCacheName) {
                    caches.open(pwaCacheName).then(cache => {
                        cache.add(window.location.href).catch(() => {});
                        cache.add(window.location.pathname).catch(() => {});
                        cache.add('/admin/zikr').catch(() => {});
                        cache.add('/admin/tasbeehs').catch(() => {});
                    });
                }
            }).catch(() => {});
        }
    })();
</script>
@endpush
@endsection
