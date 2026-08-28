@extends('layouts.admin')
@section('title', 'Edit Role')
@section('page_title', 'Edit Role')
@section('content')
<form method="POST" action="{{ route('admin.roles.update', $role) }}">
    @csrf
    @method('PUT')

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>{{ ucfirst($role->name) }} role</h2>
                <p class="text-muted-custom small mb-0 mt-1">Update role name and assigned CRUD permissions.</p>
            </div>
            <div class="responsive-actions">
                <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.roles.index') }}">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <button class="btn btn-accent btn-sm" type="submit">
                    <i class="bi bi-check-lg me-1"></i>Save role
                </button>
            </div>
        </div>

        @include('admin.roles._form')

        <div class="admin-card-head justify-content-end">
            <button class="btn btn-accent" type="submit">
                <i class="bi bi-check-lg me-1"></i>Save role
            </button>
        </div>
    </section>
</form>
@endsection
