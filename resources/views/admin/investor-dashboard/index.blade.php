@extends('layouts.admin')
@section('title','Investor Dashboard')
@section('page_title','Investor Dashboard')
@section('content')
<div class="row g-4 mb-4">
@foreach($stats as $stat)<div class="col-sm-6 col-xl-3"><article class="admin-card stat-card"><div class="stat-icon {{ $stat['color'] }}"><i class="bi {{ $stat['icon'] }}"></i></div><div><strong>{{ $stat['value'] }}</strong><span>{{ $stat['label'] }}</span></div></article></div>@endforeach
</div>
<div class="row g-4">
<div class="col-xl-6"><section class="admin-card"><div class="admin-card-head"><h2>Recent Investments</h2><a class="btn btn-sm btn-soft" href="{{ route('admin.investments.index') }}">View all</a></div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Investor</th><th>Date</th><th class="text-end">Amount</th></tr></thead><tbody>@forelse($latestInvestments as $row)<tr><td>{{ $row->investor->name }}</td><td>{{ $row->investment_date->format('M d, Y') }}</td><td class="text-end"><strong>Rs. {{ number_format($row->amount,2) }}</strong></td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted-custom">No investments yet.</td></tr>@endforelse</tbody></table></div></section></div>
<div class="col-xl-6"><section class="admin-card"><div class="admin-card-head"><h2>Recent Profit Payments</h2><a class="btn btn-sm btn-soft" href="{{ route('admin.profit-payments.index') }}">View all</a></div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Investor</th><th>Date</th><th class="text-end">Amount</th></tr></thead><tbody>@forelse($latestPayments as $row)<tr><td>{{ $row->investor->name }}</td><td>{{ $row->payment_date->format('M d, Y') }}</td><td class="text-end"><strong>Rs. {{ number_format($row->amount,2) }}</strong></td></tr>@empty<tr><td colspan="3" class="text-center py-4 text-muted-custom">No profit payments yet.</td></tr>@endforelse</tbody></table></div></section></div>
</div>
@endsection
