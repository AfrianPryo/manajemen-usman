<div class="p-6 max-w-7xl mx-auto space-y-6">
    
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Unit Usaha</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola daftar unit usaha dan alokasi admin penanggung jawab.</p>
        </div>
        <button 
            wire:click="openCreateModal" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition inline-flex items-center gap-2 shadow-sm"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Unit Usaha
        </button>
    </div>

    {{-- Stats Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-gray-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Unit Usaha</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalUnits }}</p>
            </div>
            <div class="h-10 w-10 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center font-bold">
                🏢
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-gray-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Unit Aktif</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $activeUnits }}</p>
            </div>
            <div class="h-10 w-10 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center font-bold">
                ✅
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-gray-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Unit Nonaktif</p>
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $inactiveUnits }}</p>
            </div>
            <div class="h-10 w-10 bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 rounded-lg flex items-center justify-center font-bold">
                ⏸️
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari nama unit atau PIC..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <select wire:model.live="departmentFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white text-sm">
            <option value="">Semua Departemen/Jurusan</option>
            <option value="PPLG">PPLG</option>
            <option value="TO">TO</option>
            <option value="MPLB">MPLB</option>
            <option value="PM">PM</option>
            <option value="Akuntansi">Akuntansi</option>
        </select>
        <select wire:model.live="categoryFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white text-sm">
            <option value="">Semua Kategori</option>
            <option value="ritel">Ritel</option>
            <option value="jasa">Jasa</option>
        </select>
        <select wire:model.live="statusFilter" class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white text-sm">
            <option value="">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
        </select>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($units as $unit)
            <div wire:key="unit-card-{{ $unit->id }}" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4">
                <div>
                    {{-- Header Card --}}
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap mb-1.5">
                                <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400">
                                    {{ $unit->department }}
                                </span>
                                <span class="inline-block text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-400">
                                    {{ $unit->category }}
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $unit->name }}</h3>
                        </div>
                        <button 
                            wire:click="toggleUnitStatus({{ $unit->id }})"
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold transition inline-flex items-center gap-1 {{ $unit->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' }}"
                        >
                            <span class="h-1.5 w-1.5 rounded-full {{ $unit->is_active ? 'bg-emerald-600 dark:bg-emerald-400' : 'bg-rose-600 dark:bg-rose-400' }}"></span>
                            {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
                        {{ $unit->description ?? 'Tidak ada deskripsi unit.' }}
                    </p>

                    {{-- Info PIC / Kontak --}}
                    @if($unit->pic_name || $unit->phone)
                        <div class="mt-3 pt-2 text-xs text-gray-600 dark:text-gray-300 flex items-center justify-between border-t border-gray-100 dark:border-slate-700/60">
                            <span class="font-medium">PIC: {{ $unit->pic_name ?? '-' }}</span>
                            <span class="text-gray-400">{{ $unit->phone ?? '-' }}</span>
                        </div>
                    @endif

                    {{-- Daftar Admin Aktif Bertugas --}}
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-slate-700/60 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-400 font-medium">Admin Aktif Bertugas:</span>
                            <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold px-1.5 py-0.5 rounded">
                                {{ $unit->users->count() }} orang
                            </span>
                        </div>

                        @if($unit->users->count() > 0)
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($unit->users as $admin)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700/70 border border-slate-200 dark:border-slate-600/50 text-slate-800 dark:text-slate-200 text-xs font-medium">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
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

                {{-- Action Edit & Hapus --}}
                <div class="pt-3 border-t border-gray-100 dark:border-slate-700/60 flex items-center justify-between gap-2">
                    <button 
                        wire:click="openEditModal({{ $unit->id }})" 
                        class="flex-1 py-2 bg-gray-50 dark:bg-slate-700/50 hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition"
                    >
                        Edit Unit Usaha
                    </button>

                    @if ($unit->is_active)
                        {{-- Tombol Hapus Mati (Disabled) jika unit masih Aktif --}}
                        <button 
                            type="button"
                            disabled
                            title="Nonaktifkan unit usaha terlebih dahulu untuk menghapus"
                            class="px-3 py-2 text-xs font-semibold text-gray-400 dark:text-slate-500 bg-gray-100 dark:bg-slate-700/40 rounded-lg border border-gray-200 dark:border-slate-700 cursor-not-allowed opacity-60"
                        >
                            Hapus
                        </button>
                    @else
                        {{-- Tombol Hapus Aktif jika unit Nonaktif --}}
                        <button 
                            wire:click="deleteUnit({{ $unit->id }})"
                            wire:confirm="Apakah Anda yakin ingin menghapus unit '{{ $unit->name }}' secara permanen?"
                            class="px-3 py-2 text-xs font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 hover:bg-rose-100 dark:hover:bg-rose-900/60 rounded-lg border border-rose-200 dark:border-rose-800 transition"
                        >
                            Hapus
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-12 text-center text-gray-400">
                Belum ada data unit usaha yang terdaftar.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $units->links() }}
    </div>

    {{-- Modal Create / Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-xl max-w-lg w-full border border-gray-200 dark:border-slate-700 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $isEditing ? 'Edit Unit Usaha' : 'Tambah Unit Usaha Baru' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Unit Usaha *</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white" placeholder="Contoh: Bengkel TO">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Departemen / Jurusan *</label>
                            <select wire:model="department" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white">
                                <option value="PPLG">PPLG</option>
                                <option value="TO">TO</option>
                                <option value="MPLB">MPLB</option>
                                <option value="PM">PM</option>
                                <option value="Akuntansi">Akuntansi</option>
                            </select>
                            @error('department') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori Usaha *</label>
                            <select wire:model="category" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white">
                                <option value="ritel">Ritel</option>
                                <option value="jasa">Jasa</option>
                            </select>
                            @error('category') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama PIC / Penanggung Jawab</label>
                            <input type="text" wire:model="pic_name" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white" placeholder="Nama PIC">
                            @error('pic_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. Telepon / HP</label>
                            <input type="text" wire:model="phone" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white" placeholder="08123456789">
                            @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi Singkat</label>
                        <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white" placeholder="Keterangan operasional..."></textarea>
                        @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-xs font-medium text-gray-700 dark:text-gray-300">Unit Usaha Aktif / Operasional</label>
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-gray-100 dark:border-slate-700">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm font-semibold hover:bg-gray-50 dark:hover:bg-slate-700 dark:text-white">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <span wire:loading.remove wire:target="save">Simpan Unit</span>
                            <span wire:loading wire:target="save">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>