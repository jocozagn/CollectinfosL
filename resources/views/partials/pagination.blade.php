@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="page-link page-link--disabled"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-link" rel="prev"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-ellipsis">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link page-link--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-link" rel="next"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></a>
        @else
            <span class="page-link page-link--disabled"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></span>
        @endif
    </nav>
@endif
