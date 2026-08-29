@extends('layouts.admin')
@section('title', 'Program Expenses')
@section('page_title', 'Program Expenses')

@section('content')
<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>Program Expenses</h2>
            <p class="text-muted-custom small mb-0 mt-1">Track and manage all program-related expenditures and costs.</p>
        </div>
        <button class="btn btn-accent btn-sm" data-crud-open data-modal="#expenseModal" data-store-url="{{ route('admin.program-expenses.store') }}">
            <i class="bi bi-plus-lg me-1"></i>Add Expense
        </button>
    </div>

    <div class="admin-list-toolbar">
        <form class="admin-search financial-filter wide-filter" method="GET" action="{{ route('admin.program-expenses.index') }}" data-live-search data-live-search-target="#admin-list-results">
            <div class="search-field">
                <i class="bi bi-search"></i>
                <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search details or category...">
                <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <select class="form-select filter-select program-filter-select" name="program_id">
                <option value="">All programs</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" @selected($programId === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <select class="form-select filter-select category-filter-select" name="expense_category_id">
                <option value="">All categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected($categoryId === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>
        <div class="finance-badge" data-finance-total>Total Expenses: <strong>Rs. {{ number_format($total, 2) }}</strong></div>
    </div>

    <div id="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Program</th>
                        <th>Category</th>
                        <th>Details</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $row)
                        <tr>
                            <td>{{ $row->expense_date->format('M d, Y') }}</td>
                            <td><strong>{{ $row->program->name }}</strong></td>
                            <td><span class="duration-pill">{{ $row->category->name }}</span></td>
                            <td>{{ Str::limit($row->details, 60) ?: '-' }}</td>
                            <td class="text-end text-danger"><strong>Rs. {{ number_format($row->amount, 2) }}</strong></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" data-crud-edit data-modal="#expenseModal" data-action="{{ route('admin.program-expenses.update', $row) }}" data-record="{{ json_encode(['program_id' => $row->program_id, 'expense_category_id' => $row->expense_category_id, 'expense_date' => $row->expense_date->format('Y-m-d'), 'amount' => $row->amount, 'details' => $row->details]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.program-expenses.destroy', $row) }}" data-ajax-delete data-confirm="Delete this expense?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-icon danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted-custom">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            @include('admin.partials.pagination', ['paginator' => $expenses])
        </div>
    </div>
</section>

{{-- Add / Edit Expense Modal --}}
<div class="modal fade finance-modal" id="expenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form>
                <input type="hidden" name="_method" value="PUT" data-method disabled>
                <div class="modal-header">
                    <h5 class="modal-title" data-modal-title>Add Expense</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program <span class="text-danger">*</span></label>
                            <select class="form-select" name="program_id" id="modalExpenseProgramSelect" required>
                                <option value="">Select program</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="program_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="expense_date" value="{{ now()->format('Y-m-d') }}" required>
                            <div class="invalid-feedback" data-error-for="expense_date"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Expense Category <span class="text-danger">*</span></label>
                            <div class="category-input-group">
                                <select class="form-select" name="expense_category_id" data-category-select required>
                                    <option value="">Select category</option>
                                    @foreach($categories->where('status', 'active') as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-category-add" type="button" data-bs-toggle="modal" data-bs-target="#quickCategoryModal" title="Add new category">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" data-error-for="expense_category_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required>
                            <div class="invalid-feedback" data-error-for="amount"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Details / Description</label>
                            <textarea class="form-control" name="details" rows="3" placeholder="Explain what this expense was for..."></textarea>
                            <div class="invalid-feedback" data-error-for="details"></div>
                        </div>

                        {{-- Paid To, Reference, and Notes fields completely removed per user request --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit><span data-submit-label>Save Expense</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick Add Category Modal --}}
<div class="modal fade finance-modal" id="quickCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form data-quick-category data-store-url="{{ route('admin.expense-categories.store') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Category</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" placeholder="e.g. Refreshments, Venue, Printing" required autofocus>
                        <input type="hidden" name="status" value="active">
                        <div class="invalid-feedback" data-error-for="name"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Brief details about this category..."></textarea>
                        <div class="invalid-feedback" data-error-for="description"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toolbarProgramFilter = document.querySelector('.financial-filter select[name="program_id"]');
    const modalProgramSelect = document.getElementById('modalExpenseProgramSelect');

    const STORAGE_KEY = 'amanullah_selected_program_id';

    function getPreferredProgramId() {
        if (toolbarProgramFilter && toolbarProgramFilter.value) {
            return toolbarProgramFilter.value;
        }
        return localStorage.getItem(STORAGE_KEY) || '';
    }

    function setPreferredProgramId(id) {
        if (id) {
            localStorage.setItem(STORAGE_KEY, id);
        }
    }

    if (toolbarProgramFilter) {
        toolbarProgramFilter.addEventListener('change', function () {
            if (this.value) setPreferredProgramId(this.value);
        });
    }

    if (modalProgramSelect) {
        modalProgramSelect.addEventListener('change', function () {
            if (this.value) setPreferredProgramId(this.value);
        });
    }

    // Auto-select program when opening Add Expense modal
    document.addEventListener('click', function (e) {
        const createBtn = e.target.closest('[data-crud-open][data-modal="#expenseModal"]');
        if (createBtn) {
            setTimeout(() => {
                const preferredProg = getPreferredProgramId();
                if (modalProgramSelect && preferredProg) {
                    modalProgramSelect.value = preferredProg;
                }
            }, 50);
        }
    });
});
</script>
@endpush
