@extends('layouts.admin')

@section('title', 'Edit Date of Birth')
@section('page_title', 'Edit Date of Birth')

@section('content')

<form
    method="POST"
    action="{{ route('admin.date-of-births.update', $dateOfBirth) }}"
    enctype="multipart/form-data"
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

                    <label class="form-label" for="images">
                        Upload Images (All formats supported &bull; Any size / Large MB supported)
                    </label>

                    <input
                        class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                        id="images"
                        name="images[]"
                        type="file"
                        accept="image/*, .jpg, .jpeg, .png, .webp, .gif, .avif, .bmp, .heic, .heif, .tiff, .tif, .svg, .jfif"
                        multiple
                        data-gallery-input="#dobEditImageSelection"
                    >

                    <div class="form-text">
                        Add new images of any format and size. Saved in public/DOB/{name}. The last uploaded image will be used as avatar in the list.
                    </div>

                    <div id="dobEditImageSelection" class="selected-gallery-preview mt-2 d-none"></div>

                    @error('images')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('images.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    @if(count($dateOfBirth->image_paths) > 0)
                        <div class="mt-3">
                            <label class="form-label small text-muted-custom mb-1">
                                Existing Images (latest at top &bull; click image to mark for deletion):
                            </label>
                            <div class="project-gallery-admin">
                                @foreach($dateOfBirth->images_with_urls as $item)
                                    <label class="gallery-delete-tile" title="Click to mark for deletion">
                                        <img src="{{ $item['url'] }}" alt="{{ $dateOfBirth->name }} image {{ $loop->iteration }}">
                                        @if($loop->first)
                                            <span class="badge bg-accent position-absolute" style="top:6px;left:6px;font-size:0.68rem;padding:3px 6px;z-index:4;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.4);">Latest</span>
                                        @endif
                                        <input type="checkbox" name="delete_images[]" value="{{ $item['path'] }}" data-gallery-delete>
                                        <span class="gallery-delete-overlay"><i class="bi bi-trash3"></i></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

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
