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
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Vendors</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Kelola data vendor, instansi, dan penyedia layanan bisnis Anda.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openModal" 
                    class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-full transition-all flex items-center gap-2 shadow-sm shadow-red-600/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Tambah Vendor</span>
            </button>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Vendor</p>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 dark:text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ number_format($totalVendors) }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Penyedia mitra terdaftar</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Status Operasional</p>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-4">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800 inline-block">
                    Aktif & Operasional
                </span>
            </div>
            <p class="mt-2 text-[11px] text-neutral-400">Siap digunakan transaksi</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Integrasi Sistem</p>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-base font-bold text-neutral-900 dark:text-white tracking-tight">Terhubung Pengeluaran</p>
            <p class="mt-2 text-[11px] text-neutral-400">Modul Keuangan & Aset</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                {{-- Search Box --}}
                <div class="relative w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kontak, atau email..." 
                           class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                </div>

                {{-- Filter Dropdown --}}
                <select wire:model.live="filterCategory" 
                        class="w-full sm:w-auto px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="perusahaan">Perusahaan</option>
                    <option value="pemerintah">Pemerintah</option>
                    <option value="individu">Individu</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Bulk Action Bar --}}
    @if(count($selectedRows) > 0)
        <div class="flex items-center justify-between bg-neutral-900 text-white p-3.5 rounded-md shadow-md text-xs mb-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-red-400">{{ count($selectedRows) }}</span> vendor dipilih
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="bulkDelete" onclick="confirm('Yakin ingin menghapus vendor terpilih?') || event.stopImmediatePropagation()" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 rounded font-semibold transition-colors cursor-pointer">
                    Hapus Terpilih
                </button>
            </div>
        </div>
    @endif

    {{-- Vendors Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                        </th>
                        <th class="px-4 py-3.5">Nama Vendor</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Kontak Utama</th>
                        <th class="px-4 py-3.5">Email & Telepon</th>
                        <th class="px-4 py-3.5">Website</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($vendors as $vendor)
                        <tr wire:key="vendor-{{ $vendor->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $vendor->id }}" class="rounded border-neutral-300 text-red-600 focus:ring-red-500/20 cursor-pointer">
                            </td>

                            {{-- Nama Vendor --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">{{ $vendor->name }}</div>
                                @if($vendor->id_number)
                                    <div class="text-[11px] font-mono text-neutral-400 mt-0.5">NPWP/ID: {{ $vendor->id_number }}</div>
                                @endif
                            </td>

                            {{-- Kategori Badge --}}
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @switch($vendor->category)
                                    @case('perusahaan')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800">Perusahaan</span>
                                        @break
                                    @case('pemerintah')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800">Pemerintah</span>
                                        @break
                                    @case('individu')
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800">Individu</span>
                                        @break
                                    @default
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-neutral-100 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300 border border-neutral-200 dark:border-slate-600">Lainnya</span>
                                @endswitch
                            </td>

                            {{-- Contact Person --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                {{ $vendor->contact_name ?: '-' }}
                            </td>

                            {{-- Email & Phone --}}
                            <td class="px-4 py-3.5 space-y-0.5 text-xs">
                                @if($vendor->email)
                                    <div class="flex items-center gap-1.5 text-neutral-600 dark:text-neutral-300">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                        <span>{{ $vendor->email }}</span>
                                    </div>
                                @endif
                                @if($vendor->phone)
                                    <div class="flex items-center gap-1.5 text-neutral-500 dark:text-neutral-400">
                                        <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.826-1.07-5.11-3.354-6.18-6.18l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                        <span>{{ $vendor->phone }}</span>
                                    </div>
                                @endif
                                @if(!$vendor->email && !$vendor->phone)
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>

                            {{-- Website --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                @if($vendor->website)
                                    <a href="{{ Str::startsWith($vendor->website, 'http') ? $vendor->website : 'https://'.$vendor->website }}" 
                                       target="_blank" 
                                       class="text-sky-600 dark:text-sky-400 hover:underline inline-flex items-center gap-1 font-medium">
                                        <span>{{ Str::limit($vendor->website, 18) }}</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $vendor->id }})" 
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all cursor-pointer" 
                                            title="Edit Vendor">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </button>
                                    <button onclick="confirm('Yakin ingin menghapus vendor ini?') || event.stopImmediatePropagation()" 
                                            wire:click="delete({{ $vendor->id }})" 
                                            class="p-1.5 text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all cursor-pointer" 
                                            title="Hapus Vendor">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Tidak ada data vendor yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination & Per Page Control --}}
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

                @if($vendors->total() > 0)
                    <span class="hidden sm:inline-block text-neutral-300 dark:text-slate-700">|</span>
                    <div class="hidden sm:block">
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $vendors->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $vendors->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $vendors->total() }}</span> total vendor
                    </div>
                @endif
            </div>

            <div class="w-full md:w-auto flex justify-end">
                {{ $vendors->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- Modal Form Tambah/Edit Vendor --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                            {{ $vendorId ? 'Edit Data Vendor' : 'Tambah Vendor Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $vendorId ? 'Perbarui informasi kontak dan profil vendor.' : 'Lengkapi formulir untuk mendaftarkan mitra vendor baru.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="save" class="p-6 space-y-4 text-xs">
                    
                    {{-- Row 1: Nama & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Perusahaan / Vendor <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" 
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                                   placeholder="Contoh: PT Sumber Makmur">
                            @error('name') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select wire:model="category" 
                                    class="w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500 cursor-pointer">
                                <option value="perusahaan">Perusahaan</option>
                                <option value="pemerintah">Pemerintah</option>
                                <option value="individu">Individu</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            @error('category') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 2: PIC & NPWP --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kontak Utama (PIC)</label>
                            <input type="text" wire:model="contact_name" 
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                                   placeholder="Nama penanggung jawab">
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">ID / NIK / NPWP</label>
                            <input type="text" wire:model="id_number" 
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                                   placeholder="Nomor Pajak / Identitas">
                        </div>
                    </div>

                    {{-- Row 3: Email & No Telp --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                            <input type="email" wire:model="email" 
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                                   placeholder="email@vendor.com">
                            @error('email') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon / WhatsApp</label>
                            <input type="text" wire:model="phone" 
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                                   placeholder="0812...">
                        </div>
                    </div>

                    {{-- Website --}}
                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Website</label>
                        <input type="text" wire:model="website" 
                               class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                               placeholder="https://vendor.com">
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Alamat Lengkap</label>
                        <textarea wire:model="address" rows="2" 
                                  class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" 
                                  placeholder="Alamat kantor / tempat usaha..."></textarea>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal" 
                                class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" 
                                class="px-5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-md transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>{{ $vendorId ? 'Perbarui Vendor' : 'Simpan Vendor' }}</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</div>