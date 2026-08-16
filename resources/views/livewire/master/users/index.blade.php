<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans text-slate-800 dark:text-slate-100">
    
    {{-- Header & Toolbar (Sejajar seperti gambar referensi) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola akun, peran, dan hak akses pengguna sistem.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Search Bar --}}
            <div class="relative min-w-[260px] sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari nama, username, NIP..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 dark:focus:ring-white/10 shadow-sm transition"
                >
            </div>

            {{-- Button Tambah Admin (Style Dark Minimalis) --}}
            <button 
                wire:click="openCreateModal" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 text-white text-xs font-semibold rounded-xl transition shadow-sm active:scale-[0.98]"
            >
                <svg class="w-4 h-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Tambah Admin</span>
            </button>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="bg-white dark:bg-slate-800/80 rounded-2xl border border-slate-200/70 dark:border-slate-700/60 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead class="bg-slate-50/50 dark:bg-slate-900/40 text-slate-400 dark:text-slate-400 font-medium border-b border-slate-100 dark:border-slate-700/60">
                    <tr>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Identitas Pegawai</th>
                        <th class="px-6 py-4">Unit Usaha</th>
                        <th class="px-6 py-4">Peran</th>
                        <th class="px-6 py-4">Status Akun</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse($users as $user)
                    <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-slate-50/60 dark:hover:bg-slate-700/20 transition-colors">
                        
                        {{-- Pengguna --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-xs font-bold shrink-0 border border-slate-200/60 dark:border-slate-600/50">
                                    {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('') }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 dark:text-white truncate text-xs">{{ $user->name }}</p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">@ {{ $user->username }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Identitas Pegawai --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-start gap-1">
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-md {{ $user->employee_status === 'nip' ? 'bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-300' : 'bg-slate-50 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                    {{ $user->employee_status === 'nip' ? 'NIP' : 'Non NIP' }}
                                </span>
                            </div>
                        </td>

                        {{-- Unit Usaha --}}
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            @if(method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin())
                                <span class="text-xs font-medium text-slate-400 italic">Semua Unit</span>
                            @else
                                <span class="text-xs font-medium text-slate-800 dark:text-slate-200">
                                    {{ $user->unit->name ?? '-' }}
                                </span>
                            @endif
                        </td>

                        {{-- Role --}}
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-medium rounded-md {{ method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300' }}">
                                {{ method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'Master Admin' : 'Unit Admin' }}
                            </span>
                        </td>

                        {{-- Status Keaktifan (Dot Style ala Gambar Referensi) --}}
                        <td class="px-6 py-4">
                            @if ($user->id === auth()->id())
                                <span class="inline-flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
                                    <span class="h-2 w-2 rounded-full bg-slate-300"></span> Aktif (Saya)
                                </span>
                            @else
                                <button 
                                    wire:click="toggleUserStatus({{ $user->id }})" 
                                    type="button"
                                    title="Klik untuk mengubah status"
                                    class="inline-flex items-center gap-2 font-medium text-xs transition hover:opacity-80"
                                >
                                    @if($user->is_active)
                                        <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-emerald-600 dark:text-emerald-400">Aktif</span>
                                    @else
                                        <span class="flex h-2 w-2 rounded-full bg-rose-500"></span>
                                        <span class="text-rose-500 dark:text-rose-400">Nonaktif</span>
                                    @endif
                                </button>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-right">
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-slate-400 dark:text-slate-500 italic">Akun Anda</span>
                            @else
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    {{-- Tombol Edit --}}
                                    <button 
                                        wire:click="editUser({{ $user->id }})"
                                        title="Edit Admin"
                                        class="p-1.5 text-slate-500 hover:text-slate-900 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Tombol Reset PW --}}
                                    <button 
                                        wire:click="resetPassword({{ $user->id }})"
                                        wire:confirm="Apakah Anda yakin ingin mereset password akun {{ $user->name }}?"
                                        title="Reset Password"
                                        class="p-1.5 text-amber-600 hover:text-amber-700 dark:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-950/40 transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/></svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    @if ($user->is_active)
                                        <button 
                                            type="button"
                                            disabled
                                            title="Nonaktifkan akun terlebih dahulu untuk menghapus"
                                            class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @else
                                        <button 
                                            wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus akun {{ $user->name }} secara PERMANEN?"
                                            title="Hapus Akun"
                                            class="p-1.5 text-rose-500 hover:text-rose-700 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="p-3 bg-slate-100 dark:bg-slate-700/50 rounded-full text-slate-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">Tidak ada data admin ditemukan.</div>
                                <p class="text-[11px] text-slate-400">Coba ubah kata kunci pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-700/60 bg-slate-50/30 dark:bg-slate-900/20">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal Form (Tambah / Edit Admin) --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        {{ isset($isEditing) && $isEditing ? 'Edit Data Admin' : 'Tambah Admin Baru' }}
                    </h3>
                    <button wire:click="closeCreateModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 text-sm">✕</button>
                </div>

                <form wire:submit="{{ isset($isEditing) && $isEditing ? 'updateUser' : 'save' }}" class="p-5 space-y-4">
                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap *</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900/10 outline-none" placeholder="Contoh: Budi Santoso">
                        @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Pegawai --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Pegawai *</label>
                        <select wire:model.live="employee_status" class="w-full px-3 py-2 border rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900/10 outline-none">
                            <option value="nip">Pegawai NIP</option>
                            <option value="non_nip">Pegawai Non-NIP</option>
                        </select>
                        @error('employee_status') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- NIP --}}
                    @if($employee_status === 'nip')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">NIP (18 Digit) *</label>
                            <input type="text" wire:model="nip" maxlength="18" class="w-full px-3 py-2 border rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-slate-900/10 outline-none" placeholder="199001012023011001">
                            @error('nip') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Role Admin --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Role / Peran *</label>
                        <select wire:model.live="role" class="w-full px-3 py-2 border rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900/10 outline-none">
                            <option value="unit-admin">Unit Admin (Pengelola Usaha)</option>
                            <option value="master-admin">Master Admin (Akses Penuh)</option>
                        </select>
                        @error('role') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Pilih Unit Usaha --}}
                    @if($role === 'unit-admin')
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Unit Usaha *</label>
                            <select wire:model="unit_id" class="w-full px-3 py-2 border rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-slate-900/10 outline-none">
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Info Box --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl text-[11px] text-slate-500 dark:text-slate-400">
                        @if(!isset($isEditing) || !$isEditing)
                            💡 <strong>Username & Password</strong> akan dibuat otomatis oleh sistem.
                        @else
                            💡 Perubahan data tidak mereset password. Gunakan fitur <strong>Reset PW</strong> jika pengguna lupa password.
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 text-white rounded-xl text-xs font-semibold transition shadow-sm">
                            <span wire:loading.remove wire:target="{{ isset($isEditing) && $isEditing ? 'updateUser' : 'save' }}">
                                {{ isset($isEditing) && $isEditing ? 'Simpan Perubahan' : 'Simpan & Generate' }}
                            </span>
                            <span wire:loading wire:target="{{ isset($isEditing) && $isEditing ? 'updateUser' : 'save' }}">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL POP-UP KREDENSIAL --}}
    @if ($createdCredentials)
        <div 
            x-data="{ copied: false }" 
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
        >
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-sm w-full border border-slate-200 dark:border-slate-700 shadow-2xl p-6 space-y-4">
                
                <div class="text-center space-y-1">
                    <div class="h-10 w-10 bg-amber-50 dark:bg-amber-950/60 text-amber-600 rounded-full flex items-center justify-center mx-auto text-lg">
                        🔑
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        {{ $createdCredentials['title'] ?? 'Informasi Akun' }}
                    </h3>
                    <p class="text-[11px] text-slate-400">
                        Harap salin kredensial berikut sebelum menutup.
                    </p>
                </div>

                <div class="p-3.5 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs space-y-2 font-mono">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-sans">Nama:</span>
                        <span class="font-semibold text-slate-800 dark:text-white font-sans">{{ $createdCredentials['name'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-sans">Username:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-bold px-1.5 py-0.5 rounded bg-slate-200/60 dark:bg-slate-800">
                            {{ $createdCredentials['username'] }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-sans">Password:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950">
                            {{ $createdCredentials['password'] }}
                        </span>
                    </div>
                </div>

                <button 
                    type="button"
                    @click="
                        navigator.clipboard.writeText('Nama: {{ $createdCredentials['name'] }}\nUsername: {{ $createdCredentials['username'] }}\nPassword: {{ $createdCredentials['password'] }}');
                        copied = true;
                        setTimeout(() => { $wire.set('createdCredentials', null) }, 1000);
                    "
                    :class="copied ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900'"
                    class="w-full py-2.5 text-white font-semibold text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-2"
                >
                    <template x-if="!copied">
                        <span>Salin Kredensial & Tutup</span>
                    </template>
                    <template x-if="copied">
                        <span>Berhasil Disalin!</span>
                    </template>
                </button>

            </div>
        </div>
    @endif

</div>