{{--
    Modal pop-up kredensial (username & password) yang bisa disalin.

    Awalnya inline di resources/views/livewire/master/users/index.blade.php
    (App\Livewire\Master\Users\Index::save() & ::resetPassword()), ditarik
    jadi komponen bersama supaya bisa dipakai ulang persis sama di manapun
    ada aksi yang menghasilkan kredensial baru untuk ditampilkan -- termasuk
    saat Admin Master menekan "Approve" pada notifikasi permintaan reset
    password (lihat App\Livewire\NotificationSidebar::approvePasswordResetRequest()
    & App\Livewire\Master\Notifications\Index versi halaman penuhnya).

    Kontrak data $credentials (array|null), null = modal tidak tampil:
    - title    : string, judul modal (mis. '🔑 Password Berhasil Direset!')
    - name     : string, nama pemilik akun
    - username : string
    - password : string, password plain-text (HANYA ada sesaat di sini,
                 tidak pernah disimpan/di-log dalam bentuk plain-text)
    - wa_sent  : bool (opsional). Kalau key ini ADA, badge status
                 pengiriman WhatsApp (berhasil/gagal) ikut ditampilkan --
                 lihat App\Services\FonnteOtpService::sendPlainMessage().
                 Kalau key ini TIDAK ADA (mis. dari alur lama yang belum
                 kirim WA), badge status tidak ditampilkan sama sekali.

    Setelah tombol "Salin Kredensial & Tutup" ditekan, modal menutup
    dengan mengeset properti Livewire 'createdCredentials' (nama properti
    ini WAJIB sama persis di setiap Livewire component pemanggil) menjadi
    null lewat $wire.set -- $wire di sini otomatis merujuk ke Livewire
    component TERDEKAT yang membungkus komponen Blade ini (Alpine
    menemukan root [wire:id] terdekat), jadi komponen ini AMAN dipasang di
    Livewire component manapun asal nama properti publiknya konsisten.
--}}
@props(['credentials'])

@if ($credentials)
    <div
        x-data="{ copied: false }"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-neutral-900/60 backdrop-blur-sm"
    >
        <div class="bg-white dark:bg-slate-800 rounded-lg max-w-sm w-full border border-neutral-200 dark:border-slate-700 shadow-2xl p-6 space-y-4 animate-in fade-in zoom-in duration-150">

            <div class="text-center space-y-1">
                <div class="h-10 w-10 bg-amber-50 dark:bg-amber-950/60 text-amber-600 rounded-full flex items-center justify-center mx-auto text-lg">
                    🔑
                </div>
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white">
                    {{ $credentials['title'] ?? 'Informasi Akun' }}
                </h3>
                <p class="text-[11px] text-neutral-400">
                    Harap salin kredensial berikut sebelum menutup.
                </p>
            </div>

            @if (array_key_exists('wa_sent', $credentials))
                @if ($credentials['wa_sent'])
                    <div class="flex items-center gap-2 px-3 py-2 rounded-md bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-[11px] font-medium">
                        <span>✅</span>
                        <span>Kredensial juga sudah terkirim ke WhatsApp admin.</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-3 py-2 rounded-md bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-[11px] font-medium">
                        <span>⚠️</span>
                        <span>Gagal mengirim ke WhatsApp. Salin manual & sampaikan langsung ke admin.</span>
                    </div>
                @endif
            @endif

            <div class="p-3.5 bg-neutral-50 dark:bg-slate-900 rounded-md border border-neutral-200 dark:border-slate-700 text-xs space-y-2 font-mono">
                <div class="flex justify-between items-center">
                    <span class="text-neutral-400 font-sans">Nama:</span>
                    <span class="font-semibold text-neutral-800 dark:text-white font-sans">{{ $credentials['name'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-neutral-400 font-sans">Username:</span>
                    <span class="text-neutral-900 dark:text-slate-100 font-bold px-1.5 py-0.5 rounded bg-neutral-200/60 dark:bg-slate-800">
                        {{ $credentials['username'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-neutral-400 font-sans">Password:</span>
                    <span class="text-amber-600 dark:text-amber-400 font-bold px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950">
                        {{ $credentials['password'] }}
                    </span>
                </div>
            </div>

            <button
                type="button"
                @click="
                    navigator.clipboard.writeText('Nama: {{ $credentials['name'] }}\nUsername: {{ $credentials['username'] }}\nPassword: {{ $credentials['password'] }}');
                    copied = true;
                    setTimeout(() => { $wire.set('createdCredentials', null) }, 1000);
                "
                :class="copied ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-900 hover:bg-blue-950'"
                class="w-full py-2.5 text-white font-bold text-xs rounded-md transition shadow-sm flex items-center justify-center gap-2 cursor-pointer"
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