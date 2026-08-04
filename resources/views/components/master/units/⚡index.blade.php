<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Unit Usaha</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola 5 unit usaha mandiri sekolah.</p>
        </div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
            + Tambah Unit
        </button>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="px-6 py-3">Nama Unit</th>
                    <th class="px-6 py-3">Jurusan</th>
                    <th class="px-6 py-3">Kepala Admin</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach(\App\Models\Unit::all() as $unit)
                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $unit->name }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $unit->department }}</td>
                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                        {{ $unit->users->where('is_active', true)->first()?->name ?? 'Belum ada' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $unit->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800' }}">
                            {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('unit.dashboard', $unit->slug) }}" class="text-blue-600 hover:underline">Lihat Dashboard</a>
                        <button class="text-gray-500 hover:text-gray-700">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>