@extends('layouts.admin')
@section('title', 'Permissions')
@section('page_title', 'Permissions')
@section('content')
<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Spatie permissions</h2>
        </div>

        <div class="head-search-wrap flex-grow-1">
            @include('admin.partials.live-search', [
                'action' => route('admin.permissions.index'),
                'searchId' => 'permission-search',
                'placeholder' => 'Search permissions...',
            ])
        </div>

        <div class="responsive-actions">
            @can('create permission')
                <a class="btn btn-accent btn-sm" href="{{ route('admin.permissions.create') }}">
                    <i class="bi bi-plus-lg me-1"></i>Add permission
                </a>
            @endcan
        </div>
    </div>

    <div id="admin-list-results" class="admin-list-results" aria-live="polite">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Permission</th>
                        <th>Guard</th>
                        <th>Roles</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $permission)
                        <tr>
                            <td><span class="permission-pill">{{ $permission->name }}</span></td>
                            <td>{{ $permission->guard_name }}</td>
                            <td>{{ $permission->roles_count }}</td>
                            <td>
                                <div class="action-buttons">
                                    @can('view permission')
                                        <a class="btn-icon" href="{{ route('admin.permissions.show', $permission) }}" aria-label="View permission">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update permission')
                                        <a class="btn-icon" href="{{ route('admin.permissions.edit', $permission) }}" aria-label="Edit permission">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete permission')
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" data-confirm="Delete this permission permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" aria-label="Delete permission">
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
                                <i class="bi bi-key fs-2 text-accent"></i>
                                <p class="text-muted-custom mt-2 mb-0">No permissions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permissions->total() > 0)
            <div class="admin-pagination">
                @include('admin.partials.pagination', ['paginator' => $permissions])
            </div>
        @endif
    </div>
</section>
@endsection
