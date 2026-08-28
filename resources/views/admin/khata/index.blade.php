@extends('layouts.admin')
@section('title', 'Khata System')
@section('page_title', 'Khata System')

@section('content')
<div class="row g-4 mb-4" data-khata-stats>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon blue"><i class="bi bi-people"></i></div>
            <div>
                <strong>{{ number_format($totalCustomers) }}</strong>
                <span>Total Customers</span>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon green"><i class="bi bi-arrow-down-left-circle"></i></div>
            <div>
                <strong>Rs. {{ number_format($totalPeseLiyeAll, 2) }}</strong>
                <span>Total Pese Liye (Cash In / Wasool)</span>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon red"><i class="bi bi-arrow-up-right-circle"></i></div>
            <div>
                <strong class="text-danger">Rs. {{ number_format($totalPeseDiyeAll, 2) }}</strong>
                <span>Total Pese Diye (Cash Out / Given)</span>
            </div>
        </article>
    </div>
    <div class="col-sm-6 col-xl-3">
        <article class="admin-card stat-card">
            <div class="stat-icon purple"><i class="bi bi-wallet2"></i></div>
            <div>
                <strong class="{{ $totalNetBalance > 0 ? 'text-success' : ($totalNetBalance < 0 ? 'text-danger' : '') }}">Rs. {{ number_format(abs($totalNetBalance), 2) }}</strong>
                <span>
                    Net Outstanding 
                    @if($totalNetBalance > 0)
                        <small class="text-success fw-bold">(Market Se Lene Hain)</small>
                    @elseif($totalNetBalance < 0)
                        <small class="text-danger fw-bold">(Customers Ko Dene Hain)</small>
                    @else
                        <small class="text-muted-custom fw-bold">(Hisab Clear)</small>
                    @endif
                </span>
            </div>
        </article>
    </div>
</div>

<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>Customers Khata</h2>
            <p class="text-muted-custom small mb-0 mt-1">Manage customers, view individual khata ledgers, and record cash in / cash out.</p>
        </div>
        @can('create khata customer')
            <button class="btn btn-accent btn-sm" data-crud-open data-modal="#customerModal" data-store-url="{{ route('admin.khata.customers.store') }}">
                <i class="bi bi-person-plus me-1"></i>Add Customer
            </button>
        @endcan
    </div>

    <div class="admin-list-toolbar">
        <form class="admin-search financial-filter" method="GET" action="{{ route('admin.khata.index') }}" data-live-search data-live-search-target="#admin-list-results">
            <div class="search-field">
                <i class="bi bi-search"></i>
                <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search by customer name, phone, address...">
                <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <select class="form-select filter-select" name="status">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </form>
    </div>

    <div id="admin-list-results" class="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th class="text-end">Opening Bal</th>
                        <th class="text-end text-success">Customer Se Liye (In)</th>
                        <th class="text-end text-danger">Customer Ko Diye (Out)</th>
                        <th class="text-end">Balance (Lena / Dena)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $row)
                        @php
                            $peseLiye = (float) ($row->total_pese_liye_sum ?? 0);
                            $peseDiye = (float) ($row->total_pese_diye_sum ?? 0);
                            $balance = (float) $row->opening_balance + $peseDiye - $peseLiye;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.khata.show', $row) }}" class="fw-bold text-accent text-decoration-none">
                                    {{ $row->name }}
                                </a>
                                @if($row->phone || $row->address)
                                    <div class="small text-muted-custom mt-1" style="font-size: 0.75rem; line-height: 1.3;">
                                        @if($row->phone)<span><i class="bi bi-telephone me-1"></i>{{ $row->phone }}</span>@endif
                                        @if($row->phone && $row->address)<span class="mx-1">•</span>@endif
                                        @if($row->address)<span><i class="bi bi-geo-alt me-1"></i>{{ \Illuminate\Support\Str::limit($row->address, 32) }}</span>@endif
                                    </div>
                                @endif
                            </td>
                            <td class="text-end">
                                Rs. {{ number_format($row->opening_balance, 2) }}
                            </td>
                            <td class="text-end">
                                <span class="text-success fw-bold">Rs. {{ number_format($peseLiye, 2) }}</span>
                                <small class="d-block text-muted-custom" style="font-size: 0.7rem;">{{ $row->name }} se wasool</small>
                            </td>
                            <td class="text-end">
                                <span class="text-danger fw-bold">Rs. {{ number_format($peseDiye, 2) }}</span>
                                <small class="d-block text-danger" style="font-size: 0.7rem;">{{ $row->name }} ko payment</small>
                            </td>
                            <td class="text-end">
                                <strong class="fs-6 {{ $balance > 0 ? 'text-success' : ($balance < 0 ? 'text-danger' : 'text-muted-custom') }}">
                                    Rs. {{ number_format(abs($balance), 2) }}
                                </strong>
                                <br>
                                @if($balance > 0)
                                    <span class="badge bg-success-subtle text-success" style="font-size: 0.68rem;" title="Aap ne {{ $row->name }} se Rs. {{ number_format($balance, 2) }} lene hain">
                                        <i class="bi bi-arrow-down-left me-1"></i>{{ $row->name }} Se Lene Hain
                                    </span>
                                @elseif($balance < 0)
                                    <span class="badge bg-danger-subtle text-danger" style="font-size: 0.68rem;" title="Aap ne {{ $row->name }} ko Rs. {{ number_format(abs($balance), 2) }} dene hain">
                                        <i class="bi bi-arrow-up-right me-1"></i>{{ $row->name }} Ko Dene Hain
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.68rem;">
                                        <i class="bi bi-check2-circle me-1"></i>Hisab Clear (0)
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    @can('create khata transaction')
                                        <button class="btn-icon success" data-khata-trx-open data-customer-id="{{ $row->id }}" data-customer-name="{{ $row->name }}" data-type="pese_liye" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $row->name }} Se Pese Liye (Cash In / Wasool)">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <button class="btn-icon danger" data-khata-trx-open data-customer-id="{{ $row->id }}" data-customer-name="{{ $row->name }}" data-type="pese_diye" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $row->name }} Ko Pese Diye (Cash Out / Given)">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                    @endcan
                                    <a class="btn-icon accent" href="{{ route('admin.khata.show', $row) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="View {{ $row->name }} Khata Ledger">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                    @can('update khata customer')
                                        <button class="btn-icon info" data-crud-edit data-modal="#customerModal" data-action="{{ route('admin.khata.customers.update', $row) }}" data-record="{{ json_encode(['name' => $row->name, 'phone' => $row->phone, 'address' => $row->address, 'opening_balance' => $row->opening_balance, 'status' => $row->status, 'notes' => $row->notes]) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit {{ $row->name }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan
                                    @can('delete khata customer')
                                        <form method="POST" action="{{ route('admin.khata.customers.destroy', $row) }}" data-ajax-delete data-confirm="Are you sure you want to delete this customer? All transaction history in this Khata will be removed.">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete {{ $row->name }}">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted-custom">
                                <i class="bi bi-journal-x fs-2 text-accent"></i>
                                <p class="mt-2 mb-0">No customers found in Khata.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            @include('admin.partials.pagination', ['paginator' => $customers])
        </div>
    </div>
