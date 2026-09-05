@extends('layouts.admin')
@section('title', 'Users')
@section('page_title', 'Users')
@section('content')
<section class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Dashboard accounts</h2>
        </div>

        <div class="head-search-wrap flex-grow-1">
            @include('admin.partials.live-search', [
                'action' => route('admin.users.index'),
                'searchId' => 'user-search',
                'placeholder' => 'Search users...',
            ])
        </div>

        <div class="responsive-actions">
            @can('create user')
                <a class="btn btn-accent btn-sm" href="{{ route('admin.users.create') }}">
                    <i class="bi bi-plus-lg me-1"></i>Add user
                </a>
            @endcan
        </div>
    </div>

    <div id="admin-list-results" class="admin-list-results" aria-live="polite">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="user-avatar" style="width:34px;height:34px">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="status-badge {{ $role->name === 'admin' ? 'new' : 'live' }}">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    @can('view user')
                                        <a class="btn-icon" href="{{ route('admin.users.show', $user) }}" aria-label="View user">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endcan
                                    @can('update user')
                                        <a class="btn-icon" href="{{ route('admin.users.edit', $user) }}" aria-label="Edit user">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('delete user')
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete this user permanently?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn-icon danger" type="submit" aria-label="Delete user">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-people fs-2 text-accent"></i>
                                <p class="text-muted-custom mt-2 mb-0">
                                    @if(request('q'))
                                        No matching users found for "{{ request('q') }}".
                                    @else
                                        No users found.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->total() > 0)
            <div class="admin-pagination">
                @include('admin.partials.pagination', ['paginator' => $users])
            </div>
        @endif
    </div>
</section>
@endsection
