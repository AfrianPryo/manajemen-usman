<div class="w-full max-w-5xl mx-auto space-y-5 text-neutral-800 dark:text-neutral-100 px-4 py-4 sm:px-6 font-sans">

    {{-- Header --}}
    @if (! $this->isAccountOnlyView())
        <div class="bg-white dark:bg-slate-800 p-5 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02]">
            <div class="flex items-center gap-2.5">
                <h1 class="text-md font-bold tracking-tight text-neutral-900 dark:text-white tracking-tight">Pengaturan Sistem</h1>
            </div>
            <p class="text-[12px] tracking-tight text-neutral-400 mt-1">Kelola profil admin master, preferensi aplikasi, dan konfigurasi fitur.</p>
        </div>
    @endif

    {{-- Flash Notification (toast) --}}
    @if (session()->has('success'))
        <div wire:key="toast-success-{{ md5(session('success')) }}" x-data x-init="$store.toast.push('success', @js(session('success')))"></div>
    @endif

    @if (! $this->isAccountOnlyView())
        <!-- Navigasi Tab -->
        <div class="inline-flex flex-wrap p-1 bg-neutral-100 dark:bg-slate-900 rounded-sm gap-1">
            <button wire:click="setTab('profile')"
                class="px-4 py-2 text-xs font-bold rounded-sm transition-all flex items-center gap-2 {{ $activeTab === 'profile' ? 'bg-blue-900 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                <x-heroicon-o-user class="w-4 h-4" stroke-width="2" />
                Profil Admin
            </button>

            @if ($this->canAccessFeaturesTab())
                <button wire:click="setTab('features')"
                    class="px-4 py-2 text-xs font-bold rounded-sm transition-all flex items-center gap-2 {{ $activeTab === 'features' ? 'bg-blue-900 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                    <x-heroicon-o-adjustments-vertical class="w-4 h-4" stroke-width="2" />
                    Fitur & Modul
                </button>
            @endif

            @if ($this->canAccessLandingTab())
                <button wire:click="setTab('landing')"
                    class="px-4 py-2 text-xs font-bold rounded-sm transition-all flex items-center gap-2 {{ $activeTab === 'landing' ? 'bg-blue-900 text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}">
                    <x-heroicon-o-globe-alt class="w-4 h-4" stroke-width="2" />
                    Landing Page
                </button>
            @endif
        </div>
    @endif

    <!-- TAB 1: PROFIL ADMIN MASTER -->
    @if($activeTab === 'profile')
        <div class="space-y-5">

            {{-- Informasi Akun (tanpa field nomor WA — pindah ke card terpisah di bawah) --}}
            <form wire:submit="saveProfile" class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
                <div>
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Informasi Akun</h2>
                    <p class="text-xs text-neutral-400 mt-0.5">Data identitas admin master yang sedang login. Foto dan nama tampil di sidebar dashboard.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-sm border border-neutral-200 dark:border-slate-700 overflow-hidden bg-neutral-50 dark:bg-slate-900 flex items-center justify-center shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif ($existingAvatar)
                            <img src="{{ asset('storage/' . $existingAvatar) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-lg font-bold text-neutral-400">{{ strtoupper(substr($name ?: 'A', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true; progress = 0"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-cancel="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Foto Profil</label>
                        <input type="file" wire:model="avatar" accept="image/*" class="text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-950 dark:file:bg-slate-700 dark:file:text-neutral-300">
                        <x-upload-progress />
                        @error('avatar') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('name') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Username</label>
                        <input type="text" wire:model="username" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('username') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('email') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Status Kepegawaian</label>
                        <select wire:model.live="employeeStatus" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                            <option value="non-nip">Non-NIP</option>
                            <option value="nip">NIP (Pegawai Negeri)</option>
                        </select>
                        @error('employeeStatus') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    @if ($employeeStatus === 'nip')
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">NIP</label>
                            <input type="text" inputmode="numeric" wire:model="nip" oninput="onlyDigits(event)" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            @error('nip') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                    <button type="submit" wire:loading.attr="disabled" wire:target="avatar,saveProfile" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 disabled:opacity-50 disabled:cursor-not-allowed">
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
                <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Ajukan Reset Password</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            Admin Unit tidak bisa mengubah password sendiri. Ajukan permintaan di sini; setelah disetujui Admin Master,
                            password baru otomatis terkirim ke WhatsApp Anda ({{ $phone ?: 'nomor belum terdaftar' }}).
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
                                class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-amber-900/20 disabled:opacity-50">
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
                <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Ubah Nomor WhatsApp</h2>
                        <p class="text-xs text-neutral-400 mt-0.5">
                            Nomor saat ini: <span class="font-semibold text-neutral-700 dark:text-neutral-200">{{ $phone ?: 'Belum terdaftar' }}</span>.
                            Nomor ini menjadi kanal OTP keamanan, sehingga perubahan wajib diverifikasi lewat OTP.
                        </p>
                    </div>

                    @if (!$phoneOtpRequested)
                        {{-- Langkah 1: input nomor baru + konfirmasi password --}}
                        <form wire:submit="requestPhoneChangeOtp" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nomor WhatsApp Baru</label>
                                    <input type="text" inputmode="numeric" wire:model="newPhone" oninput="onlyDigits(event)" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                    @error('newPhone') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Konfirmasi Password Anda</label>
                                    <input type="password" wire:model="phoneChangePassword" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                    @error('phoneChangePassword') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20">
                                    <span wire:loading.remove wire:target="requestPhoneChangeOtp">Kirim Kode OTP</span>
                                    <span wire:loading wire:target="requestPhoneChangeOtp">Mengirim...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Langkah 2: verifikasi OTP --}}
                        <form wire:submit="verifyPhoneChangeOtp" class="space-y-4">
                            <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200/60 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-[11px] font-medium px-3 py-2 rounded-sm">
                                Kode OTP telah dikirim ke WhatsApp {{ $phone ? 'nomor lama Anda (untuk konfirmasi)' : 'nomor baru (untuk verifikasi kepemilikan)' }}. Kode berlaku 5 menit.
                            </div>

                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kode OTP (6 digit)</label>
                                <input type="text" inputmode="numeric" maxlength="6" wire:model="phoneOtp" oninput="onlyDigits(event)" placeholder="123456" class="w-full px-3.5 py-2 text-sm font-bold tracking-widest text-center border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                @error('phoneOtp') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="button" wire:click="cancelPhoneOtp" class="px-5 py-2 text-xs font-bold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 rounded-sm transition-all">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20">
                                    <span wire:loading.remove wire:target="verifyPhoneChangeOtp">Verifikasi & Simpan</span>
                                    <span wire:loading wire:target="verifyPhoneChangeOtp">Memverifikasi...</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Ubah Password — 2 langkah: request OTP -> verifikasi --}}
                <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
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
                                    <input type="password" wire:model="currentPassword" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                    @error('currentPassword') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Password Baru</label>
                                    <input type="password" wire:model="newPassword" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                    @error('newPassword') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    <p class="text-[10px] text-neutral-400 mt-1">Min. 8 karakter, kombinasi huruf besar/kecil, angka, dan simbol.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Konfirmasi Password Baru</label>
                                    <input type="password" wire:model="newPassword_confirmation" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20">
                                    <span wire:loading.remove wire:target="requestPasswordChangeOtp">Kirim Kode OTP</span>
                                    <span wire:loading wire:target="requestPasswordChangeOtp">Mengirim...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        {{-- Langkah 2: verifikasi OTP --}}
                        <form wire:submit="verifyPasswordChangeOtp" class="space-y-4">
                            <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200/60 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-[11px] font-medium px-3 py-2 rounded-sm">
                                Kode OTP dikirim ke WhatsApp terdaftar ({{ $phone }}), berlaku 5 menit. Sesi di perangkat lain otomatis keluar setelah password berhasil diubah.
                            </div>

                            <div class="max-w-xs">
                                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kode OTP (6 digit)</label>
                                <input type="text" inputmode="numeric" maxlength="6" wire:model="passwordOtp" placeholder="123456" class="w-full px-3.5 py-2 text-sm font-bold tracking-widest text-center border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                @error('passwordOtp') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-4 border-t border-neutral-100 dark:border-slate-700">
                                <button type="button" wire:click="cancelPasswordOtp" class="px-5 py-2 text-xs font-bold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 rounded-sm transition-all">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20">
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
        <form wire:submit="saveFeatures" class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-6">
            <div>
                <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Fitur & Modul</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Atur identitas aplikasi, modul aktif, dan hak akses untuk unit usaha ritel maupun jasa.</p>
            </div>

            <!-- Bagian: Parameter Aplikasi -->
            <div class="space-y-4">
                <h3 class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Parameter Aplikasi</h3>

                {{-- Logo Aplikasi -- tampil di sidebar dashboard Master & seluruh Unit,
                     lihat components/layouts/app.blade.php dan unit/app.blade.php --}}
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-sm border border-neutral-200 dark:border-slate-700 overflow-hidden bg-neutral-50 dark:bg-slate-900 flex items-center justify-center shrink-0">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-contain">
                        @elseif ($existingLogo)
                            <img src="{{ asset('storage/' . $existingLogo) }}" class="w-full h-full object-contain">
                        @else
                            <x-heroicon-o-photo class="w-6 h-6 text-neutral-300" />
                        @endif
                    </div>
                    <div
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true; progress = 0"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-cancel="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Logo Aplikasi</label>
                        <input type="file" wire:model="logo" accept="image/*" class="text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-950 dark:file:bg-slate-700 dark:file:text-neutral-300">
                        <x-upload-progress />
                        <p class="text-[10px] text-neutral-400 mt-1">Tampil di sidebar dashboard Admin Master & seluruh Admin Unit.</p>
                        @error('logo') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        @if ($existingLogo)
                            <button type="button"
                                    x-on:click.prevent="$store.confirmDialog.open({
                                        message: 'Hapus logo aplikasi dan kembali ke ikon default?',
                                        confirmText: 'Ya, Hapus',
                                        variant: 'danger',
                                        onConfirm: () => $wire.removeLogo()
                                    })"
                                    class="mt-1.5 text-[11px] font-semibold text-rose-500 hover:text-rose-600">
                                Hapus Logo
                            </button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Nama Aplikasi</label>
                        <input type="text" wire:model="appName" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('appName') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                        <p class="text-[10px] text-neutral-400 mt-1">
                            Nama & logo di atas otomatis dipakai juga di landing page (halaman depan publik).
                            Untuk mengatur isi tiap section landing page, lihat tab
                            <button type="button" wire:click="setTab('landing')" class="font-semibold text-blue-900 dark:text-blue-400 hover:underline">Landing Page</button>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bagian: Akses Fitur & Otomatisasi -->
            <div class="pt-4 space-y-4 border-t border-neutral-100 dark:border-slate-700">
                <h3 class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Akses Fitur & Otomatisasi</h3>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Kategori Unit Usaha Default</label>
                    <select wire:model="defaultCategory" class="w-full md:w-1/2 px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                        <option value="ritel">Ritel (Produk / Toko)</option>
                        <option value="jasa">Jasa / Layanan</option>
                    </select>
                    @error('defaultCategory') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3">
                    <!-- Notifikasi WhatsApp -->
                    <div>
                        <x-toggle wire:model.live="enableWaNotifications"
                            label="Notifikasi WhatsApp"
                            description="Kirim pemberitahuan penting dan kode OTP keamanan langsung ke WhatsApp." />

                        @if ($enableWaNotifications)
                            <div class="mt-3 ml-7 pl-4 border-l-2 border-blue-100 dark:border-slate-600 space-y-3">
                                <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-[11px] font-medium px-3 py-2 rounded-sm">
                                    Konfigurasi ini juga dipakai untuk kode OTP saat mengubah password atau nomor WhatsApp di tab Profil Admin.
                                </div>

                                {{-- Panduan singkat cara mendapatkan API Key -- tertutup by default
                                     (native <details>) supaya form tidak terasa penuh untuk admin yang
                                     sudah paham, tapi tetap satu klik untuk yang baru pertama kali. --}}
                                <details class="group text-[11px] text-neutral-500 dark:text-neutral-400">
                                    <summary class="cursor-pointer select-none font-semibold text-blue-800 dark:text-sky-400 hover:underline inline-flex items-center gap-1 [&::-webkit-details-marker]:hidden marker:content-none">
                                        <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0" />
                                        Belum punya API Key? Lihat cara mendapatkannya
                                    </summary>
                                    <ol class="mt-2 ml-0.5 space-y-1 list-decimal list-inside">
                                        <li>Buka <span class="font-medium text-neutral-600 dark:text-neutral-300">fonnte.com</span>, daftar atau masuk, lalu tambah perangkat baru di menu <span class="font-medium text-neutral-600 dark:text-neutral-300">Device</span>.</li>
                                        <li>Pindai (scan) kode QR yang muncul memakai WhatsApp dari nomor pengirim yang akan diisi di bawah.</li>
                                        <li>Setelah status perangkat "Connected", salin token yang tertera di halaman Device tersebut, lalu tempel ke kolom <span class="font-medium text-neutral-600 dark:text-neutral-300">API Key / Token Fonnte</span> di bawah.</li>
                                    </ol>
                                </details>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Provider WhatsApp Gateway</label>
                                        <select wire:model="waProvider" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                                            <option value="fonnte">Fonnte</option>
                                        </select>
                                        @error('waProvider') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="flex items-center gap-1 text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">
                                            Nomor WhatsApp Pengirim
                                            <x-help-tip text="Nomor HP yang perangkatnya sudah discan/dihubungkan ke akun Fonnte kamu, bukan sembarang nomor. Dari nomor inilah OTP dan notifikasi akan dikirim." />
                                        </label>
                                        <input type="text" inputmode="numeric" wire:model="waSenderNumber" oninput="onlyDigits(event)" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                        @error('waSenderNumber') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="md:col-span-2" x-data="{ showApiKey: false }">
                                        <label class="flex items-center gap-1 text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">
                                            API Key / Token Fonnte
                                            <x-help-tip text="Token rahasia dari dashboard akun Fonnte kamu (fonnte.com > Device), bukan password WhatsApp. Simpan baik-baik — siapa pun yang memegang token ini bisa mengirim pesan atas nama nomor tersebut." />
                                        </label>
                                        <div class="relative">
                                            <input
                                                :type="showApiKey ? 'text' : 'password'"
                                                wire:model="waApiKey"
                                                placeholder="Masukkan API Key / Token"
                                                class="w-full px-3.5 py-2 pr-9 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"
                                            >
                                            <button
                                                type="button"
                                                @click="showApiKey = !showApiKey"
                                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:text-neutral-500 dark:hover:text-neutral-300 focus:outline-none"
                                                tabindex="-1"
                                            >
                                                <x-heroicon-o-eye x-show="!showApiKey" class="h-4 w-4" stroke-width="1.8" />
                                                <x-heroicon-o-eye-slash x-show="showApiKey" x-cloak class="h-4 w-4" stroke-width="1.8" />
                                            </button>
                                        </div>
                                        @error('waApiKey') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                {{-- Tes Koneksi Fonnte -- cek apakah API Key yang diisi valid &
                                     perangkat WhatsApp sudah terhubung, TANPA perlu menyimpan form
                                     & tanpa mengirim pesan apa pun. Lihat testWaConnection() di
                                     App\Livewire\Master\Settings\Index &
                                     App\Services\FonnteOtpService::testConnection(). --}}
                                <div class="flex flex-wrap items-center gap-3">
                                    <button
                                        type="button"
                                        wire:click="testWaConnection"
                                        wire:loading.attr="disabled"
                                        wire:target="testWaConnection"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-blue-900 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-950 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                                    >
                                        <x-heroicon-o-signal wire:loading.remove wire:target="testWaConnection" class="h-3.5 w-3.5" stroke-width="2" />
                                        <svg wire:loading wire:target="testWaConnection" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="testWaConnection">Tes Koneksi</span>
                                        <span wire:loading wire:target="testWaConnection">Menguji koneksi...</span>
                                    </button>
                                    <p class="text-[11px] text-neutral-400">
                                        Menguji API Key di atas ke server Fonnte (belum perlu disimpan dulu). Kalau kosong, memakai API Key yang sudah tersimpan.
                                    </p>
                                </div>

                                @if ($waTestResult)
                                    <div
                                        @class([
                                            'text-[11px] font-medium px-3 py-2.5 rounded-sm border space-y-1',
                                            'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200/60 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400' => $waTestResult['success'] && ($waTestResult['connected'] ?? false),
                                            'bg-amber-50 dark:bg-amber-950/40 border-amber-200/60 dark:border-amber-800 text-amber-700 dark:text-amber-400' => $waTestResult['success'] && ! ($waTestResult['connected'] ?? false),
                                            'bg-rose-50 dark:bg-rose-950/40 border-rose-200/60 dark:border-rose-800 text-rose-600 dark:text-rose-400' => ! $waTestResult['success'],
                                        ])
                                    >
                                        <div class="flex items-center gap-1.5">
                                            @if ($waTestResult['success'] && ($waTestResult['connected'] ?? false))
                                                <x-heroicon-o-check-circle class="h-4 w-4 shrink-0" stroke-width="2" />
                                            @elseif ($waTestResult['success'])
                                                <x-heroicon-o-exclamation-triangle class="h-4 w-4 shrink-0" stroke-width="2" />
                                            @else
                                                <x-heroicon-o-x-circle class="h-4 w-4 shrink-0" stroke-width="2" />
                                            @endif
                                            <span>{{ $waTestResult['message'] }}</span>
                                        </div>

                                        @if ($waTestResult['success'])
                                            <ul class="ml-6 pl-0 text-[10.5px] opacity-90 space-y-0.5 list-disc list-inside">
                                                @if (!empty($waTestResult['device']))
                                                    <li>Nomor perangkat: {{ $waTestResult['device'] }}</li>
                                                @endif
                                                @if (!empty($waTestResult['device_name']))
                                                    <li>Nama perangkat: {{ $waTestResult['device_name'] }}</li>
                                                @endif
                                                @if (!empty($waTestResult['package']))
                                                    <li>Paket: {{ $waTestResult['package'] }}@if (!empty($waTestResult['quota'])), sisa kuota {{ $waTestResult['quota'] }}@endif</li>
                                                @endif
                                                @if (!empty($waTestResult['expired']))
                                                    <li>Masa aktif hingga: {{ $waTestResult['expired'] }}</li>
                                                @endif
                                            </ul>
                                        @endif
                                    </div>
                                @endif

                                {{-- Preferensi Notifikasi per Channel -- kategori WA non-OTP
                                     yang boleh dimatikan satu per satu. OTP TIDAK ada di sini
                                     sama sekali (selalu wajib terkirim, lihat penjelasan di
                                     bawah), gerbangnya ada di
                                     App\Services\FonnteOtpService::channelEnabled(). --}}
                                <div class="pt-3 mt-1 border-t border-neutral-100 dark:border-slate-600 space-y-2.5">
                                    <p class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Preferensi Notifikasi per Channel</p>
                                    <p class="text-[11px] text-neutral-400 -mt-1.5">
                                        Kode OTP keamanan selalu wajib terkirim dan tidak bisa dimatikan. Kategori di bawah opsional —
                                        jika dimatikan, notifikasi tetap muncul di aplikasi, hanya salinan WhatsApp yang tidak dikirim.
                                    </p>

                                    <x-toggle wire:model="waNotifyCredentials"
                                        label="Kredensial Akun (Password Baru)"
                                        description="Username & password saat akun dibuat, direset, atau permintaan reset disetujui. Tetap muncul di popup layar meski dimatikan." />

                                    <x-toggle wire:model="waNotifyAnnouncements"
                                        label="Broadcast Pengumuman"
                                        description="Salinan WhatsApp untuk pengumuman yang dikirim Master Admin ke Admin Unit lewat menu Pengumuman." />
                                </div>

                                {{-- Laporan Rutin Otomatis -- ringkasan aspek penting sistem
                                     dikirim berkala ke seluruh Admin Master aktif via WhatsApp.
                                     Lihat App\Services\RoutineReportService. --}}
                                <div class="pt-3 mt-1 border-t border-neutral-100 dark:border-slate-600 space-y-2.5">
                                    <x-toggle wire:model.live="reportRoutineEnabled"
                                        label="Laporan Rutin Otomatis"
                                        description="Kirim ringkasan aspek penting sistem (keuangan, unit usaha, admin, stok, dst) secara berkala ke seluruh Admin Master aktif lewat WhatsApp." />

                                    @if ($reportRoutineEnabled)
                                        <div class="mt-3 ml-7 pl-4 border-l-2 border-blue-100 dark:border-slate-600 space-y-3">
                                            @if ($reportRoutineLastSentAt)
                                                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-[11px] font-medium px-3 py-2 rounded-sm">
                                                    Laporan terakhir terkirim: {{ \Carbon\Carbon::parse($reportRoutineLastSentAt)->translatedFormat('d M Y H:i') }}.
                                                </div>
                                            @else
                                                <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-[11px] font-medium px-3 py-2 rounded-sm">
                                                    Belum pernah terkirim. Pengiriman pertama mengikuti jadwal di bawah, maksimal 15 menit setelah waktu terjadwal.
                                                </div>
                                            @endif

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Frekuensi</label>
                                                    <select wire:model.live="reportRoutineFrequency" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                                                        <option value="daily">Harian</option>
                                                        <option value="weekly">Mingguan</option>
                                                        <option value="monthly">Bulanan</option>
                                                    </select>
                                                    @error('reportRoutineFrequency') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Jam Pengiriman</label>
                                                    <input type="time" wire:model="reportRoutineTime" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                                    @error('reportRoutineTime') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                                </div>

                                                @if ($reportRoutineFrequency === 'weekly')
                                                    <div>
                                                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Hari Pengiriman</label>
                                                        <select wire:model="reportRoutineDayOfWeek" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 cursor-pointer">
                                                            <option value="0">Minggu</option>
                                                            <option value="1">Senin</option>
                                                            <option value="2">Selasa</option>
                                                            <option value="3">Rabu</option>
                                                            <option value="4">Kamis</option>
                                                            <option value="5">Jumat</option>
                                                            <option value="6">Sabtu</option>
                                                        </select>
                                                        @error('reportRoutineDayOfWeek') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                                    </div>
                                                @endif

                                                @if ($reportRoutineFrequency === 'monthly')
                                                    <div>
                                                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Tanggal Pengiriman</label>
                                                        <input type="text" inputmode="numeric" wire:model="reportRoutineDayOfMonth" oninput="onlyDigits(event)" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                                        <p class="text-[10px] text-neutral-400 mt-1">Maks. tanggal 28 supaya berlaku untuk semua bulan (termasuk Februari).</p>
                                                        @error('reportRoutineDayOfMonth') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                                    </div>
                                                @endif
                                            </div>

                                            <div>
                                                <p class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-2">Kategori yang Dikirim</p>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2.5">
                                                    @foreach (\App\Services\RoutineReportService::SECTIONS as $sectionKey => $sectionLabel)
                                                        <x-toggle wire:model="reportRoutineSections" value="{{ $sectionKey }}">{{ $sectionLabel }}</x-toggle>
                                                    @endforeach
                                                </div>
                                                @error('reportRoutineSections') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bagian: Sesi & Keamanan -->
            <div class="pt-4 space-y-4 border-t border-neutral-100 dark:border-slate-700">
                <h3 class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Sesi & Keamanan</h3>

                <div>
                    <x-toggle wire:model.live="sessionTimeoutEnabled"
                        label="Auto-Logout Karena Idle (Session Timeout)"
                        description="Keluarkan otomatis dari akun kalau tidak ada aktivitas selama durasi tertentu. Berlaku terpisah untuk Admin Master dan Admin Unit." />

                    @if ($sessionTimeoutEnabled)
                        <div class="mt-3 ml-7 pl-4 border-l-2 border-blue-100 dark:border-slate-600 space-y-3">
                            <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-[11px] font-medium px-3 py-2 rounded-sm">
                                Admin Unit biasanya memakai perangkat kasir/shared, jadi wajar diberi durasi lebih pendek. Idle dihitung sejak interaksi terakhir (klik, submit form), bukan sejak login.
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Durasi untuk Admin Master (menit)</label>
                                    <input type="text" inputmode="numeric" wire:model="sessionTimeoutMasterMinutes" oninput="onlyDigits(event)" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                    @error('sessionTimeoutMasterMinutes') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Durasi untuk Admin Unit (menit)</label>
                                    <input type="text" inputmode="numeric" wire:model="sessionTimeoutUnitMinutes" oninput="onlyDigits(event)" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                    @error('sessionTimeoutUnitMinutes') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bagian: Retensi & Arsip Log -->
            <div class="pt-4 space-y-4 border-t border-neutral-100 dark:border-slate-700">
                <h3 class="text-[11px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Retensi & Arsip Log</h3>

                <div class="max-w-xs">
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Batas Retensi Log (hari)</label>
                    <input type="text" inputmode="numeric" wire:model="logRetentionDays" oninput="onlyDigits(event)" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('logRetentionDays') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800 text-blue-600 dark:text-blue-400 text-[11px] font-medium px-3 py-2 rounded-sm space-y-1">
                    <p>Log yang lebih tua dari batas ini <span class="font-bold">tidak langsung dihapus</span>: diekspor ke Excel per bulan, diverifikasi, baru dihapus dari tabel utama agar query tetap cepat.</p>
                    <p>Pengarsipan berjalan otomatis tiap hari pukul 02:00 per bulan penuh, sehingga data bisa bertahan hingga ~31 hari lebih lama dari batas ini. Minimal {{ \App\Services\LogArchiveService::MIN_RETENTION_DAYS }} hari.
                        @if (Route::has('master.log-archives.index'))
                            <a href="{{ route('master.log-archives.index') }}" class="underline font-semibold">Lihat Arsip Log</a>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-neutral-100 dark:border-slate-700">
                <button type="submit" wire:loading.attr="disabled" wire:target="logo,saveFeatures" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveFeatures">Simpan Pengaturan</span>
                    <span wire:loading wire:target="saveFeatures">Menyimpan...</span>
                </button>
            </div>
        </form>
    @endif

    <!-- TAB 3: LANDING PAGE (konten landing page publik -- khusus Master Admin) -->
    @if($activeTab === 'landing' && $this->canAccessLandingTab())
        <div class="space-y-5">

            {{-- Intro + navigasi cepat antar section. Section sengaja dipisah jadi
                 card sendiri-sendiri (bukan satu form panjang tanpa jeda) supaya
                 tiap bagian landing page mudah dipindai & tidak tertukar saat
                 discroll. Semua tetap satu <form> yang sama (saveLanding) --
                 pemisahan ini murni visual/navigasi, bukan perubahan data. --}}
            <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6">
                <h2 class="text-sm font-bold text-neutral-900 dark:text-white">Landing Page</h2>
                <p class="text-xs text-neutral-400 mt-0.5">Atur judul, deskripsi, dan tampil/sembunyikan tiap bagian halaman depan (landing page) publik. Nama & logo aplikasi diatur lewat tab <button type="button" wire:click="setTab('features')" class="font-semibold text-blue-900 dark:text-blue-400 hover:underline">Fitur & Modul</button>.</p>

                <div class="flex flex-wrap gap-1 mt-4 pt-4 border-t border-neutral-100 dark:border-slate-700">
                    <a href="#section-hero" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">Hero</a>
                    <a href="#section-mitra" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">Mitra Unit Usaha</a>
                    <a href="#section-fitur" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">Fitur Unggulan</a>
                    <a href="#section-carakerja" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">Cara Kerja</a>
                    <a href="#section-tentang" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">Tentang</a>
                    <a href="#section-faq" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">FAQ</a>
                    <a href="#section-footer" class="px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-900 dark:hover:text-blue-400 hover:bg-neutral-100 dark:hover:bg-slate-900 rounded-sm transition-all">Footer</a>
                </div>
            </div>

        <form wire:submit="saveLanding" class="space-y-5">

            <!-- Bagian: Hero (Halaman Utama) -->
            <div id="section-hero" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-sparkles class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Hero (Halaman Utama)</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm bg-neutral-100 text-neutral-500 dark:bg-slate-700 dark:text-neutral-400">Selalu Tampil</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Judul besar paling atas landing page, halaman pembuka yang pertama dilihat pengunjung.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul Baris Atas</label>
                        <textarea wire:model="landingHeroTitleTop" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                        <p class="text-[10px] text-neutral-400 mt-1">Tampil sebelum animasi 3D (di desktop). Baris baru akan tampil sebagai baris terpisah.</p>
                        @error('landingHeroTitleTop') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul Baris Bawah</label>
                        <textarea wire:model="landingHeroTitleBottom" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                        <p class="text-[10px] text-neutral-400 mt-1">Tampil setelah animasi 3D (di desktop). Baris baru akan tampil sebagai baris terpisah.</p>
                        @error('landingHeroTitleBottom') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="max-w-xs">
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Label Scroll Indicator</label>
                    <input type="text" wire:model="landingHeroScrollText" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 uppercase">
                    <p class="text-[10px] text-neutral-400 mt-1">Teks kecil di pojok kanan bawah hero (mis. "SCROLL").</p>
                    @error('landingHeroScrollText') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Bagian: Mitra Unit Usaha -->
            <div id="section-mitra" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-building-storefront class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Mitra Unit Usaha</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm {{ $showUnitsOnLanding ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-700 dark:text-neutral-500' }}">{{ $showUnitsOnLanding ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Menampilkan logo unit usaha aktif sebagai mitra di landing page.</p>
                    </div>
                </div>

                <x-toggle wire:model="showUnitsOnLanding"
                    label="Tampilkan Section Ini"
                    description='Tampilkan logo unit usaha aktif pada bagian "Mitra Unit Usaha" di landing page. Section otomatis tersembunyi kalau belum ada unit usaha aktif.' />

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul</label>
                    <textarea wire:model="landingMitraTitle" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                    <p class="text-[10px] text-neutral-400 mt-1">Baris baru akan tampil sebagai baris terpisah, mengikuti tata letak desain.</p>
                    @error('landingMitraTitle') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi</label>
                    <textarea wire:model="landingMitraDescription" rows="3" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                    @error('landingMitraDescription') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1.5">Unit Usaha yang Ditampilkan</label>
                    <div class="flex flex-wrap gap-1 p-1 bg-neutral-100 dark:bg-slate-900 rounded-sm w-fit">
                        <button type="button" wire:click="$set('landingUnitsMode', 'all')"
                            class="px-3 py-1.5 text-[11px] font-bold rounded-sm transition-all {{ $landingUnitsMode === 'all' ? 'bg-white dark:bg-slate-700 text-blue-900 dark:text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-200' }}">
                            Semua Unit Aktif
                        </button>
                        <button type="button" wire:click="$set('landingUnitsMode', 'selected')"
                            class="px-3 py-1.5 text-[11px] font-bold rounded-sm transition-all {{ $landingUnitsMode === 'selected' ? 'bg-white dark:bg-slate-700 text-blue-900 dark:text-white shadow-sm' : 'text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-200' }}">
                            Pilih Unit Tertentu
                        </button>
                    </div>

                    @if ($landingUnitsMode === 'selected')
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-2 p-3 border border-neutral-200 dark:border-slate-700 rounded-sm bg-neutral-50/50 dark:bg-slate-900/50 max-h-56 overflow-y-auto">
                            @forelse ($availableUnits as $unit)
                                <x-toggle wire:model="landingSelectedUnitIds" value="{{ $unit['id'] }}"
                                    label="{{ $unit['name'] }}{{ ! $unit['is_active'] ? ' (nonaktif)' : '' }}" />
                            @empty
                                <p class="text-[11px] text-neutral-400 col-span-full">Belum ada unit usaha yang bisa dipilih.</p>
                            @endforelse
                        </div>
                        @error('landingSelectedUnitIds') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    @else
                        <p class="text-[10px] text-neutral-400 mt-1.5">Seluruh unit usaha berstatus aktif tampil otomatis, tanpa perlu dipilih satu per satu.</p>
                    @endif
                </div>
            </div>

            <!-- Bagian: Fitur Unggulan -->
            <div id="section-fitur" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-star class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Fitur Unggulan</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm {{ $landingFiturEnabled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-700 dark:text-neutral-500' }}">{{ $landingFiturEnabled ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Daftar kartu fitur unggulan aplikasi di landing page.</p>
                    </div>
                </div>

                <x-toggle wire:model="landingFiturEnabled"
                    label="Tampilkan Section Ini"
                    description="Nonaktifkan untuk menyembunyikan seluruh section Fitur Unggulan dari landing page publik." />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Label Kecil (Eyebrow)</label>
                        <input type="text" wire:model="landingFiturEyebrow" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                        @error('landingFiturEyebrow') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul</label>
                    <textarea wire:model="landingFiturTitle" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                    <p class="text-[10px] text-neutral-400 mt-1">Baris baru akan tampil sebagai baris terpisah, mengikuti tata letak desain.</p>
                    @error('landingFiturTitle') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3" x-data="{ dragIndex: null, overIndex: null }">
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Daftar Fitur ({{ count($landingFiturItems) }}/8)</label>

                    @foreach ($landingFiturItems as $index => $item)
                        <div wire:key="fitur-item-{{ $index }}"
                            draggable="true"
                            x-on:dragstart="dragIndex = {{ $index }}; $event.dataTransfer.effectAllowed = 'move'"
                            x-on:dragend="dragIndex = null; overIndex = null"
                            x-on:dragover.prevent="overIndex = {{ $index }}"
                            x-on:drop.prevent="if (dragIndex !== null && dragIndex !== {{ $index }}) { $wire.moveFiturItemTo(dragIndex, {{ $index }}) }; dragIndex = null; overIndex = null"
                            :class="dragIndex === {{ $index }} ? 'opacity-40' : (overIndex === {{ $index }} ? 'ring-2 ring-blue-300 dark:ring-blue-500/40' : '')"
                            class="p-3 border border-neutral-200 dark:border-slate-700 rounded-sm bg-neutral-50/60 dark:bg-slate-900/40 space-y-2 transition-shadow cursor-grab active:cursor-grabbing">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="shrink-0 -ml-1 p-0.5 text-neutral-300 dark:text-neutral-600 pointer-events-none" title="Geser area kartu untuk mengubah urutan">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><circle cx="6" cy="5" r="1.4"/><circle cx="6" cy="10" r="1.4"/><circle cx="6" cy="15" r="1.4"/><circle cx="13" cy="5" r="1.4"/><circle cx="13" cy="10" r="1.4"/><circle cx="13" cy="15" r="1.4"/></svg>
                                    </span>
                                    <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wide">Fitur #{{ $index + 1 }}</span>
                                </div>
                                <div class="flex items-center gap-1" x-on:mousedown.stop draggable="false">
                                    <button type="button" wire:click="moveFiturItem({{ $index }}, 'up')" @if($index === 0) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-blue-900 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Naikkan urutan">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" wire:click="moveFiturItem({{ $index }}, 'down')" @if($index === count($landingFiturItems) - 1) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-blue-900 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Turunkan urutan">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                                    </button>
                                    <button type="button" wire:click="removeFiturItem({{ $index }})" @if(count($landingFiturItems) <= 1) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-rose-600 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Hapus fitur ini">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                                    </button>
                                </div>
                            </div>

                            <input type="text" wire:model="landingFiturItems.{{ $index }}.title" placeholder="Judul fitur"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            @error("landingFiturItems.{$index}.title") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror

                            <textarea wire:model="landingFiturItems.{{ $index }}.description" rows="2" placeholder="Deskripsi fitur"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                            @error("landingFiturItems.{$index}.description") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <button type="button" wire:click="addFiturItem" @if(count($landingFiturItems) >= 8) disabled @endif
                        class="w-full px-3.5 py-2 text-xs font-bold text-blue-900 dark:text-blue-400 border border-dashed border-blue-900/30 dark:border-blue-400/30 rounded-sm hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all disabled:opacity-40 disabled:pointer-events-none">
                        + Tambah Fitur
                    </button>
                    @error('landingFiturItems') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Bagian: Cara Kerja -->
            <div id="section-carakerja" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-list-bullet class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Cara Kerja</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm {{ $landingCaraKerjaEnabled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-700 dark:text-neutral-500' }}">{{ $landingCaraKerjaEnabled ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Langkah-langkah alur penggunaan aplikasi, ditampilkan berurutan.</p>
                    </div>
                </div>

                <x-toggle wire:model="landingCaraKerjaEnabled"
                    label="Tampilkan Section Ini"
                    description="Nonaktifkan untuk menyembunyikan seluruh section Cara Kerja dari landing page publik." />

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul</label>
                    <input type="text" wire:model="landingCaraKerjaTitle" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('landingCaraKerjaTitle') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi</label>
                    <textarea wire:model="landingCaraKerjaDescription" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                    @error('landingCaraKerjaDescription') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3" x-data="{ dragIndex: null, overIndex: null }">
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Daftar Langkah ({{ count($landingCaraKerjaItems) }}/8)</label>

                    @foreach ($landingCaraKerjaItems as $index => $item)
                        <div wire:key="carakerja-item-{{ $index }}"
                            draggable="true"
                            x-on:dragstart="dragIndex = {{ $index }}; $event.dataTransfer.effectAllowed = 'move'"
                            x-on:dragend="dragIndex = null; overIndex = null"
                            x-on:dragover.prevent="overIndex = {{ $index }}"
                            x-on:drop.prevent="if (dragIndex !== null && dragIndex !== {{ $index }}) { $wire.moveCaraKerjaItemTo(dragIndex, {{ $index }}) }; dragIndex = null; overIndex = null"
                            :class="dragIndex === {{ $index }} ? 'opacity-40' : (overIndex === {{ $index }} ? 'ring-2 ring-blue-300 dark:ring-blue-500/40' : '')"
                            class="p-3 border border-neutral-200 dark:border-slate-700 rounded-sm bg-neutral-50/60 dark:bg-slate-900/40 space-y-2 transition-shadow cursor-grab active:cursor-grabbing">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="shrink-0 -ml-1 p-0.5 text-neutral-300 dark:text-neutral-600 pointer-events-none" title="Geser area kartu untuk mengubah urutan">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><circle cx="6" cy="5" r="1.4"/><circle cx="6" cy="10" r="1.4"/><circle cx="6" cy="15" r="1.4"/><circle cx="13" cy="5" r="1.4"/><circle cx="13" cy="10" r="1.4"/><circle cx="13" cy="15" r="1.4"/></svg>
                                    </span>
                                    <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wide">Langkah #{{ $index + 1 }}</span>
                                </div>
                                <div class="flex items-center gap-1" x-on:mousedown.stop draggable="false">
                                    <button type="button" wire:click="moveCaraKerjaItem({{ $index }}, 'up')" @if($index === 0) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-blue-900 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Naikkan urutan">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" wire:click="moveCaraKerjaItem({{ $index }}, 'down')" @if($index === count($landingCaraKerjaItems) - 1) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-blue-900 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Turunkan urutan">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                                    </button>
                                    <button type="button" wire:click="removeCaraKerjaItem({{ $index }})" @if(count($landingCaraKerjaItems) <= 1) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-rose-600 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Hapus langkah ini">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <input type="text" wire:model="landingCaraKerjaItems.{{ $index }}.badge" placeholder="Label singkat (mis. AKUN)" maxlength="20"
                                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 uppercase">
                                    @error("landingCaraKerjaItems.{$index}.badge") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    {{-- Pilihan ikon garis (line icon) untuk kartu langkah ini --
                                         daftar lengkapnya ada di App\Livewire\Master\Settings\Index::CARA_KERJA_ICONS,
                                         dipetakan ke markup SVG asli di landing.blade.php ($__caraKerjaIcons). --}}
                                    <select wire:model="landingCaraKerjaItems.{{ $index }}.icon"
                                        class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                                        @foreach (\App\Livewire\Master\Settings\Index::CARA_KERJA_ICONS as $iconKey => $iconLabel)
                                            <option value="{{ $iconKey }}">{{ $iconLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error("landingCaraKerjaItems.{$index}.icon") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <input type="text" wire:model="landingCaraKerjaItems.{{ $index }}.title" placeholder="Judul langkah"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            @error("landingCaraKerjaItems.{$index}.title") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror

                            <textarea wire:model="landingCaraKerjaItems.{{ $index }}.description" rows="2" placeholder="Deskripsi singkat langkah ini"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                            @error("landingCaraKerjaItems.{$index}.description") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <button type="button" wire:click="addCaraKerjaItem" @if(count($landingCaraKerjaItems) >= 8) disabled @endif
                        class="w-full px-3.5 py-2 text-xs font-bold text-blue-900 dark:text-blue-400 border border-dashed border-blue-900/30 dark:border-blue-400/30 rounded-sm hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all disabled:opacity-40 disabled:pointer-events-none">
                        + Tambah Langkah
                    </button>
                    @error('landingCaraKerjaItems') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Bagian: Tentang -->
            <div id="section-tentang" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-identification class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Tentang</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm {{ $landingTentangEnabled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-700 dark:text-neutral-500' }}">{{ $landingTentangEnabled ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Foto & narasi singkat tentang aplikasi di landing page.</p>
                    </div>
                </div>

                <x-toggle wire:model="landingTentangEnabled"
                    label="Tampilkan Section Ini"
                    description="Nonaktifkan untuk menyembunyikan seluruh section Tentang dari landing page publik." />

                {{-- Kustomisasi foto sisi kiri section "Tentang" -- fallback ke
                     asset bawaan (images/images (1).jpg) SELAMA admin belum
                     pernah mengunggah foto sendiri. Lihat removeTentangPhoto()
                     & resources/views/landing.blade.php bagian ABOUT SECTION. --}}
                <div class="flex items-center gap-4">
                    <div class="w-28 h-16 rounded-sm border border-neutral-200 dark:border-slate-700 overflow-hidden bg-neutral-50 dark:bg-slate-900 flex items-center justify-center shrink-0">
                        @if ($landingTentangPhoto)
                            <img src="{{ $landingTentangPhoto->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif ($existingLandingTentangPhoto)
                            <img src="{{ asset('storage/' . $existingLandingTentangPhoto) }}" class="w-full h-full object-cover">
                        @else
                            <img src="{{ asset('images/images (1).jpg') }}" class="w-full h-full object-cover opacity-60">
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Foto Section Tentang</label>
                        <input type="file" wire:model="landingTentangPhoto" accept="image/*" class="text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-950 dark:file:bg-slate-700 dark:file:text-neutral-300">
                        <p class="text-[10px] text-neutral-400 mt-1">Tampil di sisi kiri section Tentang landing page publik.</p>
                        @error('landingTentangPhoto') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                        @if ($existingLandingTentangPhoto)
                            <button type="button"
                                    x-on:click.prevent="$store.confirmDialog.open({
                                        message: 'Hapus foto custom section Tentang dan kembali ke foto default?',
                                        confirmText: 'Ya, Hapus',
                                        variant: 'danger',
                                        onConfirm: () => $wire.removeTentangPhoto()
                                    })"
                                    class="mt-1.5 text-[11px] font-semibold text-rose-500 hover:text-rose-600">
                                Hapus Foto
                            </button>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul</label>
                    <input type="text" wire:model="landingTentangTitle" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('landingTentangTitle') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Deskripsi</label>
                    <textarea wire:model="landingTentangDescription" rows="3" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                    @error('landingTentangDescription') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Bagian: FAQ -->
            <div id="section-faq" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">FAQ</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm {{ $landingFaqEnabled ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-400 dark:bg-slate-700 dark:text-neutral-500' }}">{{ $landingFaqEnabled ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Pertanyaan yang sering diajukan, ditampilkan sebagai accordion.</p>
                    </div>
                </div>

                <x-toggle wire:model="landingFaqEnabled"
                    label="Tampilkan Section Ini"
                    description="Nonaktifkan untuk menyembunyikan seluruh section FAQ dari landing page publik." />

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul</label>
                    <input type="text" wire:model="landingFaqTitle" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                    @error('landingFaqTitle') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-3" x-data="{ dragIndex: null, overIndex: null }">
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300">Daftar Pertanyaan ({{ count($landingFaqItems) }}/12)</label>

                    @foreach ($landingFaqItems as $index => $item)
                        <div wire:key="faq-item-{{ $index }}"
                            draggable="true"
                            x-on:dragstart="dragIndex = {{ $index }}; $event.dataTransfer.effectAllowed = 'move'"
                            x-on:dragend="dragIndex = null; overIndex = null"
                            x-on:dragover.prevent="overIndex = {{ $index }}"
                            x-on:drop.prevent="if (dragIndex !== null && dragIndex !== {{ $index }}) { $wire.moveFaqItemTo(dragIndex, {{ $index }}) }; dragIndex = null; overIndex = null"
                            :class="dragIndex === {{ $index }} ? 'opacity-40' : (overIndex === {{ $index }} ? 'ring-2 ring-blue-300 dark:ring-blue-500/40' : '')"
                            class="p-3 border border-neutral-200 dark:border-slate-700 rounded-sm bg-neutral-50/60 dark:bg-slate-900/40 space-y-2 transition-shadow cursor-grab active:cursor-grabbing">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="shrink-0 -ml-1 p-0.5 text-neutral-300 dark:text-neutral-600 pointer-events-none" title="Geser area kartu untuk mengubah urutan">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><circle cx="6" cy="5" r="1.4"/><circle cx="6" cy="10" r="1.4"/><circle cx="6" cy="15" r="1.4"/><circle cx="13" cy="5" r="1.4"/><circle cx="13" cy="10" r="1.4"/><circle cx="13" cy="15" r="1.4"/></svg>
                                    </span>
                                    <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-wide">Pertanyaan #{{ $index + 1 }}</span>
                                </div>
                                <div class="flex items-center gap-1" x-on:mousedown.stop draggable="false">
                                    <button type="button" wire:click="moveFaqItem({{ $index }}, 'up')" @if($index === 0) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-blue-900 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Naikkan urutan">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" wire:click="moveFaqItem({{ $index }}, 'down')" @if($index === count($landingFaqItems) - 1) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-blue-900 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Turunkan urutan">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                                    </button>
                                    <button type="button" wire:click="removeFaqItem({{ $index }})" @if(count($landingFaqItems) <= 1) disabled @endif
                                        class="h-6 w-6 flex items-center justify-center rounded-sm text-neutral-400 hover:text-rose-600 hover:bg-white dark:hover:bg-slate-700 disabled:opacity-30 disabled:pointer-events-none" title="Hapus pertanyaan ini">
                                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                                    </button>
                                </div>
                            </div>

                            <input type="text" wire:model="landingFaqItems.{{ $index }}.question" placeholder="Pertanyaan"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400">
                            @error("landingFaqItems.{$index}.question") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror

                            <textarea wire:model="landingFaqItems.{{ $index }}.answer" rows="3" placeholder="Jawaban"
                                class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                            @error("landingFaqItems.{$index}.answer") <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <button type="button" wire:click="addFaqItem" @if(count($landingFaqItems) >= 12) disabled @endif
                        class="w-full px-3.5 py-2 text-xs font-bold text-blue-900 dark:text-blue-400 border border-dashed border-blue-900/30 dark:border-blue-400/30 rounded-sm hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all disabled:opacity-40 disabled:pointer-events-none">
                        + Tambah Pertanyaan
                    </button>
                    @error('landingFaqItems') <p class="text-[11px] text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Bagian: Footer -->
            <div id="section-footer" class="scroll-mt-4 bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-6 space-y-4">
                <div class="flex items-start gap-2.5">
                    <span class="w-7 h-7 rounded-sm bg-blue-50 dark:bg-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-bars-3-bottom-left class="w-3.5 h-3.5 text-blue-900 dark:text-blue-300" stroke-width="2" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">Footer</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-sm bg-neutral-100 text-neutral-500 dark:bg-slate-700 dark:text-neutral-400">Selalu Tampil</span>
                        </div>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Berisi navigasi & kontak inti situs, jadi tidak punya opsi sembunyikan.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 mb-1">Judul</label>
                    <textarea wire:model="landingFooterTitle" rows="2" class="w-full px-3.5 py-2 text-xs font-medium border border-neutral-200 dark:border-slate-700 rounded-sm bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400"></textarea>
                    <p class="text-[10px] text-neutral-400 mt-1">Baris baru akan tampil sebagai baris terpisah, mengikuti tata letak desain.</p>
                    @error('landingFooterTitle') <p class="text-[11px] text-rose-500 mt-0.5">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Aksi simpan -- dipisah jadi card ringan sendiri di akhir daftar
                 section, supaya tetap terasa satu aksi untuk seluruh tab
                 meskipun tiap section kini punya card masing-masing. --}}
            <div class="bg-white dark:bg-slate-800 rounded-sm border border-neutral-100 dark:border-slate-700 shadow-sm shadow-black/[0.02] p-4 flex justify-end">
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all flex items-center gap-2 shadow-sm shadow-blue-900/20">
                    <span wire:loading.remove wire:target="saveLanding">Simpan Landing Page</span>
                    <span wire:loading wire:target="saveLanding">Menyimpan...</span>
                </button>
            </div>
        </form>
        </div>
    @endif
</div>