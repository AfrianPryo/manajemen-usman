<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- Action & Title Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-2">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Monitoring Aktivitas</h1>
            <p class="text-xs text-neutral-400">Seluruh riwayat aktivitas login, logout, dan audit keamanan sistem.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            {{-- Tombol Export Log (Optional) --}}
            <button wire:click="exportLog" class="px-3.5 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>Export Log</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari email, nama pengguna, IP address, atau identifier..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
            </div>

            <div class="md:col-span-2">
                <select wire:model.live="eventFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Jenis Event</option>
                    <option value="login.success">Login Berhasil</option>
                    <option value="login.failed">Login Gagal</option>
                    <option value="logout">Logout</option>
                    <option value="password.changed">Password Diubah</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Logs Table Container --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Waktu Aktivitas</th>
                        <th class="px-4 py-3.5">Pengguna / Identifier</th>
                        <th class="px-4 py-3.5 text-center">Jenis Event</th>
                        <th class="px-4 py-3.5 text-right">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                        <tr wire:key="log-{{ $log->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            {{-- Tanggal & Relative Time --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">
                                    {{ optional($log->created_at)->format('d M Y, H:i:s') ?? '-' }}
                                </div>
                                <div class="text-[11px] font-mono text-neutral-400">
                                    {{ optional($log->created_at)->diffForHumans() }}
                                </div>
                            </td>

                            {{-- User Info --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="text-xs font-semibold text-neutral-900 dark:text-white">
                                    {{ $log->user->name ?? $log->identifier ?? 'Sistem / Guest' }}
                                </div>
                                @if(isset($log->user->email))
                                    <div class="text-[11px] text-neutral-400">
                                        {{ $log->user->email }}
                                    </div>
                                @endif
                            </td>

                            {{-- Event Badge Status --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($log->event === 'login.success')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">
                                        Login Berhasil
                                    </span>
                                @elseif($log->event === 'login.failed')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800">
                                        Login Gagal
                                    </span>
                                @elseif($log->event === 'logout')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-neutral-100 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300 border border-neutral-200 dark:border-slate-600">
                                        Logout
                                    </span>
                                @elseif($log->event === 'password.changed')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">
                                        Password Diubah
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">
                                        {{ $log->event }}
                                    </span>
                                @endif
                            </td>

                            {{-- IP Address --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-mono text-neutral-600 dark:text-neutral-300">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada riwayat aktivitas tercatat yang sesuai dengan filter.
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
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $logs->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $logs->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $logs->total() }}</span> total aktivitas
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto flex justify-end">
                {{-- Memanggil custom-pagination blade --}}
                {{ $logs->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

</div>