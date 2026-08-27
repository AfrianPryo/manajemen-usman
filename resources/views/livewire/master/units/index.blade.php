<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Flash Notification --}}
    @if (session()->has('message'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('message') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- Action Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Manajemen Unit Usaha</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Kelola daftar unit usaha dan alokasi admin penanggung jawab.</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <button 
                wire:click="openCreateModal" 
                class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[2px] transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Tambah Unit Usaha</span>
            </button>
        </div>
    </div>

    {{-- Stats Ringkasan (KPI Cards) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Unit Usaha</p>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5s0 0 0 0m0 3h-1.5s0 0 0 0m0 3h1.5s0 0 0 0m3-6h1.5s0 0 0 0m0 3h-1.5s0 0 0 0m0 3h1.5s0 0 0 0"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ number_format($totalUnits) }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Unit terdaftar dalam sistem</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Unit Aktif</p>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ number_format($activeUnits) }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Beroperasi secara aktif</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Unit Nonaktif</p>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-rose-600 dark:text-rose-400 tracking-tight">{{ number_format($inactiveUnits) }}</p>
            <p class="mt-2 text-[11px] text-neutral-400">Ditangguhkan / nonaktif</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 space-y-3 shadow-sm shadow-black/[0.02]">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <div class="md:col-span-2 relative">
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari nama unit atau PIC..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[2px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400"
                >
            </div>

            <div>
                <select wire:model.live="departmentFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[2px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Departemen/Jurusan</option>
                    <option value="PPLG">PPLG</option>
                    <option value="TO">TO</option>
                    <option value="MPLB">MPLB</option>
                    <option value="PM">PM</option>
                    <option value="Akuntansi">Akuntansi</option>
                </select>
            </div>

            <div>
                <select wire:model.live="categoryFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[2px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="ritel">Ritel</option>
                    <option value="jasa">Jasa</option>
                </select>
            </div>

            <div>
                <select wire:model.live="statusFilter" class="w-full px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-[2px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($units as $unit)
            <div wire:key="unit-card-{{ $unit->id }}" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02] hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                <div>
                    {{-- Header Card --}}
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap mb-2">
                                <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-200/60 dark:border-sky-800">
                                    {{ $unit->department }}
                                </span>
                                <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200/60 dark:border-purple-800">
                                    {{ $unit->category }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white tracking-tight">{{ $unit->name }}</h3>
                        </div>

                        <button 
                            wire:click="toggleUnitStatus({{ $unit->id }})"
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold transition inline-flex items-center gap-1 cursor-pointer {{ $unit->is_active ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800' }}"
                        >
                            <span class="h-1.5 w-1.5 rounded-full {{ $unit->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>

                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2 line-clamp-2 leading-relaxed">
                        {{ $unit->description ?? 'Tidak ada deskripsi unit.' }}
                    </p>

                    {{-- Info PIC / Kontak --}}
                    @if($unit->pic_name || $unit->phone)
                        <div class="mt-3.5 pt-2.5 text-xs text-neutral-600 dark:text-neutral-300 flex items-center justify-between border-t border-neutral-100 dark:border-slate-700/60">
                            <span class="font-medium">PIC: {{ $unit->pic_name ?? '-' }}</span>
                            <span class="text-neutral-400 text-[11px] font-mono">{{ $unit->phone ?? '-' }}</span>
                        </div>
                    @endif

                    {{-- Daftar Admin Aktif Bertugas --}}
                    <div class="mt-3 pt-3 border-t border-neutral-100 dark:border-slate-700/60 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[11px] text-neutral-400 font-medium">Admin Aktif Bertugas:</span>
                            <span class="text-[10px] bg-neutral-100 dark:bg-slate-700 text-neutral-600 dark:text-neutral-300 font-bold px-2 py-0.5 rounded-full">
                                {{ $unit->users->count() }} orang
                            </span>
                        </div>

                        @if($unit->users->count() > 0)
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($unit->users as $admin)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-neutral-50 dark:bg-slate-900 border border-neutral-200/60 dark:border-slate-700 text-neutral-700 dark:text-neutral-300 text-[11px] font-medium">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $admin->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-amber-600 dark:text-amber-400 font-medium italic pt-1 flex items-center gap-1">
                                ⚠️ Belum ada admin aktif di unit ini
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Action Dashboard, Edit & Hapus --}}
                <div class="pt-3 border-t border-neutral-100 dark:border-slate-700/60 flex items-center gap-2">
                    {{-- Tombol Utama: Buka Dashboard --}}
                    <a 
                        href="{{ Route::has('unit.dashboard') ? route('unit.dashboard', $unit->slug ?? $unit->id) : '#' }}"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-semibold text-neutral-700 dark:text-neutral-200 bg-neutral-100 dark:bg-slate-700/60 hover:bg-neutral-200 dark:hover:bg-slate-700 border border-neutral-200/60 dark:border-slate-700 rounded-md transition-all cursor-pointer"
                    >
                        <span>Buka Dashboard</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>

                    {{-- Icon Edit --}}
                    <button 
                        wire:click="openEditModal({{ $unit->id }})" 
                        title="Edit Unit Usaha"
                        class="p-2 bg-white dark:bg-slate-900 hover:bg-neutral-100 dark:hover:bg-slate-700 text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white rounded-md border border-neutral-200 dark:border-slate-700 transition cursor-pointer shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                        </svg>
                    </button>

                    {{-- Icon Hapus --}}
                    @if ($unit->is_active)
                        {{-- Disabled state --}}
                        <button 
                            type="button"
                            disabled
                            title="Nonaktifkan unit usaha terlebih dahulu untuk menghapus"
                            class="p-2 text-neutral-300 dark:text-slate-600 bg-neutral-50 dark:bg-slate-900 rounded-md border border-neutral-200/60 dark:border-slate-800 cursor-not-allowed shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    @else
                        {{-- Active state --}}
                        <button 
                            wire:click="deleteUnit({{ $unit->id }})"
                            wire:confirm="Apakah Anda yakin ingin menghapus unit '{{ $unit->name }}' secara permanen?"
                            title="Hapus Unit Usaha"
                            class="p-2 text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-md border border-rose-200 dark:border-rose-900/50 transition cursor-pointer shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-12 text-center text-xs text-neutral-400 shadow-sm shadow-black/[0.02]">
                Belum ada data unit usaha yang terdaftar.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $units->links('components.custom-pagination') }}
    </div>

    {{-- Modal Create / Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-2xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Unit Usaha' : 'Tambah Unit Usaha Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400">
                            {{ $isEditing ? 'Perbarui informasi operasional & pengelola unit usaha.' : 'Daftarkan unit usaha baru ke dalam sistem finansial.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                </div>

                {{-- Modal Form --}}
                <form wire:submit="save" class="p-6 space-y-4">
                    {{-- Nama Unit Usaha --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Unit Usaha <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="Contoh: Bengkel TO">
                        @error('name') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Departemen & Kategori --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Departemen / Jurusan <span class="text-red-500">*</span></label>
                            <select wire:model="department" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="PPLG">PPLG</option>
                                <option value="TO">TO</option>
                                <option value="MPLB">MPLB</option>
                                <option value="PM">PM</option>
                                <option value="Akuntansi">Akuntansi</option>
                            </select>
                            @error('department') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori Usaha <span class="text-red-500">*</span></label>
                            <select wire:model="category" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="ritel">Ritel</option>
                                <option value="jasa">Jasa</option>
                            </select>
                            @error('category') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- PIC & No Telepon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama PIC / Penanggung Jawab</label>
                            <input type="text" wire:model="pic_name" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="Nama PIC">
                            @error('pic_name') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon / HP</label>
                            <input type="text" wire:model="phone" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="08123456789">
                            @error('phone') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi Singkat</label>
                        <textarea wire:model="description" rows="3" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="Keterangan operasional..."></textarea>
                        @error('description') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Toggle Checkbox --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-neutral-300 text-blue-900 focus:ring-red-500/20 cursor-pointer">
                        <label for="is_active" class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 cursor-pointer">Unit Usaha Aktif / Operasional</label>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                            <span wire:loading.remove wire:target="save">Simpan Unit</span>
                            <span wire:loading wire:target="save">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>