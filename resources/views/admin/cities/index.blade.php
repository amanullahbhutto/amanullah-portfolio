@extends('layouts.admin')
@section('title', 'Cities Management')
@section('page_title', 'Cities')

@section('content')
<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>Cities</h2>
            <p class="text-muted-custom small mb-0 mt-1">Manage cities and locations used in program contributions and donors.</p>
        </div>
        <button class="btn btn-accent btn-sm" data-crud-open data-modal="#cityModal" data-store-url="{{ route('admin.cities.store') }}">
            <i class="bi bi-plus-lg me-1"></i>Add City
        </button>
    </div>

    <div class="admin-list-toolbar">
        <form class="admin-search financial-filter" method="GET" action="{{ route('admin.cities.index') }}" data-live-search data-live-search-target="#admin-list-results">
            <div class="search-field">
                <i class="bi bi-search"></i>
                <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search cities, states...">
                <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <select class="form-select filter-select" name="status">
                <option value="">All status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </form>
    </div>

    <div id="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>City Name</th>
                        <th>Province / State</th>
                        <th>Country</th>
                        <th>Linked Contributors</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cities as $row)
                        <tr>
                            <td>
                                <strong><i class="bi bi-geo-alt text-accent me-1"></i>{{ $row->name }}</strong>
                            </td>
                            <td>{{ $row->state ?: '-' }}</td>
                            <td>{{ $row->country ?: 'Pakistan' }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-white">{{ $row->contributors_count }} Contributors</span>
                            </td>
                            <td>
                                <span class="status-badge {{ $row->status === 'active' ? 'live' : 'draft' }}">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </td>
                            <td>{{ $row->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" data-crud-edit data-modal="#cityModal" data-action="{{ route('admin.cities.update', $row) }}" data-record="{{ json_encode(['name' => $row->name, 'state' => $row->state, 'country' => $row->country, 'status' => $row->status]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.cities.destroy', $row) }}" data-ajax-delete data-confirm="Delete this city? Cities with linked contributors cannot be deleted.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-icon danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted-custom">No cities found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            @include('admin.partials.pagination', ['paginator' => $cities])
        </div>
    </div>
</section>

{{-- City Modal --}}
<div class="modal fade finance-modal" id="cityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form>
                <input type="hidden" name="_method" value="PUT" data-method disabled>
                <div class="modal-header">
                    <h5 class="modal-title" data-modal-title>Add City</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">City Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" placeholder="e.g. Karachi, Lahore, Islamabad" required>
                        <div class="invalid-feedback" data-error-for="name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Province / State</label>
                        <input class="form-control" name="state" placeholder="e.g. Sindh, Punjab, Balochistan, KPK">
                        <div class="invalid-feedback" data-error-for="state"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <input class="form-control" name="country" value="Pakistan" placeholder="e.g. Pakistan">
                        <div class="invalid-feedback" data-error-for="country"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="invalid-feedback" data-error-for="status"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit><span data-submit-label>Save City</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

