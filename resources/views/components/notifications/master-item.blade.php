@props(['item', 'id' => null])

<div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
    <div class="flex items-center justify-between gap-2">
        <p class="font-bold text-slate-800 dark:text-slate-200 truncate">
            {{ $item['title'] ?? '' }}
        </p>
        @if(isset($item['badge']))
            <span class="text-[9px] px-1.5 py-0.5 rounded font-medium bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:border-amber-900 dark:text-amber-400 border border-amber-200 shrink-0">
                {{ $item['badge'] }}
            </span>
        @endif
    </div>

    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
        {{ $item['message'] ?? '' }}
    </p>

    {{-- Tombol Aksi Interaktif via Livewire --}}
    @if(isset($item['actionable']) && $item['actionable'])
        <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/60 flex items-center gap-2">
            
            <button type="button" 
                    wire:click="approve('{{ $id }}')"
                    wire:loading.attr="disabled"
                    class="px-2.5 py-1 text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded transition-colors cursor-pointer shadow-2xs disabled:opacity-50">
                Approve
            </button>

            <button type="button" 
                    wire:click="reject('{{ $id }}')"
                    wire:loading.attr="disabled"
                    class="px-2.5 py-1 text-[10px] font-semibold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 rounded dark:bg-rose-950/40 dark:border-rose-900 dark:text-rose-400 transition-colors cursor-pointer disabled:opacity-50">
                Reject
            </button>

            @if(isset($item['url']) && $item['url'] !== '#')
                <button type="button" 
                        wire:click="markAsRead('{{ $id }}', '{{ $item['url'] }}')"
                        class="ml-auto text-[10px] font-medium text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 transition-colors cursor-pointer">
                    Detail &rarr;
                </button>
            @endif
        </div>
    @else
        <div class="mt-2 flex items-center justify-between">
            <span class="text-[10px] text-slate-400 dark:text-slate-500">
                {{ $item['created_at'] ?? 'Baru saja' }}
            </span>
            @if(isset($item['url']) && $item['url'] !== '#')
                <button type="button" 
                        wire:click="markAsRead('{{ $id }}', '{{ $item['url'] }}')"
                        class="text-[10px] font-medium text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 transition-colors cursor-pointer">
                    Lihat &rarr;
                </button>
            @endif
        </div>
    @endif
</div>