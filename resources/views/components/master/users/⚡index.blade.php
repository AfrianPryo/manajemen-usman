<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola hak akses dan akun pengelola unit.</p>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
            + Tambah Admin
        </button>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Nama & Email</th>
                    <th class="px-6 py-3">NIP</th>
                    <th class="px-6 py-3">Unit Usaha</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach(\App\Models\User::with('unit')->get() as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $user->nip ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $user->isMasterAdmin() ? 'Master (Semua)' : ($user->unit->name ?? '-') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">
                            <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="text-amber-600 hover:underline">Reset PW</button>
                        <button class="text-gray-500 hover:text-gray-700">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>