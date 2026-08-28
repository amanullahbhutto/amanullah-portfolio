@php
    $searchId = $searchId ?? 'admin-search';
    $placeholder = $placeholder ?? 'Search...';
    $target = $target ?? '#admin-list-results';
    $hiddenInputs = $hiddenInputs ?? [];
@endphp

<form class="admin-search" method="GET" action="{{ $action }}" data-live-search data-live-search-target="{{ $target }}">
    @foreach($hiddenInputs as $name => $value)
        @if(filled($value))
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <label class="visually-hidden" for="{{ $searchId }}">{{ $placeholder }}</label>
    <div class="search-field">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input
            class="form-control"
            id="{{ $searchId }}"
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
        >
        <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear aria-label="Clear search">
            <i class="bi bi-x-lg" aria-hidden="true"></i>
        </button>
    </div>
</form>
