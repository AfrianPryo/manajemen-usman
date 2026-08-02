<div>
    <div class="flex items-center gap-2 mb-4">
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 text-sm">
            <option value="">Semua Tipe</option>
            <option value="in">Stok Masuk</option>
            <option value="out">Stok Keluar</option>
        </select>
    </div>

    <x-data-table :headers="['Tanggal', 'Produk', 'Tipe', 'Jumlah', 'Catatan', 'Oleh']">
        @forelse($movements as $movement)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500">{{ $movement->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $movement->product->name }}</td>
                <td class="px-4 py-3">
                    @if($movement->type === 'in')
                        <x-badge color="green">Masuk</x-badge>
                    @else
                        <x-badge color="red">Keluar</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3">{{ $movement->quantity }} {{ $movement->product->unit }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $movement->note ?: '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $movement->user->name ?? '-' }}</td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <div class="mt-4">{{ $movements->links() }}</div>
</div>
