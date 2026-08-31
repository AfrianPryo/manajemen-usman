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
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Manajemen Pelanggan</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Kelola data dan riwayat kunjungan pelanggan di seluruh Unit Usaha.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button wire:click="openCreateModal"
                    class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Tambah Pelanggan</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan Cepat --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Total Pelanggan</p>
            <p class="text-2xl font-bold text-neutral-800 dark:text-neutral-100 tracking-tight mt-2">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Pelanggan Baru</p>
            <p class="text-2xl font-bold text-sky-600 dark:text-sky-400 tracking-tight mt-2">{{ $newCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">Member</p>
            <p class="text-2xl font-bold text-violet-600 dark:text-violet-400 tracking-tight mt-2">{{ $memberCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
            <p class="text-xs font-medium text-neutral-400">VIP</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 tracking-tight mt-2">{{ $vipCount }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="sm:col-span-2 md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, telepon, atau email..."
                       class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
            </div>

            <div>
                <select wire:model.live="unitFilter"
                        class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Unit Usaha</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="categoryFilter"
                        class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="baru">Baru</option>
                    <option value="reguler">Reguler</option>
                    <option value="member">Member</option>
                    <option value="vip">VIP</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabel Pelanggan --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Unit Usaha</th>
                        <th class="px-4 py-3">Kontak</th>
                        <th class="px-4 py-3 text-center">Kategori</th>
                        <th class="px-4 py-3 text-center">Kunjungan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($customers as $customer)
                        @php
                            $categoryMap = [
                                'baru'    => ['label' => 'Baru', 'class' => 'bg-sky-100 text-sky-700 border-sky-200'],
                                'reguler' => ['label' => 'Reguler', 'class' => 'bg-neutral-100 text-neutral-700 border-neutral-200'],
                                'member'  => ['label' => 'Member', 'class' => 'bg-violet-100 text-violet-700 border-violet-200'],
                                'vip'     => ['label' => 'VIP', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                            ];
                            $categoryInfo = $categoryMap[$customer->category] ?? $categoryMap['reguler'];
                        @endphp
                        <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-[13px] leading-tight">{{ $customer->name }}</div>
                                <div class="text-[11px] text-neutral-400 mt-0.5">
                                    {{ $customer->gender === 'L' ? 'Laki-laki' : ($customer->gender === 'P' ? 'Perempuan' : '-') }}
                                    @if($customer->birth_date)
                                        &middot; {{ $customer->birth_date->format('d M Y') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-neutral-100 dark:bg-slate-700 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-slate-600">
                                    {{ $customer->unit->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-[12px] leading-relaxed">
                                @if($customer->phone)
                                    <div class="text-neutral-600 dark:text-neutral-300">{{ $customer->phone }}</div>
                                @endif
                                @if($customer->email)
                                    <div class="text-neutral-400">{{ $customer->email }}</div>
                                @endif
                                @if(!$customer->phone && !$customer->email)
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full border {{ $categoryInfo['class'] }}">
                                    {{ $categoryInfo['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <div class="text-[12px] font-semibold text-neutral-800 dark:text-neutral-100">{{ $customer->total_visits }}x</div>
                                <div class="text-[10px] text-neutral-400 mt-0.5">
                                    {{ $customer->last_visit_at?->translatedFormat('d M Y') ?? 'Belum pernah' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full border {{ $customer->is_active ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-neutral-100 text-neutral-500 border-neutral-200' }}">
                                    {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="recordVisit({{ $customer->id }})"
                                            class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-md transition-all cursor-pointer"
                                            title="Catat Kunjungan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </button>
                                    <button wire:click="openEditModal({{ $customer->id }})"
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all cursor-pointer"
                                            title="Edit Pelanggan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </button>
                                    <button type="button"
                                            x-on:click.prevent="$store.confirmDialog.open({
                                                message: 'Yakin ingin menghapus data pelanggan ini?',
                                                confirmText: 'Ya, Hapus',
                                                onConfirm: () => $wire.deleteCustomer({{ $customer->id }})
                                            })"
                                            class="p-1.5 text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all cursor-pointer"
                                            title="Hapus Pelanggan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada data pelanggan yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                @if($customers->total() > 0)
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $customers->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $customers->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $customers->total() }}</span> total pelanggan
                @endif
            </div>
            <div class="w-full md:w-auto flex justify-end">
                {{ $customers->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- Modal Form Tambah/Edit Pelanggan --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $isEditing ? 'Perbarui informasi dan status pelanggan.' : 'Lengkapi formulir untuk mendaftarkan pelanggan baru.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="save" class="p-6 space-y-4 text-xs">

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                        <select wire:model="unit_id"
                                class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer">
                            <option value="">-- Pilih Unit Usaha --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Pelanggan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name"
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="Nama lengkap pelanggan">
                            @error('name') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select wire:model="category"
                                    class="w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer">
                                <option value="baru">Baru</option>
                                <option value="reguler">Reguler</option>
                                <option value="member">Member</option>
                                <option value="vip">VIP</option>
                            </select>
                            @error('category') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon / WhatsApp</label>
                            <input type="text" wire:model="phone"
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="0812...">
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                            <input type="email" wire:model="email"
                                   class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="email@pelanggan.com">
                            @error('email') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jenis Kelamin</label>
                            <select wire:model="gender"
                                    class="w-full px-3 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Lahir</label>
                            <input type="date" wire:model="birth_date"
                                class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500">
                            @error('birth_date') <span class="text-rose-500 text-[11px] mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Alamat</label>
                        <textarea wire:model="address" rows="2"
                                  class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Alamat pelanggan..."></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Catatan Internal</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full px-3.5 py-2 border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Preferensi, alergi, riwayat khusus, dsb..."></textarea>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="customer_is_active"
                               class="rounded border-neutral-300 text-blue-900 focus:ring-blue-500/20 cursor-pointer">
                        <label for="customer_is_active" class="font-semibold text-neutral-600 dark:text-neutral-300 cursor-pointer">Pelanggan Aktif</label>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>{{ $isEditing ? 'Perbarui Pelanggan' : 'Simpan Pelanggan' }}</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</div>
