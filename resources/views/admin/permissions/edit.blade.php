@extends('layouts.admin')
@section('title', 'Edit Permission')
@section('page_title', 'Edit Permission')
@section('content')
<form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
    @csrf
    @method('PUT')

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>{{ $permission->name }}</h2>
                <p class="text-muted-custom small mb-0 mt-1">Update this Spatie permission name.</p>
            </div>
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.permissions.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        @include('admin.permissions._form')

        <div class="admin-card-head justify-content-end">
            <button class="btn btn-accent" type="submit">
                <i class="bi bi-check-lg me-1"></i>Save permission
            </button>
        </div>
    </section>
</form>
@endsection
