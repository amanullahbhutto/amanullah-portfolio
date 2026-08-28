@extends('layouts.admin')
@section('title', 'Roles')
@section('page_title', 'Roles')
@section('content')
<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Spatie roles</h2>
            <p class="text-muted-custom small mb-0 mt-1">Create roles and assign permission groups.</p>
        </div>
        <div class="responsive-actions">
            @can('create role')
                <a class="btn btn-accent btn-sm" href="{{ route('admin.roles.create') }}">
                    <i class="bi bi-plus-lg me-1"></i>Add role
                </a>
            @endcan
        </div>
    </div>

    <div class="admin-list-toolbar">
        @include('admin.partials.live-search', [
            'action' => route('admin.roles.index'),
            'searchId' => 'role-search',
            'placeholder' => 'Search roles...',
        ])
    </div>

    <div id="admin-list-results" class="admin-list-results" aria-live="polite">
        <div class="admin-list-summary">
            @if(request('q'))
                Search results for "{{ request('q') }}"
            @else
                Role list
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="role-icon"><i class="bi bi-shield-check"></i></span>
                                    <div>
                                        <strong>{{ ucfirst($role->name) }}</strong>
                                        <small class="d-block text-muted-custom">{{ $role->guard_name }} guard</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td><span class="status-badge {{ $role->permissions_count > 0 ? 'live' : 'draft' }}">{{ $role->permissions_count }} permissions</span></td>
                            <td>
                                <div class="action-buttons">
                                    @can('view role')
                                        <a class="btn-icon" href="{{ route('admin.roles.show', $role) }}" aria-label="View role">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update role')
                                        <a class="btn-icon" href="{{ route('admin.roles.edit', $role) }}" aria-label="Edit role">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete role')
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" data-confirm="Delete this role permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" aria-label="Delete role">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-shield-x fs-2 text-accent"></i>
                                <p class="text-muted-custom mt-2 mb-0">No roles found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->total() > 0)
            <div class="admin-pagination">
                @include('admin.partials.pagination', ['paginator' => $roles])
            </div>
        @endif
    </div>
</section>
@endsection
