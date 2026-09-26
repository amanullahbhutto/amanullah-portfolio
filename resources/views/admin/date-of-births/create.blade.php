@extends('layouts.admin')

@section('title', 'Add Date of Birth')
@section('page_title', 'Add Date of Birth')

@section('content')

<form
    method="POST"
    action="{{ route('admin.date-of-births.store') }}"
    enctype="multipart/form-data"
>

    @csrf


    <div class="admin-card">

        <div class="admin-card-head">

            <div>

                <h2>New Date of Birth Record</h2>

                <p class="text-muted-custom small mb-0 mt-1">
                    Add a person and their date of birth information.
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
                        value="{{ old('name') }}"
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
                        value="{{ old('father_name') }}"
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
                        value="{{ old('start_date') }}"
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
                        value="{{ old('end_date') }}"
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

                    <label class="form-label" for="images">
                        Images (JPG, PNG, WebP, GIF &bull; Any size / Large MB supported)
                    </label>

                    <input
                        class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                        id="images"
                        name="images[]"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif,image/avif,image/bmp,image/*"
                        multiple
                        data-gallery-input="#dobCreateImageSelection"
                    >

                    <div class="form-text">
                        Upload one or multiple images of any size. Saved in public/DOB/{name}. The last uploaded image will be used as the avatar in the list.
                    </div>

                    <div id="dobCreateImageSelection" class="selected-gallery-preview mt-2 d-none"></div>

                    @error('images')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('images.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                </div>


            </div>

        </div>


        <div class="admin-card-head justify-content-end">

            <button
                class="btn btn-accent"
                type="submit"
            >
                <i class="bi bi-check-lg me-1"></i>
                Save Record
            </button>

        </div>

    </div>

</form>

@endsection
