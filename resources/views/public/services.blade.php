@extends('layouts.public')
@section('title', 'Web Development Services - Amanullah')
@section('meta_description', 'Laravel, PHP, CodeIgniter, MySQL, Bootstrap, responsive front-end, deployment, and maintenance services in Karachi.')
@section('content')
<section class="page-hero"><div class="container"><span class="eyebrow">Web development</span><h1>Services</h1><p>Practical development support for new applications, existing PHP systems, responsive interfaces, and production deployments.</p></div></section>
<section class="section-space"><div class="container"><div class="row g-4">@foreach($services as $service)<div class="col-md-6 col-xl-4" data-aos="fade-up"><article class="service-card"><div class="service-icon"><i class="bi {{ $service->icon }}"></i></div><h3>{{ $service->title }}</h3><p>{{ $service->short_description }}</p><a class="card-arrow" href="{{ route('contact.create', ['service' => $service->slug]) }}">Request this service <i class="bi bi-arrow-up-right ms-2"></i></a></article></div>@endforeach</div></div></section>
<section class="section-space-sm"><div class="container"><div class="cta-panel"><span class="eyebrow">Start a project</span><h2>Need a reliable PHP or Laravel developer?</h2><p class="text-muted-custom mt-3">Share your requirements, preferred timeline, and current technical setup.</p><a class="btn btn-accent mt-2" href="{{ route('contact.create') }}">Let's talk</a></div></div></section>
@endsection
