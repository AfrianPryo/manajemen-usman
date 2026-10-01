@props([
    'notifications' => [],
    'unreadCount' => 0,
    'viewAllUrl' => '#',
    'role' => 'master',
])

<div x-data="{ open: false }"
     @keydown.window.escape="open = false"
     wire:poll.visible.30s="$refresh"
     class="relative">

    {{-- Toast untuk hasil aksi (approve/reject/detail gagal dibuka, dst) --}}
    <x-notifications.flash />

    {{-- Trigger Bell Button --}}
    <button type="button"
            @click="open = true"
            aria-label="Notifikasi"
            :aria-expanded="open"
            class="relative p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800 rounded-sm transition-colors focus:outline-none cursor-pointer">
        <x-heroicon-o-bell class="w-4 h-4" />
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[15px] h-[15px] px-1 flex items-center justify-center text-[9px] font-bold leading-none text-white bg-rose-500 rounded-full ring-2 ring-white dark:ring-slate-900">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Backdrop (sama dengan backdrop sidebar mobile) --}}
    <div x-show="open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 z-50 bg-neutral-900/50 dark:bg-black/60 backdrop-blur-sm"
         aria-hidden="true"
         style="display: none;"></div>

    {{-- Slide-over Panel --}}
    <aside x-show="open"
           x-transition:enter="transform transition ease-out duration-300"
           x-transition:enter-start="translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="fixed inset-y-0 right-0 z-50 w-full sm:w-96 flex flex-col bg-white dark:bg-slate-900 border-l border-slate-200/70 dark:border-slate-800 shadow-xl dark:shadow-black/40"
           style="display: none;">

        {{-- Header (tinggi sama dengan header halaman) --}}
        <div class="h-12 px-4 flex items-center justify-between gap-2 border-b border-slate-200/70 dark:border-slate-800 shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">Notifikasi</h2>
                @if ($unreadCount > 0)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-sm border bg-blue-50 text-blue-900 border-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-900">
                        {{ $unreadCount }} baru
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-0.5 shrink-0">
                @if ($unreadCount > 0)
                    <button type="button"
                            wire:click="markAllAsRead"
                            wire:loading.attr="disabled"
                            wire:target="markAllAsRead"
                            title="Tandai semua dibaca"
                            class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-sm transition-colors cursor-pointer disabled:opacity-50">
                        <x-heroicon-o-check class="w-4 h-4" />
                    </button>
                @endif

                <button type="button"
                        wire:click="$refresh"
                        wire:loading.attr="disabled"
                        wire:target="$refresh"
                        title="Muat ulang notifikasi"
                        class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-sm transition-colors cursor-pointer disabled:opacity-50">
                    <x-heroicon-o-arrow-path wire:loading.class="animate-spin" wire:target="$refresh" class="w-4 h-4" />
                </button>

                <button type="button"
                        @click="open = false"
                        title="Tutup"
                        class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-sm transition-colors cursor-pointer">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="no-scrollbar flex-1 overflow-y-auto divide-y divide-neutral-100 dark:divide-slate-800">
            @forelse ($notifications as $notification)
                <x-notifications.item :notification="$notification" />
            @empty
                <div class="px-6 py-16 flex flex-col items-center text-center">
                    <span class="w-10 h-10 rounded-sm bg-neutral-50 dark:bg-slate-800 border border-neutral-100 dark:border-slate-700 flex items-center justify-center text-neutral-400">
                        <x-heroicon-o-bell class="w-5 h-5" />
                    </span>
                    <p class="mt-3 text-xs font-semibold text-neutral-700 dark:text-neutral-200">Tidak ada notifikasi baru</p>
                    <p class="mt-1 text-[11px] text-neutral-400">Semua notifikasi sudah Anda baca.</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if ($viewAllUrl && $viewAllUrl !== '#')
            <div class="p-3 border-t border-slate-200/70 dark:border-slate-800 shrink-0 space-y-2">
                @if ($unreadCount > $notifications->count())
                    <p class="text-[11px] text-center text-neutral-400">
                        Menampilkan {{ $notifications->count() }} dari {{ $unreadCount }} notifikasi belum dibaca
                    </p>
                @endif
                <a href="{{ $viewAllUrl }}"
                   @click="open = false"
                   class="block w-full px-4 py-2 text-xs font-bold text-center text-neutral-700 dark:text-neutral-200 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 hover:bg-neutral-50 dark:hover:bg-slate-700 rounded-sm transition-all shadow-sm">
                    Lihat Semua Notifikasi
                </a>
            </div>
        @endif
    </aside>

    {{-- Popup kredensial baru -- muncul saat Admin Master menekan "Approve"
         pada permintaan reset password (lihat trait HandlesNotificationActions).
         Ditaruh di LUAR panel supaya z-index-nya (z-[60], lihat komponen)
         tetap di atas backdrop & panel apa pun kondisi 'open'. --}}
    <x-credentials-modal :credentials="$createdCredentials" />
</div>