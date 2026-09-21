{{--
    Tabel "Admin & Hak Akses" — dipindahkan apa adanya dari
    resources/views/livewire/master/dashboard.blade.php agar menjadi
    komponen Livewire yang berdiri sendiri (lihat audit performa poin 3).
    Tampilan & kelas CSS sengaja dipertahankan 100% sama; yang berubah
    hanya sumber datanya (sekarang paginated) dan footer tabel.
--}}
<div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02] flex flex-col justify-between">
    <div>
        {{-- Header Tabel & Filter --}}
        <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Admin &amp; Hak Akses</h2>
                <p class="text-xs text-neutral-400">Daftar staf pengelola sistem dan unit</p>
            </div>

            {{-- Quick Search Table --}}
            <div class="relative">
                <input type="text"
                    wire:model.live.debounce.300ms="searchAdmin"
                    placeholder="Cari nama/email..."
                    class="w-full sm:w-64 pl-9 pr-3 py-2.5 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-all shadow-sm shadow-black/[0.02]">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-neutral-400 absolute left-3 top-3" />
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Unit Kerja</th>
                        <th class="px-5 py-3.5">Akses</th>
                        <th class="px-5 py-3.5">Login Terakhir</th>
                        <th class="px-5 py-3.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse ($users as $user)
                        <tr wire:key="dashboard-user-{{ $user->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="h-9 w-9 rounded-sm bg-blue-50 dark:bg-slate-900 text-[#0d3b74] dark:text-neutral-300 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('') }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-neutral-900 dark:text-white truncate text-xs sm:text-sm">{{ $user->name }}</p>
                                        <p class="text-[11px] text-neutral-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-neutral-500 dark:text-neutral-400 font-medium">
                                {{ (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? 'Semua Unit' : ($user->unit->name ?? '—') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm {{ (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? 'bg-[#0d3b74] text-white' : 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' }}">
                                    {{ (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? 'Master Admin' : 'Admin Unit' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-neutral-400">
                                {{ $user->last_login_at ? ucfirst($user->last_login_at->diffForHumans()) : 'Belum pernah' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400' }}">
                                    <span class="h-1.5 w-1.5 rounded-sm {{ $user->is_active ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-slate-600' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-xs text-neutral-400">
                                Data admin tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer Tabel --}}
    <div class="p-4 border-t border-neutral-100 dark:border-slate-700 bg-neutral-50/40 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-neutral-400">
        <span>
            Menampilkan {{ $users->count() }} dari {{ $users->total() }} admin terdaftar
        </span>

        <div class="flex items-center gap-4">
            @if ($users->hasPages())
                <div>{{ $users->links() }}</div>
            @endif

            <a href="{{ Route::has('master.users.index') ? route('master.users.index') : '#' }}" class="font-bold text-[#0d3b74] dark:text-white hover:text-blue-700 dark:hover:text-neutral-300 transition-colors shrink-0">Kelola Semua Admin &rarr;</a>
        </div>
    </div>
</div>
