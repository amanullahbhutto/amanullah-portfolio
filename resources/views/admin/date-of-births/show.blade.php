@extends('layouts.admin')

@section('title', 'Date of Birth Details')
@section('page_title', 'Date of Birth Details')

@section('content')

<section class="admin-card">

    <div class="admin-card-head">

        <div>

            <h2>{{ $dateOfBirth->name }}</h2>

            <p class="text-muted-custom small mb-0 mt-1">
                Date of birth record details.
            </p>

        </div>


        <div class="responsive-actions">

            @can('update date of birth')

                <a
                    class="btn btn-accent btn-sm"
                    href="{{ route('admin.date-of-births.edit', $dateOfBirth) }}"
                >
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>

            @endcan


            <a
                class="btn btn-outline-theme btn-sm"
                href="{{ route('admin.date-of-births.index') }}"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

        </div>

    </div>


    <div class="admin-card-body">

        <div class="row g-4">


            <div class="col-md-6">

                <label class="text-muted-custom small">
                    Name
                </label>

                <div class="fw-semibold mt-1">
                    {{ $dateOfBirth->name }}
                </div>

            </div>


            <div class="col-md-6">

                <label class="text-muted-custom small">
                    Father Name
                </label>

                <div class="fw-semibold mt-1">
                    @if($dateOfBirth->father_name)
                        {{ $dateOfBirth->father_name }}
                    @else
                        &mdash;
                    @endif
                </div>

            </div>


            <div class="col-md-6">

                <label class="text-muted-custom small">
                    Date of Birth
                </label>

                <div class="fw-semibold mt-1">
                    {{ $dateOfBirth->start_date->format('F d, Y') }}
                </div>

            </div>


            <div class="col-md-6">

                <label class="text-muted-custom small">
                    End Date
                </label>

                <div class="fw-semibold mt-1">

                    @if($dateOfBirth->end_date)

                        {{ $dateOfBirth->end_date->format('F d, Y') }}

                    @else

                        <span class="status-badge live">
                            Present
                        </span>

                    @endif

                </div>

            </div>


            <div class="col-md-6">

                <label class="text-muted-custom small">
                    Next Birthday
                </label>

                <div class="fw-semibold mt-1">
                    {{ $dateOfBirth->next_birthday->format('F d, Y') }}
                </div>

            </div>


            <div class="col-md-6">

                <label class="text-muted-custom small">
                    Time Remaining
                </label>

                <div class="fw-semibold mt-1">
                    {{ $dateOfBirth->formatted_next_birthday_countdown }}
                </div>

            </div>


            <div class="col-12">

                <div class="admin-card mt-2">

                    <div class="admin-card-body">

                        <div class="row text-center g-4">


                            <div class="col-md-4">

                                <div class="text-muted-custom small mb-1">
                                    Years
                                </div>

                                <div class="fs-3 fw-bold text-accent">
                                    {{ $dateOfBirth->age['years'] }}
                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="text-muted-custom small mb-1">
                                    Months
                                </div>

                                <div class="fs-3 fw-bold text-accent">
                                    {{ $dateOfBirth->age['months'] }}
                                </div>

                            </div>


                            <div class="col-md-4">

                                <div class="text-muted-custom small mb-1">
                                    Days
                                </div>

                                <div class="fs-3 fw-bold text-accent">
                                    {{ $dateOfBirth->age['days'] }}
                                </div>

                            </div>


                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>

@endsection
