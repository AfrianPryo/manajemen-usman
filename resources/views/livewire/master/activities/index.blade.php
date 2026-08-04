<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoring Aktivitas</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Seluruh aktivitas login & sistem.</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari email/identifier..."
               class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
        <select wire:model.live="eventFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            <option value="">Semua Event</option>
            <option value="login.success">Login Berhasil</option>
            <option value="login.failed">Login Gagal</option>
            <option value="logout">Logout</option>
            <option value="password.changed">Password Diubah</option>
        </select>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <ul class="divide-y divide-gray-100 dark:divide-slate-700">
            @forelse($logs as $log)
            <li class="p-4 hover:bg-gray-50 dark:hover:bg-slate-700/30 flex gap-4">
                <span class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $log->event }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $log->user->name ?? $log->identifier ?? 'Sistem' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p>
                    <p class="text-[10px] text-gray-400 font-mono">{{ $log->ip_address }}</p>
                </div>
            </li>
            @empty
            <li class="p-12 text-center text-gray-400">Belum ada aktivitas tercatat.</li>
            @endforelse
        </ul>
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $logs->links() }}
        </div>
    </div>
</div>