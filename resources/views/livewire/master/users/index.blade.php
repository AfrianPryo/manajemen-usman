<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola seluruh akun admin sistem.</p>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Admin
        </button>
    </div>

    {{-- Search --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, email, atau NIP..."
               class="w-full px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Nama & Email</th>
                        <th class="px-6 py-3">NIP</th>
                        <th class="px-6 py-3">Unit Usaha</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('') }}
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $user->nip ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $user->isMasterAdmin() ? 'Semua Unit' : ($user->unit->name ?? '-') }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $user->isMasterAdmin() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }}">
                                {{ $user->isMasterAdmin() ? 'Master' : 'Unit' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button class="text-amber-600 hover:underline text-xs">Reset PW</button>
                            <button class="text-blue-600 hover:underline text-xs">Edit</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">Tidak ada data admin.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $users->links() }}
        </div>
    </div>
</div>