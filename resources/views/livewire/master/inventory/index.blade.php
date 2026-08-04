<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoring Inventaris</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Stok produk dari seluruh unit usaha.</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari produk..."
               class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
        <select wire:model.live="unitFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            <option value="">Semua Unit</option>
            @foreach($units as $u) <option value="{{ $u->id }}">{{ $u->name }}</option> @endforeach
        </select>
        <select wire:model.live="stockFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            <option value="">Semua Status</option>
            <option value="out">Habis</option>
            <option value="low">Hampir Habis</option>
            <option value="normal">Normal</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Nama Produk</th>
                        <th class="px-6 py-3">Unit Usaha</th>
                        <th class="px-6 py-3">Kategori</th>
                        <th class="px-6 py-3 text-right">Stok</th>
                        <th class="px-6 py-3 text-right">Harga</th>
                        <th class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($products as $p)
                    @php
                        $status = $p->stock == 0 ? 'out' : ($p->stock <= 10 ? 'low' : 'normal');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $p->name }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $p->unit->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $p->category->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-mono text-gray-900 dark:text-white">{{ $p->stock }}</td>
                        <td class="px-6 py-4 text-right font-mono text-gray-900 dark:text-white">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @if($status === 'out')
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Habis</span>
                            @elseif($status === 'low')
                                <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Hampir Habis</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada produk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $products->links() }}
        </div>
    </div>
</div>