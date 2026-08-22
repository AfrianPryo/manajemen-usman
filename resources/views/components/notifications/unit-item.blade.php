@props(['item'])

<a href="{{ $item['url'] ?? '#' }}" class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors group">
    <div class="flex items-center justify-between gap-2">
        <p class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-slate-900 dark:group-hover:text-white transition-colors truncate">
            {{ $item['title'] }}
        </p>
        @if(isset($item['badge']))
            <span class="text-[9px] px-1.5 py-0.5 rounded font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                {{ $item['badge'] }}
            </span>
        @endif
    </div>
    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
        {{ $item['message'] }}
    </p>
    <span class="text-[10px] text-slate-400 dark:text-slate-500 mt-2 block">
        {{ $item['created_at'] ?? 'Baru saja' }}
    </span>
</a>