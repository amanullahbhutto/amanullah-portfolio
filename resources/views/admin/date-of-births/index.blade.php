@extends('layouts.admin')

@section('title', 'Date of Births')
@section('page_title', 'Date of Births')

@section('content')
@php
    $canCreateDateOfBirth = auth()->user()->can('create date of birth');
    $canUpdateDateOfBirth = auth()->user()->can('update date of birth');
@endphp

<section
    class="admin-card"
    data-dob-crud
    data-dob-store-url="{{ route('admin.date-of-births.store') }}"
>

    <div class="admin-card-head d-flex align-items-center justify-content-between gap-3 p-3">
        <form
            class="admin-search dob-filter-form flex-grow-1"
            method="GET"
            action="{{ route('admin.date-of-births.index') }}"
            data-live-search
            data-live-search-target="#admin-list-results"
        >
            <label class="visually-hidden" for="date-of-birth-search">Search name or father name...</label>
            <div class="search-field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    class="form-control"
                    id="date-of-birth-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search name or father name..."
                    autocomplete="off"
                >
                <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear aria-label="Clear search">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>

            <label class="visually-hidden" for="father-name-filter">Filter by father name</label>
            <div class="searchable-select-wrapper dob-father-filter-wrapper" data-searchable-select>
                <button type="button" class="searchable-select-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filter or search by father name">
                    <span class="selected-text text-truncate">{{ $selectedFatherName ? 'Father: '.$selectedFatherName : 'All Fathers' }}</span>
                    <span class="btn-clear-selection ms-auto {{ empty($selectedFatherName) ? 'd-none' : '' }}" data-clear-selection title="Clear father filter"><i class="bi bi-x-lg"></i></span>
                </button>
                
                <div class="dropdown-menu searchable-select-menu shadow">
                    <div class="search-box mb-2 position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted-custom" style="display: inline-block !important; font-size: 0.8rem; pointer-events: none;"></i>
                        <input type="text" class="form-control form-control-sm searchable-select-input" placeholder="Search father name..." autocomplete="off">
                    </div>
                    <div class="searchable-select-options custom-scrollbar" style="max-height: 220px; overflow-y: auto;">
                        <div class="searchable-option-item {{ empty($selectedFatherName) ? 'active' : '' }}" data-value="" data-label="All Fathers">
                            <i class="bi bi-people me-2"></i>All Fathers
                        </div>
                        @foreach($fatherNames as $fatherName)
                            <div class="searchable-option-item {{ $selectedFatherName === $fatherName ? 'active' : '' }}" data-value="{{ $fatherName }}" data-label="{{ $fatherName }}">
                                <i class="bi bi-person me-2"></i>{{ $fatherName }}
                            </div>
                        @endforeach
                        <div class="searchable-no-results text-muted-custom small text-center py-2 d-none">
                            No matching father name
                        </div>
                    </div>
                </div>

                {{-- Native select for form submit, live-search and tests --}}
                <select class="d-none" id="father-name-filter" name="father_name" aria-label="Filter by father name">
                    <option value="">All</option>
                    @foreach($fatherNames as $fatherName)
                        <option value="{{ $fatherName }}" @selected($selectedFatherName === $fatherName)>
                            {{ $fatherName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="visually-hidden" for="date-of-birth-per-page">Records per page</label>
            <select class="form-select dob-page-size" id="date-of-birth-per-page" name="per_page" aria-label="Records per page">
                @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($selectedPerPage === $size)>
                        {{ $size }} / page
                    </option>
                @endforeach
            </select>
        </form>

        <div class="responsive-actions flex-shrink-0">
            @can('create date of birth')
                <a
                    class="btn btn-accent btn-sm d-inline-flex align-items-center justify-content-center"
                    style="min-width: 44px; height: 44px; padding: 0 14px; font-size: 1.15rem; border-radius: 12px;"
                    href="{{ route('admin.date-of-births.create') }}"
                    data-dob-open
                    title="Add Record"
                    aria-label="Add Record"
                >
                    <i class="bi bi-plus-lg"></i>
                </a>
            @endcan
        </div>
    </div>


    <div
        id="admin-list-results"
        class="admin-list-results"
        aria-live="polite"
    >

        @if(request('q') || $selectedFatherName)
            <div class="admin-list-summary">
                @if(request('q'))
                    Search results for "{{ request('q') }}"
                @else
                    Filtered records
                @endif
                @if($selectedFatherName)
                    <span class="ms-2">Father: {{ $selectedFatherName }}</span>
                @endif
            </div>
        @endif


        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Name</th>

                        <th>Father Name</th>

                        <th>Date of Birth</th>

                        <th>Next Birthday</th>

                        <th>End Date</th>

                        <th>Age / Duration</th>

                        <th class="text-end">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($dateOfBirths as $dateOfBirth)

                        <tr id="dob-row-{{ $dateOfBirth->id }}" data-dob-row="{{ $dateOfBirth->id }}">

                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    @if($dateOfBirth->image_url)
                                        <button
                                            type="button"
                                            class="user-avatar overflow-hidden p-0 border-0 dob-avatar-btn"
                                            style="width:44px;height:44px;border-radius:12px;"
                                            title="Click to view & manage photos"
                                            data-dob-avatar-photos
                                            data-dob-name="{{ $dateOfBirth->name }}"
                                            data-dob-father-name="{{ $dateOfBirth->father_name }}"
                                            data-dob-photos-action="{{ route('admin.date-of-births.photos.update', $dateOfBirth) }}"
                                            data-dob-images='@json($dateOfBirth->images_with_urls)'
                                        >
                                            <img
                                                src="{{ $dateOfBirth->image_url }}"
                                                alt="{{ $dateOfBirth->name }}"
                                                style="width:100%;height:100%;object-fit:cover;display:block;"
                                            >
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            class="user-avatar border-0 dob-avatar-btn"
                                            style="width:44px;height:44px;border-radius:12px;font-size:1.1rem;font-weight:600;"
                                            title="Click to view & manage photos"
                                            data-dob-avatar-photos
                                            data-dob-name="{{ $dateOfBirth->name }}"
                                            data-dob-father-name="{{ $dateOfBirth->father_name }}"
                                            data-dob-photos-action="{{ route('admin.date-of-births.photos.update', $dateOfBirth) }}"
                                            data-dob-images='@json($dateOfBirth->images_with_urls)'
                                        >
                                            {{ strtoupper(substr($dateOfBirth->name, 0, 1)) }}
                                        </button>
                                    @endif

                                    <div>
                                        <strong>
                                            {{ $dateOfBirth->name }}
                                        </strong>
                                        @if(count($dateOfBirth->image_paths) > 1)
                                            <small class="text-muted-custom d-block" style="font-size:0.72rem;">
                                                <i class="bi bi-images me-1"></i>{{ count($dateOfBirth->image_paths) }} photos
                                            </small>
                                        @endif
                                    </div>

                                </div>

                            </td>

                            <td>

                                @if($dateOfBirth->father_name)
                                    {{ $dateOfBirth->father_name }}
                                @else
                                    &mdash;
                                @endif

                            </td>


                            <td>

                                <strong>
                                    {{ $dateOfBirth->start_date->format('d/m/Y') }}
                                </strong>

                                <small class="duration-range">
                                    {{ $dateOfBirth->start_date->format('M d, Y') }}
                                </small>

                            </td>


                            <td>

                                <span class="duration-pill">
                                    {{ $dateOfBirth->formatted_next_birthday_countdown }}
                                </span>

                                <small class="duration-range">
                                    {{ $dateOfBirth->days_until_next_birthday === 0 ? 'Today' : 'Next: '.$dateOfBirth->next_birthday->format('M d, Y') }}
                                </small>

                            </td>


                            <td>

                                @if($dateOfBirth->end_date)

                                    {{ $dateOfBirth->end_date->format('M d, Y') }}

                                @else

                                    <span class="status-badge live">
                                        Present
                                    </span>

                                @endif

                            </td>


                            <td>
                                <strong>{{ $dateOfBirth->age['years'] }}</strong> Y,
                                <strong>{{ $dateOfBirth->age['months'] }}</strong> M,
                                <strong>{{ $dateOfBirth->age['days'] }}</strong> D
                            </td>


                            <td>

                                <div class="action-buttons">

                                    @can('view date of birth')

                                        <a
                                            class="btn-icon"
                                            href="{{ route('admin.date-of-births.show', $dateOfBirth) }}"
                                            data-dob-view
                                            data-dob-name="{{ $dateOfBirth->name }}"
                                            data-dob-father-name="{{ $dateOfBirth->father_name }}"
                                            data-dob-start-date="{{ $dateOfBirth->start_date->format('d/m/Y') }}"
                                            data-dob-end-date="{{ $dateOfBirth->end_date?->format('d/m/Y') }}"
                                            data-dob-age="{{ $dateOfBirth->formatted_age }}"
                                            data-dob-next-birthday="{{ $dateOfBirth->next_birthday->format('M d, Y') }}"
                                            data-dob-next-countdown="{{ $dateOfBirth->formatted_next_birthday_countdown }}"
                                            data-dob-images='@json($dateOfBirth->image_urls)'
                                            aria-label="View record"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                    @endcan


                                    @can('update date of birth')

                                        <a
                                            class="btn-icon"
                                            href="{{ route('admin.date-of-births.edit', $dateOfBirth) }}"
                                            data-dob-edit
                                            data-dob-action="{{ route('admin.date-of-births.update', $dateOfBirth) }}"
                                            data-dob-name="{{ $dateOfBirth->name }}"
                                            data-dob-father-name="{{ $dateOfBirth->father_name }}"
                                            data-dob-start-date="{{ $dateOfBirth->start_date->format('j/n/Y') }}"
                                            data-dob-end-date="{{ $dateOfBirth->end_date?->format('j/n/Y') }}"
                                            data-dob-images='@json($dateOfBirth->images_with_urls)'
                                            aria-label="Edit record"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                    @endcan


                                    @can('delete date of birth')

                                        <form
                                            method="POST"
                                            action="{{ route('admin.date-of-births.destroy', $dateOfBirth) }}"
                                            data-confirm="Delete this record permanently?"
                                            data-dob-delete
                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button
                                                class="btn-icon danger"
                                                type="submit"
                                                aria-label="Delete record"
                                            >
                                                <i class="bi bi-trash3"></i>
                                            </button>

                                        </form>

                                    @endcan

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i class="bi bi-calendar-date fs-2 text-accent"></i>

                                <p class="text-muted-custom mt-2 mb-0">

                                    @if(request('q') || $selectedFatherName)

                                        No matching records found.

                                    @else

                                        No date of birth records found.

                                    @endif

                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($dateOfBirths->total() > 0)

            <div class="admin-pagination">

                @include('admin.partials.pagination', [
                    'paginator' => $dateOfBirths
                ])

            </div>

        @endif

    </div>

</section>

@if($canCreateDateOfBirth || $canUpdateDateOfBirth)
    <div class="modal fade dob-modal" id="dateOfBirthFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content" method="POST" action="{{ route('admin.date-of-births.store') }}" data-dob-form enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT" data-dob-method disabled>

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5" data-dob-modal-title>Add Date of Birth</h2>
                        <p class="text-muted-custom small mb-0">DD/MM/YYYY example 25/3/2008</p>
                    </div>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="dob_name">Name *</label>
                            <input class="form-control" id="dob_name" name="name" type="text" required data-dob-field="name">
                            <div class="invalid-feedback" data-dob-error-for="name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="dob_father_name">Father Name</label>
                            <input class="form-control" id="dob_father_name" name="father_name" type="text" data-dob-field="father_name">
                            <div class="invalid-feedback" data-dob-error-for="father_name"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="dob_start_date">Date of Birth *</label>
                            <input
                                class="form-control"
                                id="dob_start_date"
                                name="start_date"
                                type="text"
                                inputmode="numeric"
                                placeholder="DD/MM/YYYY example 25/3/2008"
                                autocomplete="off"
                                required
                                data-date-mask
                                data-dob-field="start_date"
                            >
                            <div class="dob-form-help">DD/MM/YYYY example 25/3/2008</div>
                            <div class="invalid-feedback" data-dob-error-for="start_date"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="dob_end_date">End Date</label>
                            <input
                                class="form-control"
                                id="dob_end_date"
                                name="end_date"
                                type="text"
                                inputmode="numeric"
                                placeholder="DD/MM/YYYY example 25/3/2008"
                                autocomplete="off"
                                data-date-mask
                                data-dob-field="end_date"
                            >
                            <div class="dob-form-help">Leave empty to calculate age up to today.</div>
                            <div class="invalid-feedback" data-dob-error-for="end_date"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="dob_images">Images (JPG, PNG, WebP)</label>
                            <input
                                class="form-control"
                                id="dob_images"
                                name="images[]"
                                type="file"
                                accept="image/jpeg,image/png,image/webp,image/gif"
                                multiple
                                data-gallery-input="#dobModalImageSelection"
                                data-dob-field="images"
                            >
                            <div class="dob-form-help">Upload one or multiple images. Saved in public/DOB/{name}. The last uploaded image will be used as avatar in the list.</div>
                            <div id="dobModalImageSelection" class="selected-gallery-preview mt-2 d-none"></div>
                            <div id="dobExistingImagesWrapper" class="mt-3 d-none">
                                <label class="form-label small text-muted-custom mb-1">
                                    Existing Images (click image to mark for deletion):
                                </label>
                                <div id="dobExistingImages" class="project-gallery-admin"></div>
                            </div>
                            <div class="invalid-feedback" data-dob-error-for="images"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-dob-submit>
                        <i class="bi bi-check-lg me-1"></i>
                        <span data-dob-submit-label>Save Record</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="modal fade dob-modal" id="dateOfBirthViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5" data-dob-view-name>Date of Birth</h2>
                    <p class="text-muted-custom small mb-0" data-dob-view-father></p>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="dob-detail-grid">
                    <div>
                        <span>Date of Birth</span>
                        <strong data-dob-view-start></strong>
                    </div>
                    <div>
                        <span>End Date</span>
                        <strong data-dob-view-end></strong>
                    </div>
                    <div>
                        <span>Age / Duration</span>
                        <strong data-dob-view-age></strong>
                    </div>
                    <div>
                        <span>Next Birthday</span>
                        <strong data-dob-view-next></strong>
                    </div>
                </div>

                <div class="dob-countdown-card mt-3">
                    <i class="bi bi-calendar-heart"></i>
                    <div>
                        <span>Remaining time</span>
                        <strong data-dob-view-countdown></strong>
                    </div>
                </div>

                <div class="mt-3 d-none" id="dobViewGalleryWrapper">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold small text-muted-custom">Photos (<span id="dobViewPhotoCount">0</span>)</span>
                    </div>
                    <div id="dobViewGallery" class="dob-view-gallery-grid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade dob-modal" id="dateOfBirthPhotosModal" tabindex="-1" aria-hidden="true" style="z-index: 1075;">
    <div class="modal-dialog dob-photos-dialog" style="max-width: 480px; margin: 24px auto;">
        <form class="modal-content" method="POST" action="" data-dob-photos-form enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="PUT">

            <div class="modal-header">
                <div>
                    <h2 class="modal-title h5" data-dob-photos-modal-title>Photos</h2>
                    <p class="text-muted-custom small mb-0" data-dob-photos-modal-subtitle>View, upload and delete photos for this person.</p>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="dob_quick_upload_images">
                        <i class="bi bi-cloud-arrow-up me-1 text-accent"></i> Upload New Photos (JPG, PNG, WebP)
                    </label>
                    <input
                        class="form-control"
                        id="dob_quick_upload_images"
                        name="images[]"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        multiple
                        data-gallery-input="#dobQuickUploadPreview"
                    >
                    <div class="dob-form-help">Upload one or multiple photos. The last uploaded photo will be displayed as the avatar in the list.</div>
                    <div id="dobQuickUploadPreview" class="selected-gallery-preview mt-2 d-none"></div>
                    <div class="invalid-feedback" data-dob-photos-error></div>
                </div>

                <hr class="my-3 opacity-25">

                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold">All Photos (<span id="dobPhotosCount">0</span>)</span>
                        <span class="text-muted-custom small">Click photo trash to mark for deletion</span>
                    </div>

                    <div id="dobPhotosEmptyState" class="text-center py-4 rounded-3 border border-dashed text-muted-custom d-none" style="background: var(--surface-2);">
                        <i class="bi bi-images fs-1 d-block mb-1 text-accent opacity-75"></i>
                        <span>No photos uploaded yet for this person.</span>
                    </div>

                    <div id="dobPhotosGrid" class="project-gallery-admin"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-accent" type="submit" data-dob-photos-submit>
                    <i class="bi bi-check-lg me-1"></i>
                    <span data-dob-photos-submit-label>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Slide View / Lightbox Modal for DOB Photos --}}
<div class="modal fade lightbox-modal" id="dobLightboxModal" tabindex="-1" aria-hidden="true" style="z-index: 1085;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title mb-0" id="dobLightboxTitle">Photos</h5>
                    <span class="lightbox-counter" id="dobLightboxCounter">1 / 1</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body position-relative">
                <button class="lightbox-nav-btn prev-btn" id="dobLightboxPrevBtn" type="button" aria-label="Previous image" title="Previous photo (Left Arrow)">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <img id="dobLightboxActiveImg" src="" alt="Full size preview" class="lightbox-preview-img">
                <button class="lightbox-nav-btn next-btn" id="dobLightboxNextBtn" type="button" aria-label="Next image" title="Next photo (Right Arrow)">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
