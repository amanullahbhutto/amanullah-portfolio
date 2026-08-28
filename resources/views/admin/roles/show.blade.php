@extends('layouts.admin')
@section('title', 'Role Details')
@section('page_title', 'Role Details')
@section('content')
<div class="row g-4">
    <div class="col-xl-4">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>{{ ucfirst($role->name) }}</h2>
            </div>
            <div class="admin-card-body d-grid gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="role-icon" style="width:54px;height:54px"><i class="bi bi-shield-check"></i></span>
                    <div>
                        <strong class="d-block">{{ $role->guard_name }} guard</strong>
                        <small class="text-muted-custom">{{ $role->users->count() }} users assigned</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @can('update role')
                        <a class="btn btn-accent" href="{{ route('admin.roles.edit', $role) }}">
                            <i class="bi bi-pencil me-1"></i>Edit role
                        </a>
                    @endcan
                    <a class="btn btn-outline-theme" href="{{ route('admin.roles.index') }}">Back</a>
                </div>
            </div>
        </section>
    </div>

    <div class="col-xl-8">
        <section class="admin-card">
            <div class="admin-card-head">
                <h2>Permissions</h2>
            </div>
            <div class="admin-card-body">
                <div class="d-flex flex-wrap gap-2">
                    @forelse($role->permissions->sortBy('name') as $permission)
                        <span class="permission-pill">{{ $permission->name }}</span>
                    @empty
                        <p class="text-muted-custom mb-0">No permissions assigned.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
