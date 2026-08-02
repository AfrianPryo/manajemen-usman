<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Produk" :value="$totalProducts" color="indigo" />
        <x-stat-card label="Stok Menipis" :value="$lowStockCount" color="red" />
        <x-stat-card label="Pemasukan Bulan Ini" value="Rp {{ number_format($income, 0, ',', '.') }}" color="green" />
        <x-stat-card label="Pengeluaran Bulan Ini" value="Rp {{ number_format($expense, 0, ',', '.') }}" color="amber" />
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-sm text-gray-500">Saldo Bersih Bulan Ini</p>
        <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
            Rp {{ number_format($balance, 0, ',', '.') }}
        </p>
    </div>

    <div>
        <h3 class="font-semibold text-gray-800 mb-3">Produk dengan Stok Menipis</h3>
        <x-data-table :headers="['Kode', 'Nama Produk', 'Stok', 'Minimum']" empty="Semua stok dalam kondisi aman.">
            @foreach($lowStockProducts as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $product->code }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                    <td class="px-4 py-3"><x-badge color="red">{{ $product->stock }} {{ $product->unit }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $product->min_stock }}</td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
</div>
