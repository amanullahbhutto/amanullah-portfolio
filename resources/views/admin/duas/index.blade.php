@extends('layouts.admin')
@section('title', 'Manage Duas')
@section('page_title', 'Duas (دعائیں)')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between gap-3 mb-4">
    {{-- Active Dua Journey / Duration Tracker (Days, Months, Years) --}}
    <div class="d-flex align-items-center justify-content-center">
        @if(isset($summary['journey_duration']))
            <div class="zikr-journey-pill d-inline-flex align-items-center px-3 py-2 rounded-4 border mx-auto mx-md-0" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.12) 0%, rgba(8, 17, 30, 0.95) 100%); border-color: rgba(6, 182, 212, 0.35); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);">
                <div class="d-flex align-items-center justify-content-center me-2 rounded-circle flex-shrink-0" style="width: 32px; height: 32px; background: rgba(6, 182, 212, 0.2); color: #06b6d4; font-size: 1rem;">
                    <i class="bi bi-calendar2-range"></i>
                </div>
                <div class="d-flex flex-column justify-content-center" style="line-height: 1.25;">
                    <div class="d-flex align-items-center gap-1 flex-wrap justify-content-center justify-content-sm-start">
                        <span class="small fw-bold text-uppercase" style="color: #38bdf8; font-size: 0.68rem; letter-spacing: 0.5px;">
                            Dua Journey:
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
        @if(isset($muslimUsers) && $muslimUsers->count() > 1 && (auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'admin']) || auth()->user()->can('manage tasbeeh')))
            <form method="GET" action="{{ route('admin.duas.index') }}" class="d-inline-block">
                <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()" style="background: #08111e; border: 1px solid #142845; color: #38bdf8; border-radius: 12px; font-weight: 600; font-size: 0.8rem; padding: 6px 32px 6px 12px;">
                    @foreach($muslimUsers as $u)
                        <option value="{{ $u->id }}" @selected($selectedUser && $selectedUser->id === $u->id)>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif

        {{-- Reset All Duas Trigger --}}
        <button class="action-btn-top danger" type="button" data-bs-toggle="modal" data-bs-target="#resetAllDuasModal" title="Reset All Duas to 0 (Start Date Today)">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>

        {{-- Mark All Complete for Today Trigger --}}
        <button class="action-btn-top green" type="button" data-bs-toggle="modal" data-bs-target="#completeAllDuasModal" title="Mark All Duas Complete for Today">
            <i class="bi bi-check2-all"></i>
        </button>

        {{-- Display Settings Modal Trigger --}}
        <button class="action-btn-top" type="button" data-bs-toggle="modal" data-bs-target="#duaSettingsModal" title="Display Settings (Font Size & Visibility)">
            <i class="bi bi-gear-fill"></i>
        </button>

        {{-- Add New Dua Trigger --}}
        <button class="action-btn-top accent" type="button" data-bs-toggle="modal" data-bs-target="#createDuaModal" title="Add New Dua">
            <i class="bi bi-plus-lg"></i>
        </button>
    </div>
</div>

{{-- ─── Filter Bar ───────────────────────────────────────────────────────── --}}
<div class="zikr-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 p-2 px-3 rounded-4 border"
     style="background: rgba(8,17,30,0.92); border-color: rgba(6,182,212,0.18); backdrop-filter: blur(8px);">

    <span class="small fw-bold text-uppercase" style="color:#64748b; font-size:0.68rem; letter-spacing:0.6px;">
        <i class="bi bi-funnel me-1"></i>Filter Duas
    </span>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- Incomplete (default active) --}}
        <button type="button"
                class="zikr-filter-btn active"
                data-dua-filter="incomplete"
                title="Show only incomplete duas">
            <i class="bi bi-hourglass-split me-1"></i>
            Incomplete
            <span class="zikr-filter-count" id="dua-count-incomplete"></span>
        </button>

        {{-- Completed --}}
        <button type="button"
                class="zikr-filter-btn"
                data-dua-filter="complete"
                title="Show only completed duas">
            <i class="bi bi-check2-circle me-1"></i>
            Completed
            <span class="zikr-filter-count" id="dua-count-complete"></span>
        </button>

        {{-- Show All --}}
        <button type="button"
                class="zikr-filter-btn"
                data-dua-filter="all"
                title="Show all duas">
            <i class="bi bi-grid me-1"></i>
            All
            <span class="zikr-filter-count" id="dua-count-all"></span>
        </button>
    </div>
</div>

