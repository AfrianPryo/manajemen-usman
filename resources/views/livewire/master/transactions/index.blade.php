<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoring Transaksi</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Seluruh transaksi dari semua unit usaha.</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari deskripsi..."
               class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
        <select wire:model.live="unitFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            <option value="">Semua Unit</option>
            @foreach($units as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
        </select>
        <select wire:model.live="typeFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            <option value="">Semua Tipe</option>
            <option value="income">Pemasukan</option>
            <option value="expense">Pengeluaran</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Unit</th>
                        <th class="px-6 py-3">Deskripsi</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($transactions as $tr)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($tr->date)->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $tr->unit->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-900 dark:text-white">{{ $tr->description }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $tr->category->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $tr->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $tr->type === 'income' ? 'Masuk' : 'Keluar' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold {{ $tr->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $tr->type === 'income' ? '+' : '-' }} Rp {{ number_format($tr->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $transactions->links() }}
        </div>
    </div>
</div>