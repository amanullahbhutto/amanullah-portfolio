@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $startPage = max(1, $currentPage - 1);
    $endPage = min($lastPage, $currentPage + 1);
@endphp

@if($paginator->total() > 0)
    <nav class="admin-pagination-bar" aria-label="List pagination">
        <div class="pagination-meta">
            <span>Showing <strong>{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong></span>
            <span>Page <strong>{{ $currentPage }}</strong> of <strong>{{ $lastPage }}</strong></span>
        </div>

        @if($paginator->hasPages())
            <ul class="pagination admin-page-links mb-0">
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() ?: '#' }}" aria-label="Previous page" @if($paginator->onFirstPage()) tabindex="-1" aria-disabled="true" @endif>
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        <span>Prev</span>
                    </a>
                </li>

                @if($startPage > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if($startPage > 2)
                        <li class="page-item disabled"><span class="page-link page-gap">...</span></li>
                    @endif
                @endif

                @for($page = $startPage; $page <= $endPage; $page++)
                    <li class="page-item {{ $page === $currentPage ? 'active' : '' }}">
                        <a class="page-link" href="{{ $paginator->url($page) }}" @if($page === $currentPage) aria-current="page" @endif>{{ $page }}</a>
                    </li>
                @endfor

                @if($endPage < $lastPage)
                    @if($endPage < $lastPage - 1)
                        <li class="page-item disabled"><span class="page-link page-gap">...</span></li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a>
                    </li>
                @endif

                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() ?: '#' }}" aria-label="Next page" @if(! $paginator->hasMorePages()) tabindex="-1" aria-disabled="true" @endif>
                        <span>Next</span>
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        @endif
    </nav>
@endif
