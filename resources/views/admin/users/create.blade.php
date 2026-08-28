@extends('layouts.admin')
@section('title', 'Create User')
@section('page_title', 'Create User')
@section('content')
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>New dashboard user</h2>
                <p class="text-muted-custom small mb-0 mt-1">Create an account and assign Spatie role(s).</p>
            </div>
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.users.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="admin-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label" for="name">Name *</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name') }}" required placeholder="Full Name">
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email *</label>
                    <input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="email@example.com">
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Password *</label>
                    <input class="form-control" type="password" id="password" name="password" required placeholder="Minimum 6 characters">
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirm password *</label>
                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password">
                </div>
                <div class="col-12">
                    <label class="form-label d-block mb-2">Assign Role(s) *</label>
                    <div class="row g-3">
                        @foreach($roles as $role)
                            <div class="col-md-4 col-sm-6">
                                <label class="permission-option p-3 d-flex align-items-center gap-2 border rounded">
                                    <input
                                        class="form-check-input mt-0"
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->name }}"
                                        @checked(is_array(old('roles')) ? in_array($role->name, old('roles'), true) : old('role') === $role->name || (old('roles') === null && $role->name === 'User'))
                                    >
                                    <div>
                                        <strong class="d-block">{{ $role->name }}</strong>
                                        <small class="text-muted-custom">{{ $role->permissions_count ?? $role->permissions()->count() }} permissions</small>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="admin-card-head justify-content-end">
            <button class="btn btn-accent" type="submit">
                <i class="bi bi-check-lg me-1"></i>Create user
            </button>
        </div>
    </div>
</form>
@endsection
