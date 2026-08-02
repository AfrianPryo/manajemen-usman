{{--
    Komponen tabel reusable.
    Cara pakai:
    <x-data-table :headers="['Nama', 'Kategori', 'Stok']">
        @foreach($items as $item)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3">{{ $item->name }}</td>
                ...
            </tr>
        @endforeach
    </x-data-table>
--}}
@props(['headers' => [], 'empty' => 'Belum ada data.'])

<div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    @foreach($headers as $head)
                        <th class="px-4 py-3 font-semibold">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if($slot->isEmpty() || trim(strip_tags($slot)) === '')
        <div class="text-center text-gray-400 py-8 text-sm">{{ $empty }}</div>
    @endif
</div>
