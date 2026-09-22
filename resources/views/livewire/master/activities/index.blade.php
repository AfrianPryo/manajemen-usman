<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-sm bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm shadow-black/[0.02]">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- Action & Title Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            {{--
                Judul & subjudul dibedakan lewat isset($blockedAccesses):
                variabel ini HANYA dikirim oleh App\Livewire\Master\Activities\Index
                (menu "Keamanan" milik Master Admin). Unit\Activities\Index
                (log aktivitas milik unit-admin sendiri) me-render blade yang
                sama tapi TIDAK mengirim variabel ini, sehingga judulnya tetap
                "Monitoring Aktivitas" seperti semula -- lihat docblock kedua
                class tersebut untuk penjelasan lengkap.
            --}}
            <div class="flex items-center gap-2.5">
                <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white tracking-tight">
                    {{ isset($blockedAccesses) ? 'Monitoring Keamanan' : 'Monitoring Aktivitas' }}
                </h1>
            </div>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">
                {{ isset($blockedAccesses)
                    ? 'Riwayat aktivitas login & logout, serta pengelolaan blokir IP/perangkat.'
                    : 'Seluruh riwayat aktivitas login, logout, dan audit keamanan sistem.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            {{-- Tombol Blokir Akses -- HANYA Master Admin (lihat catatan di atas) --}}
            @isset($blockedAccesses)
                <button wire:click="openBlockModal" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-sm hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all shadow-sm shadow-black/[0.02] cursor-pointer">
                    <x-heroicon-o-no-symbol class="w-4 h-4" />
                    <span>Blokir IP/Perangkat</span>
                </button>
            @endisset

            {{-- Pintasan ke arsip log bulanan (data lama yang sudah dirotasi) --}}
            @if (Route::has('master.log-archives.index'))
                <a href="{{ route('master.log-archives.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-50 dark:hover:bg-slate-700 transition-all shadow-sm shadow-black/[0.02] cursor-pointer">
                    <x-heroicon-o-archive-box class="w-4 h-4" />
                    <span>Arsip Log</span>
                </a>
            @endif

            {{-- Tombol Export Log (Optional) --}}
            <button wire:click="exportLog" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-[#0d3b74] dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all shadow-sm shadow-black/[0.02] cursor-pointer">
                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                <span>Export Log</span>
            </button>
        </div>
    </div>

    {{-- ================= DAFTAR BLOKIR AKTIF (KHUSUS MASTER ADMIN) ================= --}}
    @isset($blockedAccesses)
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
            <div class="px-4 py-3.5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Daftar Blokir Aktif</h2>
                    <p class="text-[11px] text-neutral-400">IP atau perangkat pada daftar ini tidak bisa login maupun melanjutkan sesi yang sedang berjalan.</p>
                </div>
                <span class="px-2.5 py-1 text-[11px] font-bold rounded-sm bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800">
                    {{ $blockedAccesses->count() }} diblokir
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Nilai (IP / User-Agent)</th>
                            <th class="px-4 py-3">Alasan</th>
                            <th class="px-4 py-3">Diblokir Oleh</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                        @forelse($blockedAccesses as $blocked)
                            <tr wire:key="blocked-{{ $blocked->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($blocked->type === 'ip')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800">
                                            Alamat IP
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-400 border border-violet-200/60 dark:border-violet-800">
                                            Perangkat
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs font-mono text-neutral-700 dark:text-neutral-200 max-w-xs truncate" title="{{ $blocked->value }}">
                                    {{ $blocked->value }}
                                </td>
                                <td class="px-4 py-3 text-xs text-neutral-500 dark:text-neutral-400 max-w-xs truncate" title="{{ $blocked->reason }}">
                                    {{ $blocked->reason ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
                                    {{ $blocked->blockedBy->name ?? 'Sistem' }}
                                    <div class="text-[10px] text-neutral-400">{{ optional($blocked->created_at)->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        x-on:click.prevent="$store.confirmDialog.open({
                                            title: 'Buka Blokir?',
                                            message: 'Akses dari &quot;{{ $blocked->value }}&quot; akan diizinkan kembali untuk login.',
                                            confirmText: 'Ya, Buka Blokir',
                                            variant: 'danger',
                                            onConfirm: () => $wire.unblockAccess({{ $blocked->id }})
                                        })"
                                        class="px-3 py-1.5 text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-sm hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                                        Buka Blokir
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-xs text-neutral-400">
                                    Belum ada IP atau perangkat yang diblokir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari email, nama pengguna, IP address, atau identifier..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
            </div>

            <div class="md:col-span-2">
                <select wire:model.live="eventFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
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
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Waktu Aktivitas</th>
                        <th class="px-4 py-3.5">Pengguna / Identifier</th>
                        <th class="px-4 py-3.5 text-center">Jenis Event</th>
                        <th class="px-4 py-3.5 text-right">Alamat IP</th>
                        @isset($blockedAccesses)
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        @endisset
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
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">
                                        Login Berhasil
                                    </span>
                                @elseif($log->event === 'login.failed')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800">
                                        Login Gagal
                                    </span>
                                @elseif($log->event === 'logout')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-neutral-100 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300 border border-neutral-200 dark:border-slate-600">
                                        Logout
                                    </span>
                                @elseif($log->event === 'password.changed')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">
                                        Password Diubah
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">
                                        {{ $log->event }}
                                    </span>
                                @endif
                            </td>

                            {{-- IP Address --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-mono text-neutral-600 dark:text-neutral-300">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>

                            {{-- Aksi Blokir Cepat -- HANYA Master Admin --}}
                            @isset($blockedAccesses)
                                <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                    @if($log->ip_address)
                                        <button
                                            type="button"
                                            wire:click="openBlockModalFor('ip', '{{ $log->ip_address }}')"
                                            title="Blokir alamat IP ini"
                                            class="px-2.5 py-1.5 text-[11px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 rounded-sm hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all cursor-pointer">
                                            Blokir IP
                                        </button>
                                    @endif
                                </td>
                            @endisset
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ isset($blockedAccesses) ? 5 : 4 }}" class="px-6 py-12 text-center text-xs text-neutral-400">
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

    {{-- ================= MODAL: BLOKIR IP/PERANGKAT (KHUSUS MASTER ADMIN) ================= --}}
    @isset($blockedAccesses)
        @if($showBlockModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
                <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8">

                    <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                        <div>
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Blokir IP / Perangkat</h3>
                            <p class="text-xs text-neutral-400">IP/perangkat yang diblokir tidak bisa login atau melanjutkan sesi yang sedang berjalan.</p>
                        </div>
                        <button wire:click="closeBlockModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
                    </div>

                    <form wire:submit.prevent="blockAccess" class="p-6 space-y-4 text-xs">

                        {{-- Tipe --}}
                        <div>
                            <label class="block font-medium text-neutral-600 dark:text-neutral-300 mb-1.5">Tipe <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all {{ $blockType === 'ip' ? 'border-rose-500 bg-rose-50/60 dark:bg-rose-950/30' : 'border-neutral-200 dark:border-slate-700' }}">
                                    <input type="radio" wire:model.live="blockType" value="ip" class="text-rose-600 focus:ring-rose-500">
                                    <span class="font-medium text-neutral-700 dark:text-neutral-200">Alamat IP</span>
                                </label>
                                <label class="flex items-center gap-2 px-3 py-2 border rounded-sm cursor-pointer transition-all {{ $blockType === 'device' ? 'border-rose-500 bg-rose-50/60 dark:bg-rose-950/30' : 'border-neutral-200 dark:border-slate-700' }}">
                                    <input type="radio" wire:model.live="blockType" value="device" class="text-rose-600 focus:ring-rose-500">
                                    <span class="font-medium text-neutral-700 dark:text-neutral-200">Perangkat (User-Agent)</span>
                                </label>
                            </div>
                            @error('blockType') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nilai --}}
                        <div>
                            <label class="block font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                                {{ $blockType === 'device' ? 'String User-Agent Perangkat' : 'Alamat IP' }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" wire:model="blockValue"
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 font-mono focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-400"
                                   placeholder="{{ $blockType === 'device' ? 'Mozilla/5.0 (...)' : 'Contoh: 103.10.20.30' }}">
                            @error('blockValue') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Alasan --}}
                        <div>
                            <label class="block font-medium text-neutral-600 dark:text-neutral-300 mb-1">Alasan (Opsional)</label>
                            <input type="text" wire:model="blockReason"
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-rose-500/10 focus:border-rose-400"
                                   placeholder="Contoh: Percobaan login mencurigakan berulang">
                            @error('blockReason') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                            <button type="button" wire:click="closeBlockModal"
                                    class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-sm hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                    class="px-5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-rose-900/20 cursor-pointer">
                                <span wire:loading.remove>Blokir Sekarang</span>
                                <span wire:loading>Memproses...</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        @endif
    @endisset

</div>