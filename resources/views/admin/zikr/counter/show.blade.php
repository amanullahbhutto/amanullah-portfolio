@extends('layouts.admin')
@section('title', $tasbeeh->title . ' — Live Counter')
@section('page_title', 'Live Zikr Counter')

@push('styles')
<style>
    /* Live Counter View Custom Styling */
    .counter-hero-card {
        background: #08111e;
        border: 1px solid #142845;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        padding: 24px;
    }

    .arabic-live-box {
        background: #0c1626;
        border: 1px solid #1c2c44;
        border-radius: 18px;
        padding: 24px 20px;
        text-align: center;
        position: relative;
    }

    .arabic-live-text {
        font-family: 'Scheherazade New', 'Amiri Quran', 'Amiri', 'PDMS_Saleem_QuranFont', '_PDMS_Saleem_Quran', 'Traditional Arabic', serif !important;
        font-feature-settings: "liga" 1, "cv01" 1;
        color: #f97316;
        font-size: 1.65rem;
        line-height: 2.0;
        direction: rtl;
        font-weight: 700;
        text-shadow: 0 0 2px rgba(249, 115, 22, 0.25);
    }

    .divider-box {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 14px 0;
        color: #d97706;
    }

    .divider-box::before, .divider-box::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #1e3352;
    }

    .divider-box span {
        padding: 0 12px;
        font-size: 0.8rem;
        color: #d97706;
    }

    .urdu-live-text {
        font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Urdu Typesetting', serif !important;
        color: #94a3b8;
        font-size: 1rem;
        line-height: 2.1;
        direction: rtl;
        text-align: center;
    }

    .counter-stat-box {
        background: #0c1626;
        border: 1px solid #18283f;
        border-radius: 18px;
        padding: 16px 20px;
    }

    .counter-huge-number {
        font-size: clamp(2.4rem, 6vw, 3.4rem) !important;
        line-height: 1.0;
        color: #f97316 !important;
        font-weight: 800;
        text-shadow: 0 0 25px rgba(249, 115, 22, 0.35);
        transition: transform 0.12s ease;
    }

    .counter-huge-number.number-bump {
        animation: numberBump 0.18s ease-out;
    }

    @keyframes numberBump {
        0% { transform: scale(1.0); }
        50% { transform: scale(1.09); }
        100% { transform: scale(1.0); }
    }

    .btn-live-zikr-tap {
        width: 110px !important;
        height: 110px !important;
        background: linear-gradient(135deg, #ea580c 0%, #f97316 100%) !important;
        box-shadow: 0 10px 30px rgba(249, 115, 22, 0.50) !important;
        border: 3px solid rgba(255, 255, 255, 0.3) !important;
        transition: transform 0.12s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.12s cubic-bezier(0.4, 0, 0.2, 1) !important;
        cursor: pointer;
    }

    .btn-live-zikr-tap:hover {
        box-shadow: 0 14px 38px rgba(249, 115, 22, 0.65) !important;
        transform: scale(1.03);
    }

    .btn-live-zikr-tap:active {
        transform: scale(0.92) !important;
        box-shadow: 0 4px 14px rgba(249, 115, 22, 0.8) !important;
    }

    .btn-live-zikr-tap.tap-pulse {
        animation: tapPulseAnimation 0.25s ease-out;
    }

    @keyframes tapPulseAnimation {
        0% { transform: scale(1.0); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1.0); }
    }

    .preset-btn {
        background: #0a1728;
        border: 1px solid #162a47;
        color: #00bcd4;
        font-weight: 700;
        border-radius: 10px;
        padding: 6px 12px;
        transition: all 0.2s ease;
    }

    .preset-btn:hover {
        background: #10223b;
        color: #22d3ee;
        border-color: #00bcd4;
        box-shadow: 0 3px 12px rgba(0, 188, 212, 0.25);
    }

    .progress-container-live {
        height: 6px;
        background: #122135;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar-custom-live {
        height: 100%;
        background: #00e5ff;
        border-radius: 10px;
        box-shadow: 0 0 10px #00e5ff;
        transition: width 0.4s ease;
    }
</style>
@endpush

@section('content')
{{-- Navigation Header Bar --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('admin.zikr.index', ['user_id' => $user->id]) }}" class="btn btn-nav-action cyan btn-sm">
            <i class="bi bi-arrow-left"></i><span>Back to Dashboard</span>
        </a>
        <div>
            <h2 class="h4 fw-bold mb-0 text-white">{{ $tasbeeh->title }}</h2>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button class="btn btn-nav-action btn-sm" data-bs-toggle="modal" data-bs-target="#changeStartDateModal">
            <i class="bi bi-calendar-event text-info"></i><span>Start Date: {{ $stats['formatted_start_date'] }}</span>
        </button>
        <button class="btn btn-nav-action amber btn-sm" data-bs-toggle="modal" data-bs-target="#resetTasbeehModal">
            <i class="bi bi-arrow-counterclockwise"></i><span>Reset</span>
        </button>
    </div>
</div>

