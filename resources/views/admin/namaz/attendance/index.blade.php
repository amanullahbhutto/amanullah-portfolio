@extends('layouts.admin')
@section('title', 'Namaz Attendance')
@section('page_title', 'Namaz Attendance')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div class="d-flex align-items-center gap-3">
        <div>
            <span class="eyebrow mb-0">Islamic & Deen</span>
            <h2 class="h4 mb-0">Namaz Attendance</h2>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if($selectedUser)
            @can('view namaz dashboard')
                <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.namaz.dashboard.index', ['user_id' => $selectedUser->id]) }}">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            @endcan
            @can('update namaz attendance')
                <button class="btn btn-outline-theme btn-sm" data-bs-toggle="modal" data-bs-target="#startDateModal">
                    <i class="bi bi-calendar-event me-1"></i>{{ $selectedUser->namaz_start_date ? 'Change Start Date' : 'Set Start Date' }}
                </button>
            @endcan
        @endif
        @can('view namaz settings')
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.namaz.settings.index') }}">
                <i class="bi bi-clock me-1"></i>Namaz Settings
            </a>
        @endcan
    </div>
</div>

<section class="admin-card" data-ajax-crud data-refresh-target="#namaz-attendance-results" data-status-url="{{ route('admin.namaz.attendance.status') }}">
    <div class="admin-card-head">
        <div>
            @if($selectedUser)
                <h2>{{ $selectedUser->name }} — Attendance Ledger</h2>
                <p class="text-muted-custom small mb-0 mt-1">
                    Role: <span class="badge bg-success-subtle text-success">Muslim</span>
                    @if($selectedUser->namaz_start_date)
                        <span class="mx-2">•</span>Tracking Started: <strong>{{ $selectedUser->namaz_start_date->format('d M, Y') }}</strong>
                    @endif
                </p>
            @else
                <h2>Namaz Attendance</h2>
                <p class="text-muted-custom small mb-0 mt-1">Select a Muslim person to view and mark attendance.</p>
            @endif
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="admin-list-toolbar">
        <form class="admin-search financial-filter d-flex flex-wrap gap-2 align-items-center w-100" method="GET" action="{{ route('admin.namaz.attendance.index') }}" data-live-search data-live-search-target="#namaz-attendance-results">
            <div style="min-width: 220px;">
                <select class="form-select filter-select" name="user_id" data-auto-submit>
                    @forelse($muslimUsers as $user)
                        <option value="{{ $user->id }}" @selected($selectedUser && $selectedUser->id === $user->id)>
                            {{ $user->name }} ({{ $user->namaz_start_date ? 'Since ' . $user->namaz_start_date->format('d M Y') : 'Start Date Not Set' }})
                        </option>
                    @empty
                        <option value="">No Muslim Users Found</option>
                    @endforelse
                </select>
            </div>

            <div class="d-flex align-items-center gap-2">
                <input class="form-control filter-date" type="date" name="start_date" value="{{ $startDate }}" title="From Date">
                <span class="text-muted-custom small">to</span>
                <input class="form-control filter-date" type="date" name="end_date" value="{{ $endDate }}" title="To Date">
            </div>

            <div class="d-flex flex-wrap gap-1 ms-md-auto">
                <button type="submit" class="btn btn-sm btn-outline-theme">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Results Container --}}
    <div id="namaz-attendance-results" class="admin-list-results">
        @if(! $selectedUser)
            <div class="text-center py-5 text-muted-custom">
                <i class="bi bi-people fs-1 text-accent"></i>
                <h5 class="mt-3 mb-1">No Muslim People Found</h5>
                <p class="mb-0">Please assign the <strong>Muslim</strong> role to a user in User Management to track their Namaz attendance.</p>
            </div>
        @elseif(! $selectedUser->namaz_start_date)
            <div class="text-center py-5 text-muted-custom">
                <i class="bi bi-calendar-x fs-1 text-warning"></i>
                <h5 class="mt-3 mb-1">Namaz attendance has not been started for {{ $selectedUser->name }}</h5>
                <p class="mb-3">Attendance tracking starts from the person's Namaz Start Date.</p>
                @can('update namaz attendance')
                    <button class="btn btn-accent btn-sm" data-bs-toggle="modal" data-bs-target="#startDateModal">
                        <i class="bi bi-calendar-plus me-1"></i>Set Namaz Start Date for {{ $selectedUser->name }}
                    </button>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Date</th>
                            <th class="text-center" style="min-width: 120px;">Fajr</th>
                            <th class="text-center" style="min-width: 130px;">Zuhr / Jumu'ah</th>
                            <th class="text-center" style="min-width: 120px;">Asr</th>
                            <th class="text-center" style="min-width: 120px;">Maghrib</th>
                            <th class="text-center" style="min-width: 120px;">Isha</th>
                            <th class="text-end" style="min-width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledger as $row)
                            <tr class="{{ $row['is_today'] ? 'table-active' : '' }}">
                                <td>
                                    <div>
                                        <strong class="d-block" style="color: var(--text) !important; font-size: 0.88rem;">{{ $row['formatted_date'] }}</strong>
                                        <div class="d-flex align-items-center gap-1 mt-1" style="color: var(--muted) !important; font-size: 0.76rem;">
                                            <span>{{ $row['day_name'] }}</span>
                                            @if($row['is_friday'])
                                                <span class="badge bg-success-subtle text-success py-0 px-1" style="font-size: 0.65rem;">Friday</span>
                                            @endif
                                            @if($row['is_today'])
                                                <span class="badge bg-primary-subtle text-primary py-0 px-1" style="font-size: 0.65rem;">Today</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- 5 Prayer Cells --}}
                                @foreach($prayers as $prayerKey)
                                    @php
                                        $prayer = $row['prayers'][$prayerKey];
                                    @endphp
                                    <td class="text-center">
                                        @can('update namaz attendance')
                                            <button
                                                class="btn btn-sm p-1 border-0 bg-transparent cursor-pointer"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#quickPrayerModal"
                                                data-user-id="{{ $selectedUser->id }}"
                                                data-user-name="{{ $selectedUser->name }}"
                                                data-date="{{ $row['date_string'] }}"
                                                data-formatted-date="{{ $row['formatted_date'] }}"
                                                data-day-name="{{ $row['day_name'] }}"
                                                data-prayer="{{ $prayerKey }}"
                                                data-prayer-label="{{ $prayer['prayer_label'] }}"
                                                data-current-status="{{ $prayer['key'] }}"
                                                data-is-manual="{{ $prayer['is_manual'] ? '1' : '0' }}"
                                                data-has-arrived="{{ $prayer['has_arrived'] ? '1' : '0' }}"
                                                data-status-url="{{ route('admin.namaz.attendance.status') }}"
                                                title="Click to change {{ $prayer['prayer_label'] }} status"
                                            >
                                                <div class="namaz-status-pill {{ $prayer['key'] }}">
                                                    @if($prayer['icon'])
                                                        <span class="status-icon"><i class="{{ $prayer['icon'] }}"></i></span>
                                                    @endif
                                                    <span class="status-label">{{ $prayer['label'] }}</span>
                                                    @if($prayer['is_manual'])
                                                        <span class="manual-dot" title="Manually recorded"><i class="bi bi-check2"></i></span>
                                                    @elseif($prayer['is_auto_kaza'])
                                                        <span class="auto-kaza-dot" title="Auto Kaza (Time passed)"><i class="bi bi-clock"></i></span>
                                                    @endif
                                                </div>
                                            </button>
                                        @else
                                            <div class="namaz-status-pill {{ $prayer['key'] }}">
                                                @if($prayer['icon'])
                                                    <span class="status-icon"><i class="{{ $prayer['icon'] }}"></i></span>
                                                @endif
                                                <span class="status-label">{{ $prayer['label'] }}</span>
                                            </div>
                                        @endcan
                                    </td>
                                @endforeach

                                {{-- Day Actions --}}
                                <td>
                                    <div class="action-buttons">
                                        @can('update namaz attendance')
                                            <button class="btn-icon info" data-bs-toggle="modal" data-bs-target="#editDayModal" data-date="{{ $row['date_string'] }}" data-formatted-date="{{ $row['formatted_date'] }}" data-fajr="{{ $row['prayers']['fajr']['manual_status'] ?? '' }}" data-zuhr="{{ $row['prayers']['zuhr']['manual_status'] ?? '' }}" data-asr="{{ $row['prayers']['asr']['manual_status'] ?? '' }}" data-maghrib="{{ $row['prayers']['maghrib']['manual_status'] ?? '' }}" data-isha="{{ $row['prayers']['isha']['manual_status'] ?? '' }}" data-bs-placement="top" title="Edit Full Day Attendance">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @if($row['attendance_id'])
                                            @can('delete namaz attendance')
                                                <form method="POST" action="{{ route('admin.namaz.attendance.destroy', $row['attendance_id']) }}" data-ajax-delete data-confirm="Reset all manual attendance for {{ $row['formatted_date'] }}?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-icon danger" type="submit" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Manual Attendance">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted-custom">
                                    <i class="bi bi-calendar2-x fs-2 text-accent"></i>
                                    <p class="mt-2 mb-0">No attendance days found in selected date range.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

