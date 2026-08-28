@extends('layouts.admin')

@section('title', 'Edit Date of Birth')
@section('page_title', 'Edit Date of Birth')

@section('content')

<form
    method="POST"
    action="{{ route('admin.date-of-births.update', $dateOfBirth) }}"
>

    @csrf
    @method('PUT')


    <div class="admin-card">

        <div class="admin-card-head">

            <div>

                <h2>Edit Date of Birth Record</h2>

                <p class="text-muted-custom small mb-0 mt-1">
                    Update person and date information.
                </p>

            </div>


            <a
                class="btn btn-outline-theme btn-sm"
                href="{{ route('admin.date-of-births.index') }}"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

        </div>


        <div class="admin-card-body">

            <div class="row g-4">


                <div class="col-md-6">

                    <label
                        class="form-label"
                        for="name"
                    >
                        Name *
                    </label>

                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $dateOfBirth->name) }}"
                        required
                    >

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                <div class="col-md-6">

                    <label
                        class="form-label"
                        for="father_name"
                    >
                        Father Name
                    </label>

                    <input
                        class="form-control @error('father_name') is-invalid @enderror"
                        id="father_name"
                        name="father_name"
                        type="text"
                        value="{{ old('father_name', $dateOfBirth->father_name) }}"
                    >

                    @error('father_name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                <div class="col-md-6">

                    <label
                        class="form-label"
                        for="start_date"
                    >
                        Date of Birth *
                    </label>

                    <input
                        class="form-control @error('start_date') is-invalid @enderror"
                        id="start_date"
                        name="start_date"
                        type="text"
                        inputmode="numeric"
                        placeholder="DD/MM/YYYY example 25/3/2008"
                        autocomplete="off"
                        value="{{ old('start_date', $dateOfBirth->start_date->format('j/n/Y')) }}"
                        required
                        data-date-mask
                    >

                    @error('start_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                <div class="col-md-6">

                    <label
                        class="form-label"
                        for="end_date"
                    >
                        End Date
                    </label>

                    <input
                        class="form-control @error('end_date') is-invalid @enderror"
                        id="end_date"
                        name="end_date"
                        type="text"
                        inputmode="numeric"
                        placeholder="DD/MM/YYYY example 25/3/2008"
                        autocomplete="off"
                        value="{{ old(
                            'end_date',
                            $dateOfBirth->end_date
                                ? $dateOfBirth->end_date->format('j/n/Y')
                                : ''
                        ) }}"
                        data-date-mask
                    >

                    @error('end_date')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Leave empty to calculate age up to today. DD/MM/YYYY example 25/3/2008.
                    </div>

                </div>


                <div class="col-12">

                    <div class="alert alert-light mb-0">

                        <strong>Current Age / Duration:</strong>

                        {{ $dateOfBirth->formatted_age }}

                    </div>

                </div>


            </div>

        </div>


        <div class="admin-card-head justify-content-end">

            <button
                class="btn btn-accent"
                type="submit"
            >
                <i class="bi bi-check-lg me-1"></i>
                Update Record
            </button>

        </div>

    </div>

</form>

@endsection
