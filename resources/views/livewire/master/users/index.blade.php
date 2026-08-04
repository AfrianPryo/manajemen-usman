<div class="p-6 max-w-7xl mx-auto space-y-6">
    
    {{-- Top Header --}}
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kelola seluruh akun admin sistem.</p>
        </div>
        <button 
            wire:click="openCreateModal" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition inline-flex items-center gap-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Admin
        </button>
    </div>

    {{-- Alert Kredensial Baru (Hasil Auto-Generate) --}}
    @if ($createdCredentials)
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl space-y-2">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-bold text-emerald-900 dark:text-emerald-300 text-sm">🎉 Akun Admin Berhasil Dibuat!</h3>
                    <p class="text-xs text-emerald-700 dark:text-emerald-400">Silakan salin kredensial awal ini untuk diserahkan kepada pengguna:</p>
                </div>
                <button wire:click="$set('createdCredentials', null)" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-200 font-bold text-sm">✕</button>
            </div>
            <div class="p-3 bg-white dark:bg-slate-900 rounded-lg border border-emerald-100 dark:border-emerald-900 font-mono text-xs sm:text-sm space-y-1">
                <div><span class="text-gray-500 dark:text-gray-400">Nama:</span> <strong>{{ $createdCredentials['name'] }}</strong></div>
                <div><span class="text-gray-500 dark:text-gray-400">Username / ID Login:</span> <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 font-bold px-1.5 py-0.5 rounded">{{ $createdCredentials['username'] }}</span></div>
                <div><span class="text-gray-500 dark:text-gray-400">Password Sementara:</span> <span class="bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 font-bold px-1.5 py-0.5 rounded">{{ $createdCredentials['password'] }}</span></div>
            </div>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-500">* Pengguna wajib mengganti password saat pertama kali login.</p>
        </div>
    @endif

    {{-- Search Filter --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-4">
        <div class="relative">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Cari nama, username, atau NIP..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    {{-- Table Admin --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-slate-900/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-3">Nama & Username</th>
                        <th class="px-6 py-3">NIP / Status</th>
                        <th class="px-6 py-3">Unit Usaha</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30">
                        {{-- Nama & Username --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('') }}
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs font-mono text-blue-600 dark:text-blue-400">@ {{ $user->username }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- NIP / Status Pegawai --}}
                        <td class="px-6 py-4">
                            <p class="text-gray-900 dark:text-gray-200 font-mono text-xs">{{ $user->official_id }}</p>
                            <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase font-semibold mt-0.5">
                                {{ $user->employee_status ?? 'Tetap' }}
                            </span>
                        </td>

                        {{-- Unit Usaha --}}
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            {{ $user->isMasterAdmin() ? 'Semua Unit' : ($user->unit->name ?? '-') }}
                        </td>

                        {{-- Role --}}
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md {{ $user->isMasterAdmin() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }}">
                                {{ $user->isMasterAdmin() ? 'Master' : 'Unit' }}
                            </span>
                        </td>

                        {{-- Status Keaktifan --}}
                        <td class="px-6 py-4">
                            <button wire:click="toggleUserStatus({{ $user->id }})" class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                                <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-right space-x-2">
                            <button class="text-amber-600 hover:underline text-xs font-medium">Reset PW</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">Tidak ada data admin.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-gray-100 dark:border-slate-700">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal Tambah Admin --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-800 rounded-xl max-w-lg w-full border border-gray-200 dark:border-slate-700 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Admin Baru</h3>
                    <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap *</label>
                        <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white" placeholder="Contoh: Budi Santoso">
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Status Pegawai --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status Pegawai *</label>
                        <select wire:model.live="employee_status" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white">
                            <option value="tetap">Pegawai Tetap (PNS / Tetap)</option>
                            <option value="part_time">Part Time</option>
                            <option value="magang">Siswa / Magang</option>
                        </select>
                        @error('employee_status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- NIP (Wajib hanya jika pegawai tetap) --}}
                    @if($employee_status === 'tetap')
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">NIP (18 Digit) *</label>
                            <input type="text" wire:model="nip" maxlength="18" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white" placeholder="199001012023011001">
                            @error('nip') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    {{-- Role Admin --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Role / Peran *</label>
                        <select wire:model.live="role" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white">
                            <option value="unit-admin">Unit Admin (Pengelola Usaha)</option>
                            <option value="master-admin">Master Admin (Akses Penuh)</option>
                        </select>
                        @error('role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Pilih Unit Usaha (Hanya jika unit-admin) --}}
                    @if($role === 'unit-admin')
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Unit Usaha *</label>
                            <select wire:model="unit_id" class="w-full px-3 py-2 border rounded-lg text-sm bg-white dark:bg-slate-900 border-gray-300 dark:border-slate-700 dark:text-white">
                                <option value="">-- Pilih Unit Usaha --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="p-3 bg-blue-50 dark:bg-slate-900/60 rounded-lg text-xs text-blue-800 dark:text-blue-300 space-y-1">
                        <div>💡 <strong>Username & Password</strong> akan dibuat otomatis oleh sistem.</div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-4 flex justify-end gap-2">
                        <button type="button" wire:click="closeCreateModal" class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm font-semibold hover:bg-gray-50 dark:hover:bg-slate-700 dark:text-white">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <span wire:loading.remove wire:target="save">Simpan & Generate Kredensial</span>
                            <span wire:loading wire:target="save">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>