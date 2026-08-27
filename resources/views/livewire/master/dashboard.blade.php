<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans"
    x-data="{ showAdminModal: false, showUnitModal: false }"
    @show-admin-form-modal.window="showAdminModal = true"
    @show-unit-form-modal.window="showUnitModal = true">

    {{-- ================= HEADER & QUICK ACTIONS ================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Dashboard Master Admin</h1>
            </div>
            <p class="text-sm tracking-tight text-neutral-400 mt-1">
                Ikhtisar kinerja bisnis, status operasional, dan manajemen unit usaha.
            </p>
        </div>

        {{-- Tombol Aksi Cepat --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Tombol Export CSV --}}
            <button type="button"
                    wire:click="export"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 rounded-[3px] hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-all shadow-sm shadow-black/[0.02] cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                <svg wire:loading.remove wire:target="export" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                <svg wire:loading wire:target="export" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="export">Export</span>
                <span wire:loading wire:target="export">Mengunduh...</span>
            </button>

            {{-- Tombol + Admin Baru --}}
            <button type="button"
                    wire:click="openCreateAdminModal"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-[3px] hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-all shadow-sm shadow-black/[0.02] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                <span>Admin Baru</span>
            </button>

            {{-- Tombol + Unit Usaha (Aksi Utama) --}}
            <button type="button"
                    wire:click="openCreateUnitModal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all shadow-sm shadow-blue-900/20 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Unit Usaha</span>
            </button>
        </div>

        {{-- Modal Form Tambah Admin --}}
        @if($showCreateAdminModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-900/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                    <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex justify-between items-center bg-neutral-50/50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Tambah Admin Baru</h3>
                        <button type="button" wire:click="closeCreateAdminModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                    </div>

                    <form wire:submit.prevent="saveAdmin" class="p-6 space-y-4">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Lengkap</label>
                            <input type="text" wire:model="admin_name" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400" placeholder="Contoh: Budi Santoso">
                            @error('admin_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Status Pegawai --}}
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Status Pegawai</label>
                            <select wire:model.live="employee_status" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                <option value="nip">Pegawai NIP</option>
                                <option value="non_nip">Pegawai Non-NIP</option>
                            </select>
                            @error('employee_status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- NIP (Wajib hanya jika pegawai tetap) --}}
                        @if($employee_status === 'nip')
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">NIP (18 Digit)</label>
                                <input type="text" wire:model="nip" maxlength="18" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400" placeholder="199001012023011001">
                                @error('nip') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Role Admin --}}
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Role / Peran</label>
                            <select wire:model.live="role" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                <option value="unit-admin">Unit Admin (Pengelola Usaha)</option>
                                <option value="master-admin">Master Admin (Akses Penuh)</option>
                            </select>
                            @error('role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Pilih Unit Usaha (Hanya jika unit-admin) --}}
                        @if($role === 'unit-admin')
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha</label>
                                <select wire:model="admin_unit_id" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                    <option value="">-- Pilih Unit Usaha --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('admin_unit_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="p-3 bg-red-50/60 dark:bg-red-950/20 rounded-xl text-xs text-blue-950 dark:text-red-300 space-y-1">
                            <div>💡 <strong>Username &amp; Password</strong> akan dibuat otomatis oleh sistem.</div>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-4 flex justify-end gap-2 border-t border-neutral-100 dark:border-slate-700">
                            <button type="button" wire:click="closeCreateAdminModal" class="px-4 py-2.5 border border-neutral-200 dark:border-slate-700 rounded-full text-sm font-semibold hover:bg-neutral-50 dark:hover:bg-slate-700 dark:text-white transition-colors">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="px-4 py-2.5 bg-blue-900 text-white rounded-full text-sm font-semibold hover:bg-blue-950 transition-colors shadow-sm shadow-blue-900/20">
                                <span wire:loading.remove wire:target="saveAdmin">Simpan &amp; Generate Kredensial</span>
                                <span wire:loading wire:target="saveAdmin">Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Modal Informasi Hasil Kredensial (Username & Password Baru) --}}
        @if($createdCredentials)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-900/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-slate-800 rounded-lg max-w-md w-full border border-emerald-100 dark:border-emerald-800 shadow-2xl p-6 text-center space-y-4">
                    <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto text-2xl">
                        ✓
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $createdCredentials['title'] }}</h3>
                    <p class="text-xs text-neutral-400">Harap catat atau berikan informasi kredensial login berikut kepada admin yang bersangkutan.</p>

                    <div class="bg-neutral-50 dark:bg-slate-900 p-4 rounded-xl text-left text-xs space-y-2 border border-neutral-100 dark:border-slate-700">
                        <div class="flex items-center justify-between"><span class="text-neutral-400">Nama</span><strong class="text-neutral-800 dark:text-neutral-200">{{ $createdCredentials['name'] }}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-neutral-400">Username</span><strong class="font-mono text-red-500 dark:text-red-400">{{ $createdCredentials['username'] }}</strong></div>
                        <div class="flex items-center justify-between"><span class="text-neutral-400">Password</span><strong class="font-mono text-emerald-600 dark:text-emerald-400">{{ $createdCredentials['password'] }}</strong></div>
                    </div>

                    {{-- Action Buttons dengan Alpine.js Copy to Clipboard --}}
                    <div x-data="{ copied: false }" class="space-y-2 pt-2">
                        <button type="button"
                                @click="
                                    navigator.clipboard.writeText('Nama: {{ $createdCredentials['name'] }}\nUsername: {{ $createdCredentials['username'] }}\nPassword: {{ $createdCredentials['password'] }}');
                                    copied = true;
                                    setTimeout(() => { $wire.set('createdCredentials', null); }, 800);
                                "
                                class="w-full py-3 bg-blue-900 hover:bg-blue-950 text-white rounded-full text-xs font-semibold transition flex items-center justify-center gap-2 cursor-pointer shadow-sm shadow-blue-900/20">
                            <template x-if="!copied">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5"/></svg>
                                    Salin Kredensial &amp; Tutup
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1.5 text-emerald-100 font-bold">
                                    ✓ Kredensial Tersalin!
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Create / Edit Unit --}}
        @if($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-neutral-900/50 backdrop-blur-sm">
                <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                    <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex justify-between items-center bg-neutral-50/50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
                            {{ $isEditing ? 'Edit Unit Usaha' : 'Tambah Unit Usaha Baru' }}
                        </h3>
                        <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none">&times;</button>
                    </div>

                    <form wire:submit.prevent="save" class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Unit Usaha</label>
                            <input type="text" wire:model="name" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400" placeholder="Contoh: Bengkel TO">
                            @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Departemen / Jurusan</label>
                                <select wire:model="department" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                    <option value="">Pilih Jurusan</option>
                                    <option value="PPLG">PPLG</option>
                                    <option value="TO">TO</option>
                                    <option value="MPLB">MPLB</option>
                                    <option value="PM">PM</option>
                                    <option value="Akuntansi">Akuntansi</option>
                                </select>
                                @error('department') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Kategori Usaha</label>
                                <select wire:model="category" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                                    <option value="">Pilih Kategori</option>
                                    <option value="ritel">Ritel</option>
                                    <option value="jasa">Jasa</option>
                                </select>
                                @error('category') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama PIC / Penanggung Jawab</label>
                                <input type="text" wire:model="pic_name" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400" placeholder="Nama PIC">
                                @error('pic_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">No. Telepon / HP</label>
                                <input type="text" wire:model="phone" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400" placeholder="08123456789">
                                @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi Singkat</label>
                            <textarea wire:model="description" rows="3" class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400" placeholder="Keterangan operasional..."></textarea>
                            @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-neutral-300 text-blue-900 focus:ring-red-400">
                            <label for="is_active" class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Unit Usaha Aktif / Operasional</label>
                        </div>

                        <div class="pt-4 flex justify-end gap-2 border-t border-neutral-100 dark:border-slate-700">
                            <button type="button" wire:click="closeModal" class="px-4 py-2.5 border border-neutral-200 dark:border-slate-700 rounded-full text-sm font-semibold hover:bg-neutral-50 dark:hover:bg-slate-700 dark:text-white transition-colors">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="px-4 py-2.5 bg-blue-900 text-white rounded-full text-sm font-semibold hover:bg-blue-950 transition-colors shadow-sm shadow-blue-900/20">
                                <span wire:loading.remove wire:target="save">Simpan Unit</span>
                                <span wire:loading wire:target="save">Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- ================= PERINGATAN / ACTION NEEDED (KONDISIONAL) ================= --}}
    @if ($inactiveUnits > 0)
        <div class="flex items-center justify-between p-4 bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-800/50 rounded-md text-amber-900 dark:text-amber-300 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center gap-3">
                <span class="p-2.5 bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-2xl shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-amber-900 dark:text-amber-200">Terdapat {{ $inactiveUnits }} Unit Usaha Nonaktif / Perlu Perhatian</p>
                    <p class="text-xs text-amber-700/80 dark:text-amber-400/80 mt-0.5">Beberapa unit usaha belum beroperasi penuh atau belum memiliki admin penanggung jawab.</p>
                </div>
            </div>
            <a href="#kesehatan-unit" class="text-xs font-bold text-amber-800 dark:text-amber-300 underline hover:text-amber-950 dark:hover:text-amber-100 shrink-0 transition-colors">Tinjau Unit &rarr;</a>
        </div>
    @endif

    {{-- ================= KARTU STATISTIK ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Card Total Omzet Ringkas (Lebar 2 Kolom di Desktop agar Input Tanggal Sangat Luas & Anti-Terpotong) --}}
        <div class="sm:col-span-2 lg:col-span-2 bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 transition-all shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            
            {{-- Header Kartu: Judul, Icon, & Dropdown Filter --}}
            <div class="flex flex-col gap-2 border-b border-neutral-100 dark:border-slate-700/60 pb-2.5 mb-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="p-1.5 rounded-lg bg-red-50 dark:bg-red-950/50 text-red-500 dark:text-red-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="truncate">
                            <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 truncate">Total Omzet</p>
                            <p class="text-[10px] text-neutral-400 dark:text-neutral-500 font-medium leading-none mt-0.5 truncate">
                                {{ $periodLabel ?? 'Bulan Ini' }}
                            </p>
                        </div>
                    </div>

                    {{-- Dropdown Filter Periode --}}
                    <select wire:model.live="periodFilter" 
                        class="px-2 py-1 text-[11px] font-medium bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded-[2px] focus:ring-1 focus:ring-blue-900 focus:border-blue-900 outline-none transition-all cursor-pointer shrink-0">
                        <option value="today">Hari Ini</option>
                        <option value="this_week">Minggu Ini</option>
                        <option value="this_month">Bulan Ini</option>
                        <option value="last_month">Bulan Lalu</option>
                        <option value="this_year">Tahun Ini</option>
                        <option value="custom">Custom...</option>
                    </select>
                </div>

                {{-- Input Tanggal Kustom (Responsive & Anti-Overflow) --}}
                @if ($periodFilter === 'custom')
                    <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-1.5 pt-2 border-t border-dashed border-neutral-100 dark:border-slate-700/60 animate-fadeIn">
                        <span class="text-[10px] text-neutral-400 font-medium shrink-0">Rentang Tanggal:</span>
                        <div class="flex items-center gap-1.5 w-full sm:w-auto">
                            <input type="date" wire:model.live="startDate" 
                                class="w-full sm:w-auto px-2 py-0.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded outline-none focus:border-red-500">
                            <span class="text-[10px] text-neutral-400">-</span>
                            <input type="date" wire:model.live="endDate" 
                                class="w-full sm:w-auto px-2 py-0.5 text-[11px] bg-neutral-50 dark:bg-slate-900 text-neutral-700 dark:text-neutral-200 border border-neutral-200 dark:border-slate-700 rounded outline-none focus:border-red-500">
                        </div>
                    </div>
                @endif
            </div>

            {{-- Body Utama: Nominal Omzet Dinamis --}}
            <div class="flex items-baseline justify-between pt-1">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    {{ $totalRevenue ?? 'Rp —' }}
                </p>

                {{-- Indikator Pertumbuhan --}}
                <div class="flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold shrink-0">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                    </svg>
                    <span>1.2%</span>
                </div>
            </div>
        </div>

        {{-- Total Unit Usaha --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 transition-all shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Unit Usaha</p>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-500 dark:text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H21m-9 0H3m2.25-1.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H9m-6 0V3.75A2.25 2.25 0 015.25 1.5h13.5A2.25 2.25 0 0121 3.75V21"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $totalUnits }}</p>
                <p class="mt-2 text-xs text-neutral-400"><span class="font-semibold text-emerald-500">{{ $activeUnits }} Aktif</span> dari seluruh jurusan</p>
            </div>
        </div>

        {{-- Total Admin Pengelola --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 transition-all shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Total Admin</p>
                <span class="p-2 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-500 dark:text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">{{ $totalAdmins }}</p>
                <p class="mt-2 text-xs text-neutral-400">Staf pengelola unit terdaftar</p>
            </div>
        </div>

        {{-- Status Sistem --}}
        <div class="bg-white dark:bg-slate-800 rounded-lg border border-neutral-100 dark:border-slate-700 p-4 transition-all shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-neutral-400">Status Sistem</p>
                <span class="p-2 rounded-xl {{ $inactiveUnits === 0 ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-500 dark:text-amber-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                </span>
            </div>
            <div class="mt-4">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $inactiveUnits === 0 ? 'bg-emerald-400' : 'bg-amber-400' }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ $inactiveUnits === 0 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    </span>
                    <p class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">
                        {{ $inactiveUnits === 0 ? 'Optimal' : 'Perlu Perhatian' }}
                    </p>
                </div>
                <p class="mt-2 text-xs text-neutral-400">
                    {{ $inactiveUnits === 0 ? 'Semua layanan beroperasional' : $inactiveUnits . ' unit perlu penanganan' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ================= GRAFIK KONTRIBUSI OMZET PER UNIT USAHA ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Widget Chart Donut (ApexCharts + Alpine.js) --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Kontribusi Omzet per Unit Usaha</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Proporsi pembagian total omzet seluruh unit bisnis</p>
                </div>
            </div>

            {{-- Container ApexChart --}}
            <div x-data="{
                    labels: @js($revenueContribution['labels']),
                    series: @js($revenueContribution['series']),
                    init() {
                        let options = {
                            series: this.series,
                            labels: this.labels,
                            chart: {
                                type: 'donut',
                                height: 310,
                                fontFamily: 'Plus Jakarta Sans, Inter, sans-serif'
                            },
                            colors: ['#2563EB', '#38BDF8', '#F43F5E', '#8B5CF6', '#F59E0B'],
                            stroke: { width: 3, colors: ['#ffffff'] },
                            legend: {
                                position: 'bottom',
                                fontSize: '12px',
                                fontWeight: 500,
                                labels: { colors: '#64748B' },
                                markers: { radius: 12 }
                            },
                            dataLabels: { enabled: false },
                            tooltip: {
                                theme: 'light',
                                y: {
                                    formatter: function(val) {
                                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                                    }
                                }
                            },
                            plotOptions: {
                                pie: {
                                    expandOnClick: true,
                                    donut: {
                                        size: '74%',
                                        labels: {
                                            show: true,
                                            name: {
                                                show: true,
                                                fontSize: '12px',
                                                fontWeight: '600',
                                                color: '#94A3B8',
                                                offsetY: -4
                                            },
                                            value: {
                                                show: true,
                                                fontSize: '20px',
                                                fontWeight: '800',
                                                color: '#0F172A',
                                                offsetY: 6,
                                                formatter: function (val) {
                                                    return 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                                }
                                            },
                                            total: {
                                                show: true,
                                                label: 'Total Omzet',
                                                fontSize: '12px',
                                                fontWeight: '600',
                                                color: '#94A3B8',
                                                formatter: function (w) {
                                                    let total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                    return 'Rp ' + (total / 1000000).toFixed(1) + ' Jt';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        let chart = new ApexCharts(this.$refs.chart, options);
                        chart.render();
                    }
                }"
                class="w-full flex justify-center items-center py-2">
                <div x-ref="chart" class="w-full"></div>
            </div>
        </div>

        {{-- Widget Rincian & Peringkat Pendapatan Unit --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-4 shadow-sm shadow-black/[0.02] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-neutral-900 dark:text-white tracking-tight">Peringkat Omzet</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Kontribusi unit bisnis</p>
                    </div>
                    <span class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 bg-neutral-100/80 dark:bg-slate-900/80 px-2.5 py-1 rounded-[2px] border border-neutral-200/50 dark:border-slate-700">
                        Top 5
                    </span>
                </div>

                <div class="space-y-3.5">
                    @php
                        $colors = [
                            ['bg' => 'bg-blue-600', 'badge' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'],
                            ['bg' => 'bg-sky-400', 'badge' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400'],
                            ['bg' => 'bg-rose-500', 'badge' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-400'],
                            ['bg' => 'bg-purple-500', 'badge' => 'bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400'],
                            ['bg' => 'bg-amber-500', 'badge' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400'],
                        ];
                    @endphp

                    @foreach($revenueContribution['labels'] as $index => $label)
                        @php
                            $nominal = $revenueContribution['series'][$index];
                            $percent = $revenueContribution['percentages'][$index];
                            $colorScheme = $colors[$index % count($colors)];
                        @endphp
                        <div class="group p-2 rounded-2xl hover:bg-neutral-50 dark:hover:bg-slate-900/50 transition-all">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div class="flex items-center gap-2.5 truncate max-w-[60%]">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $colorScheme['bg'] }} shrink-0"></span>
                                    <span class="font-bold text-neutral-800 dark:text-neutral-200 truncate group-hover:text-neutral-900 dark:group-hover:text-white">{{ $label }}</span>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md {{ $colorScheme['badge'] }}">
                                        {{ $percent }}%
                                    </span>
                                    <span class="font-extrabold text-neutral-900 dark:text-white text-xs">
                                        Rp {{ number_format($nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="w-full bg-neutral-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                <div class="{{ $colorScheme['bg'] }} h-full rounded-full transition-all duration-700" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ================= KESEHATAN UNIT USAHA ================= --}}
    <section id="kesehatan-unit" class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-neutral-900 dark:text-white tracking-tight">Kesehatan Unit Usaha</h2>
                <p class="text-xs text-neutral-400">Status operasional dan pengelola setiap unit usaha</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text"
                        wire:model.live.debounce.300ms="searchUnit"
                        placeholder="Cari unit..."
                        class="w-48 sm:w-60 pl-9 pr-3 py-2.5 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 rounded-[3px] focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 transition-all shadow-sm shadow-black/[0.02]">
                    <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
            </div>
        </div>

        {{-- Horizontal Scroll Container --}}
        <div class="flex overflow-x-auto gap-3 pb-2 snap-x snap-mandatory scrollbar-thin scrollbar-thumb-neutral-200 scrollbar-track-transparent">
            @forelse ($units as $unit)
                @php $admin = $unit->users->first(); @endphp
                <div class="shrink-0 w-64 snap-start bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 p-4 hover:shadow-md hover:border-neutral-200 dark:hover:border-slate-600 transition-all flex flex-col justify-between group shadow-sm shadow-black/[0.02]">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <span class="h-10 w-10 rounded-sm bg-red-50 dark:bg-red-950/50 text-red-500 dark:text-red-400 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ strtoupper(substr($unit->name, 0, 1)) }}
                            </span>
                            <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-[2px] {{ $unit->is_active ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' }}">
                                {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <h3 class="mt-3 text-sm font-bold text-neutral-900 dark:text-white group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-colors leading-tight truncate">
                            {{ $unit->name }}
                        </h3>
                        <p class="text-[11px] text-neutral-400 mt-0.5 truncate">Jurusan: {{ $unit->department ?? '-' }}</p>

                        <div class="mt-4 pt-3 border-t border-neutral-100 dark:border-slate-700 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-neutral-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <p class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 truncate">
                                {{ $admin ? $admin->name : 'Belum Ada Admin' }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ Route::has('unit.dashboard') ? route('unit.dashboard', $unit->slug ?? $unit->id) : '#' }}"
                        class="mt-4 w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-3 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-50 dark:bg-slate-900 hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-[2px] transition-all">
                        <span>Buka Dashboard</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a>
                </div>
            @empty
                <div class="w-full bg-white dark:bg-slate-800 rounded-md p-6 text-center border border-neutral-100 dark:border-slate-700 text-xs text-neutral-400">
                    Tidak ditemukan unit usaha yang sesuai dengan pencarian.
                </div>
            @endforelse
        </div>
    </section>

    {{-- ================= TRANSAKSI TERKINI & LOG AKTIVITAS ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pt-2">

        {{-- Transaksi Terkini --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-slate-700">
                <div>
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white">Transaksi Terkini</h2>
                    <p class="text-xs text-neutral-400">Aktivitas keuangan terbaru dari seluruh unit usaha</p>
                </div>
                <a href="{{ Route::has('master.transactions.index') ? route('master.transactions.index') : '#' }}" class="text-xs font-bold text-neutral-900 dark:text-white hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors shrink-0">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="mt-2 divide-y divide-neutral-100 dark:divide-slate-700">
                @forelse ($recentTransactions as $tr)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="h-9 w-9 rounded-2xl flex items-center justify-center shrink-0 {{ $tr->type === 'income' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-500 dark:text-rose-400' }}">
                                @if($tr->type === 'income')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6-6m-6 6l-6-6"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6 6m6-6l6 6"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-neutral-900 dark:text-white truncate">{{ $tr->description }}</p>
                                <p class="text-[11px] text-neutral-400 truncate">
                                    {{ $tr->unit->name ?? '-' }} &middot; {{ optional($tr->transaction_date)->format('d M Y') ?? '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-bold {{ $tr->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $tr->type === 'income' ? '+' : '-' }} Rp {{ number_format($tr->amount, 0, ',', '.') }}
                            </p>
                            <span class="mt-0.5 inline-block px-2 py-0.5 text-[9px] font-bold rounded-full uppercase
                                {{ $tr->status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : ($tr->status === 'pending' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400') }}">
                                {{ $tr->status }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-neutral-400 italic">
                        Belum ada transaksi tercatat.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Log Aktivitas Sistem --}}
        <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 p-5 shadow-sm shadow-black/[0.02] flex flex-col">
            <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-slate-700">
                <div>
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white">Log Aktivitas</h2>
                    <p class="text-xs text-neutral-400">Riwayat aksi sistem terkini dari seluruh unit</p>
                </div>
                <a href="{{ Route::has('master.audit-logs.index') ? route('master.audit-logs.index') : '#' }}" class="text-xs font-bold text-neutral-900 dark:text-white hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors shrink-0">
                    Lihat Semua Audit Log &rarr;
                </a>
            </div>

            <div class="mt-2 space-y-1">
                @forelse ($logs as $log)
                    @php $info = $this->eventInfo($log->event); @endphp
                    <div class="p-3 rounded-md hover:bg-neutral-50 dark:hover:bg-slate-900/50 border border-transparent hover:border-neutral-100 dark:hover:border-slate-700 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="h-9 w-9 rounded-2xl bg-neutral-50 dark:bg-slate-900 text-neutral-500 dark:text-neutral-400 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded-[2px] border {{ $info['class'] }}">
                                        {{ $info['label'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-neutral-400 truncate mt-0.5">
                                    Oleh <span class="font-semibold text-neutral-600 dark:text-neutral-300">{{ $log->user->name ?? $log->identifier ?? 'Sistem' }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="text-[11px] font-medium text-neutral-300 dark:text-slate-600 shrink-0 self-start sm:self-center pl-12 sm:pl-0">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-neutral-400 italic">
                        Belum ada aktivitas tercatat.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= TABEL ADMIN & HAK AKSES ================= --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02] flex flex-col justify-between">
        <div>
            {{-- Header Tabel & Filter --}}
            <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white">Admin &amp; Hak Akses</h2>
                    <p class="text-xs text-neutral-400">Daftar staf pengelola sistem dan unit</p>
                </div>

                {{-- Quick Search Table --}}
                <div class="relative">
                    <input type="text"
                        wire:model.live.debounce.300ms="searchAdmin"
                        placeholder="Cari nama/email..."
                        class="w-full sm:w-64 pl-9 pr-3 py-2.5 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 transition-all shadow-sm shadow-black/[0.02]">
                    <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                </div>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                        <tr>
                            <th class="px-5 py-3.5">Pengguna</th>
                            <th class="px-5 py-3.5">Unit Kerja</th>
                            <th class="px-5 py-3.5">Akses</th>
                            <th class="px-5 py-3.5">Login Terakhir</th>
                            <th class="px-5 py-3.5 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="h-9 w-9 rounded-2xl bg-neutral-50 dark:bg-slate-900 text-neutral-600 dark:text-neutral-300 flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('') }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-neutral-900 dark:text-white truncate text-xs sm:text-sm">{{ $user->name }}</p>
                                            <p class="text-[11px] text-neutral-400 truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-neutral-500 dark:text-neutral-400 font-medium">
                                    {{ (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? 'Semua Unit' : ($user->unit->name ?? '—') }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 text-[10px] font-bold tracking-wide rounded-[2px] {{ (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400' : 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' }}">
                                        {{ (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) ? 'Master Admin' : 'Admin Unit' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-neutral-400">
                                    {{ $user->last_login_at ? ucfirst($user->last_login_at->diffForHumans()) : 'Belum pernah' }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-slate-600' }}"></span>
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-neutral-400">
                                    Data admin tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer Tabel --}}
        <div class="p-4 border-t border-neutral-100 dark:border-slate-700 bg-neutral-50/40 dark:bg-slate-900/40 flex items-center justify-between text-xs text-neutral-400">
            <span>Menampilkan {{ count($users) }} admin terdaftar</span>
            <a href="{{ Route::has('master.users.index') ? route('master.users.index') : '#' }}" class="font-bold text-neutral-900 dark:text-white hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">Kelola Semua Admin &rarr;</a>
        </div>
    </div>

    {{-- ================= MODAL DIALOGS (ALPINE.JS DEMO) ================= --}}

    {{-- Modal Admin Baru --}}
    <div x-show="showAdminModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showAdminModal" x-transition.opacity class="fixed inset-0 bg-neutral-900/40 backdrop-blur-xs" @click="showAdminModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showAdminModal" x-transition.scale.95 class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-md text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-slate-700">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">+ Tambah Admin Baru</h3>
                    <button @click="showAdminModal = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form class="mt-4 space-y-3" @submit.prevent="showAdminModal = false">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Lengkap</label>
                        <input type="text" placeholder="Masukkan nama" class="w-full px-3 py-2.5 text-xs border border-neutral-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Alamat Email</label>
                        <input type="email" placeholder="email@sekolah.sch.id" class="w-full px-3 py-2.5 text-xs border border-neutral-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Kerja</label>
                        <select class="w-full px-3 py-2.5 text-xs border border-neutral-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                            <option value="">Pilih Unit...</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pt-3 flex justify-end gap-2">
                        <button type="button" @click="showAdminModal = false" class="px-4 py-2.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-full transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-white bg-blue-900 rounded-full shadow-sm shadow-blue-900/20 hover:bg-blue-950 transition-colors">Simpan Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Unit Usaha --}}
    <div x-show="showUnitModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showUnitModal" x-transition.opacity class="fixed inset-0 bg-neutral-900/40 backdrop-blur-xs" @click="showUnitModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showUnitModal" x-transition.scale.95 class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-md text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-slate-700">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white">+ Tambah Unit Usaha</h3>
                    <button @click="showUnitModal = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form class="mt-4 space-y-3" @submit.prevent="showUnitModal = false">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Unit Usaha</label>
                        <input type="text" placeholder="Contoh: Kantin Utama, Unit Print & Copy" class="w-full px-3 py-2.5 text-xs border border-neutral-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jurusan / Departemen</label>
                        <input type="text" placeholder="Contoh: Tata Boga, RPL, Dll" class="w-full px-3 py-2.5 text-xs border border-neutral-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400">
                    </div>
                    <div class="pt-3 flex justify-end gap-2">
                        <button type="button" @click="showUnitModal = false" class="px-4 py-2.5 text-xs font-semibold text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-full transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2.5 text-xs font-semibold text-white bg-blue-900 rounded-full shadow-sm shadow-blue-900/20 hover:bg-blue-950 transition-colors">Simpan Unit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>