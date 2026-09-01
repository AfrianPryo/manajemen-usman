@props([
    'notifications' => [],
    'viewAllUrl' => '#',
    'badgeText' => 'Baru',
    'role' => 'master'
])

<div x-data="{ open: false }"
     @keydown.window.escape="open = false"
     wire:poll.visible.30s="$refresh"
     class="relative">
    {{-- Trigger Bell Button --}}
    <button @click="open = true" 
            class="relative p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors focus:outline-none cursor-pointer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if(count($notifications) > 0)
            <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
        @endif
    </button>

    {{-- Backdrop --}}
    <div x-show="open" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50"
         style="display: none;">
    </div>

    {{-- Slide-over Panel --}}
    <div x-show="open"
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 w-full sm:w-80 md:w-96 bg-white dark:bg-slate-900 shadow-2xl border-l border-slate-200 dark:border-slate-800 z-50 flex flex-col"
         style="display: none;">

        {{-- Sidebar Header --}}
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
            <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-slate-900 dark:text-slate-100">
                    Notifikasi {{ $role === 'master' ? 'System' : 'Unit' }}
                </span>
                <span class="text-[10px] bg-rose-50 text-rose-600 font-semibold px-2 py-0.5 rounded-md border border-rose-100 dark:bg-rose-950/50 dark:border-rose-900 dark:text-rose-400">
                    {{ $badgeText }}
                </span>
            </div>

            <div class="flex items-center gap-1">
                {{-- Tombol Refresh Manual --}}
                <button type="button"
                        wire:click="$refresh"
                        wire:loading.attr="disabled"
                        wire:target="$refresh"
                        title="Muat ulang notifikasi"
                        class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer disabled:opacity-50">
                    <svg wire:loading.class="animate-spin" wire:target="$refresh"
                         class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>

                {{-- Tombol Tutup --}}
                <button @click="open = false" 
                        class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Sidebar Body --}}
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 text-xs">
            @forelse($notifications as $notification)
                @php
                    $itemData = array_merge($notification->data, [
                        'created_at' => $notification->created_at->diffForHumans()
                    ]);
                @endphp

                @if($role === 'master')
                    <x-notifications.master-item :item="$itemData" :id="$notification->id" />
                @else
                    <x-notifications.unit-item :item="$itemData" :id="$notification->id" />
                @endif
            @empty
                <div class="p-8 text-center text-slate-400 dark:text-slate-500">
                    <p class="text-xs">Tidak ada notifikasi baru.</p>
                </div>
            @endforelse
        </div>

        {{-- Sidebar Footer --}}
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 text-center bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
            <a href="{{ $viewAllUrl }}" class="text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">
                Lihat Semua Notifikasi &rarr;
            </a>
        </div>
    </div>

    {{-- Popup kredensial baru (username/password) -- muncul saat Admin
         Master menekan "Approve" pada notifikasi permintaan reset password
         (lihat App\Livewire\NotificationSidebar::approvePasswordResetRequest()).
         Ditaruh di LUAR panel slide-over (bukan di dalam) supaya z-index-nya
         (z-[60], lihat komponen) tetap di atas backdrop & panel (z-50) apa
         pun kondisi 'open' Alpine saat itu. --}}
    <x-credentials-modal :credentials="$createdCredentials" />
</div>