<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- ============ SECTION: EXPORT DATA (TABLE LAYOUT) ============ --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Export Data</h2>
            <span class="text-[11px] text-neutral-400">Centang beberapa jenis data untuk export sekaligus</span>
        </div>

        {{-- Bulk Action Bar --}}
        @if(count($bulkSelected) > 0)
            <div class="flex items-center justify-between bg-neutral-900 dark:bg-slate-950 text-white p-3.5 rounded-md shadow-md text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-red-400">{{ count($bulkSelected) }}</span> jenis data dipilih
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="bulkExport" wire:loading.attr="disabled" wire:target="bulkExport"
                        class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-500 rounded font-semibold transition-colors flex items-center gap-1.5 disabled:opacity-60">
                        <svg wire:loading.remove wire:target="bulkExport" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        <span wire:loading.remove wire:target="bulkExport">Export Terpilih (.zip)</span>
                        <span wire:loading wire:target="bulkExport">Memproses...</span>
                    </button>
                    <button wire:click="$set('bulkSelected', [])" class="px-3 py-1.5 bg-neutral-700 hover:bg-neutral-600 rounded font-semibold transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        @endif

        {{-- Tabel Opsi Export --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                        <tr>
                            <th class="p-4 w-10 text-center">
                                <input type="checkbox"
                                    onclick="this.checked
                                        ? @this.set('bulkSelected', ['trx','prod','asset','stock','fin','authlog','auditlog','dash'])
                                        : @this.set('bulkSelected', [])"
                                    @checked(count($bulkSelected) === $totalExportTypes)
                                    class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </th>
                            <th class="px-4 py-3.5">Jenis Data</th>
                            <th class="px-4 py-3.5">Deskripsi</th>
                            <th class="px-4 py-3.5 text-center">Filter</th>
                            <th class="px-4 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">

                        {{-- ROW: Transaksi --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="trx" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Data Transaksi</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Rekap pemasukan &amp; pengeluaran seluruh unit usaha
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('trx')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'trx' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportTransactions" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>.xlsx</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'trx')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Cari (Ref/Deskripsi)</label>
                                            <input type="text" wire:model="trx_search" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Unit Usaha</label>
                                            <select wire:model="trx_unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Unit</option>
                                                @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Tipe</label>
                                            <select wire:model="trx_typeFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Tipe</option>
                                                <option value="income">Pemasukan</option>
                                                <option value="expense">Pengeluaran</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Status</label>
                                            <select wire:model="trx_statusFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Status</option>
                                                <option value="completed">Selesai</option>
                                                <option value="pending">Menunggu</option>
                                                <option value="cancelled">Dibatalkan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Dari Tanggal</label>
                                            <input type="date" wire:model="trx_startDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Sampai Tanggal</label>
                                            <input type="date" wire:model="trx_endDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Inventaris --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="prod" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Data Inventaris</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Data produk, stok, dan estimasi nilai inventaris
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('prod')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'prod' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportProducts" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>.xlsx</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'prod')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Cari Produk</label>
                                            <input type="text" wire:model="prod_search" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Unit Usaha</label>
                                            <select wire:model="prod_unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Unit</option>
                                                @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Kategori</label>
                                            <select wire:model="prod_categoryFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Kategori</option>
                                                @foreach($productCategories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Status Stok</label>
                                            <select wire:model="prod_stockFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Status</option>
                                                <option value="normal">Stok Aman</option>
                                                <option value="low">Stok Menipis</option>
                                                <option value="out">Stok Habis</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Aset --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="asset" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Data Aset</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Data aset, status, kondisi, dan penanggung jawab
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('asset')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'asset' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportAssets" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>.xlsx</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'asset')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Cari Aset</label>
                                            <input type="text" wire:model="asset_search" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Status</label>
                                            <select wire:model="asset_statusFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Status</option>
                                                <option value="available">Tersedia</option>
                                                <option value="assigned">Ditugaskan</option>
                                                <option value="maintenance">Perawatan</option>
                                                <option value="retired">Pensiun</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Kategori</label>
                                            <select wire:model="asset_categoryFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Kategori</option>
                                                @foreach($assetCategories as $cat)<option value="{{ $cat }}">{{ $cat }}</option>@endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Stok --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="stock" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Data Stok Barang</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Status stok saat ini beserta estimasi nilai persediaan
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('stock')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'stock' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportStockReport" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Stok</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'stock')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Unit Usaha</label>
                                            <select wire:model="stock_unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Unit</option>
                                                @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Kategori</label>
                                            <select wire:model="stock_categoryFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Kategori</option>
                                                @foreach($productCategories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Status Stok</label>
                                            <select wire:model="stock_stockFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Status</option>
                                                <option value="normal">Stok Aman</option>
                                                <option value="low">Stok Menipis</option>
                                                <option value="out">Stok Habis</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Keuangan --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="fin" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Data Keuangan</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Ringkasan arus kas &amp; rincian per kategori (2 sheet)
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('fin')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'fin' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportFinanceReport" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-full hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Keuangan</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'fin')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Unit Usaha</label>
                                            <select wire:model="fin_unitFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Unit</option>
                                                @foreach($units as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Dari Tanggal</label>
                                            <input type="date" wire:model="fin_startDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Sampai Tanggal</label>
                                            <input type="date" wire:model="fin_endDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Log Aktivitas Login --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="authlog" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Log Aktivitas Login</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Riwayat login, logout, dan aktivitas keamanan akun
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('authlog')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'authlog' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportAuthLogs" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>.xlsx</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'authlog')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Cari (Identifier/Deskripsi)</label>
                                            <input type="text" wire:model="authlog_search" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Jenis Event</label>
                                            <select wire:model="authlog_eventFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Event</option>
                                                @foreach($authLogEvents as $event)
                                                    <option value="{{ $event }}">{{ ucwords(str_replace(['.', '_'], ' ', $event)) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Audit Log Sistem --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="auditlog" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Audit Log Sistem</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Rekam jejak perubahan data (dibuat, diubah, dihapus)
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('auditlog')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'auditlog' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportAuditLogs" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>.xlsx</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'auditlog')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Cari (Identifier/Deskripsi)</label>
                                            <input type="text" wire:model="auditlog_search" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Jenis Event</label>
                                            <select wire:model="auditlog_eventFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="">Semua Event</option>
                                                @foreach($auditLogEvents as $event)
                                                    <option value="{{ $event }}">{{ str_replace('_', ' ', $event) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Dari Tanggal</label>
                                            <input type="date" wire:model="auditlog_startDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                        </div>
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Sampai Tanggal</label>
                                            <input type="date" wire:model="auditlog_endDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        {{-- ROW: Dashboard Master Admin --}}
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center align-top">
                                <input type="checkbox" wire:model.live="bulkSelected" value="dash" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>
                            <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                <span class="font-semibold text-neutral-900 dark:text-white text-xs">Dashboard Master Admin</span>
                            </td>
                            <td class="px-4 py-3.5 align-top text-[11px] text-neutral-400">
                                Ringkasan, kontribusi omzet, unit, admin &amp; aktivitas (multi-sheet)
                            </td>
                            <td class="px-4 py-3.5 align-top text-center">
                                <button type="button" wire:click="togglePanel('dash')"
                                    class="px-3 py-1.5 text-[11px] font-semibold rounded-full transition-all {{ $openPanel === 'dash' ? 'bg-neutral-800 text-white dark:bg-slate-600' : 'text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600' }}">
                                    Filter
                                </button>
                            </td>
                            <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                <button wire:click="exportDashboardReport" wire:loading.attr="disabled"
                                    class="px-3.5 py-1.5 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-full hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>.xlsx</span>
                                </button>
                            </td>
                        </tr>
                        @if($openPanel === 'dash')
                            <tr class="bg-neutral-50/70 dark:bg-slate-900/50">
                                <td></td>
                                <td colspan="4" class="px-4 pb-4 pt-1">
                                    <div class="rounded-md p-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <label class="block font-medium text-neutral-400 mb-1">Periode Omzet</label>
                                            <select wire:model.live="dash_periodFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                                                <option value="today">Hari Ini</option>
                                                <option value="this_week">Minggu Ini</option>
                                                <option value="this_month">Bulan Ini</option>
                                                <option value="this_quarter">Kuartal Ini</option>
                                                <option value="last_month">Bulan Lalu</option>
                                                <option value="this_year">Tahun Ini</option>
                                                <option value="custom">Custom...</option>
                                            </select>
                                        </div>
                                        @if($dash_periodFilter === 'custom')
                                            <div>
                                                <label class="block font-medium text-neutral-400 mb-1">Dari Tanggal</label>
                                                <input type="date" wire:model="dash_startDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                            </div>
                                            <div>
                                                <label class="block font-medium text-neutral-400 mb-1">Sampai Tanggal</label>
                                                <input type="date" wire:model="dash_endDate" class="w-full px-3.5 py-2 text-xs border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-700 dark:text-neutral-300 focus:outline-none focus:border-red-400">
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============ SECTION: TEMPLATE IMPORT (TABLE LAYOUT) ============ --}}
    <div class="space-y-3">
        <h2 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Template Import</h2>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3.5">Jenis Template</th>
                            <th class="px-4 py-3.5">Deskripsi</th>
                            <th class="px-4 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-neutral-900 dark:text-white text-xs">
                                Template Data Transaksi
                            </td>
                            <td class="px-4 py-3.5 text-[11px] text-neutral-400">
                                Format kolom untuk import transaksi massal
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <button wire:click="downloadTransactionTemplate" class="px-3.5 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Unduh (.xlsx)</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-neutral-900 dark:text-white text-xs">
                                Template Data Produk
                            </td>
                            <td class="px-4 py-3.5 text-[11px] text-neutral-400">
                                Format kolom untuk import produk/inventaris
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <button wire:click="downloadProductTemplate" class="px-3.5 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Unduh (.xlsx)</span>
                                </button>
                            </td>
                        </tr>
                        <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-neutral-900 dark:text-white text-xs">
                                Template Data Aset
                            </td>
                            <td class="px-4 py-3.5 text-[11px] text-neutral-400">
                                Format kolom untuk import aset massal
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <button wire:click="downloadAssetTemplate" class="px-3.5 py-1.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 rounded-full transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    <span>Unduh (.xlsx)</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>