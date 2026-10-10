@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 w-100 py-2" aria-label="Page navigation">
        {{-- Results Info (Left Side) --}}
        <div class="text-muted small">
            @if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
                Showing <span class="fw-semibold text-dark">{{ $paginator->firstItem() ?? 0 }}</span> to <span class="fw-semibold text-dark">{{ $paginator->lastItem() ?? 0 }}</span> of <span class="fw-semibold text-dark">{{ $paginator->total() }}</span> results
            @endif
        </div>

        {{-- Pagination Buttons (Right Side) --}}
        <ul class="pagination pagination-sm mb-0">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link shadow-none" aria-hidden="true">
                        <i class="fas fa-chevron-left me-1 small"></i> Previous
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-chevron-left me-1 small"></i> Previous
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @if ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator)
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link fw-bold">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        Next <i class="fas fa-chevron-right ms-1 small"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link shadow-none" aria-hidden="true">
                        Next <i class="fas fa-chevron-right ms-1 small"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@elseif ($paginator instanceof \Illuminate\Pagination\LengthAwarePaginator && $paginator->total() > 0)
    <div class="d-flex justify-content-between align-items-center w-100 py-2 text-muted small">
        <div>
            Showing <span class="fw-semibold text-dark">{{ $paginator->firstItem() ?? 0 }}</span> to <span class="fw-semibold text-dark">{{ $paginator->lastItem() ?? 0 }}</span> of <span class="fw-semibold text-dark">{{ $paginator->total() }}</span> results
        </div>
    </div>
@endif
