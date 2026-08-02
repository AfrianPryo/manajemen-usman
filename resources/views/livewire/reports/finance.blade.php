<div class="space-y-4">
    <div class="flex items-center justify-between">
        <input type="month" wire:model.live="month" class="rounded-lg border-gray-300 text-sm">
        <div class="space-x-2">
            <a href="{{ route('reports.finance.pdf', ['month' => $month]) }}" target="_blank"
               class="bg-red-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-red-700">Export PDF</a>
            <a href="{{ route('reports.finance.excel', ['month' => $month]) }}"
               class="bg-green-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-green-700">Export Excel</a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <x-stat-card label="Total Pemasukan" value="Rp {{ number_format($income, 0, ',', '.') }}" color="green" />
        <x-stat-card label="Total Pengeluaran" value="Rp {{ number_format($expense, 0, ',', '.') }}" color="red" />
        <x-stat-card label="Laba / Rugi Bersih" value="Rp {{ number_format($balance, 0, ',', '.') }}" :color="$balance >= 0 ? 'green' : 'red'" />
    </div>

    <x-data-table :headers="['Tanggal', 'Kategori', 'Tipe', 'Jumlah', 'Keterangan']">
        @foreach($transactions as $trx)
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
            </tr>
        @endforeach
    </x-data-table>
</div>