{{-- Single Line Comprehensive Summary Ribbon --}}
<div class="admin-card mb-4 p-2 px-3 rounded-4 border" style="background: #08111e; border-color: #142845;">
    <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-between gap-2 text-nowrap">
        <div class="zikr-pill pill-target">
            <i class="bi bi-bullseye text-warning"></i>
            <span class="pill-label">Target:</span>
            <span class="pill-value font-monospace">{{ number_format($stats['daily_target']) }}/day</span>
        </div>
        <div class="zikr-pill pill-started">
            <i class="bi bi-calendar-check"></i>
            <span class="pill-label">Started:</span>
            <span class="pill-value font-monospace">{{ $stats['formatted_start_date'] }} ({{ $stats['active_days'] }} {{ Str::plural('day', $stats['active_days']) }})</span>
        </div>
        <div class="zikr-pill pill-required">
            <span class="pill-label">Required:</span>
            <span class="pill-value font-monospace" id="statTotalRequired">{{ number_format($stats['total_required']) }}</span>
        </div>
        <div class="zikr-pill pill-completed">
            <span class="pill-label">Completed:</span>
            <span class="pill-value font-monospace text-success" id="statTotalCompleted">{{ number_format($stats['total_completed']) }}</span>
        </div>
        <div class="zikr-pill {{ $stats['extra'] > 0 ? 'pill-extra' : ($stats['remaining'] > 0 ? 'pill-remaining' : 'pill-done') }}" id="statBacklogBadge">
            <span class="pill-label" id="statBacklogPrefix">{{ $stats['extra'] > 0 ? 'Extra' : 'Remaining' }}:</span>
            <span class="pill-value font-monospace" id="statBacklog">{{ $stats['extra'] > 0 ? '+' . number_format($stats['extra']) : number_format($stats['remaining']) }}</span>
        </div>
    </div>
</div>

<div class="row g-4 align-items-stretch">
    {{-- Left Column: Arabic & Urdu Card --}}
    <div class="col-12 col-lg-7 d-flex flex-column">
        <div class="counter-hero-card h-100 d-flex flex-column justify-content-between">
            <div class="arabic-live-box my-auto">
                <div class="arabic-live-text">
                    {!! nl2br(e($tasbeeh->arabic_text)) !!}
                </div>
                <div class="divider-box">
                    <span>✦</span>
                </div>
                @if($tasbeeh->urdu_meaning)
                    <div class="urdu-live-text">
                        {!! nl2br(e($tasbeeh->urdu_meaning)) !!}
                    </div>
                @endif
            </div>

            @if($tasbeeh->description || $tasbeeh->reference)
                <div class="mt-3 pt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 text-muted-custom small px-2 border-top border-secondary border-opacity-25" style="font-size: 0.8rem;">
                    @if($tasbeeh->description)
                        <span><i class="bi bi-info-circle me-1 text-info"></i>{{ $tasbeeh->description }}</span>
                    @endif
                    @if($tasbeeh->reference)
                        <span class="badge bg-surface-2 text-muted-custom border border-secondary border-opacity-25"><i class="bi bi-book me-1 text-warning"></i>{{ $tasbeeh->reference }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Right Column: Live Tap Counter & Manual Entry --}}
    <div class="col-12 col-lg-5 d-flex flex-column">
        <div class="counter-hero-card text-center h-100 d-flex flex-column justify-content-between">
            {{-- Compact Live Number Box --}}
            <div class="counter-stat-box mb-3">
                <div class="d-flex align-items-center justify-content-between text-muted-custom small font-monospace mb-1" style="font-size: 0.75rem;">
                    <span>TOTAL COMPLETED</span>
                    <span>Target: <strong class="text-white">{{ number_format($stats['total_required']) }}</strong></span>
                </div>
                <div class="counter-huge-number font-monospace py-2" id="liveCounterNumber">
                    {{ number_format($stats['total_completed']) }}
                </div>
                <div class="progress-container-live mt-2">
                    <div class="progress-bar-custom-live"
                         id="liveProgressBar"
                         style="width: {{ $stats['percentage'] }}%;">
                    </div>
                </div>
            </div>

            {{-- Compact Interactive Tap Button --}}
            <div class="d-flex justify-content-center my-2">
                <button
                    type="button"
                    class="btn-live-zikr-tap rounded-circle shadow-lg d-flex flex-column align-items-center justify-content-center cursor-pointer position-relative border-0"
                    id="liveTapButton"
                    data-increment-url="{{ route('admin.zikr.counter.increment', $tasbeeh) }}"
                    data-user-id="{{ $user->id }}"
                    aria-label="Add one zikr"
                >
                    <i class="bi bi-plus-lg fs-2 text-white mb-0"></i>
                    <span class="font-monospace fw-bold text-white opacity-75" style="font-size: 0.75rem; letter-spacing: 0.05em;">TAP +1</span>
                </button>
            </div>

            {{-- Compact Manual Count Section --}}
            <div class="mt-3 pt-3 border-top border-secondary border-opacity-25 text-start">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <button type="button" class="preset-btn flex-fill font-monospace" onclick="submitManualCount(33)">+33</button>
                    <button type="button" class="preset-btn flex-fill font-monospace" onclick="submitManualCount(100)">+100</button>
                    <button type="button" class="preset-btn flex-fill font-monospace" onclick="submitManualCount(300)">+300</button>
                    <button type="button" class="preset-btn flex-fill font-monospace" onclick="submitManualCount(1000)">+1,000</button>
                </div>

                <form id="manualCountForm" class="d-flex gap-2 mt-2" method="POST" action="{{ route('admin.zikr.counter.manual', $tasbeeh) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input class="form-control text-center font-monospace fw-bold" type="number" name="count" id="manualCountInput" min="-1000000" max="1000000" placeholder="Custom (e.g. 200 or -33)" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                    <button class="btn btn-accent text-nowrap px-4 d-flex align-items-center gap-1" type="submit" id="manualCountSubmitBtn">
                        <i class="bi bi-plus-circle"></i><span>Add</span>
                    </button>
                </form>
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
@endsection
