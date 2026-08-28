@extends('layouts.admin')
@section('title', 'Zikr & Tasbeeh')
@section('page_title', 'Zikr & Tasbeeh Dashboard')

@push('styles')
<style>
    /* Zikr Card UI Styles matching reference design */
    .zikr-card {
        background: #08111e;
        border: 1px solid #142845;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        width: 100%;
        padding: 24px;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .zikr-card:hover {
        border-color: #1c3d6c;
        box-shadow: 0 24px 48px rgba(0, 0, 0, 0.75);
    }

    .tasbeeh-icon-box {
        width: 48px;
        height: 48px;
        min-width: 48px;
        background: #111d2e;
        border: 1.5px solid #d97706;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f59e0b;
        font-size: 1.4rem;
        box-shadow: 0 0 16px rgba(217, 119, 6, 0.2);
    }

    .extra-badge {
        background: #00bcd4;
        color: #04121e;
        font-weight: 700;
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 0.85rem;
        box-shadow: 0 0 14px rgba(0, 188, 212, 0.35);
        display: inline-block;
        white-space: nowrap;
    }

    .arabic-box {
        background: #0c1626;
        border: 1px solid #1c2c44;
        border-radius: 18px;
        padding: 20px;
        text-align: center;
        position: relative;
    }

    .arabic-text {
        font-family: 'Scheherazade New', 'Amiri Quran', 'Amiri', 'PDMS_Saleem_QuranFont', '_PDMS_Saleem_Quran', 'Traditional Arabic', serif !important;
        font-feature-settings: "liga" 1, "cv01" 1;
        color: #f97316;
        font-size: 1.55rem;
        line-height: 2.0;
        direction: rtl;
        font-weight: 700;
    }

    .divider-box {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 12px 0;
        color: #d97706;
    }

    .divider-box::before, .divider-box::after {
        content: "";
        flex: 1;
        height: 1px;
        background: #1e3352;
    }

    .divider-box span {
        padding: 0 10px;
        font-size: 0.75rem;
        color: #d97706;
    }

    .urdu-text {
        font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Urdu Typesetting', serif !important;
        color: #94a3b8;
        font-size: 0.95rem;
        line-height: 2.1;
        direction: rtl;
        text-align: center;
    }

    .stat-card {
        background: #0c1626;
        border: 1px solid #18283f;
        border-radius: 14px;
        padding: 10px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .icon-blue { background: rgba(0, 188, 212, 0.1); color: #00bcd4; border: 1px solid #00bcd4; }
    .icon-green { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid #10b981; }
    .icon-cyan { background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border: 1px solid #0ea5e9; }
    .icon-amber { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid #f59e0b; }

    .progress-container {
        height: 6px;
        background: #122135;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar-custom {
        height: 100%;
        background: #00e5ff;
        border-radius: 10px;
        box-shadow: 0 0 10px #00e5ff;
        transition: width 0.4s ease;
    }

    .btn-open {
        background: linear-gradient(90deg, #ea580c 0%, #f97316 100%);
        color: #ffffff !important;
        font-weight: 600;
        border: none;
        border-radius: 12px;
        padding: 10px 18px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-open:hover {
        background: linear-gradient(90deg, #c2410c 0%, #ea580c 100%);
        color: #ffffff !important;
        box-shadow: 0 4px 16px rgba(249, 115, 22, 0.4);
        transform: translateY(-1px);
    }

    .btn-quick {
        background: #0a1728;
        border: 1px solid #162a47;
        color: #00bcd4;
        font-weight: 600;
        border-radius: 12px;
        padding: 10px 18px;
        transition: all 0.2s ease;
    }

    .btn-quick:hover {
        background: #10223b;
        color: #22d3ee;
        border-color: #00bcd4;
        box-shadow: 0 4px 16px rgba(0, 188, 212, 0.25);
        transform: translateY(-1px);
    }

    .btn-reset {
        background: #0a1728;
        border: 1px solid #162a47;
        color: #f97316;
        border-radius: 12px;
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.2s ease;
    }

    .btn-reset:hover {
        background: #10223b;
        color: #ea580c;
        border-color: #f97316;
        box-shadow: 0 4px 16px rgba(249, 115, 22, 0.25);
        transform: translateY(-1px);
    }

    /* Responsive adjustments */
    @media (max-width: 575.98px) {
        .zikr-card {
            padding: 18px 14px;
            border-radius: 18px;
        }
        .arabic-text {
            font-size: 1.6rem;
            line-height: 1.7;
        }
        .urdu-text {
            font-size: 0.88rem;
            line-height: 2.0;
        }
        .stat-card {
            padding: 8px 6px;
            gap: 6px;
        }
        .stat-icon {
            width: 30px;
            height: 30px;
            min-width: 30px;
            font-size: 0.95rem;
        }
        .btn-open, .btn-quick {
            padding: 9px 10px;
            font-size: 0.84rem;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <span class="eyebrow mb-0">Islamic & Deen</span>
        <h2 class="h4 mb-0">Daily Zikr / Tasbeeh Tracking</h2>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->can('manage tasbeeh'))
            <a class="btn btn-nav-action cyan btn-sm" href="{{ route('admin.tasbeehs.index') }}">
                <i class="bi bi-card-checklist text-info"></i><span>Manage Tasbeehs</span>
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
    {{-- Person Filter Toolbar (for Admins) --}}
    @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->can('manage tasbeeh'))
        <div class="admin-card mb-4 p-3" style="background: #08111e; border: 1px solid #142845; border-radius: 18px;">
            <form class="d-flex flex-wrap align-items-center justify-content-between gap-3" method="GET" action="{{ route('admin.zikr.index') }}">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 380px;">
                    <label class="form-label mb-0 small text-muted-custom fw-bold text-nowrap">Muslim Person:</label>
                    <select class="form-select form-select-sm" name="user_id" onchange="this.form.submit()" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        @foreach($muslimUsers as $user)
                            <option value="{{ $user->id }}" @selected($selectedUser && $selectedUser->id === $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="text-muted-custom small">
                    Tracking for: <strong class="text-white">{{ $selectedUser->name }}</strong>
                </div>
            </form>
        </div>
    @endif

    {{-- Top Overall Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Daily Target</span>
                <strong class="fs-2 text-white d-block my-1 font-monospace">{{ number_format($summary['overall_today_required']) }}</strong>
                <small class="text-muted-custom d-block">Across {{ $summary['total_active_tasbeehs'] }} Tasbeehs</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Total Required</span>
                <strong class="fs-2 text-info d-block my-1 font-monospace">{{ number_format($summary['overall_total_required']) }}</strong>
                <small class="text-muted-custom d-block">Cumulative till today</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="zikr-stat-card p-3 rounded-4 border h-100" style="background: #08111e; border-color: #142845;">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Total Completed</span>
                <strong class="fs-2 text-success d-block my-1 font-monospace">{{ number_format($summary['overall_total_completed']) }}</strong>
                <small class="text-success d-block fw-semibold">{{ $summary['overall_percentage'] }}% Completed</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
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
    <div class="row g-4" id="tasbeeh-list-container">
        @forelse($summary['tasbeehs'] as $item)
            <div class="col-12 col-xl-6" id="tasbeeh-card-{{ $item['tasbeeh_id'] }}">
                <div class="zikr-card h-100 d-flex flex-column justify-content-between position-relative">
                    <div>
                        {{-- Top Header --}}
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                                <div class="tasbeeh-icon-box flex-shrink-0">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="4" r="1.3" fill="currentColor"/>
                                        <circle cx="16.5" cy="5.5" r="1.3" fill="currentColor"/>
                                        <circle cx="19.5" cy="9.5" r="1.3" fill="currentColor"/>
                                        <circle cx="19.5" cy="14.5" r="1.3" fill="currentColor"/>
                                        <circle cx="16.5" cy="18.5" r="1.3" fill="currentColor"/>
                                        <circle cx="12" cy="19.8" r="1.6" fill="currentColor"/>
                                        <circle cx="7.5" cy="18.5" r="1.3" fill="currentColor"/>
                                        <circle cx="4.5" cy="14.5" r="1.3" fill="currentColor"/>
                                        <circle cx="4.5" cy="9.5" r="1.3" fill="currentColor"/>
                                        <circle cx="7.5" cy="5.5" r="1.3" fill="currentColor"/>
                                        <path d="M12 21.4v2M10.5 23.4h3"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h6 class="mb-1 fw-bold text-white fs-6 text-truncate">{{ $item['title'] }}</h6>
                                    <div class="text-secondary" style="font-size: 0.8rem; line-height: 1.4;">
                                        <span class="text-nowrap">Target: <strong class="text-info font-monospace">{{ number_format($item['daily_target']) }}</strong>/day</span>
                                        <span class="mx-1 text-secondary opacity-50">•</span>
                                        <span class="text-nowrap">Started: <strong class="text-info">{{ $item['formatted_start_date'] }}</strong></span>
                                        <span class="text-nowrap text-secondary opacity-75">({{ $item['active_days'] }} {{ Str::plural('day', $item['active_days']) }})</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0 align-self-start">
                                @if($item['extra'] > 0)
                                    <span class="extra-badge font-monospace">+{{ number_format($item['extra']) }} Extra</span>
                                @elseif($item['remaining'] === 0)
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill font-monospace fw-bold" style="font-size: 0.8rem;">Target Completed</span>
                                @else
                                    <span class="badge rounded-pill font-monospace fw-bold px-3 py-2" style="font-size: 0.8rem; background: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.45) !important; white-space: nowrap;">{{ number_format($item['remaining']) }} Remaining</span>
                                @endif
                            </div>
                        </div>

                        {{-- Arabic & Urdu Box --}}
                        <div class="arabic-box mb-3">
                            <div class="arabic-text">
                                {!! nl2br(e($item['arabic_text'])) !!}
                            </div>
                            <div class="divider-box">
                                <span>✦</span>
                            </div>
                            <div class="urdu-text">
                                {!! nl2br(e($item['urdu_meaning'] ?? '—')) !!}
                            </div>
                        </div>

                        {{-- Stats 3-Column Grid --}}
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-icon icon-blue"><i class="bi bi-bullseye"></i></div>
                                    <div class="text-start">
                                        <span class="d-block text-secondary" style="font-size: 0.7rem;">Required</span>
                                        <strong class="fs-6 text-white font-monospace">{{ number_format($item['total_required']) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-icon icon-green"><i class="bi bi-check2-circle"></i></div>
                                    <div class="text-start">
                                        <span class="d-block text-secondary" style="font-size: 0.7rem;">Completed</span>
                                        <strong class="fs-6 text-success font-monospace">{{ number_format($item['total_completed']) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card">
                                    @if($item['extra'] > 0)
                                        <div class="stat-icon icon-cyan"><i class="bi bi-star"></i></div>
                                        <div class="text-start">
                                            <span class="d-block text-secondary" style="font-size: 0.7rem;">Extra</span>
                                            <strong class="fs-6 text-info font-monospace">+{{ number_format($item['extra']) }}</strong>
                                        </div>
                                    @elseif($item['remaining'] > 0)
                                        <div class="stat-icon icon-amber"><i class="bi bi-hourglass-split"></i></div>
                                        <div class="text-start">
                                            <span class="d-block text-secondary" style="font-size: 0.7rem;">Remaining</span>
                                            <strong class="fs-6 text-warning font-monospace">{{ number_format($item['remaining']) }}</strong>
                                        </div>
                                    @else
                                        <div class="stat-icon icon-green"><i class="bi bi-patch-check"></i></div>
                                        <div class="text-start">
                                            <span class="d-block text-secondary" style="font-size: 0.7rem;">Status</span>
                                            <strong class="fs-6 text-success font-monospace">Done</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Cycle Progress Bar --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1 text-secondary" style="font-size: 0.8rem;">
                                <span><i class="bi bi-bar-chart-fill me-1"></i> Cycle Progress</span>
                                <span class="fw-bold text-light font-monospace">{{ $item['percentage'] }}%</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar-custom" style="width: {{ $item['percentage'] }}%;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Action Buttons --}}
                    <div class="d-flex align-items-center gap-2 mt-auto">
                        <a href="{{ route('admin.zikr.counter.show', ['tasbeeh' => $item['tasbeeh_id'], 'user_id' => $selectedUser->id]) }}" class="btn btn-open flex-grow-1 d-flex align-items-center justify-content-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="10" r="6"/>
                                <path d="M12 16v5M10 21h4"/>
                            </svg>
                            <span>Open Counter</span>
                        </a>

                        <button
                            type="button"
                            class="btn btn-quick flex-grow-1 d-flex align-items-center justify-content-center gap-1"
                            data-bs-toggle="modal"
                            data-bs-target="#quickAddModal"
                            data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                            data-tasbeeh-title="{{ $item['title'] }}"
                            data-user-id="{{ $selectedUser->id }}"
                            data-post-url="{{ route('admin.zikr.counter.manual', $item['tasbeeh_id']) }}"
                            title="Add Zikr Manually"
                        >
                            <i class="bi bi-plus-lg"></i>
                            <span>Quick Add</span>
                        </button>

                        <button
                            type="button"
                            class="btn btn-reset flex-shrink-0"
                            data-bs-toggle="modal"
                            data-bs-target="#resetTasbeehModal"
                            data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                            data-tasbeeh-title="{{ $item['title'] }}"
                            data-user-id="{{ $selectedUser->id }}"
                            data-reset-url="{{ route('admin.zikr.counter.reset', $item['tasbeeh_id']) }}"
                            title="Reset Tracking for this Tasbeeh"
                        >
                            <i class="bi bi-arrow-counterclockwise fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted-custom">
                <i class="bi bi-gem fs-1 text-accent"></i>
                <h5 class="mt-3 mb-1">No Active Tasbeehs Found</h5>
                <p class="mb-0">Please add or activate Tasbeeh master definitions in Manage Tasbeehs.</p>
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
                        <input class="form-control form-control-lg text-center font-monospace fw-bold" type="number" name="count" id="quickAddCountInput" min="-1000000" max="1000000" placeholder="e.g. 100 or -33" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
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
@endsection
