<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="p-4 rounded-sm bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300">&times;</button>
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Vendor & Supplier</h1>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">Kelola data vendor, supplier, instansi, dan mitra penyedia bisnis Anda.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button"
                    wire:click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Tambah Vendor/Supplier</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan Cepat --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Vendor & Supplier</p>
                <x-heroicon-o-building-office stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $totalVendors }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Berperan sebagai Supplier</p>
                <x-heroicon-o-truck stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-[#0d3b74] dark:text-blue-400 tracking-tight">{{ $totalSuppliers }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                {{-- Search Box --}}
                <div class="relative w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kontak, atau email..."
                           class="w-full pl-9 pr-3 py-2.5 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-all shadow-sm shadow-black/[0.02]">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-neutral-400 absolute left-3 top-3" />
                </div>

                {{-- Filter Tipe (Vendor / Supplier) --}}
                <select wire:model.live="filterType"
                        class="w-full sm:w-auto px-3.5 py-2.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Tipe</option>
                    <option value="vendor">Vendor</option>
                    <option value="supplier">Supplier</option>
                    <option value="both">Vendor & Supplier</option>
                </select>

                {{-- Filter Dropdown --}}
                <select wire:model.live="filterCategory"
                        class="w-full sm:w-auto px-3.5 py-2.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
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
        <div class="flex items-center justify-between bg-[#0d3b74] text-white p-3.5 rounded-sm shadow-sm shadow-blue-900/20 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-sky-300">{{ count($selectedRows) }}</span> vendor/supplier dipilih
            </div>
            <div class="flex items-center gap-2">
                <button type="button" x-on:click.prevent="$store.confirmDialog.open({
                        message: 'Yakin ingin menghapus vendor/supplier terpilih?',
                        confirmText: 'Ya, Hapus',
                        onConfirm: () => $wire.bulkDelete()
                    })" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 rounded-sm font-semibold transition-colors cursor-pointer">
                    Hapus Terpilih
                </button>
            </div>
        </div>
    @endif

    {{-- Vendors Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded-sm border-neutral-300 text-blue-900 focus:ring-blue-500/20 cursor-pointer">
                        </th>
                        <th class="px-5 py-3.5">Vendor / Supplier</th>
                        <th class="px-5 py-3.5 text-center">Tipe</th>
                        <th class="px-5 py-3.5">Kontak</th>
                        <th class="px-5 py-3.5">Website</th>
                        <th class="px-5 py-3.5">Periode Kontrak</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($vendors as $vendor)
                        @php
                            $categoryLabel = match($vendor->category) {
                                'perusahaan' => 'Perusahaan',
                                'pemerintah' => 'Pemerintah',
                                'individu' => 'Individu',
                                default => 'Lainnya',
                            };
                            $categoryDot = match($vendor->category) {
                                'perusahaan' => 'bg-blue-500',
                                'pemerintah' => 'bg-purple-500',
                                'individu' => 'bg-emerald-500',
                                default => 'bg-neutral-400',
                            };

                            $typeMap = [
                                'vendor'   => ['label' => 'Vendor', 'class' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border-blue-200/60 dark:border-blue-800'],
                                'supplier' => ['label' => 'Supplier', 'class' => 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400 border-purple-200/60 dark:border-purple-800'],
                                'both'     => ['label' => 'Vendor & Supplier', 'class' => 'bg-[#0d3b74] text-white border-[#0d3b74]'],
                            ];
                            $typeInfo = $typeMap[$vendor->type] ?? $typeMap['vendor'];

                            $contractStatus = null;
                            if ($vendor->contract_end_date) {
                                if ($vendor->contract_end_date->isPast()) {
                                    $contractStatus = ['dot' => 'bg-rose-500', 'text' => 'text-rose-500', 'label' => 'Berakhir'];
                                } elseif ($vendor->contract_end_date->diffInDays(now(), false) >= -30) {
                                    $contractStatus = ['dot' => 'bg-amber-500', 'text' => 'text-amber-600 dark:text-amber-400', 'label' => 'Segera berakhir'];
                                }
                            }
                        @endphp
                        <tr wire:key="vendor-{{ $vendor->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="p-4 text-center">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $vendor->id }}" class="rounded-sm border-neutral-300 text-blue-900 focus:ring-blue-500/20 cursor-pointer">
                            </td>

                            {{-- Vendor: Nama + Kategori + ID Number --}}
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">{{ $vendor->name }}</div>
                                <div class="flex items-center gap-1.5 mt-1 text-[11px] text-neutral-400">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $categoryDot }}"></span>
                                    <span>{{ $categoryLabel }}</span>
                                    @if($vendor->id_number)
                                        <span class="text-neutral-300 dark:text-slate-600">&middot;</span>
                                        <span class="font-mono">{{ $vendor->id_number }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Tipe: Vendor / Supplier / Keduanya --}}
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-sm border whitespace-nowrap {{ $typeInfo['class'] }}">
                                    {{ $typeInfo['label'] }}
                                </span>
                            </td>

                            {{-- Kontak: PIC + Email + Phone --}}
                            <td class="px-5 py-3.5 text-[12px] leading-relaxed">
                                @if($vendor->contact_name)
                                    <div class="font-medium text-neutral-700 dark:text-neutral-200">{{ $vendor->contact_name }}</div>
                                @endif
                                @if($vendor->email)
                                    <div class="text-neutral-500 dark:text-neutral-400">{{ $vendor->email }}</div>
                                @endif
                                @if($vendor->phone)
                                    <div class="text-neutral-500 dark:text-neutral-400">{{ $vendor->phone }}</div>
                                @endif
                                @if(!$vendor->contact_name && !$vendor->email && !$vendor->phone)
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>

                            {{-- Website --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-[12px]">
                                @if($vendor->website)
                                    <a href="{{ Str::startsWith($vendor->website, 'http') ? $vendor->website : 'https://'.$vendor->website }}"
                                       target="_blank"
                                       class="text-[#0d3b74] dark:text-sky-400 hover:underline font-medium">
                                        {{ Str::limit($vendor->website, 20) }}
                                    </a>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>

                            {{-- Periode Kontrak --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-[12px]">
                                @if($vendor->contract_start_date || $vendor->contract_end_date)
                                    <div class="text-neutral-600 dark:text-neutral-300">
                                        {{ $vendor->contract_start_date?->format('d M Y') ?? '-' }} &ndash; {{ $vendor->contract_end_date?->format('d M Y') ?? '-' }}
                                    </div>
                                    @if($contractStatus)
                                        <div class="flex items-center gap-1.5 mt-1 text-[11px] {{ $contractStatus['text'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $contractStatus['dot'] }}"></span>
                                            <span>{{ $contractStatus['label'] }}</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $vendor->id }})"
                                            title="Edit Vendor"
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-sm transition-all cursor-pointer">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button type="button"
                                            x-on:click.prevent="$store.confirmDialog.open({
                                                message: 'Yakin ingin menghapus vendor/supplier ini?',
                                                confirmText: 'Ya, Hapus',
                                                onConfirm: () => $wire.delete({{ $vendor->id }})
                                            })"
                                            title="Hapus Vendor/Supplier"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-all cursor-pointer">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Tidak ada data vendor/supplier yang sesuai dengan pencarian.
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
                    <select wire:model.live="perPage" class="py-1.5 px-2 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 text-neutral-700 dark:text-neutral-300 font-medium cursor-pointer">
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
                        Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $vendors->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $vendors->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $vendors->total() }}</span> total vendor/supplier
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $vendorId ? 'Edit Data Vendor/Supplier' : 'Tambah Vendor/Supplier Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            {{ $vendorId ? 'Perbarui informasi kontak dan profil mitra.' : 'Lengkapi formulir untuk mendaftarkan mitra vendor/supplier baru.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="save" class="p-6 space-y-4">

                    {{-- Row 1: Nama --}}
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Perusahaan / Vendor / Supplier</label>
                        <input type="text" wire:model="name"
                               class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                               placeholder="Contoh: PT Sumber Makmur">
                        @error('name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Row 2: Tipe & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Tipe Mitra</label>
                            <select wire:model="type"
                                    class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                                <option value="vendor">Vendor</option>
                                <option value="supplier">Supplier</option>
                                <option value="both">Vendor & Supplier</option>
                            </select>
                            @error('type') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Kategori</label>
                            <select wire:model="category"
                                    class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                                <option value="perusahaan">Perusahaan</option>
                                <option value="pemerintah">Pemerintah</option>
                                <option value="individu">Individu</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            @error('category') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Row 2: PIC & NPWP --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Kontak Utama (PIC)</label>
                            <input type="text" wire:model="contact_name"
                                   class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="Nama penanggung jawab">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">ID / NIK / NPWP</label>
                            <input type="text" wire:model="id_number"
                                   class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="Nomor Pajak / Identitas">
                        </div>
                    </div>

                    {{-- Row 3: Email & No Telp --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                            <input type="email" wire:model="email"
                                   class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="email@vendor.com">
                            @error('email') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon / WhatsApp</label>
                            <input type="text" wire:model="phone"
                                   class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="0812...">
                        </div>
                    </div>

                    {{-- Row: Periode Kontrak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Mulai Kontrak</label>
                            <input type="date" wire:model="contract_start_date"
                                class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            @error('contract_start_date') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Selesai Kontrak</label>
                            <input type="date" wire:model="contract_end_date"
                                class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            @error('contract_end_date') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Website --}}
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Website</label>
                        <input type="text" wire:model="website"
                               class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                               placeholder="https://vendor.com">
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Alamat Lengkap</label>
                        <textarea wire:model="address" rows="2"
                                  class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                  placeholder="Alamat kantor / tempat usaha..."></textarea>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 flex justify-end gap-2 border-t border-neutral-100 dark:border-slate-700">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2.5 border border-neutral-200 dark:border-slate-700 rounded-sm text-sm font-semibold hover:bg-neutral-50 dark:hover:bg-slate-700 dark:text-white transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-blue-900 text-white rounded-sm text-sm font-semibold hover:bg-blue-950 transition-colors shadow-sm shadow-blue-900/20 cursor-pointer">
                            <span wire:loading.remove>{{ $vendorId ? 'Perbarui Data' : 'Simpan Data' }}</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</div>