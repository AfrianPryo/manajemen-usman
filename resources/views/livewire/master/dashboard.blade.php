<div>
    <div class="p-6 max-w-7xl mx-auto space-y-6">

        {{-- ================= KARTU STATISTIK ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Unit Usaha --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Unit Usaha</p>
                    <span class="h-8 w-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l1-5h16l1 5M4 9v11h16V9M9 20v-6h6v6"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalUnits }}</p>
                <p class="mt-1 text-xs text-gray-400">di seluruh jurusan</p>
            </div>

            {{-- Unit Aktif --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit Aktif</p>
                    <span class="h-8 w-8 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $activeUnits }}</p>
                <p class="mt-1 text-xs text-gray-400">dari {{ $totalUnits }} unit usaha</p>
            </div>

            {{-- Total Admin --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Admin</p>
                    <span class="h-8 w-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a3 3 0 11-3-3"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAdmins }}</p>
                <p class="mt-1 text-xs text-gray-400">admin unit terdaftar</p>
            </div>

            {{-- Status Sistem --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Sistem</p>
                    <span class="h-8 w-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                </div>
                <p class="mt-2 text-2xl font-bold flex items-center gap-2 {{ $inactiveUnits === 0 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                    <span class="h-2.5 w-2.5 rounded-full {{ $inactiveUnits === 0 ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                    {{ $inactiveUnits === 0 ? 'Optimal' : 'Perlu Perhatian' }}
                </p>
                <p class="mt-1 text-xs text-gray-400">{{ $inactiveUnits === 0 ? 'semua unit aktif' : $inactiveUnits . ' unit nonaktif' }}</p>
            </div>
        </div>

        {{-- ================= KESEHATAN UNIT USAHA ================= --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Kesehatan Unit Usaha</h2>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($units as $unit)
                    @php $admin = $unit->users->first(); @endphp
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 shadow-sm flex flex-col">
                        <div class="flex items-start justify-between">
                            <span class="h-9 w-9 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($unit->name, 0, 1)) }}
                            </span>
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $unit->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $unit->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>

                        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white leading-tight">{{ $unit->name }}</h3>
                        <p class="text-xs text-gray-400">Jurusan {{ $unit->department }}</p>

                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $admin ? 'Admin: ' . $admin->name : 'Belum ada admin' }}
                        </p>

                        <a href="{{ route('unit.dashboard', $unit->slug) }}"
                           class="mt-4 block text-center py-2 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 rounded-lg transition-colors">
                            Masuk Dashboard →
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ================= TABEL ADMIN & LOG AKTIVITAS ================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Tabel Admin & Hak Akses --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-slate-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Admin & Hak Akses</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Staf pengelola seluruh unit usaha</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Pengguna</th>
                                <th class="px-5 py-3">Unit Utama</th>
                                <th class="px-5 py-3">Level Akses</th>
                                <th class="px-5 py-3">Login Terakhir</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 flex items-center justify-center text-xs font-bold shrink-0">
                                                {{ collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('') }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 dark:text-gray-300">
                                        {{ $user->isMasterAdmin() ? 'Semua Unit' : ($user->unit->name ?? '—') }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $user->isMasterAdmin() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }}">
                                            {{ $user->isMasterAdmin() ? 'Master Admin' : 'Admin Unit' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $user->last_login_at ? ucfirst($user->last_login_at->diffForHumans()) : 'Belum pernah' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                                            <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Log Aktivitas Sistem --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-5">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Log Aktivitas</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Aktivitas sistem terkini</p>

                <ul class="mt-5 space-y-5">
                    @forelse ($logs as $log)
                        @php $info = $this->eventInfo($log->event); @endphp
                        <li class="flex gap-3">
                            <span class="h-8 w-8 rounded-full {{ $info['class'] }} flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $info['label'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $log->user->name ?? $log->identifier ?? 'Sistem' }}</p>
                                <p class="text-[11px] text-gray-400 uppercase tracking-wide mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400 italic">Belum ada aktivitas tercatat.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>