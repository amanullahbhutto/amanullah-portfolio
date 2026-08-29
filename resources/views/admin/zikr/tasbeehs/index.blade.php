@extends('layouts.admin')
@section('title', 'Manage Tasbeehs')
@section('page_title', 'Tasbeeh')

@push('styles')
<style>
    .zikr-item-card {
        background: linear-gradient(180deg, #0b1526 0%, #070d18 100%);
        border: 1px solid #162a45;
        border-radius: 20px;
        width: 100%;
        padding: 16px;
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
        padding: 18px 20px 14px 20px;
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

    /* Bottom Footer Strip */
    .card-footer-strip {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 6px 2px 0 2px;
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

    .btn-edit-icon {
        color: #2dd4bf;
    }
    .btn-edit-icon:hover {
        background: rgba(45, 212, 191, 0.12);
        border-color: #2dd4bf;
        color: #2dd4bf;
    }

    .btn-del-icon {
        color: #f87171;
        border: 1px solid #182c48;
    }
    .btn-del-icon:hover {
        background: rgba(248, 113, 113, 0.15);
        border-color: #f87171;
        color: #ef4444;
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
<div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-4">
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

    {{-- Zikr Dashboard Shortcut --}}
    <a class="action-btn-top cyan" href="{{ route('admin.zikr.index') }}" title="Zikr Dashboard">
        <i class="bi bi-speedometer2"></i>
    </a>

    {{-- Add New Tasbeeh Trigger --}}
    <button class="action-btn-top accent" type="button" data-bs-toggle="modal" data-bs-target="#createTasbeehModal" title="Add New Tasbeeh">
        <i class="bi bi-plus-lg"></i>
    </button>
</div>

<section data-ajax-crud data-refresh-target="#tasbeehs-grid-container">
    <div id="tasbeehs-grid-container" class="admin-list-results">
        <div class="row g-3 g-md-4">
            @forelse($tasbeehs as $t)
                <div class="col-12 col-lg-6 d-flex">
                    <div class="zikr-item-card w-100 d-flex flex-column justify-content-between">
                        <!-- Right-Aligned Text Area with Custom Center Divider -->
                        <div class="text-container">
                            <!-- Arabic Text -->
                            <div class="arabic-text">
                                {{ $t->arabic_text }}
                            </div>

                            <!-- Sleek Glowing Islamic Center Divider -->
                            <div class="islamic-divider">
                                <div class="divider-icon">✦ ✧ ✦</div>
                            </div>

                            <!-- Urdu Translation -->
                            <div class="urdu-text">
                                {{ $t->urdu_meaning ?? '—' }}
                            </div>
                        </div>

                        <!-- Footer Area -->
                        <div class="card-footer-strip">
                            <!-- Badges -->
                            <div class="badge-info-group">
                                @if(isset($t->stats))
                                    @if($t->stats['extra'] > 0)
                                        <span class="badge-remaining extra">+{{ number_format($t->stats['extra']) }} Extra</span>
                                    @elseif($t->stats['remaining'] === 0)
                                        <span class="badge-remaining completed-badge">Completed</span>
                                    @else
                                        <span class="badge-remaining">Remaining {{ number_format($t->stats['remaining']) }}</span>
                                    @endif
                                    <span class="badge-completed">Completed: <strong class="text-white">{{ number_format($t->stats['total_completed']) }}</strong> / {{ number_format($t->stats['total_required']) }}</span>
                                @else
                                    <span class="badge-remaining">Target: {{ number_format($t->daily_target) }}/day</span>
                                @endif
                                @if(! $t->is_active)
                                    <span class="badge bg-danger-subtle text-danger font-monospace" style="font-size: 0.72rem; padding: 5px 8px; border-radius: 6px;">Inactive</span>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="badge-actions-group">
                                {{-- Quick Add Count Modal Trigger --}}
                                <button
                                    class="action-icon-btn btn-plus-icon"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#quickAddModal"
                                    data-tasbeeh-id="{{ $t->id }}"
                                    data-tasbeeh-title="{{ $t->title }}"
                                    data-user-id="{{ auth()->id() }}"
                                    data-post-url="{{ route('admin.zikr.counter.manual', $t) }}"
                                    title="Add Count"
                                >
                                    <i class="bi bi-plus-lg"></i>
                                </button>

                                {{-- Open Counter Page --}}
                                <a href="{{ route('admin.zikr.counter.show', $t) }}" class="action-icon-btn btn-speed-icon" title="Speed / Counter">
                                    <i class="bi bi-speedometer2"></i>
                                </a>

                                {{-- Edit Tasbeeh --}}
                                <button
                                    class="action-icon-btn btn-edit-icon"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editTasbeehModal"
                                    data-id="{{ $t->id }}"
                                    data-title="{{ $t->title }}"
                                    data-arabic="{{ $t->arabic_text }}"
                                    data-urdu="{{ $t->urdu_meaning }}"
                                    data-target="{{ $t->daily_target }}"
                                    data-order="{{ $t->sort_order }}"
                                    data-active="{{ $t->is_active ? '1' : '0' }}"
                                    data-desc="{{ $t->description }}"
                                    data-ref="{{ $t->reference }}"
                                    data-update-url="{{ route('admin.tasbeehs.update', $t) }}"
                                    title="Edit Zikr"
                                >
                                    <i class="bi bi-pencil"></i>
                                </button>

                                {{-- Delete Tasbeeh --}}
                                <form method="POST" action="{{ route('admin.tasbeehs.destroy', $t) }}" data-ajax-delete data-confirm="Are you sure you want to delete this Tasbeeh? All users' progress for this Tasbeeh will also be deleted." class="d-inline m-0 p-0">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-icon-btn btn-del-icon" type="submit" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted-custom">
                    <i class="bi bi-gem fs-2 text-accent"></i>
                    <p class="mt-2 mb-0">No Tasbeeh found.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Quick Add Count Modal --}}
<div class="modal fade finance-modal" id="quickAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form id="quickAddForm" method="POST" action="">
                @csrf
                <input type="hidden" name="user_id" id="quickAddUserId" value="{{ auth()->id() }}">
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
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill font-monospace" onclick="document.getElementById('quickAddCountInput').value=33">+33</button>
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill font-monospace" onclick="document.getElementById('quickAddCountInput').value=100">+100</button>
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill font-monospace" onclick="document.getElementById('quickAddCountInput').value=300">+300</button>
                        <button type="button" class="btn btn-outline-theme btn-sm flex-fill font-monospace" onclick="document.getElementById('quickAddCountInput').value=1000">+1,000</button>
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

{{-- Create Tasbeeh Modal --}}
<div class="modal fade finance-modal" id="createTasbeehModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form data-ajax-form action="{{ route('admin.tasbeehs.store') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title text-white mb-0">Add New Tasbeeh Master</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label text-white fw-bold">Title <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="title" placeholder="e.g. Tasbeeh-e-Fatima" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                            <div class="invalid-feedback" data-error-for="title"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white fw-bold">Daily Target <span class="text-danger">*</span></label>
                            <input class="form-control font-monospace" type="number" name="daily_target" value="100" min="1" max="100000" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                            <div class="invalid-feedback" data-error-for="daily_target"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white fw-bold">Arabic Text (الْمَتْنُ الْعَرَبِيُّ) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-arabic" name="arabic_text" rows="3" dir="rtl" placeholder="سُبْحَانَ اللهِ..." required style="background: #0c1626; border-color: #1c2c44; color: #f97316; font-size: 1.3rem; line-height: 1.8;"></textarea>
                            <div class="invalid-feedback" data-error-for="arabic_text"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white fw-bold">Urdu Meaning (اردو ترجمہ)</label>
                            <textarea class="form-control font-urdu" name="urdu_meaning" rows="2" dir="rtl" placeholder="اللہ پاک ہے..." style="background: #0c1626; border-color: #1c2c44; color: #94a3b8; font-size: 1.05rem; line-height: 1.8;"></textarea>
                            <div class="invalid-feedback" data-error-for="urdu_meaning"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-bold">Reference / Source (Optional)</label>
                            <input class="form-control" type="text" name="reference" placeholder="e.g. Sahih Bukhari" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white fw-bold">Sort Order</label>
                            <input class="form-control font-monospace" type="number" name="sort_order" value="0" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                                <label class="form-check-label text-white fw-semibold" for="createIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Create Tasbeeh</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Tasbeeh Modal --}}
<div class="modal fade finance-modal" id="editTasbeehModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #08111e; border: 1px solid #142845; border-radius: 20px;">
            <form data-ajax-form id="editTasbeehForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title text-white mb-0">Edit Tasbeeh</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label text-white fw-bold">Title <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="title" id="editTasbeehTitle" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                            <div class="invalid-feedback" data-error-for="title"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-white fw-bold">Daily Target <span class="text-danger">*</span></label>
                            <input class="form-control font-monospace" type="number" name="daily_target" id="editTasbeehTarget" min="1" max="100000" required style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                            <div class="invalid-feedback" data-error-for="daily_target"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white fw-bold">Arabic Text (الْمَتْنُ الْعَرَبِيُّ) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-arabic" name="arabic_text" id="editTasbeehArabic" rows="3" dir="rtl" required style="background: #0c1626; border-color: #1c2c44; color: #f97316; font-size: 1.3rem; line-height: 1.8;"></textarea>
                            <div class="invalid-feedback" data-error-for="arabic_text"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white fw-bold">Urdu Meaning (اردو ترجمہ)</label>
                            <textarea class="form-control font-urdu" name="urdu_meaning" id="editTasbeehUrdu" rows="2" dir="rtl" style="background: #0c1626; border-color: #1c2c44; color: #94a3b8; font-size: 1.05rem; line-height: 1.8;"></textarea>
                            <div class="invalid-feedback" data-error-for="urdu_meaning"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-white fw-bold">Reference / Source (Optional)</label>
                            <input class="form-control" type="text" name="reference" id="editTasbeehRef" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white fw-bold">Sort Order</label>
                            <input class="form-control font-monospace" type="number" name="sort_order" id="editTasbeehOrder" style="background: #0c1626; border-color: #1c2c44; color: #fff;">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editTasbeehIsActive">
                                <label class="form-check-label text-white fw-semibold" for="editTasbeehIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Update Tasbeeh</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.zikr.partials.settings-modal')
@include('admin.zikr.partials.bulk-actions-modals')
@endsection
