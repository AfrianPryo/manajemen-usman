<div class="w-full max-w-5xl mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Header --}}
    @if (! $this->isAccountOnlyView())
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white tracking-tight">Pengaturan Sistem</h1>
            <p class="text-xs text-neutral-400 mt-0.5">Kelola profil admin master, preferensi aplikasi, dan konfigurasi fitur.</p>
        </div>
    @endif

    {{-- Flash Notification --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-md bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm flex items-center justify-between">
            <span class="font-medium">{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    @if (! $this->isAccountOnlyView())
        <!-- Navigasi Tab -->
        <div class="inline-flex flex-wrap p-1 bg-neutral-100 dark:bg-slate-900 rounded-lg gap-1">
            <button wire:click="setTab('profile')"
                class="px-4 py-2 text-xs font-bold rounded-md transition-all flex items-center gap-2 {{ $activeTab === 'profile' ? 'bg-blue-900 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                Profil Admin
            </button>

            @if ($this->canAccessFeaturesTab())
                <button wire:click="setTab('features')"
                    class="px-4 py-2 text-xs font-bold rounded-md transition-all flex items-center gap-2 {{ $activeTab === 'features' ? 'bg-blue-900 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Fitur & Modul
                </button>
            @endif
        </div>
    @endif

    <!-- TAB 1: PROFIL ADMIN MASTER -->
    @if($activeTab === 'profile')
        <div class="space-y-5">

            {{-- Informasi Akun (tanpa field nomor WA — pindah ke card terpisah di bawah) --}}
            <form wire:submit="saveProfile" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
                <div>
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Informasi Akun</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Data identitas admin master yang sedang login. Foto dan nama ini akan tampil di sidebar dashboard.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-full border border-neutral-200 dark:border-slate-700 overflow-hidden bg-neutral-50 dark:bg-slate-900 flex items-center justify-center shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif ($existingAvatar)
                            <img src="{{ asset('storage/' . $existingAvatar) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-lg font-bold text-neutral-400">{{ strtoupper(substr($name ?: 'A', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Foto Profil</label>
                        <input type="file" wire:model="avatar" accept="image/*" class="text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-blue-950 dark:file:bg-slate-700 dark:file:text-neutral-300">
                        @error('avatar') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        @error('name') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Username</label>
                        <input type="text" wire:model="username" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        @error('username') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        @error('email') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Status Kepegawaian</label>
                        <select wire:model.live="employeeStatus" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500 cursor-pointer">
                            <option value="non-nip">Non-NIP</option>
                            <option value="nip">NIP (Pegawai Negeri)</option>
                        </select>
                        @error('employeeStatus') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    @if ($employeeStatus === 'nip')
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">NIP</label>
                            <input type="text" wire:model="nip" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                            @error('nip') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                        <span wire:loading.remove wire:target="saveProfile">Simpan Profil</span>
                        <span wire:loading wire:target="saveProfile">Menyimpan...</span>
                    </button>
                </div>
            </form>

            {{-- Card "Ajukan Reset Password" -- KHUSUS mode "hanya akun"
                 (Unit Admin) DAN hanya kalau canRequestPasswordReset() true,
                 yaitu akun yang login benar-benar ber-role 'unit-admin'.
                 Master Admin yang sedang membuka dashboard unit (lewat
                 middleware 'unit.access' untuk monitoring) TIDAK akan
                 melihat card ini sama sekali -- lihat App\Livewire\Unit\
                 Profile\Index::canRequestPasswordReset(). --}}
            @if ($this->isAccountOnlyView() && $this->canRequestPasswordReset())
                <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Ajukan Reset Password</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            Lupa password atau ingin menggantinya? Admin Unit tidak bisa mengubah password sendiri --
                            ajukan permintaan di sini, dan Admin Master akan menyetujui atau menolaknya lewat notifikasi.
                            Password baru akan otomatis terkirim ke WhatsApp Anda ({{ $phone ?: 'nomor belum terdaftar' }})
                            setelah disetujui.
                        </p>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                        <button type="button"
                                x-on:click.prevent="$store.confirmDialog.open({
                                    message: 'Kirim permintaan reset password ke Admin Master?',
                                    confirmText: 'Ya, Kirim',
                                    variant: 'default',
                                    onConfirm: () => $wire.requestPasswordReset()
                                })"
                                wire:loading.attr="disabled"
                                wire:target="requestPasswordReset"
                                class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-md transition-all flex items-center gap-2 shadow-sm disabled:opacity-50">
                            <span wire:loading.remove wire:target="requestPasswordReset">Ajukan Reset Password</span>
                            <span wire:loading wire:target="requestPasswordReset">Mengirim permintaan...</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Card "Ubah Nomor WhatsApp" & "Ubah Password" hanya untuk mode
                 lengkap (Master Admin). Untuk mode "hanya akun" (Unit Admin,
                 lihat isAccountOnlyView()), keduanya disembunyikan -- ganti
                 nomor WA & password Admin Unit tetap lewat Master Admin. --}}
            @if (! $this->isAccountOnlyView())
                {{-- Ubah Nomor WhatsApp — 2 langkah: request OTP -> verifikasi --}}
                <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Ubah Nomor WhatsApp</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            Nomor saat ini: <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $phone ?: 'Belum terdaftar' }}</span>.
                            Nomor ini dipakai sebagai kanal OTP untuk verifikasi keamanan, sehingga perubahan wajib dikonfirmasi lewat OTP.
                        </p>
                    </div>

                    @if (!$phoneOtpRequested)
                        {{-- Langkah 1: input nomor baru + konfirmasi password --}}
                        <form wire:submit="requestPhoneChangeOtp" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nomor WhatsApp Baru</label>
                                    <input type="text" wire:model="newPhone" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                    @error('newPhone') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Konfirmasi Password Anda</label>
                                    <input type="password" wire:model="phoneChangePassword" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                    @error('phoneChangePassword') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                                    <span wire:loading.remove wire:target="requestPhoneChangeOtp">Kirim Kode OTP</span>
                                    <span wire:loading wire:target="requestPhoneChangeOtp">Mengirim...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Langkah 2: verifikasi OTP --}}
                        <form wire:submit="verifyPhoneChangeOtp" class="space-y-4">
                            <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200/60 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-[11px] font-medium px-3 py-2 rounded-md">
                                Kode OTP telah dikirim ke WhatsApp {{ $phone ? 'nomor lama Anda (untuk konfirmasi)' : 'nomor baru (untuk verifikasi kepemilikan)' }}. Kode berlaku 5 menit.
                            </div>

                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kode OTP (6 digit)</label>
                                <input type="text" inputmode="numeric" maxlength="6" wire:model="phoneOtp" placeholder="123456" class="w-full px-3.5 py-2 text-sm font-bold tracking-widest text-center border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                @error('phoneOtp') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="button" wire:click="cancelPhoneOtp" class="px-5 py-2 text-xs font-bold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 rounded-md transition-all">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                                    <span wire:loading.remove wire:target="verifyPhoneChangeOtp">Verifikasi & Simpan</span>
                                    <span wire:loading wire:target="verifyPhoneChangeOtp">Memverifikasi...</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Ubah Password — 2 langkah: request OTP -> verifikasi --}}
                <div class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Ubah Password</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">Gunakan password yang kuat dan tidak digunakan di layanan lain. Perubahan wajib dikonfirmasi lewat OTP WhatsApp.</p>
                    </div>

                    @if (!$passwordOtpRequested)
                        {{-- Langkah 1: input password lama & baru --}}
                        <form wire:submit="requestPasswordChangeOtp" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Password Saat Ini</label>
                                    <input type="password" wire:model="currentPassword" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                    @error('currentPassword') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Password Baru</label>
                                    <input type="password" wire:model="newPassword" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                    @error('newPassword') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    <p class="text-[10px] text-neutral-400 mt-1">Min. 8 karakter, kombinasi huruf besar/kecil, angka, dan simbol.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Konfirmasi Password Baru</label>
                                    <input type="password" wire:model="newPassword_confirmation" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                                    <span wire:loading.remove wire:target="requestPasswordChangeOtp">Kirim Kode OTP</span>
                                    <span wire:loading wire:target="requestPasswordChangeOtp">Mengirim...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Langkah 2: verifikasi OTP --}}
                        <form wire:submit="verifyPasswordChangeOtp" class="space-y-4">
                            <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200/60 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-[11px] font-medium px-3 py-2 rounded-md">
                                Kode OTP telah dikirim ke WhatsApp terdaftar ({{ $phone }}). Kode berlaku 5 menit. Sesi di perangkat lain akan otomatis keluar setelah password berhasil diubah.
                            </div>

                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kode OTP (6 digit)</label>
                                <input type="text" inputmode="numeric" maxlength="6" wire:model="passwordOtp" placeholder="123456" class="w-full px-3.5 py-2 text-sm font-bold tracking-widest text-center border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                @error('passwordOtp') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="button" wire:click="cancelPasswordOtp" class="px-5 py-2 text-xs font-bold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 rounded-md transition-all">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                                    <span wire:loading.remove wire:target="verifyPasswordChangeOtp">Verifikasi & Ubah Password</span>
                                    <span wire:loading wire:target="verifyPasswordChangeOtp">Memverifikasi...</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 2: FITUR & MODUL (pengaturan aplikasi global -- khusus Master Admin) -->
    @if($activeTab === 'features' && $this->canAccessFeaturesTab())
        <form wire:submit="saveFeatures" class="bg-white dark:bg-slate-800 rounded-md border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
            <div>
                <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Fitur & Modul</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Atur identitas aplikasi, modul aktif, dan hak akses untuk unit usaha ritel maupun jasa.</p>
            </div>

            <!-- Bagian: Parameter Aplikasi -->
            <div class="space-y-4">
                <h3 class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Parameter Aplikasi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Aplikasi</label>
                        <input type="text" wire:model="appName" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                        @error('appName') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Bagian: Akses Fitur & Otomatisasi -->
            <div class="pt-4 space-y-4 border-t border-neutral-100 dark:border-slate-700">
                <h3 class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Akses Fitur & Otomatisasi</h3>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori Unit Usaha Default</label>
                    <select wire:model="defaultCategory" class="w-full md:w-1/2 px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500 cursor-pointer">
                        <option value="ritel">Ritel (Produk / Toko)</option>
                        <option value="jasa">Jasa / Layanan</option>
                    </select>
                    @error('defaultCategory') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="allowMultiUnitAdmin" class="w-4 h-4 text-blue-900 rounded border-neutral-300 focus:ring-red-500/20 cursor-pointer">
                        <div>
                            <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Izinkan Admin Mengelola Banyak Unit</span>
                            <p class="text-[11px] text-neutral-400">Satu akun admin unit dapat ditugaskan ke lebih dari 1 unit usaha.</p>
                        </div>
                    </label>

                    <!-- Notifikasi WhatsApp -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="enableWaNotifications" class="w-4 h-4 text-blue-900 rounded border-neutral-300 focus:ring-red-500/20 cursor-pointer">
                            <div>
                                <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Notifikasi WhatsApp</span>
                                <p class="text-[11px] text-neutral-400">Kirim laporan harian/transaksi, pemberitahuan penting, dan kode OTP keamanan langsung ke WhatsApp.</p>
                            </div>
                        </label>

                        @if ($enableWaNotifications)
                            <div class="mt-3 ml-7 pl-4 border-l-2 border-red-100 dark:border-slate-600 space-y-3">
                                <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-[11px] font-medium px-3 py-2 rounded-md">
                                    Konfigurasi ini juga dipakai untuk mengirim kode OTP saat admin master mengubah password atau nomor WhatsApp di tab Profil Admin.
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Provider WhatsApp Gateway</label>
                                        <select wire:model="waProvider" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500 cursor-pointer">
                                            <option value="fonnte">Fonnte</option>
                                        </select>
                                        @error('waProvider') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nomor WhatsApp Pengirim</label>
                                        <input type="text" wire:model="waSenderNumber" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                        @error('waSenderNumber') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">API Key / Token Fonnte</label>
                                        <input type="password" wire:model="waApiKey" placeholder="Masukkan API Key / Token" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:border-red-500">
                                        @error('waApiKey') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-md transition-all flex items-center gap-2 shadow-sm">
                    <span wire:loading.remove wire:target="saveFeatures">Simpan Pengaturan</span>
                    <span wire:loading wire:target="saveFeatures">Menyimpan...</span>
                </button>
            </div>
        </form>
    @endif
</div>