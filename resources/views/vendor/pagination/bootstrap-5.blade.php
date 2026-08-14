@if ($paginator->hasPages())
    <style>
        .custom-enterprise-pagination .page-item .page-link {
            border-radius: 50rem !important;
            padding: 0.35rem 0.8rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            text-decoration: none;
        }
        .custom-enterprise-pagination .page-item .page-link:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        .custom-enterprise-pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%) !important;
            border-color: #1e1b4b !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(30, 27, 75, 0.35) !important;
        }
        .custom-enterprise-pagination .page-item.disabled .page-link {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
        .custom-enterprise-pagination .page-link:focus {
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(30, 27, 75, 0.15);
        }
    </style>

    <nav class="d-flex justify-content-between align-items-center flex-wrap gap-3 py-1 custom-enterprise-pagination w-100" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        {{-- Mobile View (< sm screens) --}}
        <div class="d-flex justify-content-between align-items-center flex-fill d-sm-none gap-2">
            <ul class="pagination pagination-sm mb-0 gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link"><i class="bi bi-chevron-left me-1"></i>Prev</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left me-1"></i>Prev</a>
                    </li>
                @endif
            </ul>

            <span class="text-xs text-muted font-monospace">
                <strong class="text-dark">{{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ $paginator->total() }}</strong>
            </span>

            <ul class="pagination pagination-sm mb-0 gap-1">
                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next<i class="bi bi-chevron-right ms-1"></i></a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">Next<i class="bi bi-chevron-right ms-1"></i></span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Desktop View (>= sm screens) --}}
        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between w-100">
            <div>
                <p class="small text-muted mb-0 font-sans text-xs">
                    {!! __('Showing') !!}
                    <span class="fw-bold text-dark font-monospace">{{ $paginator->firstItem() ?? 0 }}</span>
                    {!! __('to') !!}
                    <span class="fw-bold text-dark font-monospace">{{ $paginator->lastItem() ?? 0 }}</span>
                    {!! __('of') !!}
                    <span class="fw-bold text-dark font-monospace">{{ $paginator->total() }}</span>
                    {!! __('entries') !!}
                </p>
            </div>

            <div>
                <ul class="pagination pagination-sm mb-0 gap-1 align-items-center">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-left text-xxs"></i></span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="bi bi-chevron-left text-xxs"></i></a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><i class="bi bi-chevron-right text-xxs"></i></a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-right text-xxs"></i></span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
