<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Pembelian Lintas-Unit</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Rekap belanja ke vendor dari seluruh Unit Usaha. Pembelian dicatat dari sisi Unit masing-masing.</p>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Total Belanja (Semua Unit)</p>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight mt-2">Rp {{ number_format($totalBelanja, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Belanja Bulan Ini</p>
            <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 tracking-tight mt-2">Rp {{ number_format($totalThisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Total Pembelian Tercatat</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight mt-2">{{ $totalPo }}</p>
        </div>
    </div>

    {{-- Top Vendor --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <h2 class="text-xs font-bold text-neutral-700 dark:text-neutral-200 mb-3">Top Vendor berdasarkan Total Belanja</h2>
        @if($vendorRecap->isEmpty())
            <p class="text-xs text-neutral-400">Belum ada data pembelian.</p>
        @else
            <div class="space-y-2">
                @foreach($vendorRecap as $recap)
                    <div class="flex items-center justify-between text-xs border-b border-neutral-50 dark:border-slate-700/50 pb-2 last:border-0 last:pb-0">
                        <div>
                            <div class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $recap->vendor?->name ?? 'Vendor tidak diketahui' }}</div>
                            <div class="text-[11px] text-neutral-400">{{ $recap->jumlah_po }} transaksi pembelian</div>
                        </div>
                        <div class="font-bold text-neutral-900 dark:text-white">Rp {{ number_format($recap->total_belanja, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 flex flex-col sm:flex-row items-center gap-3 shadow-sm shadow-black/[0.02]">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. PO, vendor, atau unit..."
               class="w-full sm:w-80 px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">

        <select wire:model.live="unitFilter"
                class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
            <option value="">Semua Unit</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="vendorFilter"
                class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
            <option value="">Semua Vendor</option>
            @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter"
                class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
            <option value="">Semua Status</option>
            <option value="completed">Selesai</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
    </div>

    {{-- Tabel Pembelian --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3">No. PO</th>
                        <th class="px-4 py-3">Unit Usaha</th>
                        <th class="px-4 py-3">Vendor</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($purchases as $purchase)
                        <tr wire:key="mpurchase-{{ $purchase->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 font-semibold text-neutral-900 dark:text-white text-[13px]">{{ $purchase->po_number }}</td>
                            <td class="px-4 py-3.5 text-[12px] text-neutral-600 dark:text-neutral-300">{{ $purchase->unit?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-[12px] text-neutral-600 dark:text-neutral-300">{{ $purchase->vendor?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-[12px] text-neutral-600 dark:text-neutral-300">
                                {{ $purchase->purchased_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-[12px] font-semibold text-neutral-800 dark:text-neutral-100 whitespace-nowrap">
                                Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($purchase->status === 'completed')
                                    <span class="text-[11px] font-medium px-2 py-1 rounded-full border bg-emerald-100 text-emerald-700 border-emerald-200">Selesai</span>
                                @else
                                    <span class="text-[11px] font-medium px-2 py-1 rounded-full border bg-rose-100 text-rose-700 border-rose-200">Dibatalkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada pembelian yang tercatat dari unit manapun.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                @if($purchases->total() > 0)
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $purchases->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $purchases->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $purchases->total() }}</span> total pembelian
                @endif
            </div>
            <div class="w-full md:w-auto flex justify-end">
                {{ $purchases->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

</div>
