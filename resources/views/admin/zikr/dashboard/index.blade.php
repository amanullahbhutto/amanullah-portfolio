@extends('layouts.admin')
@section('title', 'Zikr & Tasbeeh')
@section('page_title', 'Zikr & Tasbeeh Dashboard')

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
        <div class="admin-card mb-4 p-3">
            <form class="d-flex flex-wrap align-items-center justify-content-between gap-3" method="GET" action="{{ route('admin.zikr.index') }}">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 380px;">
                    <label class="form-label mb-0 small text-muted-custom fw-bold text-nowrap">Muslim Person:</label>
                    <select class="form-select form-select-sm" name="user_id" onchange="this.form.submit()">
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
            <div class="zikr-stat-card p-3 rounded-4 border bg-surface h-100">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Daily Target</span>
                <strong class="fs-2 text-white d-block my-1 font-monospace">{{ number_format($summary['overall_today_required']) }}</strong>
                <small class="text-muted-custom d-block">Across {{ $summary['total_active_tasbeehs'] }} Tasbeehs</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="zikr-stat-card p-3 rounded-4 border bg-surface h-100">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Total Required</span>
                <strong class="fs-2 text-info d-block my-1 font-monospace">{{ number_format($summary['overall_total_required']) }}</strong>
                <small class="text-muted-custom d-block">Cumulative till today</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="zikr-stat-card p-3 rounded-4 border bg-surface h-100">
                <span class="text-muted-custom small fw-bold text-uppercase d-block mb-1">Total Completed</span>
                <strong class="fs-2 text-success d-block my-1 font-monospace">{{ number_format($summary['overall_total_completed']) }}</strong>
                <small class="text-success d-block fw-semibold">{{ $summary['overall_percentage'] }}% Completed</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="zikr-stat-card p-3 rounded-4 border bg-surface h-100">
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
            <div class="col-12 col-lg-6" id="tasbeeh-card-{{ $item['tasbeeh_id'] }}">
                <div class="admin-card h-100 d-flex flex-column p-4 rounded-4 shadow-sm border position-relative">
                    {{-- Card Header: Title & Status Badge --}}
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <h3 class="h5 fw-bold mb-1">{{ $item['title'] }}</h3>
                            <span class="text-muted-custom small">
                                Target: <strong class="text-white">{{ number_format($item['daily_target']) }} / day</strong> • Started: <strong>{{ $item['formatted_start_date'] }}</strong> ({{ $item['active_days'] }} {{ Str::plural('day', $item['active_days']) }})
                            </span>
                        </div>
                        <span class="badge {{ $item['status_badge'] }} px-3 py-2 rounded-pill font-monospace fw-bold" style="font-size: 0.8rem;">
                            {{ $item['status_label'] }}
                        </span>
                    </div>

                    {{-- Arabic Text Box (RTL) --}}
                    <div class="arabic-text-box p-3 rounded-3 mb-3 bg-surface-2 border text-center" dir="rtl">
                        <div class="arabic-script fw-bold text-accent" style="font-size: 1.45rem; line-height: 1.8; font-family: 'Amiri', 'Traditional Arabic', 'Scheherazade New', 'Lateef', serif;">
                            {{ $item['arabic_text'] }}
                        </div>
                        @if($item['urdu_meaning'])
                            <div class="urdu-translation mt-2 pt-2 border-top text-muted-custom small" style="line-height: 1.6; font-size: 0.92rem;">
                                {{ $item['urdu_meaning'] }}
                            </div>
                        @endif
                    </div>

                    {{-- Progress & Metrics Breakdown --}}
                    <div class="metrics-grid row g-2 mb-3 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-surface-2 border">
                                <span class="d-block text-muted-custom small" style="font-size: 0.7rem;">Required</span>
                                <strong class="fs-6 text-white">{{ number_format($item['total_required']) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-surface-2 border">
                                <span class="d-block text-muted-custom small" style="font-size: 0.7rem;">Completed</span>
                                <strong class="fs-6 text-success">{{ number_format($item['total_completed']) }}</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-surface-2 border">
                                <span class="d-block text-muted-custom small" style="font-size: 0.7rem;">{{ $item['extra'] > 0 ? 'Extra' : 'Remaining' }}</span>
                                <strong class="fs-6 {{ $item['extra'] > 0 ? 'text-info' : ($item['remaining'] > 0 ? 'text-warning' : 'text-success') }}">
                                    {{ $item['extra'] > 0 ? '+' . number_format($item['extra']) : number_format($item['remaining']) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center small mb-1">
                            <span class="text-muted-custom">Cycle Progress</span>
                            <span class="fw-bold text-white">{{ $item['percentage'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px; background: var(--line);">
                            <div class="progress-bar {{ $item['extra'] > 0 ? 'bg-info' : ($item['remaining'] === 0 ? 'bg-success' : 'bg-warning') }}"
                                 role="progressbar"
                                 style="width: {{ $item['percentage'] }}%; transition: width 0.4s ease;"
                                 aria-valuenow="{{ $item['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="mt-auto d-flex align-items-center justify-content-between gap-2 pt-2 border-top">
                        <a href="{{ route('admin.zikr.counter.show', ['tasbeeh' => $item['tasbeeh_id'], 'user_id' => $selectedUser->id]) }}" class="btn btn-accent btn-sm d-flex align-items-center gap-1 px-3">
                            <i class="bi bi-disc"></i>
                            <span>Open Counter</span>
                        </a>

                        <div class="d-flex align-items-center gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-theme btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#quickAddModal"
                                data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                                data-tasbeeh-title="{{ $item['title'] }}"
                                data-user-id="{{ $selectedUser->id }}"
                                data-post-url="{{ route('admin.zikr.counter.manual', $item['tasbeeh_id']) }}"
                                title="Add Zikr Manually"
                            >
                                <i class="bi bi-plus-lg me-1"></i>Quick Add
                            </button>

                            <button
                                type="button"
                                class="btn btn-icon danger btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#resetTasbeehModal"
                                data-tasbeeh-id="{{ $item['tasbeeh_id'] }}"
                                data-tasbeeh-title="{{ $item['title'] }}"
                                data-user-id="{{ $selectedUser->id }}"
                                data-reset-url="{{ route('admin.zikr.counter.reset', $item['tasbeeh_id']) }}"
                                title="Reset Tracking for this Tasbeeh"
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
                <p class="mb-0">Please add or activate Tasbeeh master definitions in Manage Tasbeehs.</p>
            </div>
        @endforelse
    </div>
@endif

{{-- Quick Add Count Modal --}}
<div class="modal fade finance-modal" id="quickAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content">
            <form id="quickAddForm" method="POST" action="">
                @csrf
                <input type="hidden" name="user_id" id="quickAddUserId">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Add Zikr Count</h5>
                        <p class="text-muted-custom small mb-0 mt-1" id="quickAddTasbeehTitle">Tasbeeh</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <label class="form-label fw-bold">Enter Count <span class="text-danger">*</span></label>
                        <input class="form-control form-control-lg text-center font-monospace fw-bold" type="number" name="count" id="quickAddCountInput" min="-1000000" max="1000000" placeholder="e.g. 100 or -33" required>
                    </div>
                </div>
                <div class="modal-footer">
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
        <div class="modal-content">
            <form id="resetTasbeehForm" method="POST" action="">
                @csrf
                <input type="hidden" name="user_id" id="resetTasbeehUserId">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title text-danger mb-0">Reset Tracking?</h5>
                        <p class="text-muted-custom small mb-0 mt-1" id="resetTasbeehTitle">Tasbeeh</p>
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
                    <button class="btn btn-danger" type="submit" id="resetTasbeehSubmitBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Yes, Reset Progress
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

