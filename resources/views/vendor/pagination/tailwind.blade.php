@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm leading-5 text-slate-500">
            {{ __('Showing') }}
            @if ($paginator->firstItem())
                <span class="font-semibold text-slate-900">{{ $paginator->firstItem() }}</span>
                <span class="text-slate-400">{{ __('to') }}</span>
                <span class="font-semibold text-slate-900">{{ $paginator->lastItem() }}</span>
            @else
                <span class="font-semibold text-slate-900">{{ $paginator->count() }}</span>
            @endif
            {{ __('of') }}
            <span class="font-semibold text-slate-900">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>

        <div class="flex items-center justify-between gap-2 sm:justify-end">
            @if ($paginator->onFirstPage())
                <span
                    aria-disabled="true"
                    aria-label="{{ __('pagination.previous') }}"
                    class="inline-flex h-9 cursor-not-allowed items-center gap-1.5 rounded-lg border border-blue-100 bg-white px-2.5 text-sm font-semibold text-slate-300 shadow-sm shadow-blue-900/5"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('pagination.previous') }}</span>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    aria-label="{{ __('pagination.previous') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-blue-100 bg-white px-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-blue-900/5 transition hover:border-blue-200 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('pagination.previous') }}</span>
                </a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-semibold text-slate-400">
                            {{ $element }}
                        </span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span
                                    aria-current="page"
                                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-brand-600 bg-brand-600 px-2 text-sm font-semibold text-white shadow-sm shadow-brand-600/20"
                                >
                                    {{ $page }}
                                </span>
                            @else
                                <a
                                    href="{{ $url }}"
                                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-transparent px-2 text-sm font-semibold text-slate-500 transition hover:border-blue-100 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500"
                                >
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            <span class="inline-flex h-9 items-center rounded-lg bg-brand-50 px-3 text-xs font-semibold text-brand-700 sm:hidden">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    aria-label="{{ __('pagination.next') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-blue-100 bg-white px-2.5 text-sm font-semibold text-slate-600 shadow-sm shadow-blue-900/5 transition hover:border-blue-200 hover:bg-brand-50 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <span class="hidden sm:inline">{{ __('pagination.next') }}</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @else
                <span
                    aria-disabled="true"
                    aria-label="{{ __('pagination.next') }}"
                    class="inline-flex h-9 cursor-not-allowed items-center gap-1.5 rounded-lg border border-blue-100 bg-white px-2.5 text-sm font-semibold text-slate-300 shadow-sm shadow-blue-900/5"
                >
                    <span class="hidden sm:inline">{{ __('pagination.next') }}</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
