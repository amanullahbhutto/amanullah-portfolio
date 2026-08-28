@extends('layouts.admin')
@section('title', 'Create Role')
@section('page_title', 'Create Role')
@section('content')
<form method="POST" action="{{ route('admin.roles.store') }}">
    @csrf

    <section class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>New Spatie role</h2>
                <p class="text-muted-custom small mb-0 mt-1">Create a role and choose its CRUD permissions.</p>
            </div>
            <div class="responsive-actions">
                <a class="btn btn-outline-theme btn-sm" href="{{ route('admin.roles.index') }}">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
                <button class="btn btn-accent btn-sm" type="submit">
                    <i class="bi bi-check-lg me-1"></i>Create role
                </button>
            </div>
        </div>

        @include('admin.roles._form')

        <div class="admin-card-head justify-content-end">
            <button class="btn btn-accent" type="submit">
                <i class="bi bi-check-lg me-1"></i>Create role
            </button>
        </div>
    </section>
</form>
@endsection
