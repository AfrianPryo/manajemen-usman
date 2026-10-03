<div class="max-w-[1500px] mx-auto space-y-5 px-4 py-4 sm:px-6 font-sans text-neutral-800 dark:text-neutral-100">
    <livewire:page-tour tour="purchasing.index" />

    {{-- Flash Notification (toast) --}}
    @if (session()->has('message'))
        <div wire:key="toast-message-{{ md5(session('message')) }}" x-data x-init="$store.toast.push('success', @js(session('message')))"></div>
    @endif

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 p-4 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div class="min-w-0">
            <h1 class="text-sm font-bold tracking-tight text-neutral-900 dark:text-white">Pembelian Lintas-Unit</h1>
            <p class="text-[11px] tracking-tight text-neutral-400 mt-0.5 truncate">
                Pantau, rekap, dan kelola belanja ke vendor dari seluruh Unit Usaha.
            </p>
        </div>

        <div class="flex flex-nowrap items-center gap-2 overflow-x-auto shrink-0 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <x-tour-replay tour="purchasing.index" />

            <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-[11px] font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer shrink-0 whitespace-nowrap">
                <x-heroicon-o-plus class="w-3.5 h-3.5" stroke-width="2.5" />
                <span>Catat Pembelian</span>
            </button>
        </div>
    </div>

    {{-- Metric / Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Belanja (Semua Unit)</p>
                <x-heroicon-o-shopping-cart stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-sm font-bold text-neutral-900 dark:text-white tracking-tight font-mono">Rp {{ number_format($totalBelanja ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-sky-500">Belanja Bulan Ini</p>
                <x-heroicon-o-calendar-days stroke-width="1.5" class="w-4 h-4 text-sky-300 dark:text-sky-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-sky-600 dark:text-sky-400 tracking-tight font-mono">Rp {{ number_format($totalThisMonth ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-emerald-500">Total Pembelian Tercatat</p>
                <x-heroicon-o-document-text stroke-width="1.5" class="w-4 h-4 text-emerald-300 dark:text-emerald-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">{{ number_format($totalPo ?? 0) }} Transaksi</p>
        </div>
    </div>

    {{-- Top Vendor --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <h2 class="text-xs font-bold text-neutral-700 dark:text-neutral-200 mb-3">Top Vendor berdasarkan Total Belanja</h2>
        @if($vendorRecap->isEmpty())
            <p class="text-xs text-neutral-400">Belum ada data pembelian.</p>
        @else
            <div class="space-y-2">
                @foreach($vendorRecap as $recap)
                    <div class="flex items-center justify-between gap-3 text-xs border-b border-neutral-100 dark:border-slate-700/60 pb-2 last:border-0 last:pb-0">
                        <div class="min-w-0">
                            <div class="font-semibold text-neutral-900 dark:text-white truncate">{{ $recap->vendor?->name ?? 'Vendor tidak diketahui' }}</div>
                            <div class="text-[11px] text-neutral-400">{{ $recap->jumlah_po }} transaksi pembelian</div>
                        </div>
                        <div class="font-mono font-bold text-xs text-neutral-900 dark:text-white whitespace-nowrap">Rp {{ number_format($recap->total_belanja, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 space-y-2 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari No. PO, vendor, atau unit..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
            </div>

            <div>
                <select wire:model.live="unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Unit Usaha</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="vendorFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-slate-700/60 text-xs">
            <div class="flex items-center gap-2">
                <select wire:model.live="statusFilter" class="px-3 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none text-xs cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>

            {{-- Reset memakai $wire langsung (tanpa method baru di class) --}}
            <button type="button"
                    x-on:click="$wire.set('search', '', false); $wire.set('unitFilter', '', false); $wire.set('vendorFilter', '', false); $wire.set('statusFilter', '')"
                    class="px-3.5 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-sm transition-all cursor-pointer">
                Reset Filter
            </button>
        </div>
    </div>

    {{-- ================= DAFTAR PEMBELIAN ================= --}}
    <section class="space-y-3 pt-1">
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3.5">No. PO</th>
                            <th class="px-4 py-3.5">Unit Usaha & Vendor</th>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5 text-right">Total</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                        @forelse($purchases as $purchase)
                            <tr wire:key="mpurchase-{{ $purchase->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-semibold text-neutral-900 dark:text-white text-xs font-mono">{{ $purchase->po_number }}</div>
                                </td>
                                <td class="px-4 py-3.5 text-xs">
                                    <div class="font-medium text-neutral-700 dark:text-neutral-300">{{ $purchase->unit?->name ?? '-' }}</div>
                                    <div class="text-[11px] text-neutral-400">{{ $purchase->vendor?->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $purchase->purchased_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-xs text-neutral-900 dark:text-white">
                                    Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    @if($purchase->status === 'completed')
                                        <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">Selesai</span>
                                    @else
                                        <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">Dibatalkan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button wire:click="viewDetail({{ $purchase->id }})" class="p-1.5 text-sky-600 hover:text-sky-800 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/40 rounded-sm transition-all cursor-pointer" title="Lihat Detail">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                        </button>
                                    </div>
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
    </section>

    {{-- Modal Form Catat Pembelian --}}
    {{-- Tutorial form (di luar modal agar posisinya tidak terpengaruh scroll/blur modal) --}}
    @if($showModal)
        <livewire:page-tour tour="purchasing.form" wire:key="tour-purchasing.form" />
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Catat Pembelian</h3>
                        <p class="text-xs text-neutral-400">Pilih Unit Usaha, vendor & item -- stok dan transaksi keuangan unit terkait otomatis diperbarui.</p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="save" class="p-6 text-xs">
                    <x-form-tabs tab1-label="Info Pembelian" tab2-label="Item Pembelian" cancel="closeModal" compact>
                    <x-slot:tab1>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                        <select wire:model.live="unit_id"
                                class="@error('unit_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer" aria-invalid="@error('unit_id') true @else false @enderror" aria-required="true">
                            <option value="">-- Pilih Unit Usaha --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id') <x-form-error :message="$message" :field="'unit_id'" /> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Vendor / Supplier <span class="text-red-500">*</span></label>
                            <select wire:model="vendor_id"
                                    class="@error('vendor_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer" aria-invalid="@error('vendor_id') true @else false @enderror" aria-required="true">
                                <option value="">-- Pilih Vendor --</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                            @error('vendor_id') <x-form-error :message="$message" :field="'vendor_id'" /> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                            <select wire:model="payment_method"
                                    class="@error('payment_method') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer" aria-invalid="@error('payment_method') true @else false @enderror" aria-required="true">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Catatan</label>
                        <textarea wire:model="notes" rows="2"
                                  class="@error('notes') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Catatan tambahan untuk pembelian ini..." aria-invalid="@error('notes') true @else false @enderror"></textarea>
                    </div>

                    </x-slot:tab1>
                    <x-slot:tab2>
                    {{-- Baris Item --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Item Pembelian <span class="text-red-500">*</span></label>
                            <button type="button" wire:click="addItemRow"
                                    class="text-[11px] font-semibold text-blue-700 hover:text-blue-900 dark:text-blue-400 flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah Baris
                            </button>
                        </div>
                        @error('items') <x-form-error :message="$message" :field="'items'" class="mb-1.5" /> @enderror
                        @if(!$unit_id)
                            <p class="text-[11px] text-amber-600 dark:text-amber-400 mb-1.5">Pilih Unit Usaha terlebih dahulu untuk memilih Produk dari stok unit tersebut (item bebas non-produk tetap bisa diisi tanpa memilih unit).</p>
                        @endif

                        <div class="space-y-2.5">
                            @foreach($items as $index => $row)
                                <div wire:key="item-row-{{ $index }}" class="border border-neutral-200 dark:border-slate-700 rounded-sm p-2.5 space-y-2">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <div>
                                            <label class="flex items-center gap-1 text-[10px] font-semibold text-neutral-500 mb-0.5">
                                                Produk (opsional)
                                                <x-help-tip text="Pilih produk kalau pembelian ini akan menambah stok barang tersebut secara otomatis. Kosongkan kalau ini pembelian jasa atau barang yang tidak dicatat di stok." />
                                            </label>
                                            <select wire:model="items.{{ $index }}.product_id"
                                                    class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500 cursor-pointer @error("items.{$index}.product_id") !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror" aria-invalid="@error("items.{$index}.product_id") true @else false @enderror">
                                                <option value="">-- Item bebas (non-stok) --</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }} (stok: {{ $product->stock }})</option>
                                                @endforeach
                                            </select>
                                            <x-form-error :field="'items.' . $index . '.product_id'" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Nama Item</label>
                                            <input type="text" wire:model="items.{{ $index }}.name"
                                                   class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500 @error("items.{$index}.name") !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror"
                                                   placeholder="Nama item / jasa" aria-invalid="@error("items.{$index}.name") true @else false @enderror">
                                            <x-form-error :field="'items.' . $index . '.name'" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 items-end">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Qty</label>
                                            <input type="text" inputmode="decimal" wire:model="items.{{ $index }}.qty" oninput="onlyDecimal(event)"
                                                   class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500 @error("items.{$index}.qty") !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror" aria-invalid="@error("items.{$index}.qty") true @else false @enderror">
                                            <x-form-error :field="'items.' . $index . '.qty'" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-neutral-500 mb-0.5">Harga Satuan (Rp)</label>
                                            <input type="text" inputmode="decimal" wire:model="items.{{ $index }}.unit_price" oninput="onlyDecimal(event)"
                                                   class="w-full px-2.5 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 text-[11px] focus:outline-none focus:border-blue-500 @error("items.{$index}.unit_price") !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror" aria-invalid="@error("items.{$index}.unit_price") true @else false @enderror">
                                            <x-form-error :field="'items.' . $index . '.unit_price'" />
                                        </div>
                                        <div class="flex justify-end">
                                            <button type="button" wire:click="removeItemRow({{ $index }})"
                                                    class="p-1.5 text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-all cursor-pointer"
                                                    title="Hapus Baris">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    </x-slot:tab2>
                    <x-slot:submit>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>Simpan Pembelian</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </x-slot:submit>
                    </x-form-tabs>
                </form>

            </div>
        </div>
    @endif

    {{-- Modal Detail Pembelian --}}
    @if($showDetailModal && $selectedPurchase)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900 dark:text-white font-mono">{{ $selectedPurchase->po_number }}</h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Unit: {{ $selectedPurchase->unit?->name ?? '-' }} · Vendor: {{ $selectedPurchase->vendor?->name ?? '-' }}</p>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-sm transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" stroke-width="2" />
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div class="divide-y divide-neutral-100 dark:divide-slate-700 border border-neutral-100 dark:border-slate-700 rounded-sm overflow-hidden">
                        @foreach($selectedPurchase->items as $item)
                            <div class="flex items-center justify-between px-3 py-2">
                                <div>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-100">{{ $item['name'] }}</div>
                                    <div class="text-[11px] text-neutral-400">{{ $item['qty'] }} x Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</div>
                                </div>
                                <div class="font-mono font-bold text-xs text-neutral-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between font-bold text-sm text-neutral-900 dark:text-white pt-1">
                        <span>Total</span>
                        <span class="font-mono">Rp {{ number_format($selectedPurchase->total_amount, 0, ',', '.') }}</span>
                    </div>

                    @if($selectedPurchase->notes)
                        <div class="text-[11px] text-neutral-500 bg-neutral-50 dark:bg-slate-900/50 rounded-sm p-2.5">
                            {{ $selectedPurchase->notes }}
                        </div>
                    @endif

                    <div class="flex items-center gap-2 text-[11px] text-neutral-400">
                        <span>Status:</span>
                        @if($selectedPurchase->status === 'completed')
                            <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">Selesai</span>
                        @else
                            <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">Dibatalkan</span>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        @if($selectedPurchase->status === 'completed')
                            <button type="button"
                                    x-on:click.prevent="$store.confirmDialog.open({
                                        message: 'Yakin ingin membatalkan pembelian ini? Stok & transaksi keuangan unit terkait akan disesuaikan otomatis.',
                                        confirmText: 'Ya, Batalkan',
                                        onConfirm: () => $wire.cancelPurchase({{ $selectedPurchase->id }})
                                    })"
                                    class="px-4 h-9 text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-sm hover:bg-rose-100 dark:hover:bg-rose-950/70 transition-all cursor-pointer">
                                Batalkan Pembelian
                            </button>
                        @endif
                        <button type="button" wire:click="closeDetailModal"
                                class="px-4 h-9 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>