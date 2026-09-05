@extends('layouts.admin')
@section('title','Investors')
@section('page_title','Investors')
@section('content')
<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>Investors</h2>
        </div>

        <div class="head-search-wrap flex-grow-1">
            <form class="admin-search financial-filter" method="GET" action="{{ route('admin.investors.index') }}" data-live-search data-live-search-target="#admin-list-results">
                <div class="search-field flex-grow-1">
                    <i class="bi bi-search"></i>
                    <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search investor...">
                    <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <select class="form-select filter-select" name="status">
                    <option value="">All status</option>
                    @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'on_hold' => 'On Hold'] as $v => $l)
                        <option value="{{ $v }}" @selected($status === $v)>{{ $l }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="responsive-actions">
            <button class="btn btn-accent btn-sm" data-crud-open data-modal="#investorModal" data-store-url="{{ route('admin.investors.store') }}">
                <i class="bi bi-plus-lg me-1"></i>Add Investor
            </button>
        </div>
    </div>
<div id="admin-list-results" class="admin-list-results"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Investor</th><th>Current Investment</th><th>Profit %</th><th>Total Profit</th><th>Paid</th><th>Pending</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
@forelse($investors as $row)<tr><td><strong>{{ $row->name }}</strong><br><small class="text-muted-custom">{{ $row->phone ?: $row->email }}</small></td><td>Rs. {{ number_format($row->current_investment,2) }}</td><td>{{ number_format($row->profit_share_percentage,2) }}%</td><td>Rs. {{ number_format($row->total_profit,2) }}</td><td>Rs. {{ number_format($row->paid_profit,2) }}</td><td><strong>Rs. {{ number_format($row->pending_profit,2) }}</strong></td><td><span class="status-badge {{ $row->status==='active'?'live':($row->status==='on_hold'?'new':'draft') }}">{{ ucfirst(str_replace('_',' ',$row->status)) }}</span></td><td><div class="action-buttons"><a class="btn-icon" href="{{ route('admin.investors.ledger',$row) }}" title="Ledger"><i class="bi bi-journal-text"></i></a><button class="btn-icon" data-crud-edit data-modal="#investorModal" data-action="{{ route('admin.investors.update',$row) }}" data-record="{{ json_encode(['name'=>$row->name,'phone'=>$row->phone,'email'=>$row->email,'cnic_reference'=>$row->cnic_reference,'profit_share_percentage'=>$row->profit_share_percentage,'joining_date'=>$row->joining_date?->format('Y-m-d'),'status'=>$row->status,'notes'=>$row->notes]) }}"><i class="bi bi-pencil"></i></button><form method="POST" action="{{ route('admin.investors.destroy',$row) }}" data-ajax-delete data-confirm="Delete this investor? Financial history will be protected.">@csrf @method('DELETE')<button class="btn-icon danger"><i class="bi bi-trash3"></i></button></form></div></td></tr>@empty<tr><td colspan="8" class="text-center py-5 text-muted-custom">No investors found.</td></tr>@endforelse
</tbody></table></div><div class="admin-pagination">@include('admin.partials.pagination',['paginator'=>$investors])</div></div>
</section>
<div class="modal fade finance-modal" id="investorModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><form data-ajax-form><input type="hidden" name="_method" value="PUT" data-method disabled><div class="modal-header"><h5 class="modal-title" data-modal-title>Add Investor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
@foreach([['name','Name','text',true],['phone','Phone','text',false],['email','Email','email',false],['cnic_reference','CNIC / Reference','text',false],['profit_share_percentage','Profit Share %','number',true],['joining_date','Joining Date','date',false]] as [$n,$l,$t,$req])<div class="col-md-6"><label class="form-label">{{ $l }}</label><input class="form-control" type="{{ $t }}" name="{{ $n }}" @if($n==='profit_share_percentage') step="0.01" min="0" max="100" @endif @required($req)><div class="invalid-feedback" data-error-for="{{ $n }}"></div></div>@endforeach
<div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status" required><option value="active">Active</option><option value="on_hold">On Hold</option><option value="inactive">Inactive</option></select><div class="invalid-feedback" data-error-for="status"></div></div><div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes"></textarea><div class="invalid-feedback" data-error-for="notes"></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-theme" data-bs-dismiss="modal">Cancel</button><button class="btn btn-accent" type="submit" data-submit><span data-submit-label>Save Investor</span></button></div></form></div></div></div>
@endsection