{{-- Duas Grid --}}
<section>
    <div id="duas-grid-container" class="admin-list-results">
        <div class="row g-3 g-md-4" id="dua-list-container">
            @forelse($duas as $d)
                @php
                    $isComplete = ($d['today_completed'] >= $d['daily_target'] && $d['daily_target'] > 0);
                @endphp
                <div class="col-12 col-lg-6 d-flex" id="dua-card-{{ $d['dua_id'] }}" data-complete="{{ $isComplete ? 'true' : 'false' }}" data-daily-target="{{ $d['daily_target'] }}" data-active-days="{{ $d['active_days'] ?? 1 }}" data-today-completed="{{ $d['today_completed'] ?? 0 }}" data-total-completed="{{ $d['total_completed'] ?? 0 }}" data-total-required="{{ $d['total_required'] ?? $d['daily_target'] }}" data-tracking-start-date="{{ $d['tracking_start_date'] ?? '' }}" data-render-date="{{ now()->format('Y-m-d') }}">
                    <div class="zikr-item-card w-100 d-flex flex-column justify-content-between">
                        <div>
                            {{-- Top Header with Sort Number Badge and Today's Count Badge --}}
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <div class="zikr-card-seq-badge" title="Dua {{ ($d['sort_order'] ?? 0) > 0 ? $d['sort_order'] : $loop->iteration }}">
                                    <span class="seq-number">{{ ($d['sort_order'] ?? 0) > 0 ? $d['sort_order'] : $loop->iteration }}</span>
                                </div>

                                {{-- Today Count Badge --}}
                                @if($isComplete)
                                    <span class="badge today-status-badge rounded-pill d-inline-flex align-items-center px-2 py-0.5" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-size: 0.72rem; font-weight: 600;">
                                        <i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">{{ number_format($d['today_completed']) }}</strong>
                                    </span>
                                @elseif($d['today_completed'] > 0)
                                    <span class="badge today-status-badge rounded-pill d-inline-flex align-items-center px-2 py-0.5" style="background: rgba(6, 182, 212, 0.15); border: 1px solid rgba(6, 182, 212, 0.4); color: #38bdf8; font-size: 0.72rem; font-weight: 600;">
                                        Today: <strong class="ms-1 font-monospace">{{ number_format($d['today_completed']) }}</strong>
                                    </span>
                                @else
                                    <span class="badge today-status-badge rounded-pill d-inline-flex align-items-center px-2 py-0.5" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; font-size: 0.72rem; font-weight: 600;">
                                        Today: <strong class="ms-1 font-monospace">0</strong>
                                    </span>
                                @endif
                            </div>

                            {{-- Dua Title --}}
                            <div class="mb-2 text-start">
                                <h5 class="dua-card-title fw-bold text-white mb-0" style="font-size: 0.95rem; line-height: 1.4;">
                                    <span class="text-info me-1"><i class="bi bi-bookmark-star-fill"></i></span>{{ $d['title'] }}
                                </h5>
                            </div>

                            <!-- Arabic Text & Urdu Translation -->
                            <div class="text-container">
                                <!-- Arabic Text -->
                                <div class="arabic-text">
                                    {{ $d['arabic_text'] }}
                                </div>

                                <!-- Islamic Center Divider -->
                                <div class="islamic-divider">
                                    <div class="divider-icon">✦ ✧ ✦</div>
                                </div>

                                <!-- Urdu Translation -->
                                <div class="urdu-text">
                                    {{ $d['urdu_meaning'] ?? '—' }}
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar & Footer Area -->
                        <div class="mt-3">
                            {{-- Cycle Progress Bar --}}
                            <div class="progress-container mb-2">
                                <div class="d-flex justify-content-between align-items-center small mb-1" style="font-size: 0.72rem;">
                                    <span class="text-secondary fw-semibold"><i class="bi bi-bar-chart-fill me-1 text-info"></i>Cycle Progress</span>
                                    <span class="font-monospace fw-bold text-white progress-percent-text">{{ $d['percentage'] }}%</span>
                                </div>
                                <div class="progress" style="height: 6px; background: rgba(255, 255, 255, 0.08); border-radius: 10px; overflow: hidden;">
                                    @php
                                        $barColor = 'amber';
                                        if ($d['extra'] > 0) $barColor = 'cyan';
                                        elseif ($d['remaining'] === 0) $barColor = 'emerald';
                                    @endphp
                                    <div class="progress-bar-custom {{ $barColor }}" role="progressbar" style="width: {{ $d['percentage'] }}%;"></div>
                                </div>
                            </div>

                            <!-- Footer Badges & Action Buttons -->
                            <div class="card-footer-strip">
                                <!-- Badges -->
                                <div class="badge-info-group">
                                    @if($d['extra'] > 0)
                                        <span class="badge-remaining extra">+{{ number_format($d['extra']) }} Extra</span>
                                    @elseif($d['remaining'] === 0)
                                        <span class="badge-remaining completed-badge">Completed</span>
                                    @else
                                        <span class="badge-remaining">Remaining {{ number_format($d['remaining']) }}</span>
                                    @endif
                                    <span class="badge-completed">Completed: <strong class="text-white">{{ number_format($d['total_completed']) }}</strong> / {{ number_format($d['total_required']) }}</span>
                                    @if(! $d['is_active'])
                                        <span class="badge bg-danger-subtle text-danger font-monospace" style="font-size: 0.72rem; padding: 5px 8px; border-radius: 6px;">Inactive</span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="badge-actions-group">
                                    {{-- 1-Click Complete Today Button --}}
                                    <button
                                        class="action-icon-btn btn-complete-icon {{ $isComplete ? 'is-completed active' : '' }}"
                                        type="button"
                                        data-dua-id="{{ $d['dua_id'] }}"
                                        data-dua-title="{{ $d['title'] }}"
                                        data-daily-target="{{ $d['daily_target'] }}"
                                        data-user-id="{{ $user->id }}"
                                        data-complete-url="{{ route('admin.duas.complete-today', $d['dua_id']) }}"
                                        title="Add Daily Target (+{{ number_format($d['daily_target']) }})"
                                    >
                                        <i class="bi bi-check2-all"></i>
                                    </button>

                                    {{-- Quick Add Count Modal Trigger --}}
                                    <button
                                        class="action-icon-btn btn-plus-icon"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#quickAddDuaModal"
                                        data-dua-id="{{ $d['dua_id'] }}"
                                        data-dua-title="{{ $d['title'] }}"
                                        data-user-id="{{ $user->id }}"
                                        data-post-url="{{ route('admin.duas.manual', $d['dua_id']) }}"
                                        title="Add Count"
                                    >
                                        <i class="bi bi-plus-lg"></i>
                                    </button>

                                    {{-- Reset Single Dua Modal Trigger --}}
                                    <button
                                        class="action-icon-btn btn-reset-icon"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#resetDuaModal"
                                        data-dua-id="{{ $d['dua_id'] }}"
                                        data-dua-title="{{ $d['title'] }}"
                                        data-user-id="{{ $user->id }}"
                                        data-reset-url="{{ route('admin.duas.reset', $d['dua_id']) }}"
                                        title="Reset Count to 0"
                                    >
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>

                                    {{-- View Details Modal Trigger --}}
                                    <button
                                        class="action-icon-btn btn-desc-icon"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#duaDescModal"
                                        data-id="{{ $d['dua_id'] }}"
                                        data-title="{{ $d['title'] }}"
                                        data-arabic="{{ $d['arabic_text'] }}"
                                        data-urdu="{{ $d['urdu_meaning'] }}"
                                        data-desc="{{ $d['description'] ?? '' }}"
                                        data-ref="{{ $d['reference'] ?? '' }}"
                                        data-target="{{ $d['daily_target'] }}"
                                        data-order="{{ ($d['sort_order'] ?? 0) > 0 ? $d['sort_order'] : $loop->iteration }}"
                                        data-active="{{ $d['is_active'] ? '1' : '0' }}"
                                        data-today-completed="{{ $d['today_completed'] ?? 0 }}"
                                        data-total-completed="{{ $d['total_completed'] ?? 0 }}"
                                        data-total-required="{{ $d['total_required'] ?? $d['daily_target'] }}"
                                        data-percentage="{{ $d['percentage'] ?? 0 }}"
                                        data-remaining="{{ $d['remaining'] ?? 0 }}"
                                        data-extra="{{ $d['extra'] ?? 0 }}"
                                        data-active-days="{{ $d['active_days'] ?? 1 }}"
                                        data-started="{{ $d['formatted_start_date'] ?? '—' }}"
                                        data-last-dua="{{ $d['formatted_last_dua'] ?? '—' }}"
                                        data-update-url="{{ route('admin.duas.update', $d['dua_id']) }}"
                                        title="View Details"
                                    >
                                        <i class="bi bi-eye-fill"></i>
                                    </button>

                                    {{-- Edit Dua Modal Trigger --}}
                                    <button
                                        class="action-icon-btn btn-edit-icon"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editDuaModal"
                                        data-id="{{ $d['dua_id'] }}"
                                        data-title="{{ $d['title'] }}"
                                        data-arabic="{{ $d['arabic_text'] }}"
                                        data-urdu="{{ $d['urdu_meaning'] }}"
                                        data-target="{{ $d['daily_target'] }}"
                                        data-order="{{ $d['sort_order'] }}"
                                        data-active="{{ $d['is_active'] ? '1' : '0' }}"
                                        data-desc="{{ $d['description'] ?? '' }}"
                                        data-ref="{{ $d['reference'] ?? '' }}"
                                        data-update-url="{{ route('admin.duas.update', $d['dua_id']) }}"
                                        title="Edit Dua"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- Delete Dua Button --}}
                                    <button
                                        class="action-icon-btn btn-del-icon"
                                        type="button"
                                        data-delete-dua
                                        data-url="{{ route('admin.duas.destroy', $d['dua_id']) }}"
                                        data-title="{{ $d['title'] }}"
                                        title="Delete"
                                    >
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bi bi-book-half fs-1 text-muted-custom mb-3 d-block"></i>
                    <h5 class="text-white">No Duas Found</h5>
                    <p class="text-muted-custom small mb-3">Add your first Dua using the button above.</p>
                    <button class="btn btn-accent btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createDuaModal">
                        <i class="bi bi-plus-lg me-1"></i>Add New Dua
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ─── MODALS ───────────────────────────────────────────────────────────── --}}

