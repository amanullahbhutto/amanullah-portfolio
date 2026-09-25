@extends('layouts.admin')

@section('title', 'Date of Birth Details')
@section('page_title', 'Date of Birth Details')

@section('content')

<section class="admin-card">

    <div class="admin-card-head">

        <div class="d-flex align-items-center gap-3">

            @if($dateOfBirth->image_url)
                <span class="user-avatar overflow-hidden p-0" style="width:48px;height:48px;border-radius:12px;">
                    <img
                        src="{{ $dateOfBirth->image_url }}"
                        alt="{{ $dateOfBirth->name }}"
                        style="width:100%;height:100%;object-fit:cover;display:block;"
                    >
                </span>
            @else
                <span class="user-avatar" style="width:48px;height:48px;border-radius:12px;font-size:1.2rem;">
                    {{ strtoupper(substr($dateOfBirth->name, 0, 1)) }}
                </span>
            @endif

            <div>
                <h2>{{ $dateOfBirth->name }}</h2>
                <p class="text-muted-custom small mb-0 mt-1">
                    Date of birth record details.
                </p>
            </div>

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


            @if(count($dateOfBirth->image_urls) > 0)
                <div class="col-12">

                    <div class="admin-card mt-2">

                        <div class="admin-card-head">
                            <div>
                                <h3 class="h6 mb-0">Photos ({{ count($dateOfBirth->image_urls) }})</h3>
                                <p class="text-muted-custom small mb-0 mt-1">Uploaded images for {{ $dateOfBirth->name }}</p>
                            </div>
                        </div>

                        <div class="admin-card-body">
                            <div class="dob-view-gallery-grid">
                                @foreach($dateOfBirth->images_with_urls as $item)
                                    <div class="dob-view-gallery-item position-relative">
                                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" title="View full image">
                                            <img src="{{ $item['url'] }}" alt="{{ $dateOfBirth->name }} photo {{ $loop->iteration }}">
                                        </a>
                                        @if($loop->first)
                                            <span class="badge bg-accent position-absolute" style="top:6px;left:6px;font-size:0.68rem;padding:3px 6px;z-index:4;border-radius:6px;box-shadow:0 2px 6px rgba(0,0,0,0.4);">Latest</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>

                </div>
            @endif


        </div>

    </div>

</section>

@endsection
