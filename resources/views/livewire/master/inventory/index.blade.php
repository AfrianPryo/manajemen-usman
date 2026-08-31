<div class="p-6 max-w-7xl mx-auto space-y-5">
    {{-- Header & Action Button --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openCreateModal" class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Produk</span>
            </button>
            <button wire:click="exportProducts" class="px-3.5 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-[3px] hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <span>Export Excel</span>
            </button>

            <button wire:click="openImportModal" class="px-3.5 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-[3px] hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                <span>Import Excel</span>
            </button>
        </div>

        <button type="button" 
                wire:click="openCategoryModal" 
                class="px-3.5 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-[3px] hover:bg-neutral-50 dark:hover:bg-slate-700 transition-all flex items-center gap-1.5 cursor-pointer shadow-sm">
            <svg class="w-4 h-4 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
            </svg>
            <span>Kelola Kategori</span>
        </button>
    </div>

    {{-- Metric / Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
            <span class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">Total Produk</span>
            <div class="mt-1 text-lg font-bold text-neutral-900 dark:text-white">{{ number_format($totalProductsCount ?? 0) }} Item</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
            <span class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">Total Unit Stok</span>
            <div class="mt-1 text-lg font-bold text-neutral-900 dark:text-white font-mono">{{ number_format($totalStockSum ?? 0) }} Pcs</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
            <span class="text-[11px] font-semibold text-amber-500 uppercase tracking-wider">Stok Menipis / Habis</span>
            <div class="mt-1 text-lg font-bold text-amber-600 dark:text-amber-400 font-mono">{{ number_format($lowStockCount ?? 0) }} Produk</div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
            <span class="text-[11px] font-semibold text-emerald-500 uppercase tracking-wider">Est. Nilai Inventaris</span>
            <div class="mt-1 text-lg font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($totalInventoryValue ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-neutral-100 dark:border-slate-700 p-4 space-y-2 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode Produk, Nama Produk, atau Deskripsi..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
            </div>

            <div>
                <select wire:model.live="unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    {{-- Placeholder "semua unit" hanya relevan kalau ada lebih dari 1 unit
                         untuk dipilih (konteks Master). Saat $units cuma berisi 1 unit
                         (konteks Unit Admin, lihat Unit\Inventory\Index::render()),
                         placeholder ini otomatis hilang -- dropdown cukup menampilkan
                         nama unit sendiri sebagai satu-satunya opsi. --}}
                    @if($units->count() > 1)
                        <option value="">Semua Unit Usaha</option>
                    @endif
                    @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="stockFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Status Stok</option>
                    <option value="normal">Stok Aman</option>
                    <option value="low">Stok Menipis</option>
                    <option value="out">Stok Habis</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-slate-700/60 text-xs">
            <div class="flex items-center gap-2">
                <select wire:model.live="categoryFilter" class="px-3 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none text-xs cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFilters" class="px-3.5 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-[3px] transition-all cursor-pointer">
                Reset Filter
            </button>
        </div>
    </div>

    {{-- ================= BULK ACTION BAR ================= --}}
    @if(count($selectedRows) > 0)
        <div class="mb-3 p-3.5 bg-neutral-900 text-white rounded-md shadow-md flex flex-col sm:flex-row items-center justify-between gap-3 text-xs animate-in fade-in duration-150">
            {{-- Counter --}}
            <div class="flex items-center gap-2">
                <span class="font-bold text-red-400">{{ count($selectedRows) }}</span>
                <span class="text-neutral-300">item produk dipilih</span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                {{-- Tombol Export Terpilih --}}
                <button type="button" 
                        wire:click="exportSelected" 
                        class="px-3 py-1.5 bg-neutral-800 hover:bg-neutral-700 text-white border border-neutral-700 rounded font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Export Terpilih</span>
                </button>

                {{-- Tombol Hapus Masal --}}
                <button type="button" 
                        x-on:click.prevent="$store.confirmDialog.open({
                            message: 'Apakah Anda yakin ingin menghapus {{ count($selectedRows) }} produk yang dipilih?',
                            confirmText: 'Ya, Hapus',
                            onConfirm: () => $wire.deleteSelected()
                        })"
                        class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Hapus Terpilih</span>
                </button>

                {{-- Tombol Batal Pilihan --}}
                <button type="button" 
                        wire:click="deselectAll" 
                        class="px-2.5 py-1.5 text-xs font-medium text-neutral-400 hover:text-white transition-colors cursor-pointer">
                    Batal
                </button>
            </div>
        </div>
    @endif

    {{-- Products Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Produk & Kode</th>
                        <th class="px-4 py-3.5">Unit Usaha & Kategori</th>
                        <th class="px-4 py-3.5 text-right">Harga Beli (HPP)</th>
                        <th class="px-4 py-3.5 text-right">Harga Jual</th>
                        <th class="px-4 py-3.5 text-center">Sisa Stok</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($products as $p)
                        @php
                            $minStock = $p->min_stock ?? 10;
                            $status = $p->stock <= 0 ? 'out' : ($p->stock <= $minStock ? 'low' : 'normal');
                        @endphp
                        <tr wire:key="prod-{{ $p->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $p->id }}" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">
                                    {{ $p->name }}
                                </div>
                                <div class="text-[11px] font-mono text-neutral-400">
                                    {{ $p->code ?? 'KODE-'.$p->id }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-xs">
                                <div class="font-medium text-neutral-700 dark:text-neutral-300">
                                    {{ $p->unit->name ?? '-' }}
                                </div>
                                <div class="text-[11px] text-neutral-400">
                                    {{ $p->category->name ?? 'Umum' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono text-xs text-neutral-500 dark:text-neutral-400">
                                Rp {{ number_format($p->purchase_price ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-xs text-neutral-900 dark:text-white">
                                Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center font-mono font-bold text-xs">
                                <span class="{{ $status === 'out' ? 'text-rose-600 dark:text-rose-400' : ($status === 'low' ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-800 dark:text-neutral-200') }}">
                                    {{ number_format($p->stock) }} {{ $p->unit_type ?? 'pcs' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($status === 'out')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800">Habis</span>
                                @elseif($status === 'low')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">Menipis</span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">Tersedia</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Restock --}}
                                    <button wire:click="openStockModal({{ $p->id }})" class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-md transition-all" title="Restock / Adjust Stok">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                    </button>

                                    {{-- Tombol Edit --}}
                                    <button wire:click="editProduct({{ $p->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all" title="Edit Produk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                                            message: 'Yakin ingin menghapus produk ini?',
                                            confirmText: 'Ya, Hapus',
                                            onConfirm: () => { $wire.set('selectedRows', ['{{ $p->id }}']); $wire.deleteSelected(); }
                                        })" class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all" title="Hapus Produk">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Tidak ada produk yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-xs text-neutral-500 dark:text-neutral-400">
                <div class="flex items-center gap-2">
                    <span>Tampilkan</span>
                    <select wire:model.live="perPage" class="py-1 px-2 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 text-neutral-700 dark:text-neutral-300 font-medium cursor-pointer">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>data</span>
                </div>

                @if($products->total() > 0)
                    <span class="hidden sm:inline-block text-neutral-300 dark:text-slate-700">|</span>
                    <div class="hidden sm:block">
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $products->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $products->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $products->total() }}</span> total produk
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto flex justify-end">
                {{ $products->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- ================= MODAL PRODUK (TAMBAH & EDIT) ================= --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Produk' : 'Tambah Produk Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $isEditing ? 'Perbarui informasi dan penentuan harga inventaris produk.' : 'Tambahkan barang dagangan baru ke dalam katalog inventaris unit usaha.' }}
                        </p>
                    </div>
                    <button wire:click="closeCreateModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="saveProduct" class="p-6 space-y-4">
                    
                    {{-- Row 1: Nama Produk & Kode --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="form_name" placeholder="Contoh: Kertas A4 80gr"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_name') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Kode Produk / SKU <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" 
                                wire:model="form_code" 
                                placeholder="Scan barcode / ketik kode..." 
                                class="w-full text-xs rounded-md border-neutral-300 dark:border-slate-700 dark:bg-slate-900 focus:ring-red-500 focus:border-red-500">
                            
                            <button type="button" 
                                    wire:click="generateProductCode" 
                                    class="px-3 py-1.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-neutral-700 dark:text-neutral-200 text-xs font-medium rounded-md whitespace-nowrap transition-colors">
                                Generate Kode
                            </button>
                        </div>
                        @error('form_code') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                    </div>

                    {{-- Row 2: Unit Usaha & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                            <select wire:model.live="form_unit_id" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                @if($units->count() > 1)
                                    <option value="">-- Pilih Unit Usaha --</option>
                                @endif
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('form_unit_id') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">
                                    Kategori Produk <span class="text-red-500">*</span>
                                </label>
                                <button type="button" wire:click="openCategoryModal" class="text-[11px] font-bold text-blue-900 hover:text-blue-950 dark:text-red-400 flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    <span>Tambah Kategori</span>
                                </button>
                            </div>

                            <select wire:key="select-prod-category-{{ $form_unit_id }}"
                                    wire:model="form_category_id" 
                                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($formCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('form_category_id') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 3: Harga Beli (HPP) & Harga Jual --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Harga Beli / HPP (Rp)</label>
                            <input type="number" wire:model="form_purchase_price" placeholder="0" min="0"
                                class="w-full px-3.5 py-2 text-xs font-bold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_purchase_price') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="form_selling_price" placeholder="0" min="0"
                                class="w-full px-3.5 py-2 text-xs font-bold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_selling_price') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 4: Stok Awal, Min Stok, Satuan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jumlah Stok <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="form_stock" placeholder="0" min="0"
                                class="w-full px-3.5 py-2 text-xs font-semibold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_stock') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Batas Minimum Stok</label>
                            <input type="number" wire:model="form_min_stock" placeholder="5" min="0"
                                class="w-full px-3.5 py-2 text-xs font-semibold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_min_stock') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Satuan Unit</label>
                            <input type="text" wire:model="form_unit_type" placeholder="pcs, rim, box, kg..."
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('form_unit_type') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi Produk</label>
                        <textarea wire:model="form_description" rows="2" placeholder="Masukkan rincian spesifikasi atau catatan barang..."
                            class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500"></textarea>
                        @error('form_description') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Gambar Produk --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">
                                Foto Produk (JPG/PNG, Max 2MB)
                            </label>
                            @if($isEditing)
                                <span class="text-[10px] text-neutral-400">*Kosongkan jika tidak ingin mengubah</span>
                            @endif
                        </div>

                        {{-- Preview foto lama saat mode edit --}}
                        @if($isEditing && $existingImage)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$existingImage) }}" alt="Foto produk saat ini"
                                    class="w-16 h-16 object-cover rounded-md border border-neutral-200 dark:border-slate-700">
                            </div>
                        @endif

                        {{-- Preview foto baru yang baru dipilih (belum disimpan) --}}
                        @if ($form_image)
                            <div class="mb-2">
                                <img src="{{ $form_image->temporaryUrl() }}" alt="Preview foto baru"
                                    class="w-16 h-16 object-cover rounded-md border border-neutral-200 dark:border-slate-700">
                            </div>
                        @endif

                        <input type="file" 
                            wire:model="form_image" 
                            accept="image/png, image/jpeg, image/jpg"
                            class="w-full text-xs border border-neutral-200 dark:border-slate-700 rounded-md p-1 bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100">
                        
                        <div wire:loading wire:target="form_image" class="text-xs text-amber-600 mt-1">
                            Mengunggah gambar...
                        </div>

                        @error('form_image') 
                            <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                            <span wire:loading.remove>{{ $isEditing ? 'Perbarui Produk' : 'Simpan Produk' }}</span>
                            <span wire:loading>{{ $isEditing ? 'Memperbarui...' : 'Menyimpan...' }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- ================= MODAL KELOLA KATEGORI ================= --}}
    @if($showCategoryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-neutral-950/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-xl shadow-2xl border border-neutral-200 dark:border-slate-700 overflow-hidden">
                
                {{-- Modal Header --}}
                <div class="px-5 py-4 border-b border-neutral-100 dark:border-slate-700/80 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/40">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Kelola Kategori Produk</h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Tambah, ubah, atau hapus kategori barang dagangan</p>
                    </div>
                    <button type="button" wire:click="closeCategoryModal" class="p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-5">
                    {{-- Flash Notifications --}}
                    @if (session()->has('category_success'))
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-md text-xs">
                            {{ session('category_success') }}
                        </div>
                    @endif
                    @if (session()->has('category_error'))
                        <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-md text-xs">
                            {{ session('category_error') }}
                        </div>
                    @endif

                    {{-- Form Input / Edit Inline --}}
                    <form wire:submit="saveCategory" class="p-4 bg-neutral-50/80 dark:bg-slate-900/60 border border-neutral-200/80 dark:border-slate-700/80 rounded-xl space-y-4">
                        {{-- Header Form & Status Mode --}}
                        <div class="flex items-center justify-between pb-2 border-b border-neutral-200/60 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-[3px] {{ $isEditingCategory ? 'bg-amber-500' : 'bg-red-500' }}"></span>
                                <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                    {{ $isEditingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                                </span>
                            </div>

                            @if($isEditingCategory)
                                <button type="button" wire:click="resetCategoryForm" class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 px-2 py-0.5 rounded-md hover:bg-neutral-200/60 dark:hover:bg-slate-800 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Batal Edit
                                </button>
                            @endif
                        </div>

                        {{-- Body Grid Input --}}
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                            {{-- Dropdown Unit Usaha --}}
                            <div class="sm:col-span-5 space-y-1">
                                <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300">
                                    Unit Usaha <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model="category_unit_id" class="w-full h-9 text-xs rounded-lg border-neutral-300 dark:border-slate-700 dark:bg-slate-800 text-neutral-800 dark:text-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all">
                                    @if($units->count() > 1)
                                        <option value="">-- Pilih Unit --</option>
                                    @endif
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_unit_id') <span class="text-[10px] text-rose-500 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            {{-- Input Nama Kategori --}}
                            <div class="sm:col-span-5 space-y-1">
                                <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300">
                                    Nama Kategori <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" wire:model="category_name" placeholder="Misal: Minuman, Alat Tulis..." class="w-full h-9 text-xs rounded-lg border-neutral-300 dark:border-slate-700 dark:bg-slate-800 px-2 text-neutral-800 dark:text-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 placeholder:text-neutral-400 dark:placeholder:text-neutral-500 transition-all">
                                @error('category_name') <span class="text-[10px] text-rose-500 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            {{-- Tombol Submit --}}
                            <div class="sm:col-span-2 space-y-1">
                                {{-- Spacer transparan untuk menyamakan posisi tombol dengan elemen input di layar desktop --}}
                                <label class="hidden sm:block text-[11px] opacity-0 select-none">Submit</label>
                                <button type="submit" wire:loading.attr="disabled" class="w-full h-9 inline-flex items-center justify-center gap-1.5 bg-blue-900 hover:bg-blue-950 active:bg-red-800 text-white text-xs font-semibold rounded-lg transition-all shadow-sm cursor-pointer disabled:opacity-50">
                                    <span wire:loading.remove wire:target="saveCategory">
                                        {{ $isEditingCategory ? 'Update' : 'Simpan' }}
                                    </span>
                                    <span wire:loading wire:target="saveCategory" class="inline-flex items-center gap-1.5">
                                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- Tabel List Kategori --}}
                    <div class="border border-neutral-200 dark:border-slate-700 rounded-lg overflow-hidden">
                        <div class="max-h-60 overflow-y-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-neutral-100 dark:bg-slate-900/80 text-neutral-500 dark:text-neutral-400 uppercase tracking-wider font-semibold sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2.5">Kategori</th>
                                        <th class="px-4 py-2.5">Unit Usaha</th>
                                        <th class="px-4 py-2.5 text-center">Total Produk</th>
                                        <th class="px-4 py-2.5 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                                    {{--
                                        PERBAIKAN: sebelumnya baris ini query LANGSUNG di blade
                                        (\App\Models\Category::with('unit')->withCount('products')->latest()->get())
                                        tanpa filter apa pun -- jadi tabel ini SELALU menampilkan
                                        kategori dari SEMUA unit usaha, walau halaman ini dibuka
                                        oleh admin unit (mis. TEFA) yang seharusnya cuma boleh
                                        melihat kategori miliknya sendiri. Variabel `$categories`
                                        dari komponen sudah benar (dikirim ter-scope oleh
                                        Unit\Inventory\Index::render() untuk admin unit, dan berisi
                                        SEMUA kategori untuk Master Admin), tapi tabel di modal ini
                                        tidak memakainya sama sekali -- jadi filternya percuma.

                                        Fix: pakai `$units` (variabel yang SUDAH konsisten di-scope
                                        di seluruh halaman ini -- 1 unit untuk admin unit/Master
                                        Admin yang sedang memantau, SEMUA unit untuk Master Admin
                                        di halamannya sendiri) untuk membatasi query ini juga,
                                        supaya satu sumber kebenaran yang sama dipakai di semua
                                        tempat pada halaman ini.
                                    --}}
                                    @php
                                        $categoriesTableData = \App\Models\Category::with('unit')
                                            ->withCount('products')
                                            ->whereIn('unit_id', $units->pluck('id'))
                                            ->latest()
                                            ->get();
                                    @endphp
                                    @forelse($categoriesTableData as $cat)
                                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="px-4 py-2.5 font-semibold text-neutral-800 dark:text-neutral-200">
                                                {{ $cat->name }}
                                            </td>
                                            <td class="px-4 py-2.5 text-neutral-500 dark:text-neutral-400">
                                                {{ $cat->unit->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                <span class="px-2 py-0.5 text-[10px] font-mono rounded-[3px] bg-neutral-100 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300">
                                                    {{ $cat->products_count }} item
                                                </span>
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button" wire:click="editCategory({{ $cat->id }})" class="p-1 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded transition-colors" title="Edit">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    </button>
                                                    <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                                                            message: 'Apakah Anda yakin ingin menghapus kategori \'{{ $cat->name }}\'?',
                                                            confirmText: 'Ya, Hapus',
                                                            onConfirm: () => $wire.deleteCategory({{ $cat->id }})
                                                        })" class="p-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded transition-colors" title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-neutral-400 text-xs">Belum ada kategori terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 bg-neutral-50/50 dark:bg-slate-900/40 border-t border-neutral-100 dark:border-slate-700 flex justify-end">
                    <button type="button" wire:click="closeCategoryModal" class="px-4 py-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-md hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= MODAL PENYESUAIAN STOK ================= --}}
    @if($showStockModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-neutral-950/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-xl shadow-2xl border border-neutral-200 dark:border-slate-700 overflow-hidden">
                
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-neutral-100 dark:border-slate-700/80 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/40">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Restock / Penyesuaian Stok</h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                            {{ $selectedProduct->name ?? '' }} 
                            (Stok saat ini: <span class="font-bold text-neutral-800 dark:text-neutral-200">{{ $selectedProduct->stock ?? 0 }}</span>)
                        </p>
                    </div>
                    <button type="button" wire:click="closeStockModal" class="p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Form --}}
                <form wire:submit="saveStock" class="p-5 space-y-4">
                    {{-- Jenis Transaksi --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">
                            Aksi Stok <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex flex-col items-center justify-center py-2 px-1 border rounded-lg cursor-pointer transition-all text-xs font-semibold {{ $stock_type === 'add' ? 'border-emerald-500 bg-emerald-50/70 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-slate-700/50' }}">
                                <input type="radio" wire:model.live="stock_type" value="add" class="sr-only">
                                <span>+ Tambah</span>
                                <span class="text-[9px] font-normal opacity-75">Restock</span>
                            </label>
                            <label class="flex flex-col items-center justify-center py-2 px-1 border rounded-lg cursor-pointer transition-all text-xs font-semibold {{ $stock_type === 'subtract' ? 'border-rose-500 bg-rose-50/70 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 ring-2 ring-rose-500/20' : 'border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-slate-700/50' }}">
                                <input type="radio" wire:model.live="stock_type" value="subtract" class="sr-only">
                                <span>- Kurangi</span>
                                <span class="text-[9px] font-normal opacity-75">Laku</span>
                            </label>
                            <label class="flex flex-col items-center justify-center py-2 px-1 border rounded-lg cursor-pointer transition-all text-xs font-semibold {{ $stock_type === 'set' ? 'border-amber-500 bg-amber-50/70 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 ring-2 ring-amber-500/20' : 'border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-slate-700/50' }}">
                                <input type="radio" wire:model.live="stock_type" value="set" class="sr-only">
                                <span>= Stock Opname</span>
                                <span class="text-[9px] font-normal opacity-75">Set Total</span>
                            </label>
                        </div>
                    </div>

                    {{-- Jumlah --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 mb-1">
                            Jumlah Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" wire:model="stock_quantity" min="1" autofocus placeholder="Masukkan jumlah unit..." class="w-full h-9 text-xs rounded-lg border-neutral-300 dark:border-slate-700 dark:bg-slate-900 text-neutral-800 dark:text-white focus:ring-2 px-2 focus:ring-red-500/20 focus:border-red-500 transition-all">
                        @error('stock_quantity') <span class="text-[10px] text-rose-500 block font-medium mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Catatan / Keterangan --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 mb-1">
                            Catatan / Alasan (Opsional)
                        </label>
                        <textarea wire:model="stock_note" rows="2" placeholder="Misal: Penambahan dari supplier A / Kadaluarsa / Hasil opname bulanan..." class="w-full text-xs rounded-lg border-neutral-300 dark:border-slate-700 dark:bg-slate-900 text-neutral-800 dark:text-white focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all p-2.5"></textarea>
                        @error('stock_note') <span class="text-[10px] text-rose-500 block font-medium mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Footer Action --}}
                    <div class="pt-3 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2">
                        <button type="button" wire:click="closeStockModal" class="px-4 h-9 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 h-9 bg-blue-900 hover:bg-blue-950 text-white text-xs font-semibold rounded-lg transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 whitespace-nowrap shrink-0">
                            <span wire:loading.remove wire:target="saveStock">Simpan Stok</span>
                            <span wire:loading wire:target="saveStock" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <span>Menyimpan...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    
    {{-- Modal Import Produk Massal --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/60 backdrop-blur-sm p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl border border-neutral-200/80 dark:border-slate-700 shadow-2xl overflow-hidden">
                
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-neutral-100 dark:border-slate-700/70 flex items-center justify-between bg-neutral-50/60 dark:bg-slate-900/40">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-100">Import Produk Massal</h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">Tambah atau perbarui stok & master produk sekaligus.</p>
                    </div>
                    <button type="button" wire:click="closeImportModal" class="p-1.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-lg hover:bg-neutral-100 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="importProducts" class="p-5 space-y-4 text-xs">
                    
                    {{-- Petunjuk Pengisian Produk --}}
                    <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl p-3 text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold text-xs">Petunjuk Pengisian Berkas:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-700 dark:text-amber-400">
                            <li>Unduh template terlebih dahulu untuk menyesuaikan struktur kolom.</li>
                            <li>Pastikan <code class="font-bold">unit_id</code> dan <code class="font-bold">category_id</code> diisi sesuai ID master.</li>
                            <li>Kode produk (<code class="font-bold">code</code>) harus unik untuk tiap unit usaha.</li>
                        </ul>
                    </div>

                    {{-- Unduh Template --}}
                    <div>
                        <button type="button" wire:click="downloadTemplate" class="w-full py-2.5 px-3 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            <span>Unduh Template Produk (.CSV)</span>
                        </button>
                    </div>

                    {{-- Unggah Berkas --}}
                    <div class="space-y-1.5">
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300">Unggah Berkas CSV / Excel</label>
                        <input type="file" wire:model="importFile" accept=".csv, .xlsx, .xls" class="block w-full text-xs text-neutral-600 dark:text-neutral-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-neutral-100 dark:file:bg-slate-700 file:text-neutral-700 dark:file:text-neutral-200 hover:file:bg-neutral-200 dark:hover:file:bg-slate-600 transition-all border border-neutral-300 dark:border-slate-700 rounded-lg dark:bg-slate-900 p-1">
                        
                        <div wire:loading wire:target="importFile" class="text-amber-600 dark:text-amber-400 text-[11px] mt-1 font-medium">Membaca file...</div>
                        @error('importFile') <span class="text-rose-500 text-[10px] font-medium mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-3 border-t border-neutral-100 dark:border-slate-700/80 grid grid-cols-2 gap-2.5 sm:flex sm:justify-end">
                        <button type="button" wire:click="closeImportModal" class="w-full sm:w-28 h-9 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-lg hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="w-full sm:w-36 h-9 bg-blue-900 hover:bg-blue-950 active:bg-red-800 text-white text-xs font-semibold rounded-lg transition-all shadow-sm inline-flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 whitespace-nowrap shrink-0">
                            <span wire:loading.remove wire:target="importProducts">Import Data</span>
                            <span wire:loading wire:target="importProducts" class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                <svg class="animate-spin h-3.5 w-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Detail Error Import Excel --}}
    @if($showErrorModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 animate-fadeIn">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-neutral-200 dark:border-slate-700">
            
            {{-- Header Modal --}}
            <div class="flex items-start justify-between border-b border-neutral-100 dark:border-slate-700/80 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Import File Dibatalkan</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Ditemukan data yang tidak sesuai pada baris dan kolom berikut:</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showErrorModal', false)" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Tabel Rincian Lokasi Error --}}
            <div class="max-h-64 overflow-y-auto border border-neutral-200 dark:border-slate-700 rounded-xl mb-5">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-neutral-50 dark:bg-slate-900 text-neutral-600 dark:text-neutral-400 font-semibold sticky top-0 z-10 border-b dark:border-slate-700">
                        <tr>
                            <th class="p-3 text-center w-20">Baris</th>
                            <th class="p-3">Nama Kolom</th>
                            <th class="p-3">Input Pengguna</th>
                            <th class="p-3">Keterangan Masalah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800 text-neutral-700 dark:text-neutral-300">
                        @foreach($importErrors as $err)
                            <tr class="hover:bg-rose-50/40 dark:hover:bg-rose-950/20 transition-colors">
                                <td class="p-3 text-center font-bold text-rose-600 dark:text-rose-400 bg-rose-50/30 dark:bg-rose-950/10">
                                    Baris {{ $err['row'] }}
                                </td>
                                <td class="p-3 font-semibold text-neutral-800 dark:text-neutral-200">
                                    {{ $err['column'] }}
                                </td>
                                <td class="p-3">
                                    <code class="px-2 py-0.5 rounded bg-neutral-100 dark:bg-slate-700 text-neutral-800 dark:text-neutral-200 font-mono text-[11px]">
                                        {{ $err['value'] }}
                                    </code>
                                </td>
                                <td class="p-3 text-rose-600 dark:text-rose-400 font-medium">
                                    {{ $err['messages'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-neutral-400">Total item bermasalah: <strong>{{ count($importErrors) }}</strong></span>
                <button type="button" 
                        wire:click="$set('showErrorModal', false)" 
                        class="px-4 py-2 text-xs font-semibold text-white bg-neutral-900 hover:bg-neutral-800 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-all shadow-sm">
                    Perbaiki File & Coba Lagi
                </button>
            </div>
        </div>
    </div>
    @endif
</div>