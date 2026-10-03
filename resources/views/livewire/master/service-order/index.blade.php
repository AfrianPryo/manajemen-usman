<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">
    <livewire:page-tour tour="service-orders.index" />

    {{-- Flash Notification (toast) --}}
    @if (session()->has('message'))
        <div wire:key="toast-message-{{ md5(session('message')) }}" x-data x-init="$store.toast.push('success', @js(session('message')))"></div>
    @endif

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 p-4 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div class="min-w-0">
            <h1 class="text-sm font-bold tracking-tight text-neutral-900 dark:text-white">Pesanan Layanan</h1>
            <p class="text-[11px] tracking-tight text-neutral-400 mt-0.5 truncate">
                Kelola pesanan, jadwal, dan status pengerjaan jasa pelanggan di seluruh Unit Usaha berkategori Jasa.
            </p>
        </div>

        {{-- Tombol Aksi Cepat: dipaksa satu baris (nowrap), scroll horizontal kalau ruangnya sempit --}}
        <div class="flex flex-nowrap items-center gap-2 overflow-x-auto shrink-0 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            {{-- Tombol Tambah Pesanan --}}
            <button type="button" wire:click="openCreateModal" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-[11px] font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer shrink-0 whitespace-nowrap">
                <x-heroicon-o-plus class="w-3.5 h-3.5" stroke-width="2.5" />
                <span>Tambah Pesanan</span>
            </button>
        </div>
    </div>

    {{-- Metric / Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-amber-500">Menunggu Dikerjakan</p>
                <x-heroicon-o-clock stroke-width="1.5" class="w-4 h-4 text-amber-300 dark:text-amber-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-amber-600 dark:text-amber-400 tracking-tight font-mono">{{ number_format($pendingCount ?? 0) }} Pesanan</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-sky-500">Sedang Dikerjakan</p>
                <x-heroicon-o-wrench-screwdriver stroke-width="1.5" class="w-4 h-4 text-sky-300 dark:text-sky-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-sky-600 dark:text-sky-400 tracking-tight font-mono">{{ number_format($inProgressCount ?? 0) }} Pesanan</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-none border border-neutral-100 dark:border-slate-700 p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs text-emerald-500">Selesai</p>
                <x-heroicon-o-check-circle stroke-width="1.5" class="w-4 h-4 text-emerald-300 dark:text-emerald-700" />
            </div>
            <p class="mt-2 text-sm font-bold text-emerald-600 dark:text-emerald-400 tracking-tight font-mono">{{ number_format($completedCount ?? 0) }} Pesanan</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <div class="sm:col-span-2 md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan, layanan, atau petugas..."
                       class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
            </div>

            <div>
                <select wire:model.live="unitFilter"
                        class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Unit Usaha</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select wire:model.live="statusFilter"
                        class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="in_progress">Dikerjakan</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabel Pesanan Layanan --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Pelanggan</th>
                        <th class="px-4 py-3.5">Unit Usaha</th>
                        <th class="px-4 py-3.5">Layanan</th>
                        <th class="px-4 py-3.5">Jadwal</th>
                        <th class="px-4 py-3.5">Petugas</th>
                        <th class="px-4 py-3.5 text-right">Biaya</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($orders as $order)
                        @php
                            $statusMap = [
                                'pending'     => ['label' => 'Menunggu', 'class' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border-amber-200/60 dark:border-amber-800'],
                                'in_progress' => ['label' => 'Dikerjakan', 'class' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border-sky-200/60 dark:border-sky-800'],
                                'completed'   => ['label' => 'Selesai', 'class' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border-emerald-200/60 dark:border-emerald-800'],
                                'cancelled'   => ['label' => 'Dibatalkan', 'class' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border-rose-200/60 dark:border-rose-800'],
                            ];
                            $statusInfo = $statusMap[$order->status] ?? $statusMap['pending'];
                        @endphp
                        <tr wire:key="service-order-{{ $order->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors align-top">
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-neutral-900 dark:text-white text-xs">{{ $order->customer_name }}</div>
                                @if($order->customer_phone)
                                    <div class="text-[11px] text-neutral-400 mt-0.5">{{ $order->customer_phone }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-neutral-100 dark:bg-slate-700 text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-slate-600">
                                    {{ $order->unit->name ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-medium text-neutral-700 dark:text-neutral-200 text-xs">{{ $order->service_name }}</div>
                                @if($order->description)
                                    <div class="text-[11px] text-neutral-400 mt-0.5 truncate max-w-[220px]">{{ $order->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-600 dark:text-neutral-300">
                                {{ $order->scheduled_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-neutral-600 dark:text-neutral-300">
                                {{ $order->assigned_to ?: '-' }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-xs text-neutral-900 dark:text-white">
                                Rp {{ number_format($order->price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <select wire:change="updateStatus({{ $order->id }}, $event.target.value)"
                                        class="text-[11px] font-bold tracking-wide px-2.5 py-1 rounded-sm border {{ $statusInfo['class'] }} focus:outline-none cursor-pointer">
                                    <option value="pending" @selected($order->status === 'pending')>Menunggu</option>
                                    <option value="in_progress" @selected($order->status === 'in_progress')>Dikerjakan</option>
                                    <option value="completed" @selected($order->status === 'completed')>Selesai</option>
                                    <option value="cancelled" @selected($order->status === 'cancelled')>Dibatalkan</option>
                                </select>
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openEditModal({{ $order->id }})"
                                            class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-sm transition-all cursor-pointer"
                                            title="Edit Pesanan">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </button>
                                    <button type="button"
                                            x-on:click.prevent="$store.confirmDialog.open({
                                                message: 'Yakin ingin menghapus pesanan layanan ini?',
                                                confirmText: 'Ya, Hapus',
                                                onConfirm: () => $wire.deleteServiceOrder({{ $order->id }})
                                            })"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-all cursor-pointer"
                                            title="Hapus Pesanan">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-xs text-neutral-400">
                                Belum ada pesanan layanan yang tercatat dari Unit Usaha berkategori Jasa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                @if($orders->total() > 0)
                    Menampilkan <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $orders->firstItem() }}</span> - <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $orders->lastItem() }}</span> dari <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $orders->total() }}</span> total pesanan
                @endif
            </div>
            <div class="w-full md:w-auto flex justify-end">
                {{ $orders->links('components.custom-pagination') }}
            </div>
        </div>
    </div>

    {{-- Modal Form Tambah/Edit Pesanan Layanan --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Pesanan Layanan' : 'Tambah Pesanan Layanan' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $isEditing ? 'Perbarui detail pesanan dan status pengerjaan.' : 'Catat pesanan jasa baru dari pelanggan.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Body / Form --}}
                <form wire:submit.prevent="save" class="p-6 text-xs">
                    <x-form-tabs tab1-label="Info Pesanan" tab2-label="Jadwal & Catatan" cancel="closeModal" compact>
                    <x-slot:tab1>
                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                        <select wire:model="unit_id"
                                class="@error('unit_id') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer" aria-required="true" aria-invalid="@error('unit_id') true @else false @enderror">
                            <option value="">-- Pilih Unit Usaha (Kategori Jasa) --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id') <x-form-error :message="$message" :field="'unit_id'" /> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Pelanggan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="customer_name"
                                   class="@error('customer_name') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="Nama pelanggan" aria-required="true" aria-invalid="@error('customer_name') true @else false @enderror">
                            @error('customer_name') <x-form-error :message="$message" :field="'customer_name'" /> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon</label>
                            <input type="text" wire:model="customer_phone" inputmode="numeric" oninput="onlyDigits(event)"
                                   class="@error('customer_phone') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="0812..." aria-invalid="@error('customer_phone') true @else false @enderror">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Layanan <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="service_name"
                               class="@error('service_name') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                               placeholder="Contoh: Servis AC, Cukur Rambut, Reparasi Elektronik" aria-required="true" aria-invalid="@error('service_name') true @else false @enderror">
                        @error('service_name') <x-form-error :message="$message" :field="'service_name'" /> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Petugas / Teknisi</label>
                            <input type="text" wire:model="assigned_to"
                                   class="@error('assigned_to') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="Nama petugas yang menangani" aria-invalid="@error('assigned_to') true @else false @enderror">
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Biaya Jasa (Rp) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model="price" oninput="onlyDecimal(event)"
                                   class="@error('price') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                   placeholder="0" aria-required="true" aria-invalid="@error('price') true @else false @enderror">
                            @error('price') <x-form-error :message="$message" :field="'price'" /> @enderror
                        </div>
                    </div>

                    </x-slot:tab1>
                    <x-slot:tab2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jadwal Pengerjaan</label>
                            <input type="datetime-local" wire:model="scheduled_at"
                                class="@error('scheduled_at') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500" aria-invalid="@error('scheduled_at') true @else false @enderror">
                            @error('scheduled_at') <x-form-error :message="$message" :field="'scheduled_at'" /> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Status <span class="text-red-500">*</span></label>
                            <select wire:model="status"
                                    class="@error('status') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500 cursor-pointer" aria-required="true" aria-invalid="@error('status') true @else false @enderror">
                                <option value="pending">Menunggu</option>
                                <option value="in_progress">Dikerjakan</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                            @error('status') <x-form-error :message="$message" :field="'status'" /> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi</label>
                        <textarea wire:model="description" rows="2"
                                  class="@error('description') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Detail permintaan pelanggan..." aria-invalid="@error('description') true @else false @enderror"></textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Catatan Internal</label>
                        <textarea wire:model="notes" rows="2"
                                  class="@error('notes') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3.5 py-2 font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-blue-500"
                                  placeholder="Catatan tambahan untuk tim internal..." aria-invalid="@error('notes') true @else false @enderror"></textarea>
                    </div>
                    </x-slot:tab2>
                    <x-slot:submit>
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove>{{ $isEditing ? 'Perbarui Pesanan' : 'Simpan Pesanan' }}</span>
                            <span wire:loading>Memproses...</span>
                        </button>
                    </x-slot:submit>
                    </x-form-tabs>
                </form>

            </div>
        </div>
    @endif

</div>