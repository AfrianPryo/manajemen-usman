<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">&times;</button>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Pembelian</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Catat belanja ke vendor/supplier -- stok & keuangan diperbarui otomatis.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openCreateModal"
                    class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Catat Pembelian</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan Cepat --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Belanja Bulan Ini</p>
            <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight mt-2">Rp {{ number_format($totalThisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Total Pembelian Tercatat</p>
            <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 tracking-tight mt-2">{{ $totalCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Vendor Digunakan</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight mt-2">{{ $vendorCount }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-80">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari No. PO atau nama vendor..."
                       class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
            </div>

            <select wire:model.live="statusFilter"
                    class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
    </div>

    {{-- Tabel Pembelian --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3">No. PO</th>
                        <th class="px-4 py-3">Vendor</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3 text-center">Jml Item</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($purchases as $purchase)
                        <tr wire:key="purchase-{{ $purchase->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-[13px] leading-tight">{{ $purchase->po_number }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-neutral-700 dark:text-neutral-200 text-[12px]">{{ $purchase->vendor?->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-[12px] text-neutral-600 dark:text-neutral-300">
                                {{ $purchase->purchased_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center text-[12px] text-neutral-600 dark:text-neutral-300">
                                {{ count($purchase->items) }}
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
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="viewDetail({{ $purchase->id }})"
                                            class="p-1.5 text-sky-600 hover:text-sky-800 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-md transition-all cursor-pointer"
                                            title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada pembelian yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
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

    {{-- Modal Form Catat Pembelian --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Catat Pembelian</h3>
                        <p class="text-xs text-neutral-400">Pilih vendor & item -- stok dan transaksi keuangan otomatis diperbarui.</p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="save" class="p-6 space-y-4 text-xs">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Vendor / Supplier <span class="text-red-500">*</span></label>
                            <select wire:model="vendor_id"
                                    class="w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            @error('vendor_id') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Metode Pembayaran</label>
                            <select wire:model="payment_method"
                                    class="w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris Item --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300">Item Pembelian <span class="text-red-500">*</span></label>
                            <button type="button" wire:click="addItemRow"
                                    class="text-[11px] font-semibold text-blue-700 hover:text-blue-900 dark:text-blue-400 flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah Baris
                            </button>
                        </div>
                        @error('items') <span class="text-rose-500 text-[11px] mb-1.5 block">{{ $message }}</span> @enderror

                        <div class="space-y-2.5">
                            @foreach($items as $index => $row)
                                <div wire:key="item-row-{{ $index }}" class="border border-neutral-200 dark:border-slate-700 rounded-md p-2.5 space-y-2">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Produk (opsional)</label>
                                            <select wire:model="items.{{ $index }}.product_id"
                                                    class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500 cursor-pointer">
                                                <option value="">-- Item bebas (non-stok) --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }} (stok: {{ $product->stock }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Nama Item</label>
                                            <input type="text" wire:model="items.{{ $index }}.name"
                                                   class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500"
                                                   placeholder="Nama item / jasa">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 items-end">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Qty</label>
                                            <input type="number" step="0.01" min="0.01" wire:model="items.{{ $index }}.qty"
                                                   class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Harga Satuan (Rp)</label>
                                            <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.unit_price"
                                                   class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500">
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="button" wire:click="removeItemRow({{ $index }})"
                                                    class="p-1.5 text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all cursor-pointer"
                                                    title="Hapus Baris">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Catatan tambahan untuk pembelian ini..."></textarea>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>Simpan Pembelian</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

    {{-- Modal Detail Pembelian --}}
    @if($showDetailModal && $selectedPurchase)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">{{ $selectedPurchase->po_number }}</h3>
                        <p class="text-xs text-neutral-400">Vendor: {{ $selectedPurchase->vendor?->name }}</p>
                    </div>
                    <button wire:click="closeDetailModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div class="divide-y divide-neutral-100 dark:divide-slate-700 border border-neutral-100 dark:border-slate-700 rounded-md overflow-hidden">
                        @foreach($selectedPurchase->items as $item)
                            <div class="flex items-center justify-between px-3 py-2">
                                <div>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-100">{{ $item['name'] }}</div>
                                    <div class="text-[11px] text-neutral-400">{{ $item['qty'] }} x Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</div>
                                </div>
                                <div class="font-semibold text-neutral-700 dark:text-neutral-200">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between font-bold text-sm text-neutral-900 dark:text-white pt-1">
                        <span>Total</span>
                        <span>Rp {{ number_format($selectedPurchase->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($selectedPurchase->notes)
                        <div class="text-[11px] text-neutral-500 bg-neutral-50 dark:bg-slate-900/50 rounded-md p-2.5">
                            {{ $selectedPurchase->notes }}
                        </div>
                    @endif

                    <div class="text-[11px] text-neutral-400">
                        Status:
                        @if($selectedPurchase->status === 'completed')
                            <span class="font-semibold text-emerald-600">Selesai</span>
                        @else
                            <span class="font-semibold text-rose-600">Dibatalkan</span>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        @if($selectedPurchase->status === 'completed')
                            <button type="button"
                                    x-on:click.prevent="$store.confirmDialog.open({
                                        message: 'Yakin ingin membatalkan pembelian ini? Stok & transaksi keuangan terkait akan disesuaikan otomatis.',
                                        confirmText: 'Ya, Batalkan',
                                        onConfirm: () => $wire.cancelPurchase({{ $selectedPurchase->id }})
                                    })"
                                    class="px-4 py-2 text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/40 rounded-md hover:bg-rose-100 transition-all cursor-pointer">
                                Batalkan Pembelian
                            </button>
                        @endif
                        <button type="button" wire:click="closeDetailModal"
                                class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
