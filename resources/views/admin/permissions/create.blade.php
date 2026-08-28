@extends('layouts.admin')
@section('title', 'Create Permission')
@section('page_title', 'Create Permission')
@section('content')
<form method="POST" action="{{ route('admin.permissions.store') }}">
    @csrf

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>New permission</h2>
                <p class="text-muted-custom small mb-0 mt-1">Create a Spatie permission for middleware checks.</p>
            </div>
            <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.permissions.index') }}">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>

        @include('admin.permissions._form')

        <div class="admin-card-head justify-content-end">
            <button class="btn btn-accent" type="submit">
                <i class="bi bi-check-lg me-1"></i>Create permission
            </button>
        </div>
    </section>
</form>
@endsection
