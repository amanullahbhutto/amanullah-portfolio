@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('content')
<div class="row g-4 mb-4">
    @foreach($stats as $stat)<div class="col-sm-6 col-xl-3"><article class="admin-card stat-card"><div class="stat-icon {{ $stat['color'] }}"><i class="bi {{ $stat['icon'] }}"></i></div><div><strong>{{ $stat['value'] }}</strong><span>{{ $stat['label'] }}</span></div></article></div>@endforeach
</div>
<div class="row g-4">
    <div class="col-xl-7"><section class="admin-card"><div class="admin-card-head"><h2>Latest contact messages</h2>@can('view message')<a class="btn btn-sm btn-soft" href="{{ route('admin.messages.index') }}">View inbox</a>@endcan</div><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead><tbody>
        @forelse($latestMessages as $message)<tr><td><strong>{{ $message->name }}</strong><br><small class="text-muted-custom">{{ $message->email }}</small></td><td>{{ Str::limit($message->subject, 35) }}</td><td>@if($message->read_at)<span class="status-badge live">Read</span>@else<span class="status-badge new">New</span>@endif</td><td>{{ $message->created_at->diffForHumans() }}</td><td class="text-end"><a class="btn-icon" href="{{ route('admin.messages.show', $message) }}" aria-label="Open message"><i class="bi bi-arrow-right"></i></a></td></tr>@empty<tr><td colspan="5" class="text-center text-muted-custom py-5">No messages yet.</td></tr>@endforelse
    </tbody></table></div></section></div>
    <div class="col-xl-5"><section class="admin-card"><div class="admin-card-head"><h2>Recently updated projects</h2>@can('view project')<a class="btn btn-sm btn-soft" href="{{ route('admin.content.index', 'projects') }}">Manage</a>@endcan</div><div class="admin-card-body p-0"><div class="list-group list-group-flush">
        @forelse($latestProjects as $project)<a href="{{ route('admin.content.edit', ['type' => 'projects', 'id' => $project->id]) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between gap-3" style="background:transparent;color:var(--text);border-color:var(--line);padding:1rem 1.25rem"><span><strong class="d-block" style="font-size:.85rem">{{ $project->title }}</strong><small class="text-muted-custom">Updated {{ $project->updated_at->diffForHumans() }}</small></span><span class="status-badge {{ $project->is_published ? 'live' : 'draft' }}">{{ $project->is_published ? 'Live' : 'Draft' }}</span></a>@empty<div class="p-4 text-center text-muted-custom">No projects added.</div>@endforelse
    </div></div></section></div>
</div>
<div class="admin-card mt-4"><div class="admin-card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"><div><span class="eyebrow">Security reminder</span><h2 class="h5 mt-1 mb-1">Change the default administrator password before launch.</h2><p class="text-muted-custom mb-0 small">Also set ALLOW_REGISTRATION=false in production unless you actively need new user accounts.</p></div>@can('view user')<a class="btn btn-outline-theme" href="{{ route('admin.users.index') }}">Manage users</a>@endcan</div></div>
@endsection
