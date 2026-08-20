<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Aset & Nilai</p>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                </span>
            </div>
            <div class="mt-4 flex items-baseline justify-between">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $totalAssets }}</p>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
            </div>
            <p class="mt-2 text-[11px] text-neutral-400">Total unit & estimasi nilai</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Tersedia</p>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $availableCount }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Siap untuk digunakan</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Sedang Digunakan</p>
                <span class="p-2 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-sky-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-sky-600 dark:text-sky-400 tracking-tight">{{ $assignedCount }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Teralokasi ke pengguna</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Dalam Perbaikan</p>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17L4.268 8.018a3.1 3.1 0 010-4.385l.178-.178a3.1 3.1 0 014.385 0l7.152 7.152M13.916 12.14l-3.03 2.496"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-amber-600 dark:text-amber-400 tracking-tight">{{ $maintenanceCount }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Dalam proses pemeliharaan</p>
        </div>
    </div>

    {{-- Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-2">
        <div>
            <h1 class="text-lg font-bold text-neutral-900 dark:text-white tracking-tight">Manajemen Aset</h1>
            <p class="text-xs text-neutral-400">Kelola, lacak nilai, dan pantau status inventaris aset Anda.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openModal" class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-full transition-all flex items-center gap-2 shadow-sm shadow-red-600/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Tambah Aset</span>
            </button>
        </div>
    </div>

    {{-- Filter Bar Toolbar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari tag aset, nama, s/n, user..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
            </div>
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="available">Tersedia</option>
                    <option value="assigned">Digunakan</option>
                    <option value="maintenance">Perbaikan</option>
                    <option value="retired">Afkir / Nonaktif</option>
                </select>
            </div>
            <div>
                <select wire:model.live="categoryFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="Elektronik">Elektronik</option>
                    <option value="Kendaraan">Kendaraan</option>
                    <option value="Mebel & Perabot">Mebel & Perabot</option>
                    <option value="Mesin & Peralatan">Mesin & Peralatan</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    @if(count($selectedRows) > 0)
        <div class="flex items-center justify-between bg-neutral-900 text-white p-3.5 rounded-md shadow-md text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-red-400">{{ count($selectedRows) }}</span> aset dipilih
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="bulkUpdateStatus('available')" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 rounded font-semibold transition-colors cursor-pointer">
                    Set Tersedia
                </button>
                <button wire:click="bulkUpdateStatus('maintenance')" class="px-3 py-1 bg-amber-600 hover:bg-amber-500 rounded font-semibold transition-colors cursor-pointer">
                    Set Perbaikan
                </button>
                <button wire:click="bulkDelete" onclick="confirm('Yakin hapus data terpilih?') || event.stopImmediatePropagation()" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 rounded font-semibold transition-colors cursor-pointer">
                    Hapus
                </button>
            </div>
        </div>
    @endif

    {{-- Table Asset --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Tag & Nama Aset</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Pengguna & Lokasi</th>
                        <th class="px-4 py-3.5 text-right">Nilai / Tgl Beli</th>
                        <th class="px-4 py-3.5 text-center">Kondisi</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($assets as $asset)
                        <tr wire:key="asset-{{ $asset->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $asset->id }}" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>

                            {{-- Tag & Nama Aset --}}
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">{{ $asset->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-mono text-red-600 dark:text-red-400 font-semibold bg-red-50 dark:bg-red-950/40 px-1.5 py-0.2 rounded border border-red-100 dark:border-red-900/50">{{ $asset->asset_tag }}</span>
                                    @if($asset->serial_number)
                                        <span class="text-[11px] font-mono text-neutral-400">S/N: {{ $asset->serial_number }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-600 dark:text-neutral-300">
                                {{ $asset->category }}
                            </td>

                            {{-- Pengguna & Lokasi --}}
                            <td class="px-4 py-3.5 text-xs">
                                <div class="font-medium text-neutral-800 dark:text-neutral-200">{{ $asset->assigned_to ?: '-' }}</div>
                                <div class="text-[11px] text-neutral-400">{{ $asset->location ?: '-' }}</div>
                            </td>

                            {{-- Nilai / Tgl Beli --}}
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="font-bold text-xs text-neutral-900 dark:text-white">
                                    Rp {{ number_format($asset->purchase_cost ?: 0, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] text-neutral-400">
                                    {{ $asset->purchase_date ? date('d M Y', strtotime($asset->purchase_date)) : '-' }}
                                </div>
                            </td>

                            {{-- Kondisi Badge --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($asset->condition === 'good')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 rounded-full border border-emerald-200/60 dark:border-emerald-800">Bagus</span>
                                @elseif($asset->condition === 'fair')
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/50 rounded-full border border-amber-200/60 dark:border-amber-800">Cukup</span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50 rounded-full border border-rose-200/60 dark:border-rose-800">Rusak</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @switch($asset->status)
                                    @case('available')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">Tersedia</span>
                                        @break
                                    @case('assigned')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">Digunakan</span>
                                        @break
                                    @case('maintenance')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">Perbaikan</span>
                                        @break
                                    @default
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-neutral-100 dark:bg-slate-700 text-neutral-500 dark:text-neutral-400 border border-neutral-200 dark:border-slate-600">Afkir</span>
                                @endswitch
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $asset->id }})" class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all cursor-pointer" title="Edit Aset">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </button>
                                    <button onclick="confirm('Yakin hapus aset ini?') || event.stopImmediatePropagation()" wire:click="delete({{ $asset->id }})" class="p-1.5 text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all cursor-pointer" title="Hapus Aset">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada data aset yang ditemukan.
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
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>data</span>
                </div>
                @if($assets->total() > 0)
                    <span class="hidden sm:inline-block text-neutral-300 dark:text-slate-700">|</span>
                    <div class="hidden sm:block">
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $assets->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $assets->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $assets->total() }}</span> aset
                    </div>
                @endif
            </div>
            <div class="w-full md:w-auto flex justify-end">
                {{ $assets->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- Form Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                            {{ $editingId ? 'Edit Data Aset' : 'Tambah Aset Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $editingId ? 'Perbarui informasi inventaris aset.' : 'Tambahkan unit aset baru ke dalam inventaris.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tag / Kode Aset <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="asset_tag" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('asset_tag') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Aset <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" placeholder="misal: Laptop MacBook Pro" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('name') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select wire:model="category" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="Elektronik">Elektronik</option>
                                <option value="Kendaraan">Kendaraan</option>
                                <option value="Mebel & Perabot">Mebel & Perabot</option>
                                <option value="Mesin & Peralatan">Mesin & Peralatan</option>
                            </select>
                            @error('category') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nomor Seri (S/N)</label>
                            <input type="text" wire:model="serial_number" placeholder="misal: C02XL123456" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Pembelian</label>
                            <input type="date" wire:model="purchase_date" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Harga Beli (Rp)</label>
                            <input type="number" wire:model="purchase_cost" placeholder="0" class="w-full px-3.5 py-2 text-xs font-bold border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Status Aset <span class="text-red-500">*</span></label>
                            <select wire:model="status" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="available">Tersedia</option>
                                <option value="assigned">Digunakan</option>
                                <option value="maintenance">Perbaikan</option>
                                <option value="retired">Afkir / Nonaktif</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kondisi <span class="text-red-500">*</span></label>
                            <select wire:model="condition" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="good">Bagus</option>
                                <option value="fair">Cukup</option>
                                <option value="damaged">Rusak</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Penanggung Jawab / User</label>
                            <input type="text" wire:model="assigned_to" placeholder="misal: Budi Santoso" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Lokasi Penempatan</label>
                            <input type="text" wire:model="location" placeholder="misal: Ruang IT Lt. 2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Catatan Tambahan</label>
                        <textarea wire:model="notes" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="Keterangan garansi, kelengkapan, dll..."></textarea>
                    </div>

                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-md transition-all shadow-sm cursor-pointer">
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Aset' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>