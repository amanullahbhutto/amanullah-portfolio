@extends('layouts.admin')
@section('title', 'Profile')
@section('page_title', 'Profile')
@section('content')
<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="admin-card">
        <div class="admin-card-head">
            <div>
                <h2>Public profile</h2>
                <p class="text-muted-custom small mb-0 mt-1">These details appear across the portfolio.</p>
            </div>
            @can('update profile')
                <button class="btn btn-accent btn-sm" type="submit">
                    <i class="bi bi-check-lg me-1"></i>Save profile
                </button>
            @endcan
        </div>

        <div class="admin-card-body">
            <div class="row g-4">
                <div class="col-12"><span class="eyebrow">Professional details</span></div>

                <div class="col-md-6">
                    <label class="form-label" for="full_name">Full name *</label>
                    <input class="form-control" id="full_name" name="full_name" value="{{ old('full_name', $profile->full_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="headline">Professional headline *</label>
                    <input class="form-control" id="headline" name="headline" value="{{ old('headline', $profile->headline) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="short_bio">Short bio *</label>
                    <textarea class="form-control" id="short_bio" name="short_bio" required>{{ old('short_bio', $profile->short_bio) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="long_bio">About text *</label>
                    <textarea class="form-control" id="long_bio" name="long_bio" rows="8" required>{{ old('long_bio', $profile->long_bio) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="profile_image">Profile image (JPG, PNG, WebP - max 3 MB)</label>
                    <div class="profile-upload-preview">
                        <div class="preview-frame {{ $profile->profile_image_url ? 'has-image' : '' }}">
                            @if($profile->profile_image_url)
                                <img id="profileImagePreview" src="{{ $profile->profile_image_url }}" alt="{{ $profile->full_name }} profile image">
                            @else
                                <img id="profileImagePreview" alt="">
                                <i class="bi bi-person-bounding-box"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp" data-image-input="#profileImagePreview">
                            <p class="text-muted-custom small mb-0 mt-2">Saved in public/assets/images after upload.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="cv_file">CV file (PDF - max 5 MB)</label>
                    <div class="file-status-card">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <div class="min-w-0">
                            @if($profile->cv_file_url)
                                <a class="text-accent fw-bold d-inline-block text-truncate mw-100" href="{{ $profile->cv_file_url }}" target="_blank" rel="noopener">Current CV</a>
                                <p class="text-muted-custom small mb-2">Upload a new PDF to replace it.</p>
                            @else
                                <strong>No CV uploaded</strong>
                                <p class="text-muted-custom small mb-2">Choose a PDF to show the download button on the site.</p>
                            @endif
                            <input class="form-control" type="file" id="cv_file" name="cv_file" accept="application/pdf">
                            <p class="text-muted-custom small mb-0 mt-2">Saved in public/assets/images after upload.</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-5"><span class="eyebrow">Contact details</span></div>

                <div class="col-md-6">
                    <label class="form-label" for="email">Email *</label>
                    <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $profile->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="phone">Primary phone</label>
                    <input class="form-control" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="address">Address</label>
                    <input class="form-control" id="address" name="address" value="{{ old('address', $profile->address) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="city">City</label>
                    <input class="form-control" id="city" name="city" value="{{ old('city', $profile->city) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="country">Country</label>
                    <input class="form-control" id="country" name="country" value="{{ old('country', $profile->country) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="languages">Languages (comma separated)</label>
                    <input class="form-control" id="languages" name="languages" value="{{ old('languages', implode(', ', $profile->languages ?? [])) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="availability">Availability *</label>
                    <input class="form-control" id="availability" name="availability" value="{{ old('availability', $profile->availability) }}" required>
                </div>

                <div class="col-12 mt-5"><span class="eyebrow">Portfolio metrics & links</span></div>

                <div class="col-md-4">
                    <label class="form-label" for="years_experience">Years of experience *</label>
                    <input class="form-control" type="number" id="years_experience" name="years_experience" min="0" value="{{ old('years_experience', $profile->years_experience) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="project_count">Projects completed *</label>
                    <input class="form-control" type="number" id="project_count" name="project_count" min="0" value="{{ old('project_count', $profile->project_count) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="happy_clients">Happy clients *</label>
                    <input class="form-control" type="number" id="happy_clients" name="happy_clients" min="0" value="{{ old('happy_clients', $profile->happy_clients) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="github_url">GitHub URL</label>
                    <input class="form-control" type="url" id="github_url" name="github_url" value="{{ old('github_url', $profile->github_url) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="linkedin_url">LinkedIn URL</label>
                    <input class="form-control" type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url) }}">
                </div>
            </div>
        </div>

        @can('update profile')
            <div class="admin-card-head justify-content-end">
                <button class="btn btn-accent" type="submit">
                    <i class="bi bi-check-lg me-1"></i>Save profile
                </button>
            </div>
        @endcan
    </div>
</form>
@endsection
