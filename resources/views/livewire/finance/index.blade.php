<div>
    <div class="flex items-center justify-between mb-4">
        <input type="month" wire:model.live="filterMonth" class="rounded-lg border-gray-300 text-sm">
        <div class="space-x-2">
            <button wire:click="create('income')" class="bg-green-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-green-700">
                + Pemasukan
            </button>
            <button wire:click="create('expense')" class="bg-red-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-red-700">
                + Pengeluaran
            </button>
        </div>
    </div>

    <x-data-table :headers="['Tanggal', 'Kategori', 'Tipe', 'Jumlah', 'Keterangan', 'Aksi']">
        @forelse($transactions as $trx)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500">{{ $trx->transaction_date->format('d M Y') }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $trx->category->name }}</td>
                <td class="px-4 py-3">
                    @if($trx->type === 'income')
                        <x-badge color="green">Pemasukan</x-badge>
                    @else
                        <x-badge color="red">Pengeluaran</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $trx->description ?: '-' }}</td>
                <td class="px-4 py-3 space-x-2">
                    <button wire:click="edit({{ $trx->id }})" class="text-indigo-600 hover:underline text-sm">Edit</button>
                    <button wire:click="delete({{ $trx->id }})" wire:confirm="Yakin ingin menghapus transaksi ini?" class="text-red-600 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <div class="mt-4">{{ $transactions->links() }}</div>

    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Transaksi' : ($type === 'income' ? 'Tambah Pemasukan' : 'Tambah Pengeluaran') }}">
        <x-form-select label="Kategori" name="finance_category_id" :options="$type === 'income' ? $incomeCategories : $expenseCategories" />
        <x-form-input label="Jumlah (Rp)" name="amount" type="number" />
        <x-form-input label="Tanggal" name="transaction_date" type="date" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (opsional)</label>
            <textarea wire:model="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>
        <x-slot:footer>
            <button @click="show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
        </x-slot:footer>
    </x-modal>
</div>