{{-- Create Dua Modal --}}
<div class="modal fade finance-modal" id="createDuaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-accent"></i>Add New Dua</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createDuaForm" action="{{ route('admin.duas.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label text-muted-custom small fw-semibold">Dua Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Rabbana Atina Fid Dunya" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted-custom small fw-semibold">Daily Target <span class="text-danger">*</span></label>
                            <input type="number" name="daily_target" class="form-control" value="100" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-custom small fw-semibold">Arabic Text (الْمَتْنُ الْعَرَبِيُّ) <span class="text-danger">*</span></label>
                            <textarea name="arabic_text" class="form-control font-arabic" rows="3" dir="rtl" placeholder="رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً..." style="font-size: 1.3rem; line-height: 2;" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-custom small fw-semibold">Urdu Meaning / Translation (اردو ترجمہ)</label>
                            <textarea name="urdu_meaning" class="form-control font-urdu" rows="3" dir="rtl" placeholder="اے ہمارے رب! ہمیں دنیا میں بھی بھلائی عطا فرما..." style="font-size: 1.1rem; line-height: 1.9;"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted-custom small fw-semibold">Reference / Source</label>
                            <input type="text" name="reference" class="form-control" placeholder="e.g. Surah Al-Baqarah (2:201)">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted-custom small fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-custom small fw-semibold">Description / Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief description, virtues, or occasion for this Dua..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="createDuaActive" value="1" checked>
                                <label class="form-check-label text-white small" for="createDuaActive">Active (Display on Dashboard and Count)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent btn-sm px-4 fw-bold" id="createDuaSubmitBtn">
                        <i class="bi bi-check2 me-1"></i>Save Dua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Dua Modal --}}
<div class="modal fade finance-modal" id="editDuaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2 text-info"></i>Edit Dua</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDuaForm">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label text-muted-custom small fw-semibold">Dua Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="editDuaTitle" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label text-muted-custom small fw-semibold">Daily Target <span class="text-danger">*</span></label>
                            <input type="number" name="daily_target" id="editDuaTarget" class="form-control" min="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-custom small fw-semibold">Arabic Text (الْمَتْنُ الْعَرَبِيُّ) <span class="text-danger">*</span></label>
                            <textarea name="arabic_text" id="editDuaArabic" class="form-control font-arabic" rows="3" dir="rtl" style="font-size: 1.3rem; line-height: 2;" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-custom small fw-semibold">Urdu Meaning / Translation (اردو ترجمہ)</label>
                            <textarea name="urdu_meaning" id="editDuaUrdu" class="form-control font-urdu" rows="3" dir="rtl" style="font-size: 1.1rem; line-height: 1.9;"></textarea>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted-custom small fw-semibold">Reference / Source</label>
                            <input type="text" name="reference" id="editDuaRef" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label text-muted-custom small fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" id="editDuaOrder" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted-custom small fw-semibold">Description / Notes</label>
                            <textarea name="description" id="editDuaDesc" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editDuaActive" value="1">
                                <label class="form-check-label text-white small" for="editDuaActive">Active (Display on Dashboard and Count)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent btn-sm px-4 fw-bold" id="editDuaSubmitBtn">
                        <i class="bi bi-check2 me-1"></i>Update Dua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick Add Count Modal --}}
