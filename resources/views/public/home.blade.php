@extends('layouts.public')

@section('title', ($profile?->full_name ?? 'Amanullah'))

@section('content')
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="status-pill"><span class="status-dot"></span>{{ $profile?->availability ?? 'Available for work' }}</div>
                <h1>Hi, I'm <span>{{ $profile?->full_name ?? 'Amanullah' }}</span></h1>
                <p class="hero-lead"><strong>{{ $profile?->headline ?? 'PHP & Laravel Web Developer' }}.</strong> {{ $profile?->short_bio }}</p>
                <div class="hero-actions">
                    <a class="btn btn-accent" href="{{ route('projects.index') }}">View my work <i class="bi bi-arrow-down-right ms-2"></i></a>
                    <a class="btn btn-outline-theme" href="{{ route('contact.create') }}">Get in touch</a>
                    @if($profile?->cv_file_url)<a class="btn btn-soft" href="{{ $profile->cv_file_url }}" download>Download CV <i class="bi bi-download ms-2"></i></a>@endif
                </div>
                <div class="hero-stats">
                    <div class="hero-stat"><strong>{{ $profile?->project_count ?? 12 }}+</strong><span>Projects done</span></div>
                    <div class="hero-stat"><strong>{{ $profile?->happy_clients ?? 8 }}+</strong><span>Happy clients</span></div>
                    <div class="hero-stat"><strong>{{ $experienceStat ?? (($profile?->years_experience ?? 2).'y') }}</strong><span>Experience</span></div>
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="120">
                <div class="portrait-card">
                    <div class="portrait-frame {{ $profile?->profile_image_url ? 'has-image' : '' }}">
                        @if($profile?->profile_image_url)<img src="{{ $profile->profile_image_url }}" alt="{{ $profile->full_name }} - PHP and Laravel developer" width="600" height="700">@endif
                    </div>
                    <div class="floating-code"><i class="bi bi-braces-asterisk"></i></div>
                    <a class="portrait-badge" href="{{ route('projects.index') }}">Open to projects <i class="bi bi-arrow-up-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space section-alt" id="services">
    <div class="container">
        <div class="section-heading centered" data-aos="fade-up">
            <span class="eyebrow">What I do</span>
            <h2>Web solutions built for real business needs</h2>
            <p>From Laravel backends to responsive Bootstrap interfaces, every solution is designed for performance, maintainability, and growth.</p>
        </div>
        <div class="row g-4">
            @foreach($services as $service)
                <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 80 }}">
                    <article class="service-card">
                        <div class="service-icon"><i class="bi {{ $service->icon }}"></i></div>
                        <h3>{{ $service->title }}</h3>
                        <p>{{ $service->short_description }}</p>
                        <a class="card-arrow" href="{{ route('contact.create', ['service' => $service->slug]) }}">Discuss a project <i class="bi bi-arrow-up-right ms-2"></i></a>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space" id="projects">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-5" data-aos="fade-up">
            <div class="section-heading mb-0">
                <span class="eyebrow">Selected work</span>
                <h2>Projects shaped with purpose</h2>
                <p>Responsive applications that combine practical functionality with clear, maintainable code.</p>
            </div>
            <a class="btn btn-outline-theme" href="{{ route('projects.index') }}">View all projects <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4 align-items-stretch">
            @foreach($projects as $project)
                <div class="col-md-6 col-xl-4 d-flex" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 80 }}">
                    <article class="project-card d-flex flex-column h-100 w-100">
                        <a href="{{ route('projects.show', $project) }}" class="project-visual d-block" aria-label="View {{ $project->title }}">
                            @if($project->image_url)
                                <img src="{{ $project->image_url }}" alt="{{ $project->title }} project preview" loading="lazy" width="800" height="500">
                            @else
                                <div class="project-placeholder"><div class="project-window"><div class="project-window-bar"><i></i><i></i><i></i></div><div class="project-code-lines"><span></span><span></span><span></span><span></span><span></span></div></div></div>
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
            @endforeach
        </div>
    </div>
</section>

