@extends('layouts.admin')
@section('title', $tasbeeh->title . ' — Live Counter')
@section('page_title', 'Live Zikr Counter')

@section('content')
{{-- Navigation Header Bar --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.zikr.index', ['user_id' => $user->id]) }}" class="btn btn-nav-action cyan btn-sm py-1 px-3">
            <i class="bi bi-arrow-left text-info"></i><span>Back to Dashboard</span>
        </a>
        <div>
            <h2 class="h5 fw-bold mb-0">{{ $tasbeeh->title }}</h2>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button class="btn btn-nav-action btn-sm py-1 px-3" data-bs-toggle="modal" data-bs-target="#changeStartDateModal">
            <i class="bi bi-calendar-event text-info"></i><span>Start Date: {{ $stats['formatted_start_date'] }}</span>
        </button>
        <button class="btn btn-outline-danger btn-sm py-1 px-3" data-bs-toggle="modal" data-bs-target="#resetTasbeehModal">
            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
        </button>
    </div>
</div>

{{-- Single Line Comprehensive Summary Ribbon --}}
<div class="admin-card mb-3 p-2 px-3 rounded-4 border bg-surface">
    <div class="d-flex flex-wrap flex-md-nowrap align-items-center justify-content-between gap-2 text-nowrap">
        <div class="zikr-pill pill-target">
            <i class="bi bi-bullseye text-warning"></i>
            <span class="pill-label">Target:</span>
            <span class="pill-value">{{ number_format($stats['daily_target']) }}/day</span>
        </div>
        <div class="zikr-pill pill-started">
            <i class="bi bi-calendar-check"></i>
            <span class="pill-label">Started:</span>
            <span class="pill-value">{{ $stats['formatted_start_date'] }} ({{ $stats['active_days'] }} {{ Str::plural('day', $stats['active_days']) }})</span>
        </div>
        <div class="zikr-pill pill-required">
            <span class="pill-label">Required:</span>
            <span class="pill-value" id="statTotalRequired">{{ number_format($stats['total_required']) }}</span>
        </div>
        <div class="zikr-pill pill-completed">
            <span class="pill-label">Completed:</span>
            <span class="pill-value" id="statTotalCompleted">{{ number_format($stats['total_completed']) }}</span>
        </div>
        <div class="zikr-pill {{ $stats['extra'] > 0 ? 'pill-extra' : ($stats['remaining'] > 0 ? 'pill-remaining' : 'pill-done') }}" id="statBacklogBadge">
            <span class="pill-label" id="statBacklogPrefix">{{ $stats['extra'] > 0 ? 'Extra' : 'Remaining' }}:</span>
            <span class="pill-value" id="statBacklog">{{ $stats['extra'] > 0 ? '+' . number_format($stats['extra']) : number_format($stats['remaining']) }}</span>
        </div>
    </div>
</div>

<div class="row g-3 align-items-stretch">
    {{-- Left Column: Arabic & Urdu Card --}}
    <div class="col-12 col-lg-7 d-flex flex-column">
        <div class="admin-card p-3 rounded-4 shadow-sm border h-100 d-flex flex-column justify-content-between">
            <div class="arabic-hero-card p-3 rounded-3 bg-surface-2 border text-center my-auto" dir="rtl">
                <div class="arabic-script fw-bold text-accent mb-2" style="font-size: 1.75rem; line-height: 1.8; font-family: 'Amiri', 'Traditional Arabic', 'Scheherazade New', 'Lateef', serif;">
                    {{ $tasbeeh->arabic_text }}
                </div>
                @if($tasbeeh->urdu_meaning)
                    <div class="urdu-translation pt-2 border-top text-muted-custom small" style="font-size: 1rem; line-height: 1.6;">
                        {{ $tasbeeh->urdu_meaning }}
                    </div>
                @endif
            </div>

            @if($tasbeeh->description || $tasbeeh->reference)
                <div class="mt-2 pt-2 d-flex flex-wrap align-items-center justify-content-between gap-2 text-muted-custom small px-1 border-top" style="font-size: 0.75rem;">
                    @if($tasbeeh->description)
                        <span><i class="bi bi-info-circle me-1"></i>{{ $tasbeeh->description }}</span>
                    @endif
                    @if($tasbeeh->reference)
                        <span class="badge bg-surface-2 text-muted-custom border"><i class="bi bi-book me-1"></i>{{ $tasbeeh->reference }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Right Column: Live Tap Counter & Manual Entry --}}
    <div class="col-12 col-lg-5 d-flex flex-column">
        <div class="admin-card p-3 rounded-4 shadow-sm border text-center h-100 d-flex flex-column justify-content-between">
            {{-- Compact Live Number Box --}}
            <div class="p-3 rounded-3 bg-surface-2 border mb-3">
                <div class="d-flex align-items-center justify-content-between text-muted-custom small font-monospace mb-1" style="font-size: 0.72rem;">
                    <span>TOTAL COMPLETED</span>
                    <span>Target: {{ number_format($stats['total_required']) }}</span>
                </div>
                <div class="fs-1 fw-bolder text-accent font-monospace counter-huge-number py-1" id="liveCounterNumber" style="font-size: 2.6rem !important; line-height: 1.0; letter-spacing: -0.02em;">
                    {{ number_format($stats['total_completed']) }}
                </div>
                <div class="progress mt-2" style="height: 5px; background: var(--line); border-radius: 3px;">
                    <div class="progress-bar {{ $stats['extra'] > 0 ? 'bg-info' : ($stats['remaining'] === 0 ? 'bg-success' : 'bg-warning') }}"
                         id="liveProgressBar"
                         role="progressbar"
                         style="width: {{ $stats['percentage'] }}%; transition: width 0.3s ease;"
                         aria-valuenow="{{ $stats['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>

            {{-- Compact Interactive Tap Button --}}
            <div class="d-flex justify-content-center my-1">
                <button
                    type="button"
                    class="btn-live-zikr-tap rounded-circle shadow-lg d-flex flex-column align-items-center justify-content-center cursor-pointer position-relative border-0"
                    id="liveTapButton"
                    data-increment-url="{{ route('admin.zikr.counter.increment', $tasbeeh) }}"
                    data-user-id="{{ $user->id }}"
                    aria-label="Add one zikr"
                >
                    <i class="bi bi-plus-lg fs-2 text-white mb-0"></i>
                    <span class="font-monospace fw-bold text-white opacity-75" style="font-size: 0.72rem; letter-spacing: 0.05em;">TAP +1</span>
                </button>
            </div>

            {{-- Compact Manual Count Section --}}
            <div class="mt-3 pt-2 border-top text-start">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 flex-fill font-monospace" style="font-size: 0.76rem;" onclick="submitManualCount(33)">+33</button>
                    <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 flex-fill font-monospace" style="font-size: 0.76rem;" onclick="submitManualCount(100)">+100</button>
                    <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 flex-fill font-monospace" style="font-size: 0.76rem;" onclick="submitManualCount(300)">+300</button>
                    <button type="button" class="btn btn-outline-theme btn-sm py-0 px-2 flex-fill font-monospace" style="font-size: 0.76rem;" onclick="submitManualCount(1000)">+1,000</button>
                </div>

                <form id="manualCountForm" class="d-flex gap-2" method="POST" action="{{ route('admin.zikr.counter.manual', $tasbeeh) }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input class="form-control form-control-sm text-center font-monospace" type="number" name="count" id="manualCountInput" min="-1000000" max="1000000" placeholder="Custom (e.g. 200 or -33)" required>
                    <button class="btn btn-accent btn-sm text-nowrap px-3" type="submit" id="manualCountSubmitBtn">
                        <i class="bi bi-plus-circle me-1"></i>Add
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Change Start Date Modal --}}
<div class="modal fade finance-modal" id="changeStartDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content">
            <form id="changeStartDateForm" method="POST" action="{{ route('admin.zikr.counter.start-date', $tasbeeh) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">Change Tracking Start Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted-custom small mb-3">
                        Total required count will be calculated from this date to today inclusive.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tracking Start Date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="tracking_start_date" value="{{ $stats['tracking_start_date'] }}" required>
                    </div>
                </div>
                <div class="modal-footer">
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
        <div class="modal-content">
            <form id="resetDetailForm" method="POST" action="{{ route('admin.zikr.counter.reset', $tasbeeh) }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title text-danger mb-0">Reset Tracking?</h5>
                        <p class="text-muted-custom small mb-0 mt-1">{{ $tasbeeh->title }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                        <div class="small">
                            <strong>Note:</strong> Resetting will set your completed count for <strong>this Tasbeeh only</strong> to 0 and restart your tracking cycle from today. Other Tasbeehs will remain completely untouched.
                        </div>
                    </div>
                    <p class="text-muted-custom small mb-0">Are you sure you want to start a new tracking cycle?</p>
                </div>
                <div class="modal-footer">
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
