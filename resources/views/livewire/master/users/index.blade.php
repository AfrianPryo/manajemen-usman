<div class="w-full max-w-[1500px] mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
        <div>
            <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">Kelola akun, peran, dan hak akses pengguna sistem.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Search Bar --}}
            <div class="relative w-full sm:w-72">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Cari nama, username, NIP, no. HP..."
                    class="w-full pl-9 pr-3 py-2.5 text-xs bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-all shadow-sm shadow-black/[0.02]"
                >
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-neutral-400 absolute left-3 top-3" />
            </div>

            {{-- Button Tambah Admin --}}
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 cursor-pointer"
            >
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Tambah Admin</span>
            </button>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 overflow-hidden shadow-sm shadow-black/[0.02]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-neutral-50/70 dark:bg-slate-900/50 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 border-b border-neutral-100 dark:border-slate-700">
                    <tr>
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Identitas Pegawai</th>
                        <th class="px-5 py-3.5">Unit Usaha</th>
                        <th class="px-5 py-3.5">Peran</th>
                        <th class="px-5 py-3.5 text-center">Status Akun</th>
                        <th class="px-5 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-slate-700">
                    @forelse($users as $user)
                    <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-neutral-50/60 dark:hover:bg-slate-700/30 transition-colors">

                        {{-- Pengguna --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 rounded-sm bg-blue-50 dark:bg-slate-900 text-[#0d3b74] dark:text-neutral-300 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('') }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-neutral-900 dark:text-white truncate text-xs">{{ $user->name }}</p>
                                    <p class="text-[11px] text-neutral-400 font-mono">@ {{ $user->username }}</p>
                                    <p class="text-[11px] text-neutral-400 font-mono flex items-center gap-1 mt-0.5">
                                        <x-heroicon-o-phone class="w-3 h-3 shrink-0" />
                                        {{ $user->phone ?: '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Identitas Pegawai --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-sm border {{ $user->employee_status === 'nip' ? 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700/60 dark:text-slate-300 dark:border-slate-600' : 'bg-neutral-50 text-neutral-500 border-neutral-200 dark:bg-slate-900 dark:text-neutral-400 dark:border-slate-700' }}">
                                {{ $user->employee_status === 'nip' ? 'NIP' : 'Non NIP' }}
                            </span>
                        </td>

                        {{-- Unit Usaha --}}
                        <td class="px-5 py-3.5 whitespace-nowrap text-xs text-neutral-700 dark:text-neutral-300 font-medium">
                            @if(method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin())
                                <span class="text-xs font-medium text-neutral-400 italic">Semua Unit</span>
                            @else
                                <span class="text-xs font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ $user->unit->name ?? '-' }}
                                </span>
                            @endif
                        </td>

                        {{-- Role --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-bold tracking-wide rounded-sm {{ method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'bg-[#0d3b74] text-white' : 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' }}">
                                {{ method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'Master Admin' : 'Unit Admin' }}
                            </span>
                        </td>

                        {{-- Status Keaktifan --}}
                        <td class="px-5 py-3.5 whitespace-nowrap text-center">
                            @if ($user->id === auth()->id())
                                <span class="inline-flex items-center px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
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
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800 flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-sm bg-emerald-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-sm bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800 flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-sm bg-rose-500"></span> Nonaktif
                                        </span>
                                    @endif
                                </button>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-3.5 whitespace-nowrap text-center">
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-neutral-400 dark:text-neutral-500 italic">Akun Anda</span>
                            @else
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Edit --}}
                                    <button
                                        wire:click="editUser({{ $user->id }})"
                                        title="Edit Admin"
                                        class="p-1.5 text-neutral-500 hover:text-neutral-900 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-slate-700 rounded-sm transition-all cursor-pointer"
                                    >
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
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
                                        class="p-1.5 text-amber-600 hover:text-amber-800 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-sm transition-all cursor-pointer"
                                    >
                                        <x-heroicon-o-key class="w-4 h-4" />
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    @if ($user->is_active)
                                        <button
                                            type="button"
                                            disabled
                                            title="Nonaktifkan akun terlebih dahulu untuk menghapus"
                                            class="p-1.5 text-neutral-300 dark:text-slate-600 cursor-not-allowed"
                                        >
                                            <x-heroicon-o-trash class="w-4 h-4" />
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
                                            class="p-1.5 text-rose-600 hover:text-rose-800 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-sm transition-all cursor-pointer"
                                        >
                                            <x-heroicon-o-trash class="w-4 h-4" />
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm p-4 overflow-y-auto">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-sm border border-neutral-200 dark:border-slate-700 shadow-2xl overflow-hidden my-8 animate-in fade-in zoom-in duration-150">

                {{-- Modal Header --}}
                <div class="p-5 border-b border-neutral-100 dark:border-slate-700 flex items-center justify-between bg-neutral-50/50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="text-lg font-bold text-neutral-900 dark:text-white">
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
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400" placeholder="Contoh: Budi Santoso">
                        @error('name') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Pegawai --}}
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Status Pegawai</label>
                        <select wire:model.live="employee_status" class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            <option value="nip">Pegawai NIP</option>
                            <option value="non_nip">Pegawai Non-NIP</option>
                        </select>
                        @error('employee_status') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- NIP --}}
                    @if($employee_status === 'nip')
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">NIP (18 Digit)</label>
                            <input type="text" wire:model="nip" maxlength="18" class="w-full px-3 py-2.5 border rounded-sm text-sm font-mono bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400" placeholder="199001012023011001">
                            @error('nip') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Nomor HP / WhatsApp --}}
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Nomor HP / WhatsApp</label>
                        <input type="text" wire:model="phone" inputmode="numeric" class="w-full px-3 py-2.5 border rounded-sm text-sm font-mono bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400" placeholder="08xxxxxxxxxx">
                        <p class="text-[11px] text-neutral-400 mt-0.5">Dipakai sistem untuk mengirim notifikasi &amp; kode OTP (Fonnte) ke akun ini.</p>
                        @error('phone') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Role Admin --}}
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Role / Peran</label>
                        <select wire:model.live="role" class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            <option value="unit-admin">Unit Admin (Pengelola Usaha)</option>
                            <option value="master-admin">Master Admin (Akses Penuh)</option>
                        </select>
                        @error('role') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Pilih Unit Usaha --}}
                    @if($role === 'unit-admin')
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">Unit Usaha</label>
                            <select wire:model="unit_id" class="w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Info Box --}}
                    <div class="p-3 bg-blue-50/60 dark:bg-blue-950/20 rounded-sm text-xs text-blue-950 dark:text-blue-300 space-y-1">
                        @if(!isset($isEditing) || !$isEditing)
                            <div>💡 <strong>Username &amp; Password</strong> akan dibuat otomatis oleh sistem setelah disimpan.</div>
                        @else
                            <div>💡 Perubahan data tidak mereset password. Gunakan fitur <strong>Reset PW</strong> jika pengguna lupa password.</div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 flex justify-end gap-2 border-t border-neutral-100 dark:border-slate-700">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2.5 border border-neutral-200 dark:border-slate-700 rounded-sm text-sm font-semibold hover:bg-neutral-50 dark:hover:bg-slate-700 dark:text-white transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2.5 bg-blue-900 text-white rounded-sm text-sm font-semibold hover:bg-blue-950 transition-colors shadow-sm shadow-blue-900/20 cursor-pointer">
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