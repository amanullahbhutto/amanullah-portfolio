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

    <div class="admin-card-head">
        <div>
            <h2>Date of Birth Records</h2>

            <p class="text-muted-custom small mb-0 mt-1">
                Nearest upcoming birthdays are shown first automatically.
            </p>
        </div>

        <div class="responsive-actions">
            @can('create date of birth')
                <a
                    class="btn btn-accent btn-sm"
                    href="{{ route('admin.date-of-births.create') }}"
                    data-dob-open
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Record
                </a>
            @endcan
        </div>
    </div>


    <div class="admin-list-toolbar">

        <form
            class="admin-search dob-filter-form"
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
            <select class="form-select dob-father-filter" id="father-name-filter" name="father_name" aria-label="Filter by father name">
                <option value="">All</option>
                @foreach($fatherNames as $fatherName)
                    <option value="{{ $fatherName }}" @selected($selectedFatherName === $fatherName)>
                        {{ $fatherName }}
                    </option>
                @endforeach
            </select>

            <label class="visually-hidden" for="date-of-birth-per-page">Records per page</label>
            <select class="form-select dob-page-size" id="date-of-birth-per-page" name="per_page" aria-label="Records per page">
                @foreach([10, 20, 50, 100] as $size)
                    <option value="{{ $size }}" @selected($selectedPerPage === $size)>
                        {{ $size }} / page
                    </option>
                @endforeach
            </select>
        </form>

    </div>


    <div
        id="admin-list-results"
        class="admin-list-results"
        aria-live="polite"
    >

        <div class="admin-list-summary">

            @if(request('q') || $selectedFatherName)

                @if(request('q'))
                    Search results for "{{ request('q') }}"
                @else
                    Filtered records
                @endif

                @if($selectedFatherName)
                    <span class="ms-2">Father: {{ $selectedFatherName }}</span>
                @endif

            @else

                Date of birth records by next birthday

            @endif

        </div>


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

                                    <span
                                        class="user-avatar"
                                        style="width:34px;height:34px"
                                    >
                                        {{ strtoupper(substr($dateOfBirth->name, 0, 1)) }}
                                    </span>

                                    <strong>
                                        {{ $dateOfBirth->name }}
                                    </strong>

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

                                <strong>
                                    {{ $dateOfBirth->age['years'] }}
                                </strong>
                                Years,

                                <strong>
                                    {{ $dateOfBirth->age['months'] }}
                                </strong>
                                Months,

                                <strong>
                                    {{ $dateOfBirth->age['days'] }}
                                </strong>
                                Days

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
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('admin.date-of-births.store') }}" data-dob-form>
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
    <div class="modal-dialog modal-dialog-centered">
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
            </div>
        </div>
    </div>
</div>

@endsection
