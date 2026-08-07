<div class="max-w-7xl mx-auto space-y-6 min-h-screen text-slate-800 bg-[#F8FAFC] p-4 sm:p-6 font-sans">

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard Master Admin</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded-full border border-emerald-200/60">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live Monitor
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Ikhtisar kinerja bisnis, status operasional, dan manajemen unit usaha.
            </p>
        </div>

        {{-- Tombol Aksi Cepat --}}
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-all shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition-all shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                + Admin Baru
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Unit Usaha
            </button>
        </div>
    </div>

    {{-- ================= PERINGATAN / ACTION NEEDED (KONDISIONAL) ================= --}}
    @if ($inactiveUnits > 0)
        <div class="flex items-center justify-between p-4 bg-amber-50/80 border border-amber-200/80 rounded-2xl text-amber-900 shadow-xs">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-amber-100 text-amber-600 rounded-xl shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-amber-900">Terdapat {{ $inactiveUnits }} Unit Usaha Nonaktif / Perlu Perhatian</p>
                    <p class="text-xs text-amber-700/90 mt-0.5">Beberapa unit usaha belum beroperasi penuh atau belum memiliki admin penanggung jawab.</p>
                </div>
            </div>
            <a href="#kesehatan-unit" class="text-xs font-bold text-amber-800 underline hover:text-amber-950 shrink-0 transition-colors">Tinjau Unit →</a>
        </div>
    @endif

    {{-- ================= KARTU STATISTIK ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Total Omzet / Pendapatan --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 hover:border-slate-300 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omzet (Bulan Ini)</p>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/60">
                    <svg class="h-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalRevenue ?? 'Rp —' }}</p>
            <div class="mt-3 flex items-center gap-1.5 text-xs">
                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200/50 flex items-center gap-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/></svg>
                    +12.5%
                </span>
                <span class="text-slate-500">vs bulan lalu</span>
            </div>
        </div>

        {{-- Total Unit Usaha --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 hover:border-slate-300 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Unit Usaha</p>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/60">
                    <svg class="h-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H21m-9 0H3m2.25-1.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H9m-6 0V3.75A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.75V21"/></svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalUnits }}</p>
            <p class="mt-3 text-xs text-slate-500"><span class="font-semibold text-emerald-600">{{ $activeUnits }} Aktif</span> dari seluruh jurusan</p>
        </div>

        {{-- Total Admin Pengelola --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 hover:border-slate-300 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Admin</p>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/60">
                    <svg class="h-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalAdmins }}</p>
            <p class="mt-3 text-xs text-slate-500">Staf pengelola unit terdaftar</p>
        </div>

        {{-- Status Sistem --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 hover:border-slate-300 transition-all shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Sistem</p>
                <span class="p-2 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/60">
                    <svg class="h-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-center gap-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $inactiveUnits === 0 ? 'bg-emerald-400' : 'bg-amber-400' }} opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 {{ $inactiveUnits === 0 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                </span>
                <p class="text-2xl font-bold text-slate-900 tracking-tight">
                    {{ $inactiveUnits === 0 ? 'Optimal' : 'Perlu Perhatian' }}
                </p>
            </div>
            <p class="mt-2 text-xs text-slate-500">
                {{ $inactiveUnits === 0 ? 'Semua layanan beroperasional' : $inactiveUnits . ' unit perlu penanganan' }}
            </p>
        </div>
    </div>

    {{-- ================= KESEHATAN UNIT USAHA ================= --}}
    <section id="kesehatan-unit" class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">Kesehatan Unit Usaha</h2>
                <p class="text-xs text-slate-500">Status operasional dan pengelola setiap unit usaha</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" placeholder="Cari unit..." class="w-48 sm:w-60 pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 text-slate-800 placeholder-slate-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all shadow-xs">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
            </div>
        </div>

        {{-- Horizontal Scroll Container --}}
        <div class="flex overflow-x-auto gap-3 pb-2 snap-x snap-mandatory scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent">
            @foreach ($units as $unit)
                @php $admin = $unit->users->first(); @endphp
                {{-- Card Unit Ringkas --}}
                <div class="shrink-0 w-64 snap-start bg-white rounded-2xl border border-slate-200/80 p-4 hover:shadow-md transition-all flex flex-col justify-between group shadow-xs">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="h-9 w-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs border border-slate-200/60 shrink-0">
                                {{ strtoupper(substr($unit->name, 0, 1)) }}
                            </span>
                            <span class="px-2.5 py-0.5 text-[10px] font-bold tracking-wider rounded-full uppercase border {{ $unit->is_active ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <h3 class="mt-3 text-sm font-bold text-slate-900 group-hover:text-slate-700 transition-colors leading-tight truncate">
                            {{ $unit->name }}
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5 truncate">Jurusan {{ $unit->department }}</p>

                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <p class="text-[11px] font-medium text-slate-600 truncate">
                                {{ $admin ? $admin->name : 'Belum Ada Admin' }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('unit.dashboard', $unit->slug) }}"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200/80 border border-slate-200/60 rounded-xl transition-all">
                        <span>Buka Dashboard</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= TABEL ADMIN & LOG AKTIVITAS ================= --}}
    <div class="space-y-6 pt-2">

        {{-- Tabel Admin & Hak Akses --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs flex flex-col justify-between">
            <div>
                {{-- Header Tabel & Filter --}}
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Admin & Hak Akses</h2>
                        <p class="text-xs text-slate-500">Daftar staf pengelola sistem dan unit</p>
                    </div>
                    
                    {{-- Quick Search Table --}}
                    <div class="relative">
                        <input type="text" wire:model.live="searchAdmin" placeholder="Cari nama/email..." class="w-full sm:w-64 pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 text-slate-800 placeholder-slate-400 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all shadow-xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                </div>

                {{-- Table Content --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5">Pengguna</th>
                                <th class="px-5 py-3.5">Unit Kerja</th>
                                <th class="px-5 py-3.5">Akses</th>
                                <th class="px-5 py-3.5">Login Terakhir</th>
                                <th class="px-5 py-3.5 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $user)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <span class="h-9 w-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200/80">
                                                {{ collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('') }}
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate text-xs sm:text-sm">{{ $user->name }}</p>
                                                <p class="text-[11px] text-slate-500 truncate">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-600 font-medium">
                                        {{ $user->isMasterAdmin() ? 'Semua Unit' : ($user->unit->name ?? '—') }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2.5 py-0.5 text-[10px] font-bold tracking-wider rounded-full uppercase border {{ $user->isMasterAdmin() ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                            {{ $user->isMasterAdmin() ? 'Master Admin' : 'Admin Unit' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500">
                                        {{ $user->last_login_at ? ucfirst($user->last_login_at->diffForHumans()) : 'Belum pernah' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->is_active ? 'text-emerald-700' : 'text-slate-400' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer Tabel --}}
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs text-slate-500">
                <span>Menampilkan {{ count($users) }} admin terdaftar</span>
                <a href="#" class="font-bold text-slate-900 hover:text-slate-700 transition-colors">Kelola Semua Admin →</a>
            </div>
        </div>

        {{-- Log Aktivitas Sistem --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Log Aktivitas</h2>
                    <p class="text-xs text-slate-500">Riwayat aksi sistem terkini dari seluruh unit</p>
                </div>
                <a href="#" class="text-xs font-bold text-slate-900 hover:text-slate-700 transition-colors">
                    Lihat Semua Audit Log →
                </a>
            </div>

            {{-- List Aktivitas --}}
            <div class="mt-4 space-y-1">
                @forelse ($logs as $log)
                    @php $info = $this->eventInfo($log->event); @endphp
                    <div class="p-3 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200/60 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="h-8 w-8 rounded-xl bg-slate-100 text-slate-600 border border-slate-200/60 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-slate-800 truncate">
                                    {{ $info['label'] }}
                                </p>
                                <p class="text-[11px] text-slate-500 truncate">
                                    Oleh: <span class="font-semibold text-slate-700">{{ $log->user->name ?? $log->identifier ?? 'Sistem' }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="text-[11px] font-medium text-slate-400 shrink-0 self-start sm:self-center pl-11 sm:pl-0">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400 italic">
                        Belum ada aktivitas tercatat.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>