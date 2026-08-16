<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">
    <!-- Header Page -->
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">Audit Log Sistem</h1>
            <p class="text-xs text-neutral-400">Riwayat rekam jejak aktivitas dan perubahan data dalam aplikasi.</p>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <!-- Search Bar -->
            <div class="md:col-span-8">
                <label for="search_log" class="sr-only">Cari Log</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-neutral-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        id="search_log"
                        name="search_log"
                        wire:model.live.debounce.300ms="search"
                        type="text" 
                        placeholder="Cari kata kunci, identifier, atau nama user..." 
                        class="w-full pl-10 pr-4 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-400"
                    >
                </div>
            </div>

            <!-- Filter Event -->
            <div class="md:col-span-4">
                <label for="event_filter" class="sr-only">Filter Event</label>
                <select 
                    id="event_filter"
                    name="event_filter"
                    wire:model.live="eventFilter" 
                    class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-emerald-500/10 focus:border-emerald-400 cursor-pointer"
                >
                    <option value="">Semua Event Aktivitas</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}">{{ str_replace('_', ' ', $event) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th scope="col" class="px-4 py-3.5">Waktu</th>
                        <th scope="col" class="px-4 py-3.5">Pengguna</th>
                        <th scope="col" class="px-4 py-3.5">Tipe Event</th>
                        <th scope="col" class="px-4 py-3.5">Identifier</th>
                        <th scope="col" class="px-4 py-3.5">Deskripsi Aktivitas</th>
                        <th scope="col" class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                        <tr wire:key="log-{{ $log->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-xs font-semibold text-neutral-900 dark:text-white">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] font-mono text-neutral-400">{{ $log->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                {{ $log->user->name ?? 'Sistem / Anonim' }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $badgeClasses = match(true) {
                                        str_contains($log->event, 'CREATED') => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800',
                                        str_contains($log->event, 'UPDATED') || str_contains($log->event, 'ADJUSTMENT') => 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border-blue-200/60 dark:border-blue-800',
                                        str_contains($log->event, 'DELETED') => 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border-rose-200/60 dark:border-rose-800',
                                        default => 'bg-neutral-100 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300 border-neutral-200 dark:border-slate-600'
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full border {{ $badgeClasses }}">
                                    {{ str_replace('_', ' ', $log->event) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs text-neutral-600 dark:text-neutral-300">
                                {{ $log->identifier ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-neutral-600 dark:text-neutral-300 max-w-md truncate">
                                {{ $log->description ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <button 
                                    wire:click="openDetail({{ $log->id }})" 
                                    class="px-3.5 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all cursor-pointer"
                                >
                                    Detail Data
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada riwayat audit log yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                @if(method_exists($logs, 'total') && $logs->total() > 0)
                    <div class="hidden sm:block">
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $logs->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $logs->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $logs->total() }}</span> total log
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto flex justify-end">
                {{-- Memanggil custom-pagination blade --}}
                {{ $logs->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    <!-- Modal Detail Audit Log -->
    @if($showDetailModal && $selectedLog)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-3xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                <!-- Modal Header -->
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Rincian Perubahan Data</h3>
                        <p class="text-xs text-neutral-400">ID Log: #{{ $selectedLog->id }} | {{ $selectedLog->created_at->format('d M Y H:i:s') }}</p>
                    </div>
                    <button wire:click="closeDetail" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                <!-- Modal Body (Diff View) -->
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Data Sebelum (Old Values) -->
                        <div class="p-4 bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-md">
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400 mb-2">Data Lama (Sebelum)</h4>
                            <pre class="text-xs font-mono text-neutral-800 dark:text-neutral-200 whitespace-pre-wrap break-all bg-white/60 dark:bg-slate-900/50 p-3 rounded-md border border-rose-100 dark:border-rose-900/30">{{ !empty($selectedLog->old_values) ? json_encode($selectedLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Tidak ada data lama' }}</pre>
                        </div>

                        <!-- Data Sesudah (New Values) -->
                        <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 rounded-md">
                            <h4 class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-2">Data Baru (Sesudah)</h4>
                            <pre class="text-xs font-mono text-neutral-800 dark:text-neutral-200 whitespace-pre-wrap break-all bg-white/60 dark:bg-slate-900/50 p-3 rounded-md border border-emerald-100 dark:border-emerald-900/30">{{ !empty($selectedLog->new_values) ? json_encode($selectedLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Tidak ada data baru' }}</pre>
                        </div>
                    </div>

                    <!-- Metadata Tambahan -->
                    <div class="p-4 bg-neutral-50 dark:bg-slate-900/50 rounded-md border border-neutral-100 dark:border-slate-700 space-y-1.5 text-xs text-neutral-600 dark:text-neutral-400">
                        <div><span class="font-semibold text-neutral-800 dark:text-neutral-200">User Agent:</span> {{ $selectedLog->user_agent }}</div>
                        <div><span class="font-semibold text-neutral-800 dark:text-neutral-200">IP Address:</span> {{ $selectedLog->ip_address }}</div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-neutral-50 dark:bg-slate-900 border-t border-neutral-100 dark:border-slate-700 text-right shrink-0">
                    <button wire:click="closeDetail" class="px-4 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-md hover:bg-neutral-100 dark:hover:bg-slate-700 transition-all cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>