<div class="modal fade finance-modal" id="quickAddDuaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-info"></i>Add Count</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddDuaForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-body p-4 text-center">
                    <p class="text-white fw-semibold mb-3" id="quickAddDuaTitle">Dua</p>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button type="button" class="btn btn-outline-theme btn-sm px-3 quick-count-chip" data-val="10">+10</button>
                        <button type="button" class="btn btn-outline-theme btn-sm px-3 quick-count-chip" data-val="33">+33</button>
                        <button type="button" class="btn btn-outline-theme btn-sm px-3 quick-count-chip" data-val="100">+100</button>
                    </div>
                    <div class="mb-3">
                        <input type="number" name="count" id="quickAddDuaCountInput" class="form-control text-center font-monospace fs-4" placeholder="Enter Count" min="1" required autofocus>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent btn-sm px-4 fw-bold" id="quickAddDuaSubmitBtn">
                        <i class="bi bi-plus-lg me-1"></i>Add Count
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset Single Dua Modal --}}
<div class="modal fade finance-modal" id="resetDuaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset Dua Count</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetDuaForm">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                    <h6 class="text-white mb-2" id="resetDuaTitle">Reset this Dua?</h6>
                    <p class="text-muted-custom small mb-0">
                        This will reset completed counts to <strong class="text-white">0</strong> and set the tracking start date to <strong class="text-info">Today</strong>.
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold" id="resetDuaSubmitBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Confirm Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reset All Duas Modal --}}
<div class="modal fade finance-modal" id="resetAllDuasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="bi bi-exclamation-octagon-fill me-2"></i>Reset All Duas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resetAllDuasForm" action="{{ route('admin.duas.reset-all') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger">
                        <i class="bi bi-arrow-counterclockwise fs-1"></i>
                    </div>
                    <h6 class="text-white mb-2">Reset All Duas to 0?</h6>
                    <p class="text-muted-custom small mb-0">
                        Are you sure you want to reset <strong class="text-white">ALL Duas</strong> to 0? Their tracking start dates will be updated to today.
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold" id="resetAllDuasSubmitBtn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Yes, Reset All Duas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Complete All Duas Modal --}}
<div class="modal fade finance-modal" id="completeAllDuasModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success"><i class="bi bi-check2-all me-2"></i>Mark All Duas Complete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeAllDuasForm" action="{{ route('admin.duas.complete-all-today') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-success">
                        <i class="bi bi-check2-circle fs-1"></i>
                    </div>
                    <h6 class="text-white mb-2">Complete Today's Target for All Duas?</h6>
                    <p class="text-muted-custom small mb-0">
                        This will automatically mark all active Duas as completed for today.
                    </p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold" id="completeAllDuasSubmitBtn">
                        <i class="bi bi-check2-all me-1"></i>Yes, Mark All Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Details Modal --}}
