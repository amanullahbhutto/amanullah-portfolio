@extends('layouts.public')
@section('title', 'Projects - Amanullah')
@section('meta_description', 'Explore Laravel, PHP, MySQL, Bootstrap, JavaScript, and responsive web development projects by Amanullah.')
@section('content')
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">Selected work</span>
        <h1>Projects</h1>
        <p>Web applications designed to solve practical problems with responsive interfaces and maintainable code.</p>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            @forelse($projects as $project)
                <div class="col-md-6 col-xl-4 d-flex" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 80 }}">
                    <article class="project-card d-flex flex-column h-100 w-100">
                        <a href="{{ route('projects.show', $project) }}" class="project-visual d-block" aria-label="View {{ $project->title }}">
                            @if($project->image_url)
                                <img src="{{ $project->image_url }}" alt="{{ $project->title }} project preview" loading="lazy" width="800" height="500">
                            @else
                                <div class="project-placeholder">
                                    <div class="project-window">
                                        <div class="project-window-bar"><i></i><i></i><i></i></div>
                                        <div class="project-code-lines"><span></span><span></span><span></span><span></span><span></span></div>
                                    </div>
                                </div>
                            @endif
                            <div class="project-card-tags">
                                <span class="project-type-pill {{ $project->project_type_badge_class }}">{{ $project->project_type_label }}</span>
                                @if($project->is_featured)<span class="featured-tag">Featured</span>@endif
                            </div>
                        </a>
                        <div class="project-body d-flex flex-column flex-grow-1">
                            <h3 class="project-card-title"><a href="{{ route('projects.show', $project) }}" title="{{ $project->title }}">{{ $project->title }}</a></h3>
                            <p class="project-card-excerpt" title="{{ $project->excerpt }}">{{ $project->excerpt }}</p>
                            
                            @php($techList = $project->technologies ?? [])
                            @if(count($techList) > 0)
                                <ul class="tech-list mb-3">
                                    @foreach(array_slice($techList, 0, 4) as $technology)
                                        <li>{{ $technology }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="project-card-footer mt-auto">
                                <a class="card-arrow" href="{{ route('projects.show', $project) }}">View full detail <i class="bi bi-arrow-up-right ms-2"></i></a>
                                @if($project->project_url)
                                    <a href="{{ $project->project_url }}" target="_blank" rel="noopener" class="btn-preview-link" title="Live Preview" aria-label="Open live demo">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12"><div class="info-card p-5 text-center"><h2>Projects are being added</h2><p class="text-muted-custom mb-0">Please check again soon.</p></div></div>
            @endforelse
        </div>
        <div class="mt-5">{{ $projects->links() }}</div>
    </div>
</section>
@endsection