</section>

{{-- Customer Add / Edit Modal --}}
<div class="modal fade finance-modal" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form>
                <div class="modal-header">
                    <h5 class="modal-title" data-modal-title data-create-title="Add Customer to Khata">Add Customer to Khata</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="name" placeholder="e.g. Ahmed Ali" required>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input class="form-control" type="text" name="phone" placeholder="e.g. 03001234567">
                            <div class="invalid-feedback" data-error-for="phone"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Opening Balance (Rs.)</label>
                            <input class="form-control" type="number" step="0.01" min="0" name="opening_balance" value="0.00" placeholder="0.00">
                            <small class="text-muted-custom">Starting balance if customer previously owed money.</small>
                            <div class="invalid-feedback" data-error-for="opening_balance"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback" data-error-for="status"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Customer address / shop / location"></textarea>
                            <div class="invalid-feedback" data-error-for="address"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Any special notes or references"></textarea>
                            <div class="invalid-feedback" data-error-for="notes"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>
                        <span data-submit-label>Save Customer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick Transaction Modal (Pese Liye / Pese Diye) --}}
<div class="modal fade finance-modal" id="quickKhataTrxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form action="{{ route('admin.khata.transactions.store') }}">
                <input type="hidden" name="khata_customer_id" id="quickTrxCustomerId">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="quickTrxModalTitle">Record Transaction</h5>
                        <p class="text-muted-custom small mb-0" id="quickTrxCustomerSubtitle">Customer: <strong id="quickTrxCustomerName">Customer</strong></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="quickTypeLiye" value="pese_liye" checked>
                                    <label class="form-check-label fw-bold text-success" for="quickTypeLiye">
                                        <i class="bi bi-arrow-down-left-circle me-1"></i><span id="quickNameLiye">Customer</span> Se Pese Liye (Cash In / Wasool)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="quickTypeDiye" value="pese_diye">
                                    <label class="form-check-label fw-bold text-danger" for="quickTypeDiye">
                                        <i class="bi bi-arrow-up-right-circle me-1"></i><span id="quickNameDiye">Customer</span> Ko Pese Diye (Cash Out / Given)
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
                            <textarea class="form-control" name="description" rows="2" placeholder="e.g. Payment for invoice #12, cash received, advance, etc."></textarea>
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
@endsection
