<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification (toast) --}}
    @if (session()->has('message'))
        <div wire:key="toast-message-{{ md5(session('message')) }}" x-data x-init="$store.toast.push('success', @js(session('message')))"></div>
    @endif

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Manajemen Pelanggan</h1>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">Kelola data dan riwayat kunjungan pelanggan di seluruh Unit Usaha.</p>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button"
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Tambah Pelanggan</span>
            </button>
        </div>
    </div>

    {{-- Ringkasan Cepat --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Total Pelanggan</p>
                <x-heroicon-o-users stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Pelanggan Baru</p>
                <x-heroicon-o-user-plus stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-sky-600 dark:text-sky-400 tracking-tight">{{ $newCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">Member</p>
                <x-heroicon-o-identification stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-violet-600 dark:text-violet-400 tracking-tight">{{ $memberCount }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs text-neutral-400">VIP</p>
                <x-heroicon-o-star stroke-width="1.5" class="w-4 h-4 text-neutral-300 dark:text-neutral-600" />
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400 tracking-tight">{{ $vipCount }}</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="sm:col-span-2 md:col-span-2 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, telepon, atau email..."
                       class="w-full pl-9 pr-3 py-2.5 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-all">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-neutral-400 absolute left-3 top-3" />
            </div>

            <div>
                <select wire:model.live="unitFilter"
                        class="w-full px-3.5 py-2.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Unit Usaha</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="categoryFilter"
                        class="w-full px-3.5 py-2.5 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
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
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Unit Usaha</th>
                        <th class="px-5 py-3.5">Kontak</th>
                        <th class="px-5 py-3.5 text-center">Kategori</th>
                        <th class="px-5 py-3.5 text-center">Kunjungan</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($customers as $customer)
                        @php
                            $categoryMap = [
                                'baru'    => ['label' => 'Baru', 'class' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400 border-sky-200/60 dark:border-sky-800'],
                                'reguler' => ['label' => 'Reguler', 'class' => 'bg-neutral-100 dark:bg-slate-700/60 text-neutral-700 dark:text-neutral-300 border-neutral-200 dark:border-slate-600'],
                                'member'  => ['label' => 'Member', 'class' => 'bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-400 border-violet-200/60 dark:border-violet-800'],
                                'vip'     => ['label' => 'VIP', 'class' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 border-amber-200/60 dark:border-amber-800'],
                            ];
                            $categoryInfo = $categoryMap[$customer->category] ?? $categoryMap['reguler'];
                        @endphp
                        <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">{{ $customer->name }}</div>
                                <div class="text-[11px] text-neutral-400 mt-0.5">
                                    {{ $customer->gender === 'L' ? 'Laki-laki' : ($customer->gender === 'P' ? 'Perempuan' : '-') }}
                                    @if($customer->birth_date)
                                        &middot; {{ $customer->birth_date->format('d M Y') }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-neutral-100 dark:bg-slate-700 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-slate-600">
                                    {{ $customer->unit->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-[12px] leading-relaxed">
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
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-sm border {{ $categoryInfo['class'] }}">
                                    {{ $categoryInfo['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="text-xs font-semibold text-neutral-800 dark:text-neutral-100">{{ $customer->total_visits }}x</div>
                                <div class="text-[10px] text-neutral-400 mt-0.5">
                                    {{ $customer->last_visit_at?->translatedFormat('d M Y') ?? 'Belum pernah' }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-[10px] font-semibold rounded-sm border {{ $customer->is_active ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800' : 'bg-neutral-50 dark:bg-slate-900 text-neutral-500 dark:text-neutral-400 border-neutral-200 dark:border-slate-700' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $customer->is_active ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-slate-600' }}"></span>
                                    {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="recordVisit({{ $customer->id }})"
                                            title="Catat Kunjungan"
                                            class="p-1.5 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-sm transition-all cursor-pointer">
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                    </button>
                                    <button wire:click="openEditModal({{ $customer->id }})"
                                            title="Edit Pelanggan"
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-sm transition-all cursor-pointer">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button type="button"
                                            x-on:click.prevent="$store.confirmDialog.open({
                                                message: 'Yakin ingin menghapus data pelanggan ini?',
                                                confirmText: 'Ya, Hapus',
                                                onConfirm: () => $wire.deleteCustomer({{ $customer->id }})
                                            })"
                                            title="Hapus Pelanggan"
                                            class="p-1.5 text-rose-500 hover:text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-all cursor-pointer">
                                        <x-heroicon-o-trash class="w-4 h-4" />
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            {{ $isEditing ? 'Perbarui informasi dan status pelanggan.' : 'Lengkapi formulir untuk mendaftarkan pelanggan baru.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form novalidate wire:submit.prevent="save" class="p-6">
                    <x-form-tabs tab1-label="Data Utama" tab2-label="Detail & Catatan" cancel="closeModal">
                    <x-slot:tab1>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                        <select wire:model="unit_id"
                                class="@error('unit_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer" aria-required="true" aria-invalid="@error('unit_id') true @else false @enderror">
                            <option value="">-- Pilih Unit Usaha --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id') <x-form-error :message="$message" :field="'unit_id'" /> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Pelanggan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name"
                                   class="@error('name') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="Nama lengkap pelanggan" aria-required="true" aria-invalid="@error('name') true @else false @enderror">
                            @error('name') <x-form-error :message="$message" :field="'name'" /> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select wire:model="category"
                                    class="@error('category') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer" aria-required="true" aria-invalid="@error('category') true @else false @enderror">
                                <option value="baru">Baru</option>
                                <option value="reguler">Reguler</option>
                                <option value="member">Member</option>
                                <option value="vip">VIP</option>
                            </select>
                            @error('category') <x-form-error :message="$message" :field="'category'" /> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon / WhatsApp</label>
                            <input type="text" wire:model="phone" inputmode="numeric" oninput="onlyDigits(event)"
                                   class="@error('phone') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="0812..." aria-invalid="@error('phone') true @else false @enderror">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                            <input type="email" wire:model="email"
                                   class="@error('email') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                   placeholder="email@pelanggan.com" aria-invalid="@error('email') true @else false @enderror">
                            @error('email') <x-form-error :message="$message" :field="'email'" /> @enderror
                        </div>
                    </div>

                    </x-slot:tab1>
                    <x-slot:tab2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Jenis Kelamin</label>
                            <select wire:model="gender"
                                    class="@error('gender') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer" aria-invalid="@error('gender') true @else false @enderror">
                                <option value="">-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Lahir</label>
                            <input type="date" wire:model="birth_date"
                                class="@error('birth_date') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400" aria-invalid="@error('birth_date') true @else false @enderror">
                            @error('birth_date') <x-form-error :message="$message" :field="'birth_date'" /> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Alamat</label>
                        <textarea wire:model="address" rows="2"
                                  class="@error('address') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                  placeholder="Alamat pelanggan..." aria-invalid="@error('address') true @else false @enderror"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Catatan Internal</label>
                        <textarea wire:model="notes" rows="2"
                                  class="@error('notes') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                  placeholder="Preferensi, alergi, riwayat khusus, dsb..." aria-invalid="@error('notes') true @else false @enderror"></textarea>
                    </div>

                    <div class="pt-1">
                        <x-toggle wire:model="is_active">Pelanggan Aktif</x-toggle>
                    </div>
                    </x-slot:tab2>
                    <x-slot:submit>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-blue-900 text-white rounded-sm text-sm font-semibold hover:bg-blue-950 transition-colors shadow-sm shadow-blue-900/20 cursor-pointer">
                            <span wire:loading.remove>{{ $isEditing ? 'Perbarui Pelanggan' : 'Simpan Pelanggan' }}</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </x-slot:submit>
                    </x-form-tabs>
                </form>

            </div>
        </div>
    @endif

</div>