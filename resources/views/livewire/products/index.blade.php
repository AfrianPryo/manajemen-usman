<div>
    <div class="flex items-center justify-between mb-4">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari produk / kode..."
               class="w-64 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        <button wire:click="create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Tambah Produk
        </button>
    </div>

    <x-data-table :headers="['Kode', 'Nama Produk', 'Kategori', 'Harga Jual', 'Stok', 'Aksi']">
        @forelse($products as $product)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500">{{ $product->code }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $product->category->name }}</td>
                <td class="px-4 py-3">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    {{ $product->stock }} {{ $product->unit }}
                    @if($product->isLowStock())
                        <x-badge color="red">Menipis</x-badge>
                    @else
                        <x-badge color="green">Aman</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3 space-x-2">
                    <button wire:click="edit({{ $product->id }})" class="text-indigo-600 hover:underline text-sm">Edit</button>
                    <button wire:click="delete({{ $product->id }})" wire:confirm="Yakin ingin menghapus produk ini?" class="text-blue-900 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <div class="mt-4">{{ $products->links() }}</div>

    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Produk' : 'Tambah Produk' }}">
        <x-form-select label="Kategori" name="category_id" :options="$categories" />
        <x-form-input label="Kode Produk (SKU)" name="code" />
        <x-form-input label="Nama Produk" name="name" />
        <div class="grid grid-cols-2 gap-3">
            <x-form-input label="Satuan (pcs/box/dll)" name="unit" />
            <x-form-input label="Stok Awal" name="stock" type="number" />
        </div>
        <div class="grid grid-cols-2 gap-3">
            <x-form-input label="Harga Beli" name="purchase_price" type="number" />
            <x-form-input label="Harga Jual" name="selling_price" type="number" />
        </div>
        <x-form-input label="Stok Minimum (alert)" name="min_stock" type="number" />

        <x-slot:footer>
            <button @click="show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
        </x-slot:footer>
    </x-modal>
</div>
