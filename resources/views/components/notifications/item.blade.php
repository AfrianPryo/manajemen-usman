{{-- Satu baris notifikasi di lonceng header (untuk Master maupun Unit). --}}
@props(['notification'])

@php
    $data       = $notification->data;
    $actionable = (bool) ($data['actionable'] ?? false);
    $hasTarget  = \App\Support\NotificationLink::hasTarget($data);
@endphp

<div wire:key="bell-{{ $notification->id }}"
     class="flex items-start gap-2.5 px-4 py-3.5 hover:bg-neutral-50/70 dark:hover:bg-slate-800/40 transition-colors">

    <span class="mt-1.5 h-2 w-2 rounded-full bg-blue-500 shrink-0"></span>

    <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
            <p class="text-[13px] font-semibold text-neutral-900 dark:text-white leading-snug">{{ $data['title'] ?? '-' }}</p>
            @if (! empty($data['badge']))
                <x-notifications.badge :label="$data['badge']" class="mt-0.5" />
            @endif
        </div>

        <p class="text-[12px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed line-clamp-3">{{ $data['message'] ?? '' }}</p>

        <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-1.5">{{ $notification->created_at?->diffForHumans() }}</p>

        <x-notifications.actions :id="$notification->id" :actionable="$actionable" :has-target="$hasTarget" class="mt-2.5" />
    </div>

    {{-- Yang menunggu keputusan tidak bisa sekadar ditandai dibaca. --}}
    @unless ($actionable)
        <button type="button"
                wire:click="markAsRead('{{ $notification->id }}')"
                title="Tandai sudah dibaca"
                class="p-1.5 -mr-1.5 text-neutral-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded-md transition-all cursor-pointer shrink-0">
            <x-heroicon-o-check class="w-4 h-4" stroke-width="2" />
        </button>
    @endunless
</div>
