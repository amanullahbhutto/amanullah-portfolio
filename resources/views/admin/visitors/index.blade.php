@extends('layouts.admin')
@section('title', 'Visitors')
@section('page_title', 'Visitor Analytics')
@section('content')
<div class="row g-4 mb-4">
    @foreach($stats as $stat)
        <div class="col-sm-6 col-xl-3">
            <article class="admin-card stat-card">
                <div class="stat-icon {{ $stat['color'] }}"><i class="bi {{ $stat['icon'] }}"></i></div>
                <div>
                    <strong>{{ number_format($stat['value']) }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            </article>
        </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <section class="admin-card h-100">
            <div class="admin-card-head">
                <div>
                    <h2>Top pages</h2>
                    <p class="text-muted-custom small mb-0 mt-1">Pages with the highest visitor views.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Views</th>
                            <th>Visitors</th>
                            <th>Last visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPages as $page)
                            <tr>
                                <td><span class="visit-path">{{ $page->path }}</span></td>
                                <td><strong>{{ number_format($page->views) }}</strong></td>
                                <td>{{ number_format($page->visitors) }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($page->last_seen_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted-custom py-5">No visits recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="col-xl-4">
        <section class="admin-card h-100">
            <div class="admin-card-head">
                <div>
                    <h2>Devices</h2>
                    <p class="text-muted-custom small mb-0 mt-1">Visitor device split.</p>
                </div>
            </div>
            <div class="admin-card-body">
                <div class="device-breakdown">
                    @forelse($deviceBreakdown as $device)
                        <div class="device-row">
                            <span><i class="bi bi-display"></i>{{ $device->device_type ?: 'Unknown' }}</span>
                            <strong>{{ number_format($device->views) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted-custom mb-0">No device data yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>

<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Recent visits</h2>
            <p class="text-muted-custom small mb-0 mt-1">Each public page is saved once per browser visitor, even when the visitor is not logged in.</p>
        </div>
    </div>

    <div class="admin-list-toolbar">
        @include('admin.partials.live-search', [
            'action' => route('admin.visitors.index'),
            'searchId' => 'visitor-search',
            'placeholder' => 'Search visitors...',
        ])
    </div>

    <div id="admin-list-results" class="admin-list-results" aria-live="polite">
        <div class="admin-list-summary">
            @if(request('q'))
                Search results for "{{ request('q') }}"
            @else
                Latest visitor records
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover visitor-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Visitor</th>
                        <th>Device</th>
                        <th>Referrer</th>
                        <th>Visited</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                        <tr>
                            <td>
                                <span class="visit-path">{{ $visit->path }}</span>
                                <small class="text-muted-custom d-block">{{ $visit->project?->title ?? $visit->route_name ?? 'Public page' }}</small>
                            </td>
                            <td>
                                <strong>{{ $visit->ip_address ?: 'Unknown IP' }}</strong>
                                <small class="text-muted-custom d-block">ID {{ Str::limit($visit->visitor_id, 12, '') }}</small>
                                <small class="text-muted-custom d-block">{{ $visit->user ? $visit->user->name.' (logged in)' : 'Guest visitor' }}</small>
                            </td>
                            <td>
                                <span class="status-badge live">{{ $visit->device_type ?: 'Unknown' }}</span>
                                <small class="text-muted-custom d-block mt-1">{{ $visit->browser ?: 'Unknown browser' }} / {{ $visit->platform ?: 'Unknown platform' }}</small>
                            </td>
                            <td>
                                @if($visit->referrer)
                                    <span class="visit-referrer">{{ Str::limit($visit->referrer, 55) }}</span>
                                @else
                                    <span class="text-muted-custom">Direct</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $visit->visited_at->format('M d, Y H:i') }}</strong>
                                <small class="text-muted-custom d-block">{{ $visit->visited_at->diffForHumans() }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-activity fs-2 text-accent"></i>
                                <p class="text-muted-custom mt-2 mb-0">
                                    @if(request('q'))
                                        No matching visitor records found for "{{ request('q') }}".
                                    @else
                                        No visitor records yet.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->total() > 0)
            <div class="admin-pagination">
                @include('admin.partials.pagination', ['paginator' => $visits])
            </div>
        @endif
    </div>
</section>
@endsection
