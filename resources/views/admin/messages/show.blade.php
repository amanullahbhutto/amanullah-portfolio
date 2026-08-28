@extends('layouts.admin')
@section('title', 'Message from '.$contactMessage->name)
@section('page_title', 'Message Details')
@section('content')
@php
    $gmailComposeUrl = 'https://mail.google.com/mail/?view=cm&fs=1&to='.rawurlencode($contactMessage->email);
    $gmailReplyUrl = $gmailComposeUrl.'&su='.rawurlencode('Re: '.$contactMessage->subject);
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        <article class="admin-card">
            <div class="admin-card-head">
                <div>
                    <span class="eyebrow">{{ $contactMessage->created_at->format('M d, Y - H:i') }}</span>
                    <h2 class="mt-2">{{ $contactMessage->subject }}</h2>
                </div>
                <span class="status-badge live">Read</span>
            </div>
            <div class="admin-card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="user-avatar">{{ strtoupper(substr($contactMessage->name, 0, 1)) }}</div>
                    <div>
                        <strong class="d-block">{{ $contactMessage->name }}</strong>
                        <a class="text-accent small" href="{{ $gmailComposeUrl }}" target="_blank" rel="noopener">{{ $contactMessage->email }}</a>
                    </div>
                </div>
                <div class="article-content" style="font-size:.95rem">{!! nl2br(e($contactMessage->message)) !!}</div>
            </div>
        </article>
    </div>

    <div class="col-xl-4">
        <aside class="admin-card">
            <div class="admin-card-head"><h2>Sender details</h2></div>
            <div class="admin-card-body d-grid gap-3">
                <div>
                    <span class="eyebrow">Email</span>
                    <a class="d-block mt-1 text-accent" href="{{ $gmailComposeUrl }}" target="_blank" rel="noopener">{{ $contactMessage->email }}</a>
                </div>
                @if($contactMessage->phone)
                    <div>
                        <span class="eyebrow">Phone</span>
                        <a class="d-block mt-1" href="tel:{{ preg_replace('/\D+/', '', $contactMessage->phone) }}">{{ $contactMessage->phone }}</a>
                    </div>
                @endif
                <div>
                    <span class="eyebrow">Received</span>
                    <span class="d-block mt-1 text-muted-custom">{{ $contactMessage->created_at->format('F d, Y - h:i A') }}</span>
                </div>
                <a class="btn btn-accent" href="{{ $gmailReplyUrl }}" target="_blank" rel="noopener">
                    <i class="bi bi-reply me-2"></i>Reply by email
                </a>
                <a class="btn btn-outline-theme" href="{{ route('admin.messages.index') }}"><i class="bi bi-arrow-left me-2"></i>Back to inbox</a>
                @can('delete message')
                    <form method="POST" action="{{ route('admin.messages.destroy', $contactMessage) }}" data-confirm="Delete this message permanently?">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-trash3 me-2"></i>Delete message</button>
                    </form>
                @endcan
            </div>
        </aside>
    </div>
</div>
@endsection
