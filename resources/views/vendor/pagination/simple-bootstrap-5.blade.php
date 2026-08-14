@if ($paginator->hasPages())
    <style>
        .custom-enterprise-pagination .page-item .page-link {
            border-radius: 50rem !important;
            padding: 0.35rem 0.9rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #334155;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 32px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            text-decoration: none;
        }
        .custom-enterprise-pagination .page-item .page-link:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
            color: #0f172a;
            transform: translateY(-1px);
        }
        .custom-enterprise-pagination .page-item.disabled .page-link {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }
    </style>

    <nav class="d-flex justify-content-between align-items-center py-1 custom-enterprise-pagination w-100" role="navigation" aria-label="{!! __('Pagination Navigation') !!}">
        <ul class="pagination pagination-sm mb-0 gap-2 w-100 justify-content-between">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link"><i class="bi bi-chevron-left me-1"></i>{!! __('Previous') !!}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left me-1"></i>{!! __('Previous') !!}</a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{!! __('Next') !!}<i class="bi bi-chevron-right ms-1"></i></a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true">
                    <span class="page-link">{!! __('Next') !!}<i class="bi bi-chevron-right ms-1"></i></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
