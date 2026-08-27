@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center gap-1">
        {{-- Tombol Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-2.5 py-1.5 text-xs font-medium text-neutral-300 dark:text-slate-600 bg-neutral-50 dark:bg-slate-800/40 rounded-md border border-neutral-100 dark:border-slate-700/50 cursor-not-allowed select-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <button wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="px-2.5 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-800 hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-md border border-neutral-200 dark:border-slate-700 transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
        @endif

        {{-- Angka-angka Halaman --}}
        @foreach ($elements as $element)
            {{-- Titik-titik (...) Pembatas --}}
            @if (is_string($element))
                <span class="px-2 py-1 text-xs font-medium text-neutral-400 dark:text-slate-500 select-none">{{ $element }}</span>
            @endif

            {{-- Link Halaman --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1.5 text-xs font-semibold text-white bg-blue-900 rounded-md shadow-xs border border-blue-900 select-none">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="px-3 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-800 hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-md border border-neutral-200 dark:border-slate-700 transition-all cursor-pointer">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Tombol Next --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="px-2.5 py-1.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-800 hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-md border border-neutral-200 dark:border-slate-700 transition-all cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        @else
            <span class="px-2.5 py-1.5 text-xs font-medium text-neutral-300 dark:text-slate-600 bg-neutral-50 dark:bg-slate-800/40 rounded-md border border-neutral-100 dark:border-slate-700/50 cursor-not-allowed select-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </nav>
@endif