<div class="max-w-[1500px] mx-auto space-y-5 px-4 py-4 sm:px-6 font-sans text-neutral-800 dark:text-neutral-100">
    <livewire:page-tour tour="inventory.index" />
    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 p-4 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div class="min-w-0">
            <h1 class="text-sm font-bold tracking-tight text-neutral-900 dark:text-white">Manajemen Inventaris Produk</h1>
            <p class="text-[11px] tracking-tight text-neutral-400 mt-0.5 truncate">
                Kelola data produk, stok, harga, dan kategori pada katalog unit usaha.
            </p>
        </div>

        {{-- Tombol Aksi Cepat: dipaksa satu baris (nowrap), scroll horizontal kalau ruangnya sempit --}}
        <div class="flex flex-nowrap items-center gap-2 overflow-x-auto shrink-0 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <x-tour-replay tour="inventory.index" />

            {{-- Tombol Export --}}
            <button type="button" wire:click="exportProducts" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-[#0d3b74] dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all cursor-pointer shrink-0 whitespace-nowrap">
                <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" stroke-width="2" />
                <span>Export Excel</span>
            </button>

            {{-- Tombol Kelola Kategori --}}
            <button type="button" wire:click="openCategoryModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-50 dark:hover:bg-slate-700 transition-all cursor-pointer shadow-sm shadow-black/[0.02] shrink-0 whitespace-nowrap">
                <x-heroicon-o-tag class="w-3.5 h-3.5 text-neutral-500 dark:text-neutral-400" />
                <span>Kelola Kategori</span>
            </button>

            {{-- Tombol Import Excel --}}
            <button type="button" wire:click="openImportModal" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-[#0d3b74] dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all cursor-pointer shrink-0 whitespace-nowrap">
                <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5" stroke-width="2" />
                <span>Import Excel</span>
            </button>

            {{-- Tombol Tambah Produk --}}
            <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-[11px] font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer shrink-0 whitespace-nowrap">
                <x-heroicon-o-plus class="w-3.5 h-3.5" stroke-width="2.5" />
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    {{-- Metric / Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Produk</p>
                <x-heroicon-o-archive-box stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-sm font-bold text-neutral-900 dark:text-white tracking-tight">{{ number_format($totalProductsCount ?? 0) }} Item</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Unit Stok</p>
                <x-heroicon-o-cube stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-sm font-bold text-neutral-900 dark:text-white tracking-tight font-mono">{{ number_format($totalStockSum ?? 0) }} Pcs</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-amber-500">Stok Menipis / Habis</p>
                <x-heroicon-o-exclamation-triangle stroke-width="1.5" class="w-4 h-4 text-amber-300 dark:text-amber-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-amber-600 dark:text-amber-400 tracking-tight font-mono">{{ number_format($lowStockCount ?? 0) }} Produk</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-emerald-500">Est. Nilai Inventaris</p>
                <x-heroicon-o-banknotes stroke-width="1.5" class="w-4 h-4 text-emerald-300 dark:text-emerald-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">Rp {{ number_format($totalInventoryValue ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 space-y-2 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Kode Produk, Nama Produk, atau Deskripsi..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
            </div>

            <div>
                @if(! empty($lockedUnitId) && $units->count() === 1)
                    <x-locked-field :value="$units->first()->name" class="px-3.5 py-2 text-xs font-semibold" />
                @else
                <select wire:model.live="unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
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
                @endif
            </div>

            <div>
                <select wire:model.live="stockFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Status Stok</option>
                    <option value="normal">Stok Aman</option>
                    <option value="low">Stok Menipis</option>
                    <option value="out">Stok Habis</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-slate-700/60 text-xs">
            <div class="flex items-center gap-2">
                <select wire:model.live="categoryFilter" class="px-3 py-1.5 border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none text-xs cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="resetFilters" class="px-3.5 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-sm transition-all cursor-pointer">
                Reset Filter
            </button>
        </div>
    </div>

    {{-- ================= BULK ACTION BAR ================= --}}
    @if(count($selectedRows) > 0)
        <div class="mb-3 p-3.5 bg-neutral-900 text-white rounded-sm shadow-md flex flex-col sm:flex-row items-center justify-between gap-3 text-xs animate-in fade-in duration-150">
            {{-- Counter --}}
            <div class="flex items-center gap-2">
                <span class="font-bold text-blue-400">{{ count($selectedRows) }}</span>
                <span class="text-neutral-300">item produk dipilih</span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                {{-- Tombol Export Terpilih --}}
                <button type="button" 
                        wire:click="exportSelected" 
                        class="px-3 py-1.5 bg-neutral-800 hover:bg-neutral-700 text-white border border-neutral-700 rounded-sm font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5 text-neutral-400" />
                    <span>Export Terpilih</span>
                </button>

                {{-- Tombol Hapus Masal --}}
                <button type="button" 
                        x-on:click.prevent="$store.confirmDialog.open({
                            message: 'Apakah Anda yakin ingin menghapus {{ count($selectedRows) }} produk yang dipilih?',
                            confirmText: 'Ya, Hapus',
                            onConfirm: () => $wire.deleteSelected()
                        })"
                        class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-sm font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <x-heroicon-o-trash class="w-3.5 h-3.5" />
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

    {{-- ================= DAFTAR PRODUK ================= --}}
    <section class="space-y-3 pt-1">

        {{-- Products Table --}}
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-neutral-300 text-blue-900 focus:ring-blue-500/20 cursor-pointer">
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
                            $isRugi = ($p->purchase_price ?? 0) > 0 && $p->purchase_price > $p->selling_price;
                        @endphp
                        <tr wire:key="prod-{{ $p->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $p->id }}" class="rounded border-neutral-300 text-blue-900 focus:ring-blue-500/20 cursor-pointer">
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
                                @if($isRugi)
                                    <span title="HPP lebih besar dari harga jual (rugi)" class="ml-1 inline-flex items-center gap-0.5 align-middle px-1.5 py-0.5 text-[9px] font-bold tracking-wide rounded-sm bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">
                                        <x-heroicon-s-exclamation-triangle class="w-2.5 h-2.5" />
                                        Rugi
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center font-mono font-bold text-xs">
                                <span class="{{ $status === 'out' ? 'text-rose-600 dark:text-rose-400' : ($status === 'low' ? 'text-amber-600 dark:text-amber-400' : 'text-neutral-800 dark:text-neutral-200') }}">
                                    {{ number_format($p->stock) }} {{ $p->unit_type ?? 'pcs' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                @if($status === 'out')
                                    <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">Habis</span>
                                @elseif($status === 'low')
                                    <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">Menipis</span>
                                @else
                                    <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-sm bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">Tersedia</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Restock --}}
                                    <button wire:click="openStockModal({{ $p->id }})" class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-sm transition-all" title="Restock / Adjust Stok">
                                        <x-heroicon-o-plus class="w-4 h-4" />                                    </button>
                                    {{-- Tombol Edit --}}
                                    <button wire:click="editProduct({{ $p->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-sm transition-all" title="Edit Produk">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                                            message: 'Yakin ingin menghapus produk ini?',
                                            confirmText: 'Ya, Hapus',
                                            onConfirm: () => { $wire.set('selectedRows', ['{{ $p->id }}']); $wire.deleteSelected(); }
                                        })" class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-all" title="Hapus Produk">
                                        <x-heroicon-o-trash class="w-4 h-4" />
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
                    <select wire:model.live="perPage" class="py-1 px-2 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-neutral-700 dark:text-neutral-300 font-medium cursor-pointer">
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
    </section>

    {{-- ================= MODAL PRODUK (TAMBAH & EDIT) ================= --}}
    {{-- Tutorial form (di luar modal agar posisinya tidak terpengaruh scroll/blur modal) --}}
    @if($showCreateModal)
        <livewire:page-tour tour="inventory.form" wire:key="tour-inventory.form" />
    @endif

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
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
                <form wire:submit.prevent="saveProduct" class="p-6">
                    <x-form-tabs tab1-label="Informasi Dasar" tab2-label="Harga, Stok & Media" cancel="closeCreateModal" compact>
                    <x-slot:tab1>
                    {{-- Row 1: Nama Produk & Kode --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="form_name" placeholder="Contoh: Kertas A4 80gr"
                                class="@error('form_name') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500" aria-invalid="@error('form_name') true @else false @enderror" aria-required="true">
                            @error('form_name') <x-form-error :message="$message" :field="'form_name'" /> @enderror
                        </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Kode Produk / SKU <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" 
                                wire:model="form_code" 
                                placeholder="Scan barcode / ketik kode..." 
                                class="@error('form_code') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full text-xs rounded-sm border-neutral-300 dark:border-slate-700 dark:bg-slate-900 focus:ring-blue-500 focus:border-blue-500" aria-invalid="@error('form_code') true @else false @enderror" aria-required="true">
                            
                            <button type="button" 
                                    wire:click="generateProductCode" 
                                    class="px-3 py-1.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-neutral-700 dark:text-neutral-200 text-xs font-medium rounded-sm whitespace-nowrap transition-colors">
                                Generate Kode
                            </button>
                        </div>
                        @error('form_code') <x-form-error :message="$message" :field="'form_code'" /> @enderror
                    </div>
                    </div>

                    {{-- Row 2: Unit Usaha & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                            @if(! empty($lockedUnitId) && $units->count() === 1)
                                <x-locked-field :value="$units->first()->name" class="px-3.5 py-2 text-xs font-medium" />
                            @else
                            <select wire:model.live="form_unit_id" class="@error('form_unit_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500" aria-invalid="@error('form_unit_id') true @else false @enderror" aria-required="true">
                                @if($units->count() > 1)
                                    <option value="">-- Pilih Unit Usaha --</option>
                                @endif
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @endif
                            @error('form_unit_id') <x-form-error :message="$message" :field="'form_unit_id'" /> @enderror
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">
                                    Kategori Produk <span class="text-red-500">*</span>
                                </label>
                                <button type="button" wire:click="openCategoryModal" class="text-[11px] font-bold text-blue-900 hover:text-blue-950 dark:text-blue-400 flex items-center gap-1 cursor-pointer">
                                    <x-heroicon-o-plus class="w-3 h-3" stroke-width="2.5" />
                                    <span>Tambah Kategori</span>
                                </button>
                            </div>

                            <select wire:key="select-prod-category-{{ $form_unit_id }}"
                                    wire:model="form_category_id" 
                                    class="@error('form_category_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500" aria-invalid="@error('form_category_id') true @else false @enderror" aria-required="true">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($formCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('form_category_id') <x-form-error :message="$message" :field="'form_category_id'" /> @enderror
                        </div>
                    </div>

                    </x-slot:tab1>
                    <x-slot:tab2>
                    {{-- Row 3: Harga Beli (HPP) & Harga Jual
                         x-data lokal di bawah ini HANYA untuk menghitung status "rugi"
                         secara instan di browser (tanpa request ke server), murni
                         tambahan tampilan -- tidak menyentuh wire:model/validasi yang
                         sudah ada, jadi alur simpan produk tetap persis seperti semula. --}}
                    <div x-data="{
                            hpp: {{ (float) ($form_purchase_price ?? 0) }},
                            jual: {{ (float) ($form_selling_price ?? 0) }},
                            get isRugi() { return this.hpp > 0 && this.jual > 0 && this.hpp > this.jual }
                         }">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Harga Beli / HPP (Rp)</label>
                                <input type="text" inputmode="decimal" wire:model="form_purchase_price" oninput="onlyDecimal(event)" placeholder="0"
                                    x-on:input="hpp = parseFloat($event.target.value) || 0"
                                    class="@error('form_purchase_price') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-bold border rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                    :class="isRugi ? 'border-amber-400 dark:border-amber-600' : 'border-neutral-200 dark:border-slate-700'" aria-invalid="@error('form_purchase_price') true @else false @enderror">
                                @error('form_purchase_price') <x-form-error :message="$message" :field="'form_purchase_price'" /> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                <input type="text" inputmode="decimal" wire:model="form_selling_price" oninput="onlyDecimal(event)" placeholder="0"
                                    x-on:input="jual = parseFloat($event.target.value) || 0"
                                    class="@error('form_selling_price') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-bold border rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                    :class="isRugi ? 'border-amber-400 dark:border-amber-600' : 'border-neutral-200 dark:border-slate-700'" aria-invalid="@error('form_selling_price') true @else false @enderror" aria-required="true">
                                @error('form_selling_price') <x-form-error :message="$message" :field="'form_selling_price'" /> @enderror
                            </div>
                        </div>

                        {{-- Peringatan (bukan blokir): harga jual masih boleh disimpan
                             lebih rendah dari HPP, hanya diberi tahu agar tidak terjadi
                             tanpa disadari (misal salah ketik / lupa update harga jual). --}}
                        <div x-show="isRugi" x-cloak x-transition
                             class="mt-2 flex items-start gap-2 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-sm p-2.5">
                            <x-heroicon-s-exclamation-triangle class="w-4 h-4 shrink-0 mt-0.5 text-amber-500 dark:text-amber-400" />
                            <p class="text-[11px] leading-relaxed text-amber-800 dark:text-amber-300">
                                <span class="font-bold">Peringatan:</span> Harga jual lebih rendah dari Harga Beli/HPP, produk ini berpotensi <span class="font-bold">rugi</span> saat terjual. Data tetap bisa disimpan &mdash; pastikan ini memang disengaja (misalnya produk promo/bonus).
                            </p>
                        </div>
                    </div>

                    {{-- Row 4: Stok Awal, Min Stok, Satuan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jumlah Stok <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="numeric" wire:model="form_stock" oninput="onlyDigits(event)" placeholder="0"
                                class="@error('form_stock') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-semibold border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400" aria-invalid="@error('form_stock') true @else false @enderror" aria-required="true">
                            @error('form_stock') <x-form-error :message="$message" :field="'form_stock'" /> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Batas Minimum Stok</label>
                            <input type="text" inputmode="numeric" wire:model="form_min_stock" oninput="onlyDigits(event)" placeholder="5"
                                class="@error('form_min_stock') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-semibold border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400" aria-invalid="@error('form_min_stock') true @else false @enderror">
                            @error('form_min_stock') <x-form-error :message="$message" :field="'form_min_stock'" /> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Satuan Unit</label>
                            <input type="text" wire:model="form_unit_type" placeholder="pcs, rim, box, kg..."
                                class="@error('form_unit_type') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500" aria-invalid="@error('form_unit_type') true @else false @enderror">
                            @error('form_unit_type') <x-form-error :message="$message" :field="'form_unit_type'" /> @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi Produk</label>
                        <textarea wire:model="form_description" rows="2" placeholder="Masukkan rincian spesifikasi atau catatan barang..."
                            class="@error('form_description') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500" aria-invalid="@error('form_description') true @else false @enderror"></textarea>
                        @error('form_description') <x-form-error :message="$message" :field="'form_description'" /> @enderror
                    </div>

                    {{-- Gambar Produk --}}
                    <div
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true; progress = 0"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-cancel="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
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
                                    class="w-16 h-16 object-cover rounded-sm border border-neutral-200 dark:border-slate-700">
                            </div>
                        @endif

                        {{-- Preview foto baru yang baru dipilih (belum disimpan) --}}
                        @if ($form_image)
                            <div class="mb-2">
                                <img src="{{ $form_image->temporaryUrl() }}" alt="Preview foto baru"
                                    class="w-16 h-16 object-cover rounded-sm border border-neutral-200 dark:border-slate-700">
                            </div>
                        @endif

                        <input type="file" 
                            wire:model="form_image" 
                            accept="image/png, image/jpeg, image/jpg"
                            class="@error('form_image') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full text-xs border border-neutral-200 dark:border-slate-700 rounded-sm p-1 bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100" aria-invalid="@error('form_image') true @else false @enderror">
                        
                        <x-upload-progress label="Mengunggah gambar..." />

                        @error('form_image') 
                            <x-form-error :message="$message" :field="'form_image'" /> 
                        @enderror
                    </div>
                    </x-slot:tab2>
                    <x-slot:submit>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm">
                            <span wire:loading.remove>{{ $isEditing ? 'Perbarui Produk' : 'Simpan Produk' }}</span>
                            <span wire:loading>{{ $isEditing ? 'Memperbarui...' : 'Menyimpan...' }}</span>
                        </button>
                    </x-slot:submit>
                    </x-form-tabs>

                </form>
            </div>
        </div>
    @endif

    {{-- ================= MODAL KELOLA KATEGORI ================= --}}
    @if($showCategoryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-neutral-950/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-800 w-full max-w-4xl rounded-sm shadow-2xl border border-neutral-200 dark:border-slate-700 overflow-hidden">

                {{-- Modal Header --}}
                <div class="px-5 py-3 border-b border-neutral-100 dark:border-slate-700/80 flex items-center justify-between gap-3 bg-neutral-50/50 dark:bg-slate-900/40">
                    <div class="flex items-baseline gap-2 min-w-0">
                        <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Kelola Kategori Produk</h3>
                        <p class="hidden sm:block text-[11px] text-neutral-400 truncate">Kategori barang dagangan</p>
                    </div>
                    <button type="button" wire:click="closeCategoryModal" class="p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-sm transition-colors cursor-pointer" aria-label="Tutup">
                        <x-heroicon-o-x-mark class="w-5 h-5" stroke-width="2" />
                    </button>
                </div>

                <div class="p-4">
                    {{-- Flash Notifications (toast) --}}
                    @if (session()->has('category_success'))
                        <div wire:key="toast-category-success-{{ md5(session('category_success')) }}" x-data x-init="$store.toast.push('success', @js(session('category_success')))"></div>
                    @endif
                    @if (session()->has('category_error'))
                        <div wire:key="toast-category-error-{{ md5(session('category_error')) }}" x-data x-init="$store.toast.push('error', @js(session('category_error')))"></div>
                    @endif

                    {{-- Layar lebar: form di kiri, tabel di kanan (sejajar, tidak bertumpuk) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-[18rem_minmax(0,1fr)] gap-4 items-start">

                        {{-- Form Input / Edit --}}
                        <form wire:submit="saveCategory" class="p-3 bg-neutral-50/80 dark:bg-slate-900/60 border border-neutral-200/80 dark:border-slate-700/80 rounded-sm space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                    <span class="w-2 h-2 rounded-sm {{ $isEditingCategory ? 'bg-amber-500' : 'bg-blue-900' }}"></span>
                                    {{ $isEditingCategory ? 'Edit Kategori' : 'Tambah Kategori' }}
                                </span>

                                @if($isEditingCategory)
                                    <button type="button" wire:click="resetCategoryForm" class="inline-flex items-center gap-1 text-[11px] font-medium text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 px-2 py-0.5 rounded-sm hover:bg-neutral-200/60 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                                        <x-heroicon-o-x-mark class="w-3 h-3" stroke-width="2" />
                                        Batal
                                    </button>
                                @endif
                            </div>

                            {{-- Unit Usaha (read-only kalau scope satu unit) --}}
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300">Unit Usaha <span class="text-rose-500">*</span></label>
                                @if(! empty($lockedUnitId) && $units->count() === 1)
                                    <x-locked-field :value="$units->first()->name" class="h-9 px-3 text-xs" />
                                @else
                                    <select wire:model="category_unit_id" class="@error('category_unit_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full h-9 text-xs rounded-sm border-neutral-300 dark:border-slate-700 dark:bg-slate-800 text-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" aria-invalid="@error('category_unit_id') true @else false @enderror">
                                        @if($units->count() > 1)
                                            <option value="">-- Pilih Unit --</option>
                                        @endif
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('category_unit_id') <x-form-error :message="$message" :field="'category_unit_id'" /> @enderror
                            </div>

                            {{-- Nama Kategori --}}
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300">Nama Kategori <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="category_name" placeholder="Contoh: Minuman, Alat Tulis" class="@error('category_name') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full h-9 text-xs rounded-sm border-neutral-300 dark:border-slate-700 dark:bg-slate-800 px-3 text-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 placeholder:text-neutral-400 dark:placeholder:text-neutral-500 transition-all" aria-invalid="@error('category_name') true @else false @enderror">
                                @error('category_name') <x-form-error :message="$message" :field="'category_name'" /> @enderror
                            </div>

                            {{-- Tombol Submit --}}
                            <button type="submit" wire:loading.attr="disabled" class="w-full h-9 inline-flex items-center justify-center gap-1.5 bg-blue-900 hover:bg-blue-950 active:bg-blue-950 text-white text-xs font-semibold rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer disabled:opacity-50">
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
                        </form>

                        {{-- Tabel List Kategori (tinggi dibatasi ke tinggi layar, baru scroll kalau memang banyak) --}}
                        <div class="border border-neutral-200 dark:border-slate-700 rounded-sm overflow-hidden min-w-0">
                            <div class="max-h-72 lg:max-h-[calc(100vh-12rem)] overflow-y-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-neutral-50 dark:bg-slate-900 text-neutral-400 uppercase tracking-wider text-[10px] font-semibold sticky top-0 border-b border-neutral-100 dark:border-slate-700">
                                        <tr>
                                            <th class="px-3 py-2">Kategori</th>
                                            <th class="px-3 py-2">Unit Usaha</th>
                                            <th class="px-3 py-2 text-center" title="Jumlah produk dalam kategori ini">Produk</th>
                                            <th class="px-3 py-2 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                                        {{--
                                            Sumber data dibatasi lewat `$units` (variabel yang sudah konsisten
                                            di-scope di seluruh halaman ini: 1 unit untuk Admin Unit / Master
                                            yang sedang memantau, semua unit untuk Master di halamannya
                                            sendiri), supaya admin unit tidak melihat kategori unit lain.
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
                                                <td class="px-3 py-1.5 font-semibold text-neutral-800 dark:text-neutral-200">
                                                    <span class="block max-w-[11rem] truncate" title="{{ $cat->name }}">{{ $cat->name }}</span>
                                                </td>
                                                <td class="px-3 py-1.5 text-neutral-500 dark:text-neutral-400">
                                                    <span class="block max-w-[9rem] truncate" title="{{ $cat->unit->name ?? '-' }}">{{ $cat->unit->name ?? '-' }}</span>
                                                </td>
                                                <td class="px-3 py-1.5 text-center font-mono text-[11px] text-neutral-500 dark:text-neutral-400">
                                                    {{ $cat->products_count }}
                                                </td>
                                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                                    <div class="flex items-center justify-center gap-0.5">
                                                        <button type="button" wire:click="editCategory({{ $cat->id }})" class="p-1 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-sm transition-colors cursor-pointer" title="Edit">
                                                            <x-heroicon-o-pencil-square class="w-4 h-4" stroke-width="2" />
                                                        </button>
                                                        <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                                                                message: 'Apakah Anda yakin ingin menghapus kategori \'{{ $cat->name }}\'?',
                                                                confirmText: 'Ya, Hapus',
                                                                onConfirm: () => $wire.deleteCategory({{ $cat->id }})
                                                            })" class="p-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-colors cursor-pointer" title="Hapus">
                                                            <x-heroicon-o-trash class="w-4 h-4" stroke-width="2" />
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
                </div>

                {{-- Footer --}}
                <div class="px-4 py-2.5 bg-neutral-50/50 dark:bg-slate-900/40 border-t border-neutral-100 dark:border-slate-700 flex justify-end">
                    <button type="button" wire:click="closeCategoryModal" class="px-4 py-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= MODAL PENYESUAIAN STOK ================= --}}
    @if($showStockModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-neutral-950/60 backdrop-blur-sm flex items-center justify-center p-4 animate-in fade-in duration-150">
            <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-sm shadow-2xl border border-neutral-200 dark:border-slate-700 overflow-hidden">
                
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-neutral-100 dark:border-slate-700/80 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/40">
                    <div>
                        <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Restock / Penyesuaian Stok</h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                            {{ $selectedProduct->name ?? '' }} 
                            (Stok saat ini: <span class="font-bold text-neutral-800 dark:text-neutral-200">{{ $selectedProduct->stock ?? 0 }}</span>)
                        </p>
                    </div>
                    <button type="button" wire:click="closeStockModal" class="p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-sm transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" stroke-width="2" />
                    </button>
                </div>

                {{-- Form --}}
                <form wire:submit="saveStock" class="p-5 space-y-4">
                    {{-- Jenis Transaksi --}}
                    <div>
                        <label class="flex items-center gap-1 text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">
                            Aksi Stok <span class="text-rose-500">*</span>
                            <x-help-tip text="Tambah/Kurangi menyesuaikan stok dari jumlah yang tercatat sekarang. Stock Opname berbeda — dipakai saat menghitung ulang barang secara fisik: masukkan jumlah TOTAL hasil hitung, dan sistem akan menggantikan (bukan menjumlahkan) angka stok yang lama." />
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex flex-col items-center justify-center py-2 px-1 border rounded-sm cursor-pointer transition-all text-xs font-semibold {{ $stock_type === 'add' ? 'border-blue-500 bg-blue-50/70 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 ring-2 ring-blue-500/20' : 'border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-slate-700/50' }}">
                                <input type="radio" wire:model.live="stock_type" value="add" class="@error('stock_type') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror sr-only" aria-invalid="@error('stock_type') true @else false @enderror">
                                <span>+ Tambah</span>
                                <span class="text-[9px] font-normal opacity-75">Restock</span>
                            </label>
                            <label class="flex flex-col items-center justify-center py-2 px-1 border rounded-sm cursor-pointer transition-all text-xs font-semibold {{ $stock_type === 'subtract' ? 'border-rose-500 bg-rose-50/70 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 ring-2 ring-rose-500/20' : 'border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-slate-700/50' }}">
                                <input type="radio" wire:model.live="stock_type" value="subtract" class="sr-only">
                                <span>- Kurangi</span>
                                <span class="text-[9px] font-normal opacity-75">Laku</span>
                            </label>
                            <label class="flex flex-col items-center justify-center py-2 px-1 border rounded-sm cursor-pointer transition-all text-xs font-semibold {{ $stock_type === 'set' ? 'border-sky-500 bg-sky-50/70 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300 ring-2 ring-sky-500/20' : 'border-neutral-200 dark:border-slate-700 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-slate-700/50' }}">
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
                        <input type="text" inputmode="numeric" wire:model="stock_quantity" oninput="onlyDigits(event)" autofocus placeholder="Masukkan jumlah unit..." class="@error('stock_quantity') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full h-9 text-xs rounded-sm border-neutral-300 dark:border-slate-700 dark:bg-slate-900 text-neutral-800 dark:text-white focus:ring-2 px-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" aria-invalid="@error('stock_quantity') true @else false @enderror">
                        @error('stock_quantity') <x-form-error :message="$message" :field="'stock_quantity'" /> @enderror
                    </div>

                    {{-- Catatan / Keterangan --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-600 dark:text-neutral-300 mb-1">
                            Catatan / Alasan (opsional)
                        </label>
                        <textarea wire:model="stock_note" rows="2" placeholder="Contoh: Penambahan dari supplier A / Kadaluarsa / Hasil opname bulanan..." class="@error('stock_note') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full text-xs rounded-sm border-neutral-300 dark:border-slate-700 dark:bg-slate-900 text-neutral-800 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all p-2.5" aria-invalid="@error('stock_note') true @else false @enderror"></textarea>
                        @error('stock_note') <x-form-error :message="$message" :field="'stock_note'" /> @enderror
                    </div>

                    {{-- Footer Action --}}
                    <div class="pt-3 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2">
                        <button type="button" wire:click="closeStockModal" class="px-4 h-9 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 h-9 bg-blue-900 hover:bg-blue-950 text-white text-xs font-semibold rounded-sm transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 whitespace-nowrap shrink-0">
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
            <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-sm border border-neutral-200/80 dark:border-slate-700 shadow-2xl overflow-hidden">
                
                {{-- Header --}}
                <div class="px-5 py-4 border-b border-neutral-100 dark:border-slate-700/70 flex items-center justify-between bg-neutral-50/60 dark:bg-slate-900/40">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-800 dark:text-neutral-100">Import Produk Massal</h3>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">Tambah atau perbarui stok & master produk sekaligus.</p>
                    </div>
                    <button type="button" wire:click="closeImportModal" class="p-1.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 rounded-sm hover:bg-neutral-100 dark:hover:bg-slate-700 transition-colors">
                        <x-heroicon-o-x-mark class="w-4 h-4" stroke-width="2" />
                    </button>
                </div>

                <form wire:submit.prevent="importProducts" class="p-5 space-y-4 text-xs">
                    
                    {{-- Petunjuk Pengisian Produk --}}
                    <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-sm p-3 text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold text-xs">Petunjuk Pengisian Berkas:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-700 dark:text-amber-400">
                            <li>Unduh template terlebih dahulu untuk menyesuaikan struktur kolom.</li>
                            <li>Pastikan <code class="font-bold">unit_id</code> dan <code class="font-bold">category_id</code> diisi sesuai ID master.</li>
                            <li>Kode produk (<code class="font-bold">code</code>) harus unik untuk tiap unit usaha.</li>
                        </ul>
                    </div>

                    {{-- Unduh Template --}}
                    <div>
                        <button type="button" wire:click="downloadTemplate" class="w-full py-2.5 px-3 text-xs font-semibold text-[#0d3b74] dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4 shrink-0" stroke-width="2" />
                            <span>Unduh Template Produk (.CSV)</span>
                        </button>
                    </div>

                    {{-- Unggah Berkas --}}
                    <div
                        class="space-y-1.5"
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true; progress = 0"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-cancel="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300">Unggah Berkas CSV / Excel <span class="text-red-500">*</span></label>
                        <input type="file" wire:model="importFile" accept=".csv, .xlsx, .xls" class="@error('importFile') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror block w-full text-xs text-neutral-600 dark:text-neutral-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-neutral-100 dark:file:bg-slate-700 file:text-neutral-700 dark:file:text-neutral-200 hover:file:bg-neutral-200 dark:hover:file:bg-slate-600 transition-all border border-neutral-300 dark:border-slate-700 rounded-sm dark:bg-slate-900 p-1" aria-invalid="@error('importFile') true @else false @enderror">

                        <x-upload-progress label="Membaca file..." />
                        @error('importFile') <x-form-error :message="$message" :field="'importFile'" /> @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="pt-3 border-t border-neutral-100 dark:border-slate-700/80 grid grid-cols-2 gap-2.5 sm:flex sm:justify-end">
                        <button type="button" wire:click="closeImportModal" class="w-full sm:w-28 h-9 text-xs font-semibold text-neutral-700 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-50 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="w-full sm:w-36 h-9 bg-blue-900 hover:bg-blue-950 active:bg-blue-950 text-white text-xs font-semibold rounded-sm transition-all shadow-sm inline-flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 whitespace-nowrap shrink-0">
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
        <div class="bg-white dark:bg-slate-800 rounded-sm max-w-2xl w-full p-6 shadow-2xl border border-neutral-200 dark:border-slate-700">
            
            {{-- Header Modal --}}
            <div class="flex items-start justify-between border-b border-neutral-100 dark:border-slate-700/80 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-sm bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 shrink-0">
                        <x-heroicon-o-exclamation-circle class="w-6 h-6" stroke-width="2" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Import File Dibatalkan</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Ditemukan data yang tidak sesuai pada baris dan kolom berikut:</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showErrorModal', false)" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-white">
                    <x-heroicon-o-x-mark class="w-5 h-5" stroke-width="2" />
                </button>
            </div>

            {{-- Tabel Rincian Lokasi Error --}}
            <div class="max-h-64 overflow-y-auto border border-neutral-200 dark:border-slate-700 rounded-sm mb-5">
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
                                    <code class="px-2 py-0.5 rounded-sm bg-neutral-100 dark:bg-slate-700 text-neutral-800 dark:text-neutral-200 font-mono text-[11px]">
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
                        class="px-4 py-2 text-xs font-semibold text-white bg-neutral-900 hover:bg-neutral-800 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-sm transition-all shadow-sm">
                    Perbaiki File & Coba Lagi
                </button>
            </div>
        </div>
    </div>
    @endif
</div>