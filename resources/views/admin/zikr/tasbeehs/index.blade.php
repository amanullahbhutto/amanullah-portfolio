@extends('layouts.admin')
@section('title', 'Manage Tasbeehs')
@section('page_title', 'Tasbeeh Master Definitions')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <span class="eyebrow mb-0">Zikr Administration</span>
        <h2 class="h4 mb-0">Tasbeeh Master Definitions</h2>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-nav-action cyan btn-sm" href="{{ route('admin.zikr.index') }}">
            <i class="bi bi-speedometer2 text-info"></i><span>Zikr Dashboard</span>
        </a>
        <button class="btn btn-accent btn-sm" data-bs-toggle="modal" data-bs-target="#createTasbeehModal">
            <i class="bi bi-plus-lg me-1"></i>Add New Tasbeeh
        </button>
    </div>
</div>

<section class="admin-card" data-ajax-crud data-refresh-target="#tasbeehs-table-container">
    <div class="admin-card-head">
        <div>
            <h2>Master Tasbeeh List</h2>
            <p class="text-muted-custom small mb-0 mt-1">Manage Arabic text, Urdu meanings, and daily targets for all Muslim users.</p>
        </div>
    </div>

    <div id="tasbeehs-table-container" class="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 65px;">Order</th>
                        <th style="width: 41%;">Arabic Text (الْمَتْنُ الْعَرَبِيُّ)</th>
                        <th style="width: 41%;">Urdu Meaning (اردو ترجمہ)</th>
                        <th class="text-center" style="width: 130px;">Daily Target</th>
                        <th class="text-end" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasbeehs as $t)
                        <tr>
                            <td>
                                <span class="badge bg-surface-2 text-muted-custom border font-monospace">{{ $t->sort_order }}</span>
                            </td>
                            <td dir="rtl" style="width: 41%;">
                                <div class="arabic-script fw-bold text-accent" style="font-size: 1.35rem; line-height: 1.8; font-family: 'Amiri', 'Traditional Arabic', serif;">
                                    {{ $t->arabic_text }}
                                </div>
                                @if($t->reference)
                                    <small class="text-muted-custom d-block mt-1" style="font-size: 0.78rem;">{{ $t->reference }}</small>
                                @endif
                            </td>
                            <td dir="rtl" style="width: 41%;">
                                <div class="urdu-text text-muted-custom" style="line-height: 1.7; font-size: 1.05rem;">
                                    {{ $t->urdu_meaning ?? '—' }}
                                </div>
                            </td>
                            <td class="text-center" style="width: 130px;">
                                <span class="badge bg-success-subtle text-success font-monospace px-3 py-2" style="font-size: 0.88rem;">
                                    {{ number_format($t->daily_target) }} / day
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="action-buttons justify-content-end">
                                    <button
                                        class="btn-icon info"
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

                                    <form method="POST" action="{{ route('admin.tasbeehs.destroy', $t) }}" data-ajax-delete data-confirm="Are you sure you want to delete this Tasbeeh? All users' progress for this Tasbeeh will also be deleted.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-icon danger" type="submit" title="Delete Tasbeeh">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted-custom">
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

{{-- Create Tasbeeh Modal --}}
<div class="modal fade finance-modal" id="createTasbeehModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form data-ajax-form action="{{ route('admin.tasbeehs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Tasbeeh Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="title" placeholder="e.g. Tasbeeh-e-Fatima" required>
                            <div class="invalid-feedback" data-error-for="title"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Daily Target <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" name="daily_target" value="100" min="1" max="100000" required>
                            <div class="invalid-feedback" data-error-for="daily_target"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Arabic Text (الْمَتْنُ الْعَرَبِيُّ) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-arabic" name="arabic_text" rows="3" dir="rtl" placeholder="سُبْحَانَ اللهِ..." required style="font-size: 1.25rem; line-height: 1.8;"></textarea>
                            <div class="invalid-feedback" data-error-for="arabic_text"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Urdu Meaning (اردو ترجمہ)</label>
                            <textarea class="form-control font-urdu" name="urdu_meaning" rows="2" dir="rtl" placeholder="اللہ پاک ہے..." style="font-size: 1rem; line-height: 1.6;"></textarea>
                            <div class="invalid-feedback" data-error-for="urdu_meaning"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference / Source (Optional)</label>
                            <input class="form-control" type="text" name="reference" placeholder="e.g. Sahih Bukhari">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sort Order</label>
                            <input class="form-control" type="number" name="sort_order" value="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                                <label class="form-check-label" for="createIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
        <div class="modal-content">
            <form data-ajax-form id="editTasbeehForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tasbeeh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="title" id="editTasbeehTitle" required>
                            <div class="invalid-feedback" data-error-for="title"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Daily Target <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" name="daily_target" id="editTasbeehTarget" min="1" max="100000" required>
                            <div class="invalid-feedback" data-error-for="daily_target"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Arabic Text (الْمَتْنُ الْعَرَبِيُّ) <span class="text-danger">*</span></label>
                            <textarea class="form-control font-arabic" name="arabic_text" id="editTasbeehArabic" rows="3" dir="rtl" required style="font-size: 1.25rem; line-height: 1.8;"></textarea>
                            <div class="invalid-feedback" data-error-for="arabic_text"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Urdu Meaning (اردو ترجمہ)</label>
                            <textarea class="form-control font-urdu" name="urdu_meaning" id="editTasbeehUrdu" rows="2" dir="rtl" style="font-size: 1rem; line-height: 1.6;"></textarea>
                            <div class="invalid-feedback" data-error-for="urdu_meaning"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference / Source (Optional)</label>
                            <input class="form-control" type="text" name="reference" id="editTasbeehRef">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sort Order</label>
                            <input class="form-control" type="number" name="sort_order" id="editTasbeehOrder">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editTasbeehIsActive">
                                <label class="form-check-label" for="editTasbeehIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
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