<div class="modal fade finance-modal" id="duaDescModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="duaDescModalTitle">Dua Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 rounded-4 mb-3" style="background: rgba(12,22,38,0.7); border: 1px solid rgba(56,189,248,0.2);">
                    <div class="text-end font-arabic mb-2" id="duaDescModalArabic" style="font-size: 1.5rem; line-height: 2; color: #f8fafc;"></div>
                    <div class="text-end font-urdu" id="duaDescModalUrdu" style="font-size: 1.15rem; line-height: 1.9; color: #cbd5e1;"></div>
                </div>
                <div class="row g-2 small text-muted-custom">
                    <div class="col-6 col-md-3">
                        <span class="d-block text-secondary">Target:</span>
                        <strong class="text-white font-monospace" id="duaDescModalTarget"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="d-block text-secondary">Started:</span>
                        <strong class="text-white" id="duaDescModalStarted"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="d-block text-secondary">Completed:</span>
                        <strong class="text-white font-monospace" id="duaDescModalCompleted"></strong>
                    </div>
                    <div class="col-6 col-md-3">
                        <span class="d-block text-secondary">Reference:</span>
                        <strong class="text-info" id="duaDescModalRef"></strong>
                    </div>
                </div>
                <div class="mt-3 p-3 rounded-3" style="background: rgba(0,0,0,0.25);" id="duaDescModalDescContainer">
                    <span class="small text-secondary d-block mb-1">Description:</span>
                    <p class="small text-white mb-0" id="duaDescModalDesc"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Display Settings Modal --}}
