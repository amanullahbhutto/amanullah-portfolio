@extends('layouts.admin')
@section('title', 'Permission Details')
@section('page_title', 'Permission Details')
@section('content')
<div class="row g-4">
    <div class="col-xl-4">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Permission</h2>
            </div>
            <div class="admin-card-body d-grid gap-3">
                <span class="permission-pill">{{ $permission->name }}</span>
                <div>
                    <span class="eyebrow">Guard</span>
                    <strong class="d-block mt-1">{{ $permission->guard_name }}</strong>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('update permission')
                        <a class="btn btn-accent" href="{{ route('admin.permissions.edit', $permission) }}">
                            <i class="bi bi-pencil me-1"></i>Edit permission
                        </a>
                    @endcan
                    <a class="btn btn-outline-theme" href="{{ route('admin.permissions.index') }}">Back</a>
                </div>
            </div>
        </section>
    </div>

    <div class="col-xl-8">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Assigned roles</h2>
            </div>
            <div class="admin-card-body">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($permission->roles as $role)
                        <span class="status-badge {{ $role->name === 'admin' ? 'new' : 'live' }}">{{ ucfirst($role->name) }}</span>
                    @empty
                        <p class="text-muted-custom mb-0">No role uses this permission.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
