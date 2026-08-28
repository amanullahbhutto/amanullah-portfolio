@extends('layouts.admin')
@section('title', $khataCustomer->name . ' - Khata Ledger')
@section('page_title', 'Khata Ledger: ' . $khataCustomer->name)

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <span class="eyebrow">Customer Khata Account</span>
        <h2 class="h4 mb-1">{{ $khataCustomer->name }}</h2>
        <p class="text-muted-custom mb-0">
            @if($khataCustomer->phone)
                <span class="me-3"><i class="bi bi-telephone text-accent me-1"></i>{{ $khataCustomer->phone }}</span>
            @endif
            @if($khataCustomer->address)
                <span><i class="bi bi-geo-alt text-accent me-1"></i>{{ $khataCustomer->address }}</span>
            @endif
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.khata.index') }}">
            <i class="bi bi-arrow-left me-1"></i>Back to Khata List
        </a>
        @can('create khata transaction')
            <button class="btn btn-sm btn-outline-success" data-ledger-trx-open data-type="pese_liye" title="{{ $khataCustomer->name }} se pese liye (Cash In)">
                <i class="bi bi-plus-circle me-1"></i>+ {{ $khataCustomer->name }} Se Liye
            </button>
            <button class="btn btn-sm btn-outline-danger" data-ledger-trx-open data-type="pese_diye" title="{{ $khataCustomer->name }} ko pese diye (Cash Out)">
                <i class="bi bi-dash-circle me-1"></i>- {{ $khataCustomer->name }} Ko Diye
            </button>
        @endcan
        @can('update khata customer')
            <button class="btn btn-outline-theme btn-sm" data-crud-edit data-modal="#customerModal" data-action="{{ route('admin.khata.customers.update', $khataCustomer) }}" data-record="{{ json_encode(['name' => $khataCustomer->name, 'phone' => $khataCustomer->phone, 'address' => $khataCustomer->address, 'opening_balance' => $khataCustomer->opening_balance, 'status' => $khataCustomer->status, 'notes' => $khataCustomer->notes]) }}">
                <i class="bi bi-pencil me-1"></i>Edit Customer
            </button>
        @endcan
    </div>
</div>

<div class="row g-4 mb-4" data-khata-stats>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon blue"><i class="bi bi-file-earmark-lock"></i></div>
            <div>
                <strong>Rs. {{ number_format($khataCustomer->opening_balance, 2) }}</strong>
                <span>Opening Balance</span>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon green"><i class="bi bi-arrow-down-left-circle"></i></div>
            <div>
                <strong class="text-success">Rs. {{ number_format($totalPeseLiye, 2) }}</strong>
                <span>{{ $khataCustomer->name }} Se Total Liye (Wasool)</span>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon red"><i class="bi bi-arrow-up-right-circle"></i></div>
            <div>
                <strong class="text-danger">Rs. {{ number_format($totalPeseDiye, 2) }}</strong>
                <span>{{ $khataCustomer->name }} Ko Total Diye (Given)</span>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon purple"><i class="bi bi-wallet2"></i></div>
            <div>
                <strong class="{{ $currentBalance > 0 ? 'text-success' : ($currentBalance < 0 ? 'text-danger' : '') }}">
                    Rs. {{ number_format(abs($currentBalance), 2) }}
                </strong>
                <span>
                    Current Balance
                    @if($currentBalance > 0)
                        <small class="text-success fw-bold">({{ $khataCustomer->name }} Se Lene Hain)</small>
                    @elseif($currentBalance < 0)
                        <small class="text-danger fw-bold">({{ $khataCustomer->name }} Ko Dene Hain)</small>
                    @else
                        <small class="text-muted-custom fw-bold">(Hisab Barabar / 0)</small>
                    @endif
                </span>
            </div>
        </article>
    </div>
</div>

