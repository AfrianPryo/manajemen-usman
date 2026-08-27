<div>
    <div class="flex items-center justify-end gap-2 mb-4">
        <button wire:click="create('in')" class="bg-green-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-green-700">
            + Stok Masuk
        </button>
        <button wire:click="create('out')" class="bg-blue-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-950">
            - Stok Keluar
        </button>
        <a href="{{ route('stock.history') }}" class="text-sm text-indigo-600 hover:underline px-2">Lihat Riwayat Mutasi &rarr;</a>
    </div>

    <x-data-table :headers="['Kode', 'Nama Produk', 'Stok Saat Ini', 'Status']">
        @forelse($products as $product)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500">{{ $product->code }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                <td class="px-4 py-3">{{ $product->stock }} {{ $product->unit }}</td>
                <td class="px-4 py-3">
                    @if($product->isLowStock())
                        <x-badge color="red">Stok Menipis</x-badge>
                    @else
                        <x-badge color="green">Aman</x-badge>
                    @endif
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <div class="mt-4">{{ $products->links() }}</div>

    <x-modal wire:model="showModal" title="{{ $type === 'in' ? 'Catat Stok Masuk' : 'Catat Stok Keluar' }}">
        <x-form-select label="Produk" name="product_id" :options="$productOptions" />
        <x-form-input label="Jumlah" name="quantity" type="number" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
            <textarea wire:model="note" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>
        <x-slot:footer>
            <button @click="show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
        </x-slot:footer>
    </x-modal>
</div>
