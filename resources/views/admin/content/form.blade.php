@extends('layouts.admin')
@section('title', ($item->exists ? 'Edit ' : 'Add ').$config['label'])
@section('page_title', ($item->exists ? 'Edit ' : 'Add ').rtrim($config['label'], 's'))
@section('content')
<form method="POST" action="{{ $item->exists ? route('admin.content.update', ['type' => $type, 'id' => $item->id]) : route('admin.content.store', $type) }}" enctype="multipart/form-data">
    @csrf @if($item->exists) @method('PUT') @endif
    <div class="admin-card"><div class="admin-card-head"><div><h2>{{ $item->exists ? 'Update content' : 'Create content' }}</h2><p class="text-muted-custom small mb-0 mt-1">Fields marked with * are required.</p></div><a class="btn btn-outline-theme btn-sm" href="{{ route('admin.content.index', $type) }}"><i class="bi bi-arrow-left me-1"></i>Back</a></div><div class="admin-card-body"><div class="row g-4">
        @if($type === 'projects')
            <div class="col-md-8"><label class="form-label" for="title">Project title *</label><input class="form-control" id="title" name="title" value="{{ old('title', $item->title) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="slug">URL slug</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $item->slug) }}" placeholder="auto-generated-if-empty"></div>
            <div class="col-12">
                <label class="form-label d-block">Project Type *</label>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="project-type-option {{ old('project_type', $item->project_type ?? 'full_development') === 'full_development' ? 'is-selected' : '' }}">
                            <input type="radio" name="project_type" value="full_development" class="d-none" @checked(old('project_type', $item->project_type ?? 'full_development') === 'full_development') required>
                            <div class="project-type-card">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="project-type-name"><i class="bi bi-hammer me-2 text-accent"></i>Full Development</span>
                                    <i class="bi bi-check-circle-fill check-badge"></i>
                                </div>
                                <p class="project-type-desc mb-0">Start se khud banaya <span class="text-muted-custom">(Built from scratch)</span></p>
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="project-type-option {{ old('project_type', $item->project_type) === 'modification_enhancement' ? 'is-selected' : '' }}">
                            <input type="radio" name="project_type" value="modification_enhancement" class="d-none" @checked(old('project_type', $item->project_type) === 'modification_enhancement')>
                            <div class="project-type-card">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="project-type-name"><i class="bi bi-gear-wide-connected me-2 text-info"></i>Modification / Enhancement</span>
                                    <i class="bi bi-check-circle-fill check-badge"></i>
                                </div>
                                <p class="project-type-desc mb-0">Pehle se bane project par kaam kiya <span class="text-muted-custom">(Enhancement on existing codebase)</span></p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-12"><label class="form-label" for="excerpt">Short summary *</label><textarea class="form-control" id="excerpt" name="excerpt" required>{{ old('excerpt', $item->excerpt) }}</textarea></div>
            <div class="col-12"><label class="form-label" for="description">Full project description *</label><textarea class="form-control" id="description" name="description" rows="10" required>{{ old('description', $item->description) }}</textarea></div>
            <div class="col-12"><label class="form-label" for="technologies">Technologies</label><input class="form-control" id="technologies" name="technologies" value="{{ old('technologies', implode(', ', $item->technologies ?? [])) }}" placeholder="Laravel, PHP, MySQL, Bootstrap 5"></div>
            <div class="col-md-6"><label class="form-label" for="project_url">Live project URL</label><input class="form-control" type="url" id="project_url" name="project_url" value="{{ old('project_url', $item->project_url) }}"></div>
            <div class="col-md-6"><label class="form-label" for="github_url">GitHub URL</label><input class="form-control" type="url" id="github_url" name="github_url" value="{{ old('github_url', $item->github_url) }}"></div>
            <div class="col-12">
                @php
                    $projectImagePaths = $item->image_paths ?? [];
                    $projectImageUrls = $item->image_urls ?? [];
                @endphp
                <label class="form-label" for="project_images">Project images (JPG, PNG, WebP - max 3 MB each)</label>
                <input class="form-control" type="file" id="project_images" name="project_images[]" accept="image/jpeg,image/png,image/webp" multiple data-gallery-input="#projectImageSelection">
                <p class="text-muted-custom small mb-0 mt-2">Saved in public/assets/images/projects. First image is used as the cover.</p>
                <div id="projectImageSelection" class="selected-gallery-preview d-none"></div>
                @if(count($projectImagePaths))
                    <div class="project-gallery-admin">
                        @foreach($projectImagePaths as $index => $imagePath)
                            <label class="gallery-delete-tile" title="Delete image">
                                <img src="{{ $projectImageUrls[$index] ?? '' }}" alt="{{ $item->title }} project image {{ $loop->iteration }}">
                                <input type="checkbox" name="delete_images[]" value="{{ $imagePath }}" data-gallery-delete>
                                <span class="gallery-delete-overlay"><i class="bi bi-trash3"></i></span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-md-4"><label class="form-label" for="sort_order">Sort order</label><input class="form-control" type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
            <div class="col-12 d-flex flex-wrap gap-4"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured))><label class="form-check-label" for="is_featured">Featured project</label></div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" @checked(old('is_published', !$item->exists || $item->is_published))><label class="form-check-label" for="is_published">Published</label></div></div>
        @elseif($type === 'posts')
            <div class="col-md-8"><label class="form-label" for="title">Post title *</label><input class="form-control" id="title" name="title" value="{{ old('title', $item->title) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="slug">URL slug</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $item->slug) }}" placeholder="auto-generated-if-empty"></div>
            <div class="col-12"><label class="form-label" for="excerpt">Excerpt *</label><textarea class="form-control" id="excerpt" name="excerpt" required>{{ old('excerpt', $item->excerpt) }}</textarea></div>
            <div class="col-12"><label class="form-label" for="content">Article content *</label><textarea class="form-control" id="content" name="content" rows="14" required>{{ old('content', $item->content) }}</textarea></div>
            <div class="col-md-6"><label class="form-label" for="meta_title">SEO title</label><input class="form-control" id="meta_title" name="meta_title" maxlength="70" value="{{ old('meta_title', $item->meta_title) }}"></div>
            <div class="col-md-6"><label class="form-label" for="published_at">Publish date</label><input class="form-control" type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $item->published_at?->format('Y-m-d\TH:i')) }}"></div>
            <div class="col-12"><label class="form-label" for="meta_description">SEO meta description</label><input class="form-control" id="meta_description" name="meta_description" maxlength="170" value="{{ old('meta_description', $item->meta_description) }}"></div>
            <div class="col-md-8">
                <label class="form-label" for="image">Featured image (JPG, PNG, WebP - max 3 MB)</label>
                <div class="profile-upload-preview content-image-preview">
                    <div class="preview-frame landscape-preview {{ $item->image_url ? 'has-image' : '' }}">
                        @if($item->image_url)
                            <img id="contentImagePreview" src="{{ $item->image_url }}" alt="{{ $item->title }} featured image">
                        @else
                            <img id="contentImagePreview" alt="">
                            <i class="bi bi-image"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-image-input="#contentImagePreview">
                        <p class="text-muted-custom small mb-0 mt-2">
                            @if($item->image)
                                Current image is saved in public/assets. Choose a new file only if you want to replace it.
                            @else
                                Blog images are saved in public/assets/images/blogs.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" @checked(old('is_published', !$item->exists || $item->is_published))><label class="form-check-label" for="is_published">Published</label></div></div>
        @elseif($type === 'services')
            <div class="col-md-8"><label class="form-label" for="title">Service title *</label><input class="form-control" id="title" name="title" value="{{ old('title', $item->title) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="slug">URL slug</label><input class="form-control" id="slug" name="slug" value="{{ old('slug', $item->slug) }}"></div>
            <div class="col-12"><label class="form-label" for="short_description">Description *</label><textarea class="form-control" id="short_description" name="short_description" required>{{ old('short_description', $item->short_description) }}</textarea></div>
            <div class="col-md-6"><label class="form-label" for="icon">Bootstrap icon class *</label><input class="form-control" id="icon" name="icon" value="{{ old('icon', $item->icon ?? 'bi-code-slash') }}" required><div class="form-text text-muted-custom">Example: bi-code-slash, bi-boxes, bi-database-check</div></div>
            <div class="col-md-3"><label class="form-label" for="sort_order">Sort order</label><input class="form-control" type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
            <div class="col-md-3 d-flex align-items-end pb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', !$item->exists || $item->is_active))><label class="form-check-label" for="is_active">Active</label></div></div>
        @elseif($type === 'experiences')
            <div class="col-md-6"><label class="form-label" for="position">Position *</label><input class="form-control" id="position" name="position" value="{{ old('position', $item->position) }}" required></div>
            <div class="col-md-6"><label class="form-label" for="company">Company *</label><input class="form-control" id="company" name="company" value="{{ old('company', $item->company) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="location">Location</label><input class="form-control" id="location" name="location" value="{{ old('location', $item->location) }}"></div>
            <div class="col-md-4"><label class="form-label" for="start_date">Start date *</label><input class="form-control" type="date" id="start_date" name="start_date" value="{{ old('start_date', $item->start_date?->format('Y-m-d')) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="end_date">End date</label><input class="form-control" type="date" id="end_date" name="end_date" value="{{ old('end_date', $item->end_date?->format('Y-m-d')) }}"></div>
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_current" name="is_current" value="1" @checked(old('is_current', $item->is_current))><label class="form-check-label" for="is_current">I currently work here</label></div></div>
            <div class="col-12"><label class="form-label" for="summary">Summary *</label><textarea class="form-control" id="summary" name="summary" required>{{ old('summary', $item->summary) }}</textarea></div>
            <div class="col-12"><label class="form-label" for="bullets">Responsibilities (one per line)</label><textarea class="form-control" id="bullets" name="bullets" rows="7">{{ old('bullets', implode("\n", $item->bullets ?? [])) }}</textarea></div>
            <div class="col-md-3"><label class="form-label" for="sort_order">Sort order</label><input class="form-control" type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
        @elseif($type === 'educations')
            <div class="col-md-7"><label class="form-label" for="institution">Institution *</label><input class="form-control" id="institution" name="institution" value="{{ old('institution', $item->institution) }}" required></div>
            <div class="col-md-5"><label class="form-label" for="degree">Degree *</label><input class="form-control" id="degree" name="degree" value="{{ old('degree', $item->degree) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="field">Field of study</label><input class="form-control" id="field" name="field" value="{{ old('field', $item->field) }}"></div>
            <div class="col-md-3"><label class="form-label" for="start_year">Start year *</label><input class="form-control" type="number" id="start_year" name="start_year" min="1980" max="2100" value="{{ old('start_year', $item->start_year) }}" required></div>
            <div class="col-md-3"><label class="form-label" for="end_year">End year *</label><input class="form-control" type="number" id="end_year" name="end_year" min="1980" max="2100" value="{{ old('end_year', $item->end_year) }}" required></div>
            <div class="col-md-2"><label class="form-label" for="sort_order">Order</label><input class="form-control" type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
            <div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description">{{ old('description', $item->description) }}</textarea></div>
        @elseif($type === 'skills')
            <div class="col-md-5"><label class="form-label" for="name">Skill name *</label><input class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" required></div>
            <div class="col-md-4"><label class="form-label" for="category">Category *</label><input class="form-control" id="category" name="category" value="{{ old('category', $item->category ?? 'Development') }}" required></div>
            <div class="col-md-3"><label class="form-label" for="proficiency">Proficiency (1-100) *</label><input class="form-control" type="number" id="proficiency" name="proficiency" min="1" max="100" value="{{ old('proficiency', $item->proficiency ?? 80) }}" required></div>
            <div class="col-md-3"><label class="form-label" for="sort_order">Sort order</label><input class="form-control" type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
            <div class="col-md-3 d-flex align-items-end pb-2"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', !$item->exists || $item->is_active))><label class="form-check-label" for="is_active">Active</label></div></div>
        @endif
    </div></div><div class="admin-card-head justify-content-end"><a class="btn btn-outline-theme" href="{{ route('admin.content.index', $type) }}">Cancel</a><button class="btn btn-accent" type="submit"><i class="bi bi-check-lg me-1"></i>{{ $item->exists ? 'Save changes' : 'Create item' }}</button></div></div>
</form>
@endsection
