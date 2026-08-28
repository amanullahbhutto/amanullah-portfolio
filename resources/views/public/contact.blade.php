@extends('layouts.public')
@section('title', 'Contact Amanullah ')
@section('meta_description', 'Contact Amanullah for Laravel, PHP, CodeIgniter, MySQL, Bootstrap, responsive development, or website maintenance.')
@section('content')
<section class="section-space" style="padding-top: calc(var(--header-height) + 70px)"><div class="container">
    <div class="contact-panel"><div class="row g-0">
        <div class="col-lg-5"><div class="contact-copy"><span class="eyebrow">Get in touch</span><h1>Let's work together</h1><p>Share your project goals, existing technology, and preferred timeline. I will review the details and get back to you.</p><div class="contact-list">
            <div class="contact-row"><i class="bi bi-envelope"></i>@if($profile?->email)<a class="gmail-link" href="{{ $profile->gmail_compose_url }}" target="_blank" rel="noopener">{{ $profile->email }}</a>@endif</div>
            <div class="contact-row"><i class="bi bi-whatsapp"></i><div>@if($profile?->phone)<a class="whatsapp-link" href="{{ $profile->whatsapp_url }}" target="_blank" rel="noopener">{{ $profile->phone }}</a>@endif</div></div>
            <div class="contact-row"><i class="bi bi-geo-alt"></i><span>{{ $profile?->address }}, {{ $profile?->city }}</span></div>
        </div></div></div>
        <div class="col-lg-7"><div class="contact-form-wrap"><form method="POST" action="{{ route('contact.store') }}">@csrf
            <div class="honeypot" aria-hidden="true"><label for="website">Website</label><input type="text" name="website" id="website" tabindex="-1" autocomplete="off"></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="name">Name *</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" placeholder="Your name"></div>
                <div class="col-md-6"><label class="form-label" for="email">Email *</label><input class="form-control" type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" placeholder="you@company.com"></div>
                <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" placeholder="Your phone number"></div>
                <div class="col-md-6"><label class="form-label" for="subject">Subject *</label><input class="form-control" id="subject" name="subject" value="{{ old('subject', request('service') ? 'Project inquiry: '.str_replace('-', ' ', request('service')) : '') }}" required maxlength="150" placeholder="Project inquiry"></div>
                <div class="col-12"><label class="form-label" for="message">Message *</label><textarea class="form-control" id="message" name="message" required minlength="10" maxlength="5000" placeholder="Tell me about your project, timeline, and goals...">{{ old('message') }}</textarea></div>
                <div class="col-12"><button class="btn btn-accent w-100" type="submit">Send message <i class="bi bi-arrow-right ms-2"></i></button></div>
            </div>
        </form></div></div>
    </div></div>
</div></section>
@endsection