<div class="modal fade finance-modal" id="duaSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2 text-info"></i>Display Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label text-muted-custom small fw-semibold d-flex justify-content-between">
                        <span>Arabic Font Size</span>
                        <span id="duaArabicSizeVal" class="font-monospace text-info">24px</span>
                    </label>
                    <input type="range" class="form-range" id="duaArabicSizeRange" min="16" max="44" value="24">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted-custom small fw-semibold d-flex justify-content-between">
                        <span>Urdu Font Size</span>
                        <span id="duaUrduSizeVal" class="font-monospace text-info">16px</span>
                    </label>
                    <input type="range" class="form-range" id="duaUrduSizeRange" min="12" max="32" value="16">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ─── Font Size Settings ───────────────────────────────────────────────────
    const savedArabicSize = localStorage.getItem('dua_arabic_size') || '24';
    const savedUrduSize   = localStorage.getItem('dua_urdu_size') || '16';

    function applyDuaFontSizes(arabic, urdu) {
        document.documentElement.style.setProperty('--zikr-arabic-size', arabic + 'px');
        document.documentElement.style.setProperty('--zikr-urdu-size', urdu + 'px');
    }
    applyDuaFontSizes(savedArabicSize, savedUrduSize);

    const arabicRange = document.getElementById('duaArabicSizeRange');
    const urduRange   = document.getElementById('duaUrduSizeRange');
    const arabicValEl = document.getElementById('duaArabicSizeVal');
    const urduValEl   = document.getElementById('duaUrduSizeVal');

    if (arabicRange) {
        arabicRange.value = savedArabicSize;
        if (arabicValEl) arabicValEl.textContent = savedArabicSize + 'px';
        arabicRange.addEventListener('input', function () {
            if (arabicValEl) arabicValEl.textContent = this.value + 'px';
            applyDuaFontSizes(this.value, urduRange?.value || 16);
            localStorage.setItem('dua_arabic_size', this.value);
        });
    }

    if (urduRange) {
        urduRange.value = savedUrduSize;
        if (urduValEl) urduValEl.textContent = savedUrduSize + 'px';
        urduRange.addEventListener('input', function () {
            if (urduValEl) urduValEl.textContent = this.value + 'px';
            applyDuaFontSizes(arabicRange?.value || 24, this.value);
            localStorage.setItem('dua_urdu_size', this.value);
        });
    }

    // ─── Filter Bar Logic (Incomplete, Completed, All) ────────────────────────
    function isDuaCompletedToday(card) {
        const target = parseInt(card.dataset.dailyTarget || '0', 10);
        let today = parseInt(card.dataset.todayCompleted || '0', 10);

        const badgeStrong = card.querySelector('.today-status-badge strong');
        if (badgeStrong) {
            const badgeNum = parseInt(badgeStrong.textContent.replace(/[^0-9]/g, ''), 10);
            if (!isNaN(badgeNum) && badgeNum > today) today = badgeNum;
        }

        if (target > 0 && today >= target) return true;

        const completeBtn = card.querySelector('.btn-complete-icon');
        if (completeBtn && (completeBtn.classList.contains('is-completed') || completeBtn.classList.contains('active'))) {
            return true;
        }

        const todayBadge = card.querySelector('.today-status-badge');
        if (todayBadge && todayBadge.querySelector('.bi-check2')) return true;

        return card.dataset.complete === 'true';
    }

    function applyDuaFilter(filter) {
        const cards = document.querySelectorAll('#dua-list-container > [id^="dua-card-"]');
        let incompleteCount = 0, completeCount = 0;

        cards.forEach(function (card) {
            const done = isDuaCompletedToday(card);
            card.dataset.complete = done ? 'true' : 'false';

            if (done) completeCount++; else incompleteCount++;

            let show = true;
            if (filter === 'incomplete') show = !done;
            else if (filter === 'complete') show = done;

            if (show) {
                card.classList.remove('d-none');
                card.classList.add('d-flex');
                card.style.setProperty('display', '', '');
            } else {
                card.classList.remove('d-flex');
                card.classList.add('d-none');
                card.style.setProperty('display', 'none', 'important');
            }
        });

        const elInc  = document.getElementById('dua-count-incomplete');
        const elComp = document.getElementById('dua-count-complete');
        const elAll  = document.getElementById('dua-count-all');
        if (elInc)  elInc.textContent  = incompleteCount;
        if (elComp) elComp.textContent = completeCount;
        if (elAll)  elAll.textContent  = incompleteCount + completeCount;
    }

    function getActiveDuaFilter() {
        const active = document.querySelector('[data-dua-filter].active');
        return active ? active.dataset.duaFilter : 'incomplete';
    }

    window.refreshDuaFilter = function () {
        applyDuaFilter(getActiveDuaFilter());
    };

    const filterBtns = document.querySelectorAll('[data-dua-filter]');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyDuaFilter(this.dataset.duaFilter);
        });
    });

    // Default: show incomplete on load
    applyDuaFilter('incomplete');

    // ─── Toast Helper ────────────────────────────────────────────────────────
    function showDuaToast(msg, type = 'success') {
        if (typeof window.showFlashToast === 'function') {
            window.showFlashToast(msg, type);
        } else if (window.App && typeof window.App.showToast === 'function') {
            window.App.showToast(type, msg);
        } else {
            alert(msg);
        }
    }

    // ─── Update Card DOM Helper ──────────────────────────────────────────────
    function updateDuaCardDOM(card, stats) {
        if (!card || !stats) return;

        card.dataset.todayCompleted = String(stats.today_completed ?? 0);
        card.dataset.totalCompleted = String(stats.total_completed ?? 0);
        card.dataset.totalRequired  = String(stats.total_required ?? 0);
        card.dataset.dailyTarget    = String(stats.daily_target ?? 0);

        const target = stats.daily_target ?? 0;
        const today  = stats.today_completed ?? 0;
        const isDone = (target > 0 && today >= target);
        card.dataset.complete = isDone ? 'true' : 'false';

        // Update Today Badge
        const todayBadge = card.querySelector('.today-status-badge');
        if (todayBadge) {
            if (isDone) {
                todayBadge.style.background = 'rgba(16, 185, 129, 0.15)';
                todayBadge.style.borderColor = 'rgba(16, 185, 129, 0.4)';
                todayBadge.style.color = '#34d399';
                todayBadge.innerHTML = `<i class="bi bi-check2 me-1"></i>Today: <strong class="ms-1 font-monospace">${today.toLocaleString()}</strong>`;
            } else if (today > 0) {
                todayBadge.style.background = 'rgba(6, 182, 212, 0.15)';
                todayBadge.style.borderColor = 'rgba(6, 182, 212, 0.4)';
                todayBadge.style.color = '#38bdf8';
                todayBadge.innerHTML = `Today: <strong class="ms-1 font-monospace">${today.toLocaleString()}</strong>`;
            } else {
                todayBadge.style.background = 'rgba(239, 68, 68, 0.12)';
                todayBadge.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                todayBadge.style.color = '#f87171';
                todayBadge.innerHTML = `Today: <strong class="ms-1 font-monospace">0</strong>`;
            }
        }

        // Update Completed Badge
        const compBadge = card.querySelector('.badge-completed');
        if (compBadge) {
            compBadge.innerHTML = `Completed: <strong class="text-white">${(stats.total_completed ?? 0).toLocaleString()}</strong> / ${(stats.total_required ?? 0).toLocaleString()}`;
        }

        // Update Remaining / Extra Badge
        const remBadge = card.querySelector('.badge-remaining');
        if (remBadge) {
            if ((stats.extra ?? 0) > 0) {
                remBadge.className = 'badge-remaining extra';
                remBadge.textContent = `+${stats.extra.toLocaleString()} Extra`;
            } else if ((stats.remaining ?? 0) === 0) {
                remBadge.className = 'badge-remaining completed-badge';
                remBadge.textContent = 'Completed';
            } else {
                remBadge.className = 'badge-remaining';
                remBadge.textContent = `Remaining ${stats.remaining.toLocaleString()}`;
            }
        }

        // Update Progress Bar
        const pBar = card.querySelector('.progress-bar-custom');
        const pText = card.querySelector('.progress-percent-text');
        const pct = stats.percentage ?? 0;
        if (pBar) {
            pBar.style.width = pct + '%';
            let barColor = 'amber';
            if ((stats.extra ?? 0) > 0) barColor = 'cyan';
            else if ((stats.remaining ?? 0) === 0) barColor = 'emerald';
            pBar.className = `progress-bar-custom ${barColor}`;
        }
        if (pText) pText.textContent = pct + '%';

        // Update Complete Icon Button
        const completeBtn = card.querySelector('.btn-complete-icon');
        if (completeBtn) {
            completeBtn.classList.toggle('is-completed', isDone);
            completeBtn.classList.toggle('active', isDone);
        }

        window.refreshDuaFilter();
    }

    // ─── 1-Click Complete Today ───────────────────────────────────────────────
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-complete-icon[data-complete-url]');
        if (!btn) return;
        e.preventDefault();

        const card = btn.closest('[id^="dua-card-"]');
        const url  = btn.dataset.completeUrl;
        const title = btn.dataset.duaTitle || 'Dua';
        const target = parseInt(btn.dataset.dailyTarget || '100', 10);

        // Immediate visual response
        btn.classList.add('btn-click-anim');
        setTimeout(() => btn.classList.remove('btn-click-anim'), 400);

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (data.success && data.stats) {
                updateDuaCardDOM(card, data.stats);
                showDuaToast(data.message || `+${target} completed for '${title}'!`, 'success');
            } else {
                showDuaToast(data.message || 'Operation failed', 'danger');
            }
        } catch (err) {
            console.error(err);
            showDuaToast('Network error while completing target', 'danger');
        }
    });

    // ─── Quick Add Modal Handler ──────────────────────────────────────────────
    const quickAddModal = document.getElementById('quickAddDuaModal');
    if (quickAddModal) {
        quickAddModal.addEventListener('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
            const form = document.getElementById('quickAddDuaForm');
            form.action = trigger.dataset.postUrl || '';
            document.getElementById('quickAddDuaTitle').textContent = trigger.dataset.duaTitle || 'Dua';
            const countIn = document.getElementById('quickAddDuaCountInput');
            if (countIn) { countIn.value = ''; setTimeout(() => countIn.focus(), 300); }
        });

        document.querySelectorAll('.quick-count-chip').forEach(chip => {
            chip.addEventListener('click', function () {
                const countIn = document.getElementById('quickAddDuaCountInput');
                if (countIn) countIn.value = this.dataset.val;
            });
        });

        document.getElementById('quickAddDuaForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('quickAddDuaSubmitBtn');
            btn.disabled = true;

            const countVal = parseInt(document.getElementById('quickAddDuaCountInput')?.value || '1', 10);
            try {
                const res = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ count: countVal })
                });
                const data = await res.json();
                if (data.success && data.stats) {
                    const card = document.getElementById(`dua-card-${data.stats.dua_id}`);
                    updateDuaCardDOM(card, data.stats);
                    bootstrap.Modal.getInstance(quickAddModal)?.hide();
                    showDuaToast(data.message || `+${countVal} count added!`, 'success');
                } else {
                    showDuaToast(data.message || 'Failed to add count', 'danger');
                }
            } catch (err) {
                console.error(err);
                showDuaToast('Error adding count', 'danger');
            } finally {
                btn.disabled = false;
            }
        });
    }

    // ─── Reset Single Dua Modal Handler ───────────────────────────────────────
    const resetModal = document.getElementById('resetDuaModal');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;
            const form = document.getElementById('resetDuaForm');
            form.action = trigger.dataset.resetUrl || '';
            document.getElementById('resetDuaTitle').textContent = `Reset '${trigger.dataset.duaTitle || 'Dua'}'?`;
        });

        document.getElementById('resetDuaForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('resetDuaSubmitBtn');
            btn.disabled = true;

            try {
                const res = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                if (data.success && data.stats) {
                    const card = document.getElementById(`dua-card-${data.stats.dua_id}`);
                    updateDuaCardDOM(card, data.stats);
                    bootstrap.Modal.getInstance(resetModal)?.hide();
                    showDuaToast(data.message || 'Dua count reset to 0.', 'success');
                } else {
                    showDuaToast(data.message || 'Failed to reset', 'danger');
                }
            } catch (err) {
                console.error(err);
                showDuaToast('Error resetting Dua', 'danger');
            } finally {
                btn.disabled = false;
            }
        });
    }

    // ─── Reset All Duas Handler ───────────────────────────────────────────────
    document.getElementById('resetAllDuasForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('resetAllDuasSubmitBtn');
        btn.disabled = true;

        try {
            const res = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('resetAllDuasModal'))?.hide();
                showDuaToast(data.message || 'All Duas reset to 0.', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showDuaToast(data.message || 'Failed to reset all', 'danger');
            }
        } catch (err) {
            console.error(err);
            showDuaToast('Error resetting all Duas', 'danger');
        } finally {
            btn.disabled = false;
        }
    });

    // ─── Complete All Duas Handler ────────────────────────────────────────────
    document.getElementById('completeAllDuasForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('completeAllDuasSubmitBtn');
        btn.disabled = true;

        try {
            const res = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('completeAllDuasModal'))?.hide();
                showDuaToast(data.message || 'All Duas completed for today!', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showDuaToast(data.message || 'Failed to complete all', 'danger');
            }
        } catch (err) {
            console.error(err);
            showDuaToast('Error completing all Duas', 'danger');
        } finally {
            btn.disabled = false;
        }
    });

    // ─── Create Dua AJAX ──────────────────────────────────────────────────────
    document.getElementById('createDuaForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('createDuaSubmitBtn');
        btn.disabled = true;

        const formData = new FormData(this);
        try {
            const res = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('createDuaModal'))?.hide();
                showDuaToast(data.message || 'Dua created successfully!', 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                showDuaToast(data.message || 'Error saving Dua', 'danger');
            }
        } catch (err) {
            console.error(err);
            showDuaToast('Validation or network error', 'danger');
        } finally {
            btn.disabled = false;
        }
    });

    // ─── Edit Dua Handler ─────────────────────────────────────────────────────
    const editModal = document.getElementById('editDuaModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;

            const form = document.getElementById('editDuaForm');
            form.action = trigger.dataset.updateUrl || '';

            document.getElementById('editDuaTitle').value  = trigger.dataset.title || '';
            document.getElementById('editDuaTarget').value = trigger.dataset.target || '100';
            document.getElementById('editDuaArabic').value = trigger.dataset.arabic || '';
            document.getElementById('editDuaUrdu').value   = trigger.dataset.urdu || '';
            document.getElementById('editDuaRef').value    = trigger.dataset.ref || '';
            document.getElementById('editDuaOrder').value  = trigger.dataset.order || '0';
            document.getElementById('editDuaDesc').value   = trigger.dataset.desc || '';
            document.getElementById('editDuaActive').checked = (trigger.dataset.active === '1');
        });

        document.getElementById('editDuaForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('editDuaSubmitBtn');
            btn.disabled = true;

            const formData = new FormData(this);
            try {
                const res = await fetch(this.action, {
                    method: 'POST', // with _method=PUT inside form
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    bootstrap.Modal.getInstance(editModal)?.hide();
                    showDuaToast(data.message || 'Dua updated successfully!', 'success');
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    showDuaToast(data.message || 'Error updating Dua', 'danger');
                }
            } catch (err) {
                console.error(err);
                showDuaToast('Validation or network error', 'danger');
            } finally {
                btn.disabled = false;
            }
        });
    }

    // ─── Delete Dua Handler ───────────────────────────────────────────────────
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-delete-dua]');
        if (!btn) return;
        e.preventDefault();

        const title = btn.dataset.title || 'this Dua';
        if (!confirm(`Are you sure you want to delete '${title}'? All completed progress records will also be deleted.`)) {
            return;
        }

        const url = btn.dataset.url;
        try {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data.success) {
                const card = btn.closest('[id^="dua-card-"]');
                if (card) {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.remove();
                        window.refreshDuaFilter();
                    }, 400);
                }
                showDuaToast(data.message || 'Dua deleted successfully.', 'success');
            } else {
                showDuaToast(data.message || 'Failed to delete Dua', 'danger');
            }
        } catch (err) {
            console.error(err);
            showDuaToast('Error deleting Dua', 'danger');
        }
    });

    // ─── View Details Modal Handler ───────────────────────────────────────────
    const descModal = document.getElementById('duaDescModal');
    if (descModal) {
        descModal.addEventListener('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            if (!trigger) return;

            document.getElementById('duaDescModalTitle').textContent = trigger.dataset.title || 'Dua Details';
            document.getElementById('duaDescModalArabic').textContent = trigger.dataset.arabic || '';
            document.getElementById('duaDescModalUrdu').textContent = trigger.dataset.urdu || '';
            document.getElementById('duaDescModalTarget').textContent = (parseInt(trigger.dataset.target, 10) || 0).toLocaleString() + '/day';
            document.getElementById('duaDescModalStarted').textContent = trigger.dataset.started || '—';
            document.getElementById('duaDescModalCompleted').textContent = (parseInt(trigger.dataset.totalCompleted, 10) || 0).toLocaleString();
            document.getElementById('duaDescModalRef').textContent = trigger.dataset.ref || 'None';

            const descText = trigger.dataset.desc || '';
            const descContainer = document.getElementById('duaDescModalDescContainer');
            if (descText) {
                descContainer.style.display = '';
                document.getElementById('duaDescModalDesc').textContent = descText;
            } else {
                descContainer.style.display = 'none';
            }
        });
    }

    // ─── Midnight Rollover Check (Auto-unhide completed duas at 12:00 AM) ─────
    let _lastDuaDay = (new Date()).toISOString().slice(0, 10);
    function checkDuaMidnightRollover() {
        const today = (new Date()).toISOString().slice(0, 10);
        if (today !== _lastDuaDay) {
            _lastDuaDay = today;
            document.querySelectorAll('[id^="dua-card-"]').forEach(card => {
                card.dataset.todayCompleted = '0';
                card.dataset.complete = 'false';
                const todayBadge = card.querySelector('.today-status-badge');
                if (todayBadge) {
                    todayBadge.style.background = 'rgba(239, 68, 68, 0.12)';
                    todayBadge.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                    todayBadge.style.color = '#f87171';
                    todayBadge.innerHTML = 'Today: <strong class="ms-1 font-monospace">0</strong>';
                }
                const completeBtn = card.querySelector('.btn-complete-icon');
                if (completeBtn) {
                    completeBtn.classList.remove('is-completed', 'active');
                }
            });
            window.refreshDuaFilter();
        }
    }
    setInterval(checkDuaMidnightRollover, 5000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') checkDuaMidnightRollover();
    });
})();
</script>
@endpush
@endsection

