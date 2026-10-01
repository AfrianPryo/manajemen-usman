{{--
    Baris aksi satu notifikasi: Approve / Reject (untuk yang butuh keputusan)
    dan "Lihat detail". Dipakai sama persis oleh lonceng di header dan halaman
    penuh. Tujuan detail dihitung di server (open()), bukan dari browser.
--}}
@props(['id', 'actionable' => false, 'hasTarget' => false, 'unread' => true])

@if ($actionable || $hasTarget)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
        @if ($actionable && $unread)
            <button type="button"
                    wire:click="approve('{{ $id }}')"
                    wire:loading.attr="disabled"
                    wire:target="approve,reject,open"
                    class="px-2.5 py-1 text-[10px] font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                Approve
            </button>
            <button type="button"
                    wire:click="reject('{{ $id }}')"
                    wire:loading.attr="disabled"
                    wire:target="approve,reject,open"
                    class="px-2.5 py-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-sm transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                Reject
            </button>
        @elseif ($actionable)
            <span class="text-[10px] font-medium text-neutral-400 dark:text-neutral-500">Sudah diproses</span>
        @endif

        @if ($hasTarget)
            <button type="button"
                    wire:click="$wire.open('{{ $id }}')"
                    wire:loading.attr="disabled"
                    wire:target="approve,reject,open"
                    class="ml-auto text-[11px] font-semibold text-blue-700 dark:text-blue-400 hover:underline transition-colors cursor-pointer disabled:opacity-50">
                Lihat detail &rarr;
            </button>
        @endif
    </div>
@endif