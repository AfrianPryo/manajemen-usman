@php
    $submitTarget = $needsPhoneSetup && $step === 1
        ? 'nextStep'
        : ($step === 2 ? ($otpSent ? 'verifyOtp' : 'sendPhoneOtp') : 'update');
    $submitLabel  = $needsPhoneSetup && $step === 1
        ? 'Lanjutkan'
        : ($step === 2 ? ($otpSent ? 'Verifikasi & Simpan' : 'Kirim Kode Verifikasi') : 'Simpan Password');
@endphp

<x-auth-card :title="$step === 2 ? ($otpSent ? 'Verifikasi Nomor WA' : 'Setup Nomor WA Aktif') : 'Ganti Password'">

    <x-slot:subtitle>
        @if ($step === 2 && $otpSent)
            Masukkan kode 6 digit yang dikirim ke WhatsApp Anda untuk membuktikan nomor ini milik Anda.
        @elseif ($step === 2)
            Nomor ini dipakai untuk menerima notifikasi & kode OTP penting terkait akun Anda.
        @elseif (auth()->user()?->must_change_password)
            Anda diwajibkan untuk mengganti password sebelum melanjutkan.
        @else
            Perbarui password Anda secara berkala untuk menjaga keamanan akun.
        @endif
    </x-slot:subtitle>

    @if ($needsPhoneSetup)
        {{-- Indikator langkah: pola dot progress ini sama persis dengan
             yang dipakai modal onboarding di dashboard, supaya konsisten. --}}
        <x-slot:progress>
            <div class="flex items-center justify-center gap-2">
                <span class="h-1.5 rounded-full transition-all {{ $step === 1 ? 'w-5 bg-blue-900 dark:bg-sky-400' : 'w-1.5 bg-neutral-200 dark:bg-slate-600' }}"></span>
                <span class="h-1.5 rounded-full transition-all {{ $step === 2 ? 'w-5 bg-blue-900 dark:bg-sky-400' : 'w-1.5 bg-neutral-200 dark:bg-slate-600' }}"></span>
                <span class="ml-1 text-[11px] text-neutral-400">Langkah {{ $step }} dari 2</span>
            </div>
        </x-slot:progress>
    @endif

    {{-- Alert Success & Error (toast) --}}
    @if (session()->has('message'))
        <div wire:key="toast-message-{{ md5(session('message')) }}" x-data x-init="$store.toast.push('success', @js(session('message')))"></div>
    @endif
    @if (session()->has('error'))
        <div wire:key="toast-error-{{ md5(session('error')) }}" x-data x-init="$store.toast.push('error', @js(session('error')))"></div>
    @endif

    {{-- Form Utama --}}
    <form
        wire:submit="{{ $submitTarget }}"
        id="changePasswordForm"
        class="space-y-5"
    >
        @if ($step === 1)

            {{-- Password Saat Ini: TIDAK ditampilkan saat wajib ganti password
                 (kredensial awal dari dev / Master Admin) -- user baru saja login
                 dengan password itu, jadi tidak perlu mengetik ulang. Tetap tampil
                 untuk ganti password sukarela. Validasi sebenarnya ada di server
                 (ChangePassword::passwordRules), bukan hanya di tampilan ini. --}}
            @unless (auth()->user()?->must_change_password)
                <x-auth-password
                    wire:model="current_password"
                    id="current_password"
                    label="Password Saat Ini"
                    autocomplete="current-password"
                    placeholder="Masukkan password saat ini"
                />
            @endunless

            <x-auth-password
                wire:model="new_password"
                id="new_password"
                label="Password Baru"
                autocomplete="new-password"
                placeholder="Minimal 8 karakter (huruf besar, kecil, angka)"
            />

            <x-auth-password
                wire:model="new_password_confirmation"
                id="new_password_confirmation"
                label="Konfirmasi Password Baru"
                autocomplete="new-password"
                placeholder="Ulangi password baru"
            />

        @endif

        @if ($step === 2)

            @if (! $otpSent)
                {{-- Nomor WA Aktif: phone input dengan pemilih negara, format otomatis,
                     dan nilai E.164 ("+6281234567890") yang dikirim ke server. --}}
                <div class="relative {{ $errors->has('phone') ? 'phone-invalid' : '' }}">
                    <label for="phone" class="mb-1 block text-xs font-medium text-neutral-600 dark:text-neutral-300">
                        Nomor WhatsApp Aktif
                    </label>
                    <x-phone-input wire:model="phone" :value="$phone" id="phone" />
                    <p class="mt-2 text-[11px] leading-relaxed text-neutral-400">Dipakai untuk notifikasi & kode OTP penting, misalnya saat ganti password mendatang. Kami akan mengirim kode verifikasi ke nomor ini.</p>
                    @error('phone') <x-form-error :message="$message" :field="'phone'" /> @enderror
                </div>
            @else
                {{-- Tahap verifikasi OTP: nomor ditampilkan read-only. Untuk koreksi
                     nomor, user memakai tombol "Kembali" (mereset proses verifikasi). --}}
                <div class="rounded-sm border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-center dark:border-slate-700 dark:bg-slate-800/50">
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Kode dikirim ke</p>
                    <p class="mt-0.5 text-sm font-semibold tabular-nums text-neutral-900 dark:text-white">{{ $phone }}</p>
                </div>

                <div>
                    <label for="otp" class="mb-1 block text-xs font-medium text-neutral-600 dark:text-neutral-300">
                        Kode OTP
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="otp"
                        id="otp"
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="6"
                        x-data
                        x-on:input="$el.value = $el.value.replace(/\D/g, '').slice(0, 6)"
                        class="@error('otp') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full rounded-sm border border-neutral-200 bg-white px-3 py-2.5 text-center text-lg font-semibold tracking-[0.5em] text-neutral-900 placeholder-neutral-300 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        placeholder="------"
                        aria-invalid="@error('otp') true @else false @enderror"
                    >
                    @error('otp') <x-form-error :message="$message" :field="'otp'" /> @enderror
                    <p class="mt-2 text-[11px] text-neutral-400">Kode berlaku 5 menit. Jangan berikan kode ini kepada siapa pun.</p>
                </div>

                {{-- Kirim ulang: hitung mundur 60 dtk hanya penanda di tampilan; batas
                     sebenarnya (jeda 60 dtk & maks 5x/jam) ditegakkan FonnteOtpService. --}}
                <div
                    wire:key="otp-resend-{{ md5($phone) }}"
                    x-data="{ s: 60, t: null }"
                    x-init="t = setInterval(() => { if (s > 0) s-- }, 1000)"
                    x-on:beforeunload.window="clearInterval(t)"
                    class="text-center text-[11px] text-neutral-400"
                >
                    <span x-show="s > 0">Kirim ulang kode dalam <span x-text="s"></span> detik</span>
                    <button
                        type="button"
                        x-show="s <= 0"
                        x-cloak
                        wire:click="sendPhoneOtp"
                        x-on:click="s = 60"
                        wire:loading.attr="disabled"
                        class="font-semibold text-blue-800 hover:underline disabled:opacity-50 dark:text-sky-400"
                    >
                        Kirim ulang kode
                    </button>
                </div>
            @endif

            @if ($canSkipVerification)
                {{-- Jalan keluar agar Master Admin tidak terkunci bila WA/Fonnte belum siap.
                     Tampil hanya bila server memastikan verifikasi memang tidak mungkin. --}}
                <div class="rounded-sm border border-amber-200 bg-amber-50 p-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <p class="text-[11px] leading-relaxed text-amber-800 dark:text-amber-300">
                        Pengiriman WhatsApp sedang tidak tersedia, jadi nomor belum bisa diverifikasi. Anda dapat melewati langkah ini; nomor tetap tersimpan namun tercatat belum terverifikasi.
                    </p>
                    <button
                        type="button"
                        wire:click="skipVerification"
                        wire:loading.attr="disabled"
                        wire:confirm="Lewati verifikasi dan simpan nomor tanpa dibuktikan? Pastikan nomor sudah benar."
                        class="mt-2 text-[11px] font-semibold text-amber-900 hover:underline disabled:opacity-50 dark:text-amber-200"
                    >
                        Lewati, verifikasi nanti
                    </button>
                </div>
            @endif

        @endif
    </form>

    {{-- Tombol Submit --}}
    <div class="mt-6">
        <x-auth-submit form="changePasswordForm" target="{{ $submitTarget }}" label="{{ $submitLabel }}" />

        @if ($step === 2)
            <button
                type="button"
                wire:click="backStep"
                wire:loading.attr="disabled"
                class="mx-auto mt-4 flex items-center gap-1.5 text-xs font-semibold text-neutral-400 transition-colors hover:text-neutral-700 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:text-neutral-200"
            >
                <x-heroicon-o-chevron-left class="h-3.5 w-3.5" stroke-width="2" />
                Kembali
            </button>
        @endif
    </div>

    @unless (auth()->user()?->must_change_password)
        <x-slot:below>
            <a href="{{ route('dashboard') }}" class="mt-5 inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-400 transition-colors duration-150 hover:text-neutral-700 dark:hover:text-neutral-200" aria-label="Kembali ke Dashboard">
                <x-heroicon-o-chevron-left class="h-3.5 w-3.5" stroke-width="2" />
                Dashboard
            </a>
        </x-slot:below>
    @endunless

    <style>
        /* Border merah untuk komponen phone-input saat ada error validasi 'phone' */
        .phone-invalid .phone-input-box { border-color: #fb7185; }
    </style>
</x-auth-card>
