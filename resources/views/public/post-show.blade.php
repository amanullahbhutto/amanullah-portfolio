@extends('layouts.public')
@section('title', ($post->meta_title ?: $post->title).' - Amanullah')
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('content')
<section class="section-space" style="padding-top: calc(var(--header-height) + 80px)"><div class="container"><article class="article-wrap">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('posts.index') }}">Blog</a><span>/</span><span>{{ $post->title }}</span></div>
    <header class="article-header"><span class="blog-date">{{ ($post->published_at ?? $post->created_at)->format('F d, Y') }}</span><h1 class="mt-3">{{ $post->title }}</h1><p class="hero-lead">{{ $post->excerpt }}</p></header>
    @if($post->image_url)<div class="article-image"><img src="{{ $post->image_url }}" alt="{{ $post->title }}" width="1000" height="600"></div>@endif
    <div class="article-content">@foreach(preg_split('/\r\n|\r|\n/', $post->content) as $paragraph)@if(trim($paragraph) !== '')<p>{{ $paragraph }}</p>@endif @endforeach</div>
    <div class="cta-panel mt-5"><span class="eyebrow">Have a project?</span><h2 class="h2">Let's discuss your web application.</h2><a class="btn btn-accent mt-3" href="{{ route('contact.create') }}">Get in touch</a></div>
</article></div></section>
@endsection
