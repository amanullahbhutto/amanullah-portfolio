@extends('layouts.admin')
@section('title', 'Edit User')
@section('page_title', 'Edit User Access')
@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>{{ $user->name }}</h2>
                <p class="text-muted-custom small mb-0 mt-1">Update account details, change password, and assign Spatie role(s).</p>
            </div>
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.users.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="admin-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label" for="name">Name *</label>
                    <input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email *</label>
                    <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">New Password <small class="text-muted-custom">(leave blank to keep current)</small></label>
                    <input class="form-control" type="password" id="password" name="password" placeholder="Minimum 6 characters">
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirm New Password</label>
                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat password">
                </div>

                <div class="col-12">
                    <label class="form-label d-block mb-2">Assign Role(s) *</label>
                    @php
                        $userRoleNames = $user->roles->pluck('name')->all();
                    @endphp
                    <div class="row g-3">
                        @foreach($roles as $role)
                            <div class="col-md-4 col-sm-6">
                                <label class="permission-option p-3 d-flex align-items-center gap-2 border rounded">
                                    <input
                                        class="form-check-input mt-0"
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->name }}"
                                        @checked(is_array(old('roles')) ? in_array($role->name, old('roles'), true) : in_array($role->name, $userRoleNames, true))
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
                    @error('role')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="admin-card-head justify-content-end">
            <button class="btn btn-accent" type="submit">
                <i class="bi bi-check-lg me-1"></i>Save access
            </button>
        </div>
    </div>
</form>
@endsection

