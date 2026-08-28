@extends('layouts.public')
@section('title', 'About Amanullah ')
@section('meta_description', 'Learn about Amanullah, a PHP and Laravel developer in Karachi with full-stack web development experience.')
@section('content')
<section class="page-hero"><div class="container"><span class="eyebrow">My background</span><h1>About Me</h1><p>PHP and Laravel developer focused on responsive, secure, and maintainable web applications.</p></div></section>
<section class="section-space"><div class="container"><div class="row align-items-center g-5">
    <div class="col-lg-5" data-aos="fade-right"><div class="about-photo {{ $profile?->profile_image_url ? 'has-image' : '' }}">@if($profile?->profile_image_url)<img src="{{ $profile->profile_image_url }}" alt="{{ $profile->full_name }} profile image">@endif</div></div>
    <div class="col-lg-7" data-aos="fade-left"><div class="about-copy"><span class="eyebrow">Who I am</span><h2>{{ $profile?->headline }}</h2><p>{!! nl2br(e($profile?->long_bio)) !!}</p><div class="detail-grid">
        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Email:</strong> @if($profile?->email)<a class="text-accent gmail-link" href="{{ $profile->gmail_compose_url }}" target="_blank" rel="noopener">{{ $profile->email }}</a>@endif</span></div>
        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Phone:</strong> @if($profile?->phone)<a class="text-accent whatsapp-link" href="{{ $profile->whatsapp_url }}" target="_blank" rel="noopener">{{ $profile->phone }}</a>@endif</span></div>
        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Address:</strong> {{ $profile?->address }}</span></div>
        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>City:</strong> {{ $profile?->city }}, {{ $profile?->country }}</span></div>
        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Languages:</strong> {{ implode(', ', $profile?->languages ?? []) }}</span></div>
        <div class="detail-item"><i class="bi bi-chevron-right"></i><span><strong>Availability:</strong> {{ $profile?->availability }}</span></div>
    </div><div class="d-flex flex-wrap gap-3"><a class="btn btn-accent" href="{{ route('contact.create') }}">Get in touch</a>@if($profile?->cv_file_url)<a class="btn btn-outline-theme" href="{{ $profile->cv_file_url }}" download>Download CV</a>@endif</div></div></div>
</div></div></section>

<section class="section-space section-alt"><div class="container"><div class="row g-5">
    <div class="col-lg-7" data-aos="fade-right"><div class="section-heading"><span class="eyebrow">Professional history</span><h2>Experience</h2></div><div class="experience-wrap">
        @foreach($experiences as $experience)<article class="experience-item"><div class="experience-icon"><i class="bi bi-code-slash"></i></div><div><div class="experience-date">{{ $experience->start_date->format('M Y') }} - {{ $experience->is_current ? 'Present' : $experience->end_date?->format('M Y') }}</div><h3>{{ $experience->position }}</h3><div class="experience-company">{{ $experience->company }}@if($experience->location) - <span class="text-muted-custom">{{ $experience->location }}</span>@endif</div><p class="experience-summary">{{ $experience->summary }}</p>@if($experience->bullets)<ul class="experience-bullets">@foreach($experience->bullets as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>@endif</div></article>@endforeach
    </div></div>
    <div class="col-lg-5" data-aos="fade-left"><div class="section-heading"><span class="eyebrow">Education</span><h2>Qualifications</h2></div><div class="d-grid gap-3">@foreach($educations as $education)<article class="education-card info-card"><span class="education-year">{{ $education->start_year }} - {{ $education->end_year }}</span><h3>{{ $education->degree }}@if($education->field) in {{ $education->field }}@endif</h3><p class="mb-2">{{ $education->institution }}</p>@if($education->description)<p>{{ $education->description }}</p>@endif</article>@endforeach</div></div>
</div></div></section>

<section class="section-space"><div class="container"><div class="section-heading centered"><span class="eyebrow">Technical ability</span><h2>Skills</h2><p>A balanced toolkit for full-stack PHP development.</p></div><div class="skills-grid">@foreach($skills as $skill)<div class="skill-item"><div class="skill-head"><span>{{ $skill->name }}</span><span>{{ $skill->proficiency }}%</span></div><div class="skill-track"><div class="skill-fill" style="--skill-width: {{ $skill->proficiency }}%"></div></div></div>@endforeach</div></div></section>
@endsection