{{-- Single Prayer Quick Status Popup Modal --}}
@if($selectedUser)
<div class="modal fade finance-modal" id="quickPrayerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content shadow-lg border">
            <div class="modal-header pb-2 border-bottom">
                <div>
                    <span class="eyebrow text-accent mb-0" id="quickPrayerEyebrow">Prayer Attendance</span>
                    <h5 class="modal-title mb-0" id="quickPrayerModalTitle">Fajr Attendance</h5>
                    <p class="text-muted-custom small mb-0 mt-1" id="quickPrayerSubtitle">{{ $selectedUser->name }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                {{-- Time not arrived warning --}}
                <div id="quickPrayerTimeNotArrivedAlert" class="alert alert-warning d-flex align-items-center gap-2 mb-3 py-2 px-3 rounded-3" style="display: none;">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-warning flex-shrink-0"></i>
                    <div class="small">
                        <strong>Namaz Ka Waqt Nahi Hua</strong>
                        <div class="text-muted-custom">Yeh namaz waqt aane ke baad hi mark ki ja sakti hai.</div>
                    </div>
                </div>

                <p class="small text-muted-custom mb-3" id="quickPrayerPromptText">Choose attendance status for this prayer:</p>
                <div class="d-grid gap-2" id="quickPrayerOptionsContainer">
                    {{-- Jamat --}}
                    <button type="button" class="btn btn-outline-success text-start d-flex align-items-center gap-3 p-3 rounded-3 namaz-option-btn" data-namaz-status-btn data-status="jamat">
                        <i class="bi bi-check-circle-fill fs-3 text-success"></i>
                        <div class="min-w-0 flex-grow-1">
                            <strong class="d-block text-success fs-6">Jamat (باجماعت)</strong>
                            <small class="text-muted-custom">Performed in congregation</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted-custom ms-auto"></i>
                    </button>

                    {{-- Without Jamat --}}
                    <button type="button" class="btn btn-outline-info text-start d-flex align-items-center gap-3 p-3 rounded-3 namaz-option-btn" data-namaz-status-btn data-status="without_jamat">
                        <i class="bi bi-person-fill fs-3 text-info"></i>
                        <div class="min-w-0 flex-grow-1">
                            <strong class="d-block text-info fs-6">Without Jamat (تنہا)</strong>
                            <small class="text-muted-custom">Performed individually</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted-custom ms-auto"></i>
                    </button>

                    {{-- Kaza --}}
                    <button type="button" class="btn btn-outline-warning text-start d-flex align-items-center gap-3 p-3 rounded-3 namaz-option-btn" data-namaz-status-btn data-status="kaza">
                        <i class="bi bi-clock-history fs-3 text-warning"></i>
                        <div class="min-w-0 flex-grow-1">
                            <strong class="d-block text-warning fs-6">Kaza (قضا)</strong>
                            <small class="text-muted-custom">Performed after time passed</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted-custom ms-auto"></i>
                    </button>

                    {{-- Absent --}}
                    <button type="button" class="btn btn-outline-danger text-start d-flex align-items-center gap-3 p-3 rounded-3 namaz-option-btn" data-namaz-status-btn data-status="absent">
                        <i class="bi bi-x-circle-fill fs-3 text-danger"></i>
                        <div class="min-w-0 flex-grow-1">
                            <strong class="d-block text-danger fs-6">Absent (نہیں پڑھی)</strong>
                            <small class="text-muted-custom">Missed / not prayed</small>
                        </div>
                        <i class="bi bi-chevron-right text-muted-custom ms-auto"></i>
                    </button>

                    {{-- Reset --}}
                    <div id="quickPrayerResetContainer" class="mt-2 pt-2 border-top">
                        <button type="button" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 py-2 rounded-3" data-namaz-status-btn data-status="">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            <span>Reset to Auto Calculation</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Namaz Start Date Modal --}}
<div class="modal fade finance-modal" id="startDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form action="{{ route('admin.namaz.attendance.start-date', $selectedUser) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Namaz Start Date for {{ $selectedUser->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted-custom small mb-3">Attendance tracking for <strong>{{ $selectedUser->name }}</strong> will start from this date. Prayers before this date will not be generated.</p>
                    <div class="mb-3">
                        <label class="form-label">Namaz Start Date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="namaz_start_date" value="{{ $selectedUser->namaz_start_date?->format('Y-m-d') ?: date('Y-m-d') }}" required>
                        <div class="invalid-feedback" data-error-for="namaz_start_date"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Save Start Date</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Full Day Edit Modal --}}
<div class="modal fade finance-modal" id="editDayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form action="{{ route('admin.namaz.attendance.day') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                <input type="hidden" name="attendance_date" id="modalDayDate">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Edit Day Attendance</h5>
                        <p class="text-muted-custom small mb-0" id="modalDayFormatted">Date</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @foreach($prayers as $pKey)
                            <div class="col-12">
                                <label class="form-label fw-bold d-flex justify-content-between">
                                    <span>{{ $pKey === 'zuhr' ? 'Zuhr / Jumu\'ah' : ucfirst($pKey) }}</span>
                                    <small class="text-muted-custom fw-normal">Optional</small>
                                </label>
                                <select class="form-select" name="{{ $pKey }}_status" id="modalStatus_{{ $pKey }}">
                                    <option value="">Auto (Pending / Kaza based on time)</option>
                                    <option value="jamat">🟢 Jamat</option>
                                    <option value="without_jamat">🔵 Without Jamat</option>
                                    <option value="kaza">🟡 Kaza</option>
                                    <option value="absent">🔴 Absent</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Save Day Attendance</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