<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>{{ $khataCustomer->name }} - Transaction Statement</h2>
            <p class="text-muted-custom small mb-0 mt-1">Detailed ledger statement with automated running balance calculation.</p>
        </div>
        @can('create khata transaction')
            <button class="btn btn-accent btn-sm" data-crud-open data-modal="#transactionModal" data-store-url="{{ route('admin.khata.transactions.store') }}">
                <i class="bi bi-plus-lg me-1"></i>Add Transaction
            </button>
        @endcan
    </div>

    <div class="admin-list-toolbar">
        <form class="admin-search financial-filter" method="GET" action="{{ route('admin.khata.show', $khataCustomer) }}" data-live-search data-live-search-target="#admin-list-results">
            <div class="search-field">
                <i class="bi bi-search"></i>
                <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search description...">
                <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <select class="form-select filter-select" name="type">
                <option value="">All Transactions</option>
                <option value="pese_liye" @selected(request('type') === 'pese_liye')>{{ $khataCustomer->name }} Se Liye (In)</option>
                <option value="pese_diye" @selected(request('type') === 'pese_diye')>{{ $khataCustomer->name }} Ko Diye (Out)</option>
            </select>
            <input class="form-control filter-date" type="date" name="start_date" value="{{ request('start_date') }}" title="Start Date">
            <input class="form-control filter-date" type="date" name="end_date" value="{{ request('end_date') }}" title="End Date">
        </form>
    </div>

    <div id="admin-list-results" class="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end text-success">{{ $khataCustomer->name }} Se Liye (In)</th>
                        <th class="text-end text-danger">{{ $khataCustomer->name }} Ko Diye (Out)</th>
                        <th class="text-end">Running Balance</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filteredLedger as $row)
                        <tr>
                            <td>
                                <strong>{{ $row->transaction_date->format('M d, Y') }}</strong>
                            </td>
                            <td>
                                @if($row->type === 'pese_liye')
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-arrow-down-left me-1"></i>{{ $khataCustomer->name }} Se Liye
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="bi bi-arrow-up-right me-1"></i>{{ $khataCustomer->name }} Ko Diye
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span>{{ $row->description ?: 'Payment entry' }}</span>
                                @if($row->creator)
                                    <br><small class="text-muted-custom">By: {{ $row->creator->name }}</small>
                                @endif
                            </td>
                            <td class="text-end text-success fw-bold">
                                {{ $row->pese_liye > 0 ? 'Rs. ' . number_format($row->pese_liye, 2) : '-' }}
                            </td>
                            <td class="text-end text-danger fw-bold">
                                {{ $row->pese_diye > 0 ? 'Rs. ' . number_format($row->pese_diye, 2) : '-' }}
                            </td>
                            <td class="text-end">
                                <strong class="{{ $row->running_balance > 0 ? 'text-success' : ($row->running_balance < 0 ? 'text-danger' : 'text-muted-custom') }}">
                                    Rs. {{ number_format(abs($row->running_balance), 2) }}
                                </strong>
                                <small class="d-block" style="font-size: 0.72rem;">
                                    @if($row->running_balance > 0)
                                        <span class="text-success fw-bold">({{ $khataCustomer->name }} se lene hain)</span>
                                    @elseif($row->running_balance < 0)
                                        <span class="text-danger fw-bold">({{ $khataCustomer->name }} ko dene hain)</span>
                                    @else
                                        <span class="text-muted-custom">(Hisab Barabar)</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @can('update khata transaction')
                                        <button class="btn-icon info" data-crud-edit data-modal="#transactionModal" data-action="{{ route('admin.khata.transactions.update', $row) }}" data-record="{{ json_encode(['khata_customer_id' => $khataCustomer->id, 'type' => $row->type, 'amount' => $row->amount, 'transaction_date' => $row->transaction_date->format('Y-m-d'), 'description' => $row->description]) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Transaction">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan
                                    @can('delete khata transaction')
                                        <form method="POST" action="{{ route('admin.khata.transactions.destroy', $row) }}" data-ajax-delete data-confirm="Delete this transaction? Khata balance will be recalculated automatically.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Transaction">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted-custom">
                                <i class="bi bi-journal-check fs-2 text-accent"></i>
                                <p class="mt-2 mb-0">No transactions recorded for {{ $khataCustomer->name }} yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- Transaction Add / Edit Modal --}}
<div class="modal fade finance-modal" id="transactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form action="{{ route('admin.khata.transactions.store') }}">
                <input type="hidden" name="_method" value="PUT" data-method disabled>
                <input type="hidden" name="khata_customer_id" value="{{ $khataCustomer->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" data-modal-title data-create-title="Transaction with {{ $khataCustomer->name }}">Transaction with {{ $khataCustomer->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="ledgerTypeLiye" value="pese_liye" checked>
                                    <label class="form-check-label fw-bold text-success" for="ledgerTypeLiye">
                                        <i class="bi bi-arrow-down-left-circle me-1"></i>{{ $khataCustomer->name }} Se Pese Liye (Cash Received)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="ledgerTypeDiye" value="pese_diye">
                                    <label class="form-check-label fw-bold text-danger" for="ledgerTypeDiye">
                                        <i class="bi bi-arrow-up-right-circle me-1"></i>{{ $khataCustomer->name }} Ko Pese Diye (Cash Given)
                                    </label>
                                </div>
                            </div>
                            <div class="invalid-feedback" data-error-for="type"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required>
                            <div class="invalid-feedback" data-error-for="amount"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback" data-error-for="transaction_date"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / Note</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="e.g. Received partial payment, goods sold, etc."></textarea>
                            <div class="invalid-feedback" data-error-for="description"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Save Transaction</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Customer Edit Modal --}}
<div class="modal fade finance-modal" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form action="{{ route('admin.khata.customers.update', $khataCustomer) }}">
                <input type="hidden" name="_method" value="PUT" data-method>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="name" value="{{ $khataCustomer->name }}" required>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input class="form-control" type="text" name="phone" value="{{ $khataCustomer->phone }}">
                            <div class="invalid-feedback" data-error-for="phone"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Opening Balance (Rs.)</label>
                            <input class="form-control" type="number" step="0.01" min="0" name="opening_balance" value="{{ $khataCustomer->opening_balance }}">
                            <div class="invalid-feedback" data-error-for="opening_balance"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" @selected($khataCustomer->status === 'active')>Active</option>
                                <option value="inactive" @selected($khataCustomer->status === 'inactive')>Inactive</option>
                            </select>
                            <div class="invalid-feedback" data-error-for="status"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2">{{ $khataCustomer->address }}</textarea>
                            <div class="invalid-feedback" data-error-for="address"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="2">{{ $khataCustomer->notes }}</textarea>
                            <div class="invalid-feedback" data-error-for="notes"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Update Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
