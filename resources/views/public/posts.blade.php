@extends('layouts.public')
@section('title', 'Blog Posts - Amanullah ')
@section('meta_description', 'Read practical articles about Laravel, PHP, Bootstrap, responsive web development, and safer deployment.')
@section('content')
<section class="page-hero"><div class="container"><span class="eyebrow">Development notes</span><h1>Blog Posts</h1><p>Practical thoughts about Laravel development, responsive interfaces, deployment, and maintainable web applications.</p></div></section>
<section class="section-space"><div class="container"><div class="row g-4">
    @forelse($posts as $post)
        <div class="col-md-6 col-xl-4" data-aos="fade-up"><article class="blog-card"><div class="blog-body"><span class="blog-date">{{ ($post->published_at ?? $post->created_at)->format('M d, Y') }}</span><h3 class="mt-3"><a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></h3><p>{{ $post->excerpt }}</p><a class="card-arrow" href="{{ route('posts.show', $post) }}">Read article <i class="bi bi-arrow-right ms-2"></i></a></div></article></div>
    @empty<div class="col-12"><div class="info-card p-5 text-center"><h2>Posts are being prepared</h2><p class="text-muted-custom mb-0">Please check again soon.</p></div></div>@endforelse
</div><div class="mt-5">{{ $posts->links() }}</div></div></section>
@endsection
