<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">
    
    {{-- Header & Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Kelola akun, peran, dan hak akses pengguna sistem.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Bar --}}
            <div class="relative min-w-[240px] sm:w-72">
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Cari nama, username, NIP, no. HP..."
                    class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-[3px] bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500/10 focus:border-red-400 shadow-sm"
                >
            </div>

            {{-- Button Tambah Admin --}}
            <button 
                wire:click="openCreateModal" 
                class="px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-[3px] transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>Tambah Admin</span>
            </button>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Identitas Pegawai</th>
                        <th class="px-4 py-3.5">Unit Usaha</th>
                        <th class="px-4 py-3.5">Peran</th>
                        <th class="px-4 py-3.5 text-center">Status Akun</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($users as $user)
                    <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">
                        
                        {{-- Pengguna --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-neutral-100 dark:bg-slate-700 text-neutral-700 dark:text-neutral-200 flex items-center justify-center text-xs font-bold shrink-0 border border-neutral-200/60 dark:border-slate-600">
                                    {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('') }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-neutral-900 dark:text-white truncate text-xs">{{ $user->name }}</p>
                                    <p class="text-[11px] text-neutral-400 font-mono">@ {{ $user->username }}</p>
                                    <p class="text-[11px] text-neutral-400 font-mono flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $user->phone ?: '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Identitas Pegawai --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] border {{ $user->employee_status === 'nip' ? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/60 dark:text-slate-300 dark:border-slate-600' : 'bg-neutral-50 text-neutral-500 border-neutral-200 dark:bg-slate-900 dark:text-neutral-400 dark:border-slate-700' }}">
                                {{ $user->employee_status === 'nip' ? 'NIP' : 'Non NIP' }}
                            </span>
                        </td>

                        {{-- Unit Usaha --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-neutral-700 dark:text-neutral-300 font-medium">
                            @if(method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin())
                                <span class="text-xs font-medium text-neutral-400 italic">Semua Unit</span>
                            @else
                                <span class="text-xs font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ $user->unit->name ?? '-' }}
                                </span>
                            @endif
                        </td>

                        {{-- Role --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] border {{ method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 border-indigo-200/60 dark:border-indigo-800' : 'bg-sky-50 text-sky-600 dark:bg-sky-950/50 dark:text-sky-400 border-sky-200/60 dark:border-sky-800' }}">
                                {{ method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'Master Admin' : 'Unit Admin' }}
                            </span>
                        </td>

                        {{-- Status Keaktifan --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            @if ($user->id === auth()->id())
                                <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    Aktif (Saya)
                                </span>
                            @else
                                <button 
                                    wire:click="toggleUserStatus({{ $user->id }})" 
                                    type="button"
                                    title="Klik untuk mengubah status"
                                    class="inline-flex items-center gap-1.5 transition hover:opacity-80 cursor-pointer"
                                >
                                    @if($user->is_active)
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800 flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-[3px] bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-[3px] bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800 flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-[3px] bg-rose-500"></span> Nonaktif
                                        </span>
                                    @endif
                                </button>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-neutral-400 dark:text-neutral-500 italic">Akun Anda</span>
                            @else
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Edit --}}
                                    <button 
                                        wire:click="editUser({{ $user->id }})"
                                        title="Edit Admin"
                                        class="p-1.5 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-md transition-all cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Tombol Reset PW --}}
                                    <button 
                                        type="button"
                                        x-on:click.prevent="$store.confirmDialog.open({
                                            message: 'Apakah Anda yakin ingin mereset password akun {{ $user->name }}?',
                                            confirmText: 'Ya, Reset',
                                            variant: 'default',
                                            onConfirm: () => $wire.resetPassword({{ $user->id }})
                                        })"
                                        title="Reset Password"
                                        class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-md transition-all cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/></svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    @if ($user->is_active)
                                        <button 
                                            type="button"
                                            disabled
                                            title="Nonaktifkan akun terlebih dahulu untuk menghapus"
                                            class="p-1.5 text-neutral-300 dark:text-slate-600 cursor-not-allowed"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @else
                                        <button 
                                            type="button"
                                            x-on:click.prevent="$store.confirmDialog.open({
                                                message: 'Apakah Anda yakin ingin menghapus akun {{ $user->name }} secara PERMANEN?',
                                                confirmText: 'Ya, Hapus Permanen',
                                                variant: 'danger',
                                                onConfirm: () => $wire.deleteUser({{ $user->id }})
                                            })"
                                            title="Hapus Akun"
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-all cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-xs text-neutral-400">
                            Tidak ada data admin ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="px-4 py-3 border-t border-neutral-100 dark:border-slate-700 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="w-full flex justify-end">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Form (Tambah / Edit Admin) --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/60 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-xl rounded-lg border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
                
                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                            {{ isset($isEditing) && $isEditing ? 'Edit Data Admin' : 'Tambah Admin Baru' }}
                        </h3>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            {{ isset($isEditing) && $isEditing ? 'Perbarui informasi dan hak akses akun admin.' : 'Buat akun pengguna baru dan tentukan hak akses unit usahanya.' }}
                        </p>
                    </div>
                    <button wire:click="closeCreateModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-2xl font-bold leading-none cursor-pointer">&times;</button>
                </div>

                {{-- Modal Form Body --}}
                <form wire:submit="{{ isset($isEditing) && $isEditing ? 'updateUser' : 'save' }}" class="p-6 space-y-4">
                    
                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="Contoh: Budi Santoso">
                        @error('name') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Pegawai --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Status Pegawai <span class="text-red-500">*</span></label>
                        <select wire:model.live="employee_status" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            <option value="nip">Pegawai NIP</option>
                            <option value="non_nip">Pegawai Non-NIP</option>
                        </select>
                        @error('employee_status') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- NIP --}}
                    @if($employee_status === 'nip')
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">NIP (18 Digit) <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="nip" maxlength="18" class="w-full px-3.5 py-2 text-xs font-mono font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="199001012023011001">
                            @error('nip') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Nomor HP / WhatsApp --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="phone" inputmode="numeric" class="w-full px-3.5 py-2 text-xs font-mono font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500" placeholder="08xxxxxxxxxx">
                        <p class="text-[11px] text-neutral-400 mt-0.5">Dipakai sistem untuk mengirim notifikasi &amp; kode OTP (Fonnte) ke akun ini.</p>
                        @error('phone') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Role Admin --}}
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Role / Peran <span class="text-red-500">*</span></label>
                        <select wire:model.live="role" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            <option value="unit-admin">Unit Admin (Pengelola Usaha)</option>
                            <option value="master-admin">Master Admin (Akses Penuh)</option>
                        </select>
                        @error('role') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Pilih Unit Usaha --}}
                    @if($role === 'unit-admin')
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha <span class="text-red-500">*</span></label>
                            <select wire:model="unit_id" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <span class="text-[11px] text-rose-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Info Box --}}
                    <div class="p-3 bg-neutral-50 dark:bg-slate-900/60 rounded-md border border-neutral-200 dark:border-slate-700 text-[11px] text-neutral-500 dark:text-neutral-400">
                        @if(!isset($isEditing) || !$isEditing)
                            💡 <strong>Username & Password</strong> akan dibuat otomatis oleh sistem setelah disimpan.
                        @else
                            💡 Perubahan data tidak mereset password. Gunakan fitur <strong>Reset PW</strong> jika pengguna lupa password.
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 rounded-md hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm cursor-pointer">
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

    {{-- Modal Pop-up Kredensial (username/password baru) -- komponen bersama,
         dipakai juga di notification-sidebar & halaman notifikasi penuh saat
         Admin Master menyetujui permintaan reset password. Lihat
         resources/views/components/credentials-modal.blade.php --}}
    <x-credentials-modal :credentials="$createdCredentials" />

</div>