<section class="section-space section-alt" id="experience">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7" data-aos="fade-right">
                <div class="section-heading">
                    <span class="eyebrow">Career journey</span>
                    <h2>Experience</h2>
                </div>
                <div class="experience-wrap">
                    @foreach($experiences as $experience)
                        <article class="experience-item">
                            <div class="experience-icon"><i class="bi bi-code-slash"></i></div>
                            <div>
                                <div class="experience-date">{{ $experience->start_date->format('M Y') }} - {{ $experience->is_current ? 'Present' : $experience->end_date?->format('M Y') }}</div>
                                <h3>{{ $experience->position }}</h3>
                                <div class="experience-company">{{ $experience->company }}@if($experience->location) <span class="text-muted-custom">- {{ $experience->location }}</span>@endif</div>
                                <p class="experience-summary">{{ $experience->summary }}</p>
                                @if($experience->bullets)<ul class="experience-bullets">@foreach($experience->bullets as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-5" data-aos="fade-left" data-aos-delay="100">
                <div class="section-heading"><span class="eyebrow">Let's connect</span><h2>Ready to build something useful?</h2><p>Feel free to reach out for Laravel development, PHP backend work, responsive interfaces, maintenance, or deployment support.</p></div>
                <a class="btn btn-accent" href="{{ route('contact.create') }}">Start a conversation <i class="bi bi-arrow-up-right ms-2"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="section-space" id="skills">
    <div class="container">
        <div class="section-heading centered" data-aos="fade-up"><span class="eyebrow">Technology stack</span><h2>Skills</h2><p>A practical full-stack toolkit for building and maintaining responsive web applications.</p></div>
        <div class="skills-grid" data-aos="fade-up">
            @foreach($skills as $skill)
                <div class="skill-item">
                    <div class="skill-head"><span>{{ $skill->name }}</span><span>{{ $skill->proficiency }}%</span></div>
                    <div class="skill-track"><div class="skill-fill" style="--skill-width: {{ $skill->proficiency }}%"></div></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space section-alt" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="about-photo {{ $profile?->profile_image_url ? 'has-image' : '' }}">@if($profile?->profile_image_url)<img src="{{ $profile->profile_image_url }}" alt="{{ $profile->full_name }} profile" loading="lazy">@endif</div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="about-copy">
                    <span class="eyebrow">About me</span>
                    <h2>{{ $profile?->headline }}</h2>
                    <p>{!! nl2br(e($profile?->long_bio)) !!}</p>
                    <div class="detail-grid">
                        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Email:</strong> @if($profile?->email)<a class="text-accent gmail-link" href="{{ $profile->gmail_compose_url }}" target="_blank" rel="noopener">{{ $profile->email }}</a>@endif</span></div>
                        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Phone:</strong> @if($profile?->phone)<a class="text-accent whatsapp-link" href="{{ $profile->whatsapp_url }}" target="_blank" rel="noopener">{{ $profile->phone }}</a>@endif</span></div>
                        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>City:</strong> {{ $profile?->city }}, {{ $profile?->country }}</span></div>
                        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Languages:</strong> {{ implode(', ', $profile?->languages ?? []) }}</span></div>
                    </div>
                    <a class="btn btn-soft" href="{{ route('about') }}">More about me <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space" id="blog">
    <div class="container">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-5" data-aos="fade-up">
            <div class="section-heading mb-0"><span class="eyebrow">Development notes</span><h2>Latest blog posts</h2><p>Practical ideas about Laravel, responsive interfaces, and safer deployment.</p></div>
            <a class="btn btn-outline-theme" href="{{ route('posts.index') }}">Browse all posts <i class="bi bi-arrow-right ms-2"></i></a>
        </div>
        <div class="row g-4">
            @foreach($posts as $post)
                <div class="col-md-6 col-xl-4" data-aos="fade-up" data-aos-delay="{{ ($loop->index % 3) * 80 }}">
                    <article class="blog-card"><div class="blog-body"><span class="blog-date">{{ ($post->published_at ?? $post->created_at)->format('M d, Y') }}</span><h3 class="mt-3"><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3><p>{{ $post->excerpt }}</p><a class="card-arrow" href="{{ route('posts.show', $post) }}">Read article <i class="bi bi-arrow-right ms-2"></i></a></div></article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space-sm">
    <div class="container" data-aos="zoom-in">
        <div class="cta-panel"><span class="eyebrow">Get in touch</span><h2>Let's build a fast, responsive, and maintainable web application.</h2><p class="text-muted-custom mt-3 mb-4">Tell me what you need, and I will respond with a practical way forward.</p><a class="btn btn-accent" href="{{ route('contact.create') }}">Discuss your project <i class="bi bi-arrow-up-right ms-2"></i></a></div>
    </div>
</section>
@endsection
