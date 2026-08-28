@extends('layouts.admin')
@section('title', 'Namaz Settings')
@section('page_title', 'Namaz Prayer Settings')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <span class="eyebrow">Islamic & Deen Configuration</span>
        <h2 class="h4 mb-1">Namaz Prayer Timings</h2>
        <p class="text-muted-custom mb-0">Configure daily prayer timings. Automatic Kaza is triggered when current time passes these prayer thresholds.</p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.namaz.attendance.index') }}">
            <i class="bi bi-calendar-check me-1"></i>Back to Attendance Table
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <section class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2>Prayer Time Configuration</h2>
                    <p class="text-muted-custom small mb-0 mt-1">Set 24-hour format (HH:MM) for each prayer.</p>
                </div>
            </div>

            <form class="p-4" data-ajax-form action="{{ route('admin.namaz.settings.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    {{-- Fajr --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-sunrise text-primary"></i>
                            <span>Fajr Time <span class="text-danger">*</span></span>
                        </label>
                        <input class="form-control" type="time" name="fajr_time" value="{{ $settings->fajr_time ?: '05:00' }}" required>
                        <small class="text-muted-custom">Fajr prayer cutoff threshold</small>
                        <div class="invalid-feedback" data-error-for="fajr_time"></div>
                    </div>

                    {{-- Zuhr (Normal Days) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-sun text-warning"></i>
                            <span>Zuhr Time (Mon - Thu, Sat - Sun) <span class="text-danger">*</span></span>
                        </label>
                        <input class="form-control" type="time" name="zuhr_time" value="{{ $settings->zuhr_time ?: '13:15' }}" required>
                        <small class="text-muted-custom">Normal daily Zuhr prayer time</small>
                        <div class="invalid-feedback" data-error-for="zuhr_time"></div>
                    </div>

                    {{-- Jumu'ah (Fridays) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-stars text-success"></i>
                            <span>Jumu'ah Time (Friday Only) <span class="text-danger">*</span></span>
                        </label>
                        <input class="form-control" type="time" name="jummah_time" value="{{ $settings->jummah_time ?: '13:30' }}" required>
                        <small class="text-muted-custom">Replaces Zuhr timing on Friday</small>
                        <div class="invalid-feedback" data-error-for="jummah_time"></div>
                    </div>

                    {{-- Asr --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-cloud-sun text-warning"></i>
                            <span>Asr Time <span class="text-danger">*</span></span>
                        </label>
                        <input class="form-control" type="time" name="asr_time" value="{{ $settings->asr_time ?: '16:45' }}" required>
                        <small class="text-muted-custom">Asr prayer cutoff threshold</small>
                        <div class="invalid-feedback" data-error-for="asr_time"></div>
                    </div>

                    {{-- Maghrib --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-sunset text-danger"></i>
                            <span>Maghrib Time <span class="text-danger">*</span></span>
                        </label>
                        <input class="form-control" type="time" name="maghrib_time" value="{{ $settings->maghrib_time ?: '18:50' }}" required>
                        <small class="text-muted-custom">Maghrib prayer cutoff threshold</small>
                        <div class="invalid-feedback" data-error-for="maghrib_time"></div>
                    </div>

                    {{-- Isha --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-flex align-items-center gap-2">
                            <i class="bi bi-moon-stars text-info"></i>
                            <span>Isha Time <span class="text-danger">*</span></span>
                        </label>
                        <input class="form-control" type="time" name="isha_time" value="{{ $settings->isha_time ?: '20:15' }}" required>
                        <small class="text-muted-custom">Isha prayer cutoff threshold</small>
                        <div class="invalid-feedback" data-error-for="isha_time"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                    @can('update namaz settings')
                        <button class="btn btn-accent px-4" type="submit" data-submit>
                            <span data-submit-label>Save Prayer Timings</span>
                        </button>
                    @endcan
                </div>
            </form>
        </section>
    </div>

    {{-- System Timezone Info Card --}}
    <div class="col-lg-4">
        <aside class="admin-card">
            <div class="admin-card-head">
                <div>
                    <h2>Timezone & Rules</h2>
                </div>
            </div>
            <div class="p-4">
                <div class="mb-3">
                    <span class="text-muted-custom small d-block">Application Timezone</span>
                    <strong class="fs-6 d-flex align-items-center gap-1 text-accent">
                        <i class="bi bi-globe"></i> {{ $timezone }}
                    </strong>
                </div>

                <div class="mb-3">
                    <span class="text-muted-custom small d-block">Current Server Time</span>
                    <strong>{{ $currentTime }}</strong>
                </div>

                <hr class="my-3">

                <h6 class="h6 mb-2">Automated Rules:</h6>
                <ul class="text-muted-custom small ps-3 mb-0 d-grid gap-2">
                    <li>Prayer slots before their configured time will show as <strong>Pending</strong>.</li>
                    <li>When prayer time passes on today without attendance, status automatically transitions to <strong>Kaza</strong>.</li>
                    <li>On Friday, Zuhr is replaced with <strong>Jumu'ah</strong> and follows Jumu'ah timing.</li>
                    <li>Manual status always overrides automated calculation.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection

