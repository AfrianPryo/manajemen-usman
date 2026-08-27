<div>
    <div class="flex items-center justify-end gap-2 mb-4">
        <a href="{{ route('reports.stock.pdf') }}" target="_blank"
           class="bg-blue-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-950">Export PDF</a>
        <a href="{{ route('reports.stock.excel') }}"
           class="bg-green-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-green-700">Export Excel</a>
    </div>

    <x-data-table :headers="['Kode', 'Nama Produk', 'Kategori', 'Stok', 'Harga Beli', 'Harga Jual', 'Status']">
        @foreach($products as $product)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500">{{ $product->code }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $product->category->name }}</td>
                <td class="px-4 py-3">{{ $product->stock }} {{ $product->unit }}</td>
                <td class="px-4 py-3">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    @if($product->isLowStock())
                        <x-badge color="red">Menipis</x-badge>
                    @else
                        <x-badge color="green">Aman</x-badge>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
</div>
