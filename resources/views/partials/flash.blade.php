@php
    $flashMessages = collect();
    $successTitle = session('flash_title', 'Success');
    $successDuration = (int) session('flash_duration', 3000);
    $successVariant = session('flash_variant');

    foreach (['success' => 'success', 'error' => 'danger', 'status' => 'success'] as $key => $type) {
        if (session()->has($key)) {
            $flashMessages->push([
                'type' => $type,
                'title' => $type === 'success' ? $successTitle : 'Error',
                'message' => session($key),
                'duration' => $type === 'success' ? $successDuration : 5000,
                'variant' => $type === 'success' ? $successVariant : null,
            ]);
        }
    }

    foreach ($errors->all() as $error) {
        $flashMessages->push([
            'type' => 'danger',
            'title' => 'Please fix this',
            'message' => $error,
            'duration' => 5000,
            'variant' => null,
        ]);
    }

    $viewportClass = $flashMessages->contains(fn ($flash): bool => $flash['variant'] === 'contact-success-popup') ? ' contact-toast-viewport' : '';
@endphp

@if($flashMessages->isNotEmpty())
    <div class="flash-toast-viewport{{ $viewportClass }}" aria-live="polite" aria-atomic="true">
        @foreach($flashMessages as $flash)
            <div class="flash-toast {{ $flash['type'] === 'success' ? 'success' : 'danger' }} {{ $flash['variant'] }}" role="{{ $flash['type'] === 'success' ? 'status' : 'alert' }}" data-flash-toast data-flash-duration="{{ $flash['duration'] }}">
                <div class="flash-toast-icon">
                    <i class="bi {{ $flash['type'] === 'success' ? 'bi-check2-circle' : 'bi-exclamation-triangle' }}" aria-hidden="true"></i>
                </div>
                <div class="flash-toast-body">
                    <strong>{{ $flash['title'] }}</strong>
                    <span>{{ $flash['message'] }}</span>
                </div>
                <button class="flash-toast-close" type="button" data-flash-close aria-label="Close notification">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <span class="flash-toast-progress" aria-hidden="true"></span>
            </div>
        @endforeach
    </div>
@endif
