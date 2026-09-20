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

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-neutral-400 uppercase text-xs">
                <tr>
                    @foreach($headers as $head)
                        <th class="px-4 py-3 font-semibold">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-slate-700 text-gray-700 dark:text-neutral-300">
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if($slot->isEmpty() || trim(strip_tags($slot)) === '')
        <div class="text-center text-gray-400 dark:text-slate-500 py-8 text-sm">{{ $empty }}</div>
    @endif
</div>
