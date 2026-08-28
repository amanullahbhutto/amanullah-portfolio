@extends('layouts.public')
@section('title', $project->title.' - Amanullah')
@section('meta_description', $project->excerpt)
@section('content')
<section class="section-space" style="padding-top: calc(var(--header-height) + 80px)"><div class="container"><article class="article-wrap">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('projects.index') }}">Projects</a><span>/</span><span>{{ $project->title }}</span></div>
    <header class="article-header">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
            <span class="eyebrow mb-0">Project case study</span>
            <span class="project-type-pill {{ $project->project_type_badge_class }}">
                @if(($project->project_type ?? 'full_development') === 'modification_enhancement')
                    <i class="bi bi-gear-wide-connected me-1"></i>
                @else
                    <i class="bi bi-hammer me-1"></i>
                @endif
                {{ $project->project_type_label }}
            </span>
        </div>
        <h1>{{ $project->title }}</h1>
        <p class="hero-lead">{{ $project->excerpt }}</p>
        <p class="text-muted-custom small"><i class="bi bi-info-circle me-1"></i><strong>Development scope:</strong> {{ $project->project_type_label }} — {{ $project->project_type_description }}</p>
        <ul class="tech-list">@foreach($project->technologies ?? [] as $technology)<li>{{ $technology }}</li>@endforeach</ul>
    </header>
    @php($projectImageUrls = $project->image_urls)
    @if(count($projectImageUrls))
        <div class="project-detail-gallery">
            <div class="project-gallery-grid">
                @foreach($projectImageUrls as $imageUrl)
                    <a class="project-gallery-thumb" href="{{ $imageUrl }}" target="_blank" rel="noopener">
                        <img src="{{ $imageUrl }}" alt="{{ $project->title }} project image {{ $loop->iteration }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="article-image project-visual"><div class="project-placeholder"><div class="project-window"><div class="project-window-bar"><i></i><i></i><i></i></div><div class="project-code-lines"><span></span><span></span><span></span><span></span><span></span></div></div></div></div>
    @endif
    <div class="article-content">{!! nl2br(e($project->description)) !!}</div>
    @if($project->project_url || $project->github_url)<div class="d-flex flex-wrap gap-3 mt-5">@if($project->project_url)<a class="btn btn-accent" href="{{ $project->project_url }}" target="_blank" rel="noopener">Visit project <i class="bi bi-arrow-up-right ms-2"></i></a>@endif @if($project->github_url)<a class="btn btn-outline-theme" href="{{ $project->github_url }}" target="_blank" rel="noopener"><i class="bi bi-github me-2"></i>View code</a>@endif</div>@endif
</article></div></section>
@endsection
