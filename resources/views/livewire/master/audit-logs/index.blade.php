<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Log Sistem</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Catatan perubahan penting yang tidak dapat diubah.</p>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari identifier/deskripsi..."
               class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Waktu</th>
                    <th class="px-6 py-3">Pengguna</th>
                    <th class="px-6 py-3">Event</th>
                    <th class="px-6 py-3">Identifier</th>
                    <th class="px-6 py-3">Deskripsi</th>
                    <th class="px-6 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                    <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $log->user->name ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                            {{ str_replace('_', ' ', $log->event) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-xs font-mono text-gray-600 dark:text-gray-300">{{ $log->identifier ?? '-' }}</td>
                    <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">{{ $log->description ?? '-' }}</td>
                    <td class="px-6 py-4 text-xs font-mono text-gray-500">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada audit log.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $logs->links() }}
        </div>
    </div>
</div>