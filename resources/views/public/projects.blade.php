@extends('layouts.public')
@section('title', 'Projects - Amanullah ')
@section('meta_description', 'Explore Laravel, PHP, MySQL, Bootstrap, JavaScript, and responsive web development projects by Amanullah.')
@section('content')
<section class="page-hero"><div class="container"><span class="eyebrow">Selected work</span><h1>Projects</h1><p>Web applications designed to solve practical problems with responsive interfaces and maintainable code.</p></div></section>
<section class="section-space"><div class="container"><div class="row g-4">
    @forelse($projects as $project)
        <div class="col-md-6 col-xl-4" data-aos="fade-up"><article class="project-card">
            <a href="{{ route('projects.show', $project) }}" class="project-visual d-block">
                @if($project->image_url)<img src="{{ $project->image_url }}" alt="{{ $project->title }} project preview" loading="lazy">@else<div class="project-placeholder"><div class="project-window"><div class="project-window-bar"><i></i><i></i><i></i></div><div class="project-code-lines"><span></span><span></span><span></span><span></span><span></span></div></div></div>@endif
                <div class="project-card-tags">
                    <span class="project-type-pill {{ $project->project_type_badge_class }}">{{ $project->project_type_label }}</span>
                    @if($project->is_featured)<span class="featured-tag">Featured</span>@endif
                </div>
            </a>
            <div class="project-body"><h3><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h3><p>{{ $project->excerpt }}</p><ul class="tech-list">@foreach(array_slice($project->technologies ?? [], 0, 5) as $technology)<li>{{ $technology }}</li>@endforeach</ul><a class="card-arrow" href="{{ route('projects.show', $project) }}">View case study <i class="bi bi-arrow-up-right ms-2"></i></a></div>
        </article></div>
    @empty
        <div class="col-12"><div class="info-card p-5 text-center"><h2>Projects are being added</h2><p class="text-muted-custom mb-0">Please check again soon.</p></div></div>
    @endforelse
</div><div class="mt-5">{{ $projects->links() }}</div></div></section>
@endsection
