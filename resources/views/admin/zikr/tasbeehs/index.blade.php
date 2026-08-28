@extends('layouts.admin')
@section('title', 'Manage Tasbeehs')
@section('page_title', 'Tasbeeh Master Definitions')

@push('styles')
<style>
    .tasbeeh-admin-card {
        background: #08111e;
        border: 1px solid #142845;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
        overflow: hidden;
    }

    .tasbeeh-admin-header {
        padding: 20px 24px;
        border-bottom: 1px solid #142845;
        background: #0a1424;
    }

    .tasbeeh-table {
        margin-bottom: 0;
        color: #f1f5f9;
    }

    .tasbeeh-table thead th {
        background: #091322;
        color: #94a3b8;
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 14px 16px;
        border-bottom: 1px solid #142845;
    }

    .tasbeeh-table tbody tr {
        border-bottom: 1px solid #102138;
        transition: background 0.15s ease;
    }

    .tasbeeh-table tbody tr:hover {
        background: #0c182c;
    }

    .tasbeeh-table tbody td {
        padding: 16px;
        vertical-align: middle;
    }

    .arabic-cell-text {
        font-family: 'Scheherazade New', 'Amiri Quran', 'Amiri', 'PDMS_Saleem_QuranFont', '_PDMS_Saleem_Quran', 'Traditional Arabic', serif !important;
        font-feature-settings: "liga" 1, "cv01" 1;
        color: #f97316;
        font-size: 1.3rem;
        line-height: 2.0;
        direction: rtl;
        font-weight: 700;
    }

    .urdu-cell-text {
        font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Urdu Typesetting', serif !important;
        color: #94a3b8;
        font-size: 1rem;
        line-height: 2.1;
        direction: rtl;
    }

    .badge-target-pill {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        white-space: nowrap !important;
        background: rgba(16, 185, 129, 0.14) !important;
        border: 1px solid rgba(16, 185, 129, 0.4) !important;
        color: #10b981 !important;
        font-weight: 700 !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-size: 0.85rem !important;
        line-height: 1.2 !important;
        letter-spacing: 0.02em !important;
    }

    .btn-quick {
        background: #0a1728;
        border: 1px solid #162a47;
        color: #00bcd4;
        font-weight: 600;
        border-radius: 10px;
        padding: 6px 12px;
        font-size: 0.82rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-quick:hover {
        background: #10223b;
        color: #22d3ee;
        border-color: #00bcd4;
        box-shadow: 0 4px 16px rgba(0, 188, 212, 0.25);
        transform: translateY(-1px);
    }

    .action-btn-circle {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #162a47;
        background: #0a1728;
        color: #00bcd4;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .action-btn-circle:hover {
        background: #10223b;
        color: #22d3ee;
        border-color: #00bcd4;
        box-shadow: 0 3px 12px rgba(0, 188, 212, 0.25);
    }

    .action-btn-circle.delete {
        color: #f87171;
    }

    .action-btn-circle.delete:hover {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border-color: #ef4444;
        box-shadow: 0 3px 12px rgba(239, 68, 68, 0.25);
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <span class="eyebrow mb-0">Zikr Administration</span>
        <h2 class="h4 mb-0 text-white">Tasbeeh Master Definitions</h2>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-nav-action cyan btn-sm" href="{{ route('admin.zikr.index') }}">
            <i class="bi bi-speedometer2"></i><span>Zikr Dashboard</span>
        </a>
        <button class="btn btn-accent btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createTasbeehModal">
            <i class="bi bi-plus-lg"></i><span>Add New Tasbeeh</span>
        </button>
    </div>
</div>

<section class="tasbeeh-admin-card" data-ajax-crud data-refresh-target="#tasbeehs-table-container">
    <div class="tasbeeh-admin-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h5 class="mb-1 text-white fw-bold">Master Tasbeeh List</h5>
            <p class="text-muted-custom small mb-0">Manage Arabic text, Urdu meanings, and daily targets for all Muslim users.</p>
        </div>
    </div>

    <div id="tasbeehs-table-container" class="admin-list-results">
        <div class="table-responsive">
            <table class="table tasbeeh-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50%;">Arabic Text (الْمَتْنُ الْعَرَبِيُّ)</th>
                        <th style="width: 50%;">Urdu Meaning (اردو ترجمہ)</th>
                        <th class="text-end text-nowrap" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasbeehs as $t)
                        <tr>
                            <td dir="rtl">
                                <div class="arabic-cell-text">
                                    {{ $t->arabic_text }}
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                    @if(! $t->is_active)
                                        <span class="badge bg-danger-subtle text-danger small" style="font-size: 0.72rem;">Inactive</span>
                                    @endif

                                    @if(isset($t->stats))
                                        <span class="badge bg-surface-2 border border-secondary border-opacity-25 font-monospace text-muted-custom small" style="font-size: 0.74rem;">
                                            Completed: <strong class="text-success">{{ number_format($t->stats['total_completed']) }}</strong> / {{ number_format($t->stats['total_required']) }}
                                        </span>
                                        @if($t->stats['extra'] > 0)
                                            <span class="badge font-monospace small" style="background: rgba(0, 188, 212, 0.15); color: #00e5ff; border: 1px solid rgba(0, 188, 212, 0.4); font-size: 0.74rem;">
                                                +{{ number_format($t->stats['extra']) }} Extra
                                            </span>
                                        @elseif($t->stats['remaining'] === 0)
                                            <span class="badge font-monospace small" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.4); font-size: 0.74rem;">
                                                Completed
                                            </span>
                                        @else
                                            <span class="badge font-monospace small" style="background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.74rem;">
                                                {{ number_format($t->stats['remaining']) }} Remaining
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td dir="rtl">
                                <div class="urdu-cell-text">
                                    {{ $t->urdu_meaning ?? '—' }}
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    {{-- Quick Add Count Modal Trigger --}}
                                    <button
                                        class="action-btn-circle"
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
                                    <a href="{{ route('admin.zikr.counter.show', $t) }}" class="action-btn-circle" title="Open Live Counter">
                                        <i class="bi bi-speedometer2"></i>
                                    </a>

                                    {{-- Edit Tasbeeh --}}
                                    <button
                                        class="action-btn-circle"
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
                                        title="Edit Tasbeeh"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- Delete Tasbeeh --}}
                                    <form method="POST" action="{{ route('admin.tasbeehs.destroy', $t) }}" data-ajax-delete data-confirm="Are you sure you want to delete this Tasbeeh? All users' progress for this Tasbeeh will also be deleted.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-btn-circle delete border-0" type="submit" title="Delete Tasbeeh">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted-custom">
                                <i class="bi bi-gem fs-2 text-accent"></i>
                                <p class="mt-2 mb-0">No Tasbeeh master definitions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
@endsection
