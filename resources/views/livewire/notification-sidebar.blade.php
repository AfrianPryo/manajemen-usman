<div>
    {{-- Header Sidebar --}}
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-lg">Notifikasi</h3>
            @if($unreadCount = auth()->user()->unreadNotifications->count())
                <span class="px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">
                    {{ $unreadCount }}
                </span>
            @endif
        </div>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <button 
                wire:click="markAllAsRead" 
                class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition"
            >
                Tandai Semua Dibaca
            </button>
        @endif
    </div>

    {{-- Daftar Notifikasi --}}
    <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-[calc(100vh-120px)] overflow-y-auto">
        @forelse(auth()->user()->notifications()->take(20)->get() as $notification)
            <div 
                wire:key="notification-{{ $notification->id }}"
                class="p-4 flex items-start gap-3 transition hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ is_null($notification->read_at) ? 'bg-indigo-50/40 dark:bg-indigo-950/20' : '' }}"
            >
                {{-- Icon --}}
                <div class="shrink-0 mt-1">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-full {{ is_null($notification->read_at) ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </span>
                </div>

                {{-- Konten --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                            {{ $notification->data['title'] ?? 'Notifikasi' }}
                        </p>
                        <span class="text-xs text-gray-400 whitespace-nowrap">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                        {{ $notification->data['message'] ?? $notification->data['body'] ?? '' }}
                    </p>

                    @if(is_null($notification->read_at))
                        <button 
                            wire:click="markAsRead('{{ $notification->id }}')" 
                            class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                        >
                            Tandai dibaca
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada notifikasi.</p>
            </div>
        @endforelse
    </div>
</div>