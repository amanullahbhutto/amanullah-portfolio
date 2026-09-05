@extends('layouts.public')
@section('title', $project->title.' - Amanullah')
@section('meta_description', $project->excerpt)
@section('content')
<section class="section-space" style="padding-top: calc(var(--header-height) + 50px)">
    <div class="container">
        <article class="article-wrap">
            <div class="breadcrumbs">
                <a href="{{ route('home') }}"><i class="bi bi-house-door me-1"></i>Home</a>
                <span>/</span>
                <a href="{{ route('projects.index') }}">Projects</a>
                <span>/</span>
                <span class="text-truncate" style="max-width: 250px;">{{ $project->title }}</span>
            </div>

            <header class="article-header mb-4">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                    <span class="eyebrow mb-0">Project case study</span>
                    <span class="project-type-pill {{ $project->project_type_badge_class }}">
                        @if(($project->project_type ?? 'full_development') === 'modification_enhancement')
                            <i class="bi bi-gear-wide-connected me-1"></i>
                        @else
                            <i class="bi bi-hammer me-1"></i>
                        @endif
                        {{ $project->project_type_label }}
                    </span>
                    @if($project->is_featured)
                        <span class="featured-tag" style="position: static;">Featured</span>
                    @endif
                </div>

                <h1 class="mb-3">{{ $project->title }}</h1>
                <p class="hero-lead mb-3">{{ $project->excerpt }}</p>

                <div class="p-3 rounded-3 mb-4" style="background: var(--surface-2); border: 1px solid var(--line);">
                    <p class="text-muted-custom small mb-0">
                        <i class="bi bi-info-circle-fill text-accent me-1"></i>
                        <strong>Development scope:</strong> {{ $project->project_type_label }} &mdash; {{ $project->project_type_description }}
                    </p>
                </div>

                @if(!empty($project->technologies))
                    <div class="mb-4">
                        <span class="text-muted-custom small d-block mb-2 font-monospace text-uppercase" style="letter-spacing: .05em;">Technologies Used</span>
                        <ul class="tech-list">
                            @foreach($project->technologies as $technology)
                                <li>{{ $technology }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </header>

            @php($projectImageUrls = $project->image_urls)
            @if(count($projectImageUrls))
                <div class="project-detail-gallery project-detail-hero-wrapper" data-aos="fade-up">
                    <div class="project-detail-hero-image" id="projectHeroImageTrigger" title="Click to view full image">
                        <img id="projectMainImage" src="{{ $projectImageUrls[0] }}" alt="{{ $project->title }} project main preview" loading="eager">
                        <div class="hero-zoom-badge">
                            <i class="bi bi-arrows-fullscreen"></i>
                            <span>View Full Image</span>
                        </div>
                    </div>

                    <div class="project-gallery-grid project-gallery-strip {{ count($projectImageUrls) > 1 ? 'mt-3' : 'd-none' }}" id="projectGalleryStrip">
                        @foreach($projectImageUrls as $index => $imageUrl)
                            <button type="button" class="project-gallery-thumb {{ $loop->first ? 'is-active' : '' }}" data-image-index="{{ $index }}" data-image-url="{{ $imageUrl }}" aria-label="View preview {{ $loop->iteration }}">
                                <img src="{{ $imageUrl }}" alt="{{ $project->title }} preview {{ $loop->iteration }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Fullscreen Lightbox Modal --}}
                <div class="modal fade lightbox-modal" id="projectLightboxModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content">
                            <div class="modal-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <h5 class="modal-title mb-0">{{ $project->title }}</h5>
                                    <span class="lightbox-counter" id="lightboxCounter">1 / {{ count($projectImageUrls) }}</span>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body position-relative">
                                @if(count($projectImageUrls) > 1)
                                    <button class="lightbox-nav-btn prev-btn" id="lightboxPrevBtn" type="button" aria-label="Previous image">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                @endif
                                <img id="lightboxActiveImg" src="{{ $projectImageUrls[0] }}" alt="{{ $project->title }} full size preview" class="lightbox-preview-img">
                                @if(count($projectImageUrls) > 1)
                                    <button class="lightbox-nav-btn next-btn" id="lightboxNextBtn" type="button" aria-label="Next image">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="article-image project-visual mb-4">
                    <div class="project-placeholder">
                        <div class="project-window">
                            <div class="project-window-bar"><i></i><i></i><i></i></div>
                            <div class="project-code-lines"><span></span><span></span><span></span><span></span><span></span></div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="article-body-wrapper mt-4">
                <h3 class="h4 mb-3" style="font-weight: 700;">Project Overview & Details</h3>
                <div class="article-content" style="font-size: 1.02rem; line-height: 1.8; color: var(--text);">
                    {!! nl2br(e($project->description)) !!}
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 mt-5 pt-4 border-top" style="border-color: var(--line) !important;">
                @if($project->project_url)
                    <a class="btn btn-accent" href="{{ $project->project_url }}" target="_blank" rel="noopener">
                        Visit live project <i class="bi bi-arrow-up-right ms-2"></i>
                    </a>
                @endif
                @if($project->github_url)
                    <a class="btn btn-outline-theme" href="{{ $project->github_url }}" target="_blank" rel="noopener">
                        <i class="bi bi-github me-2"></i>View code repository
                    </a>
                @endif
                <a class="btn btn-soft ms-auto" href="{{ route('projects.index') }}">
                    <i class="bi bi-arrow-left me-2"></i>Back to all projects
                </a>
            </div>
        </article>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    'use strict';
    const images = @json($projectImageUrls ?? []);
    if (!images.length) return;

    let currentIndex = 0;
    const mainImg = document.getElementById('projectMainImage');
    const heroTrigger = document.getElementById('projectHeroImageTrigger');
    const lightboxModalEl = document.getElementById('projectLightboxModal');
    const lightboxImg = document.getElementById('lightboxActiveImg');
    const lightboxCounter = document.getElementById('lightboxCounter');
    const prevBtn = document.getElementById('lightboxPrevBtn');
    const nextBtn = document.getElementById('lightboxNextBtn');
    const thumbs = document.querySelectorAll('#projectGalleryStrip .project-gallery-thumb');

    const updateImage = (index) => {
        if (index < 0 || index >= images.length) return;
        currentIndex = index;
        const newSrc = images[currentIndex];

        if (mainImg) {
            mainImg.style.opacity = '0.6';
            mainImg.src = newSrc;
            mainImg.onload = () => { mainImg.style.opacity = '1'; };
        }

        if (lightboxImg) lightboxImg.src = newSrc;
        if (lightboxCounter) lightboxCounter.textContent = `${currentIndex + 1} / ${images.length}`;

        thumbs.forEach((thumb, idx) => {
            thumb.classList.toggle('is-active', idx === currentIndex);
        });
    };

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const idx = parseInt(thumb.dataset.imageIndex, 10);
            updateImage(idx);
        });
    });

    if (heroTrigger && lightboxModalEl && window.bootstrap) {
        const modal = window.bootstrap.Modal.getOrCreateInstance(lightboxModalEl);
        heroTrigger.addEventListener('click', () => {
            updateImage(currentIndex);
            modal.show();
        });
    }

    prevBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const nextIdx = (currentIndex - 1 + images.length) % images.length;
        updateImage(nextIdx);
    });

    nextBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const nextIdx = (currentIndex + 1) % images.length;
        updateImage(nextIdx);
    });

    document.addEventListener('keydown', (e) => {
        if (!lightboxModalEl?.classList.contains('show')) return;
        if (e.key === 'ArrowLeft') {
            const nextIdx = (currentIndex - 1 + images.length) % images.length;
            updateImage(nextIdx);
        } else if (e.key === 'ArrowRight') {
            const nextIdx = (currentIndex + 1) % images.length;
            updateImage(nextIdx);
        }
    });
})();
</script>
@endpush

