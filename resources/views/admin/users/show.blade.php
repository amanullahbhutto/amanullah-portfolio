@extends('layouts.admin')
@section('title', 'User Details')
@section('page_title', 'User Details')
@section('content')
<div class="row g-4">
    <div class="col-xl-4">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>{{ $user->name }}</h2>
            </div>
            <div class="admin-card-body d-grid gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="user-avatar" style="width:54px;height:54px">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <strong class="d-block">{{ $user->email }}</strong>
                        <small class="text-muted-custom">Created {{ $user->created_at->format('M d, Y') }}</small>
                    </div>
                </div>
                <div>
                    <span class="eyebrow">Roles</span>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($user->roles as $role)
                            <span class="status-badge {{ $role->name === 'admin' ? 'new' : 'live' }}">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('update user')
                        <a class="btn btn-accent" href="{{ route('admin.users.edit', $user) }}">
                            <i class="bi bi-pencil me-1"></i>Edit user
                        </a>
                    @endcan
                    <a class="btn btn-outline-theme" href="{{ route('admin.users.index') }}">Back</a>
                </div>
            </div>
        </section>
    </div>

    <div class="col-xl-8">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Role permissions</h2>
            </div>
            <div class="admin-card-body">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($user->getAllPermissions()->sortBy('name') as $permission)
                        <span class="permission-pill">{{ $permission->name }}</span>
                    @empty
                        <p class="text-muted-custom mb-0">No permissions assigned through roles.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
