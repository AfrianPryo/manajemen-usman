{{--
    Toast notifikasi global -- pengganti kotak flash message inline
    (session('success') / session('error') dkk) yang sebelumnya dirender
    di dalam alur konten dan gampang tidak kelihatan / ikut tergeser
    layout. Sekarang semua pesan flash session tampil sebagai popup
    mengambang di pojok kanan atas, konsisten dengan pola komponen
    global lain di aplikasi ini (bandingkan dengan store Alpine
    'confirmDialog' di components/confirm-dialog.blade.php).

    DIPASANG SEKALI per layout (lihat components/layouts/app.blade.php,
    unit.blade.php, & guest.blade.php). Store toast ('$store.toast')
    didaftarkan sekali secara global lewat event 'alpine:init' di bawah
    dan persist selama sesi browser berjalan -- artinya panel popup ini
    TIDAK perlu ikut di-render ulang oleh Livewire tiap kali komponen
    lain melakukan aksi.

    CARA HALAMAN/KOMPONEN LIVewire LAIN MEMICU TOAST:
    Karena panel ini hidup di luar boundary Livewire manapun, ia tidak
    otomatis tahu kalau ada session flash baru yang di-set oleh sebuah
    komponen Livewire lain saat aksi ajax (mis. session()->flash('message', ...)
    di dalam method Livewire). Untuk itu, di setiap Blade view Livewire
    yang sebelumnya punya blok flash inline, cukup taruh trigger sekecil
    ini di posisi yang sama (lihat livewire/master/transactions/index.blade.php
    utk contoh nyata):

        @if (session()->has('message'))
            <div wire:key="toast-message-{{ md5(session('message')) }}"
                 x-data x-init="$store.toast.push('success', @js(session('message')))"></div>
        @endif

    `wire:key` WAJIB unik terhadap ISI pesan (bukan cuma nama key-nya),
    supaya kalau dua aksi berturut-turut menghasilkan pesan flash yang
    berbeda, Livewire menganggap elemen trigger-nya sebagai node baru
    (bukan elemen lama yang cuma di-morph), sehingga x-init benar-benar
    terpanggil lagi dan toast baru muncul. Elemen trigger sendiri tidak
    menghasilkan tampilan apapun (kosong) -- yang tampil ke user adalah
    popup dari panel global ini.

    Tipe yang didukung: 'success' (biru, check-circle) & 'error'
    (merah, x-circle).
--}}
@php
    $__toastMap = [
        'success' => 'success',
        'message' => 'success',
        'status' => 'success',
        'category_success' => 'success',
        'success_password' => 'success',
        'success_profile' => 'success',
        'error' => 'error',
        'category_error' => 'error',
    ];

    $__toastFlash = [];
    foreach ($__toastMap as $__key => $__type) {
        if (session()->has($__key)) {
            $__toastFlash[] = ['type' => $__type, 'message' => session($__key)];
        }
    }
@endphp

<div
    x-data
    x-init="$store.toast.hydrate(@js($__toastFlash))"
    class="fixed z-[80] top-4 inset-x-4 sm:inset-x-auto sm:right-4 flex flex-col items-stretch sm:items-end gap-2 pointer-events-none"
    aria-live="polite"
    aria-atomic="true"
>
    <template x-for="item in $store.toast.items" :key="item.id">
        <div
            x-show="item.show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1 sm:translate-y-0 sm:translate-x-4"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave-end="opacity-0 sm:translate-x-4"
            @mouseenter="$store.toast.pause(item.id)"
            @mouseleave="$store.toast.resume(item.id)"
            class="pointer-events-auto w-full sm:w-96 max-w-full bg-white dark:bg-slate-800 rounded-sm shadow-lg shadow-black/5 border border-neutral-200 dark:border-slate-700 overflow-hidden"
            role="alert"
        >
            <div class="flex items-start gap-3 p-3.5">
                <div
                    class="h-8 w-8 shrink-0 rounded-sm flex items-center justify-center"
                    :class="item.type === 'error'
                        ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400'
                        : 'bg-blue-50 dark:bg-blue-950/50 text-blue-900 dark:text-blue-400'"
                >
                    <span x-show="item.type === 'error'">
                        <x-heroicon-s-x-circle class="size-5 fill-current" />
                    </span>
                    <span x-show="item.type !== 'error'">
                        <x-heroicon-s-check-circle class="size-5 fill-current" />
                    </span>
                </div>
                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-xs font-bold text-neutral-900 dark:text-white" x-text="item.type === 'error' ? 'Gagal' : 'Berhasil'"></p>
                    <p class="mt-0.5 text-xs leading-relaxed text-neutral-500 dark:text-neutral-400 break-words" x-text="item.message"></p>
                </div>
                <button
                    type="button"
                    @click="$store.toast.dismiss(item.id)"
                    class="shrink-0 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 -m-1 p-1 rounded-sm transition-colors cursor-pointer"
                    aria-label="Tutup notifikasi"
                >
                    <x-heroicon-o-x-mark class="size-4" stroke-width="2" />
                </button>
            </div>
            <div class="h-0.5 w-full bg-neutral-100 dark:bg-slate-700/60">
                <div
                    class="h-full"
                    :class="item.type === 'error' ? 'bg-rose-500' : 'bg-blue-900 dark:bg-blue-500'"
                    :style="`width: ${item.remaining}%; transition: width ${item.paused ? '0ms' : '80ms'} linear`"
                ></div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        // Guard supaya store tidak didaftar ulang kalau komponen ini
        // ter-render lebih dari sekali (sama seperti pola 'confirmDialog'
        // di components/confirm-dialog.blade.php).
        if (Alpine.store('toast')) {
            return;
        }

        Alpine.store('toast', {
            items: [],
            _seq: 0,
            _timers: {},
            // Pesan yang baru saja ditampilkan (kunci 'tipe|pesan' -> waktu). Satu flash session bisa
            // dipicu lebih dari satu tempat pada render yang sama (panel ini lewat hydrate(), trigger
            // di view halaman, dan trigger di panel notifikasi header), sehingga pesan yang sama
            // muncul 2-3x bersamaan. Pesan identik dalam jendela DEDUPE_MS dianggap satu.
            _recent: {},
            DEDUPE_MS: 1500,
            DURATION: 4000,
            TICK: 80,

            // Dipanggil oleh panel ini sendiri saat mount, untuk pesan
            // flash yang sudah ada di render Blade pertama (full page
            // load / navigasi wire:navigate).
            hydrate(flashes) {
                (flashes || []).forEach((flash) => this.push(flash.type, flash.message));
            },

            // Dipanggil dari trigger kecil di masing-masing Blade Livewire
            // (lihat komentar di atas) setiap kali sebuah aksi ajax
            // menghasilkan flash message baru.
            push(type, message) {
                if (!message) {
                    return;
                }

                const kind = type === 'error' ? 'error' : 'success';
                const key = kind + '|' + message;
                const now = Date.now();

                if (this._recent[key] && now - this._recent[key] < this.DEDUPE_MS) {
                    return;
                }
                this._recent[key] = now;

                // Bersihkan catatan lama supaya map tidak menumpuk.
                for (const k in this._recent) {
                    if (now - this._recent[k] > this.DEDUPE_MS * 4) delete this._recent[k];
                }

                const id = ++this._seq;

                this.items.push({
                    id,
                    type: type === 'error' ? 'error' : 'success',
                    message,
                    show: false,
                    paused: false,
                    remaining: 100,
                    _elapsed: 0,
                });

                // Tunda 1 tick supaya x-transition "enter" sempat terpasang
                // dulu sebelum show di-set true -- kalau langsung true,
                // Alpine kadang melewati animasi masuk untuk elemen yang
                // baru saja dibuat lewat x-for.
                //
                // PENTING: item HARUS diambil ulang lewat this.items.find()
                // di sini (bukan menyimpan referensi object mentah dari
                // sebelum di-push ke atas). Array `items` dibungkus reaktif
                // oleh Alpine/Vue secara "lazy" saat diakses -- referensi
                // mentah sebelum push() adalah object BERBEDA dari proxy
                // reaktif yang dipakai template (x-for/x-show). Mengubah
                // `.show` lewat referensi mentah tidak pernah lewat proxy
                // itu, jadi Alpine tidak pernah tahu ada perubahan & popup
                // tidak akan pernah kelihatan meski datanya sudah benar.
                setTimeout(() => {
                    const item = this.items.find((i) => i.id === id);
                    if (!item) {
                        return;
                    }
                    item.show = true;
                    this._startTimer(id);
                }, 20);
            },

            _startTimer(id) {
                this._timers[id] = setInterval(() => {
                    const item = this.items.find((i) => i.id === id);
                    if (!item) {
                        this._stopTimer(id);
                        return;
                    }
                    if (item.paused) {
                        return;
                    }
                    item._elapsed += this.TICK;
                    item.remaining = Math.max(0, 100 - (item._elapsed / this.DURATION) * 100);
                    if (item._elapsed >= this.DURATION) {
                        this.dismiss(id);
                    }
                }, this.TICK);
            },

            _stopTimer(id) {
                clearInterval(this._timers[id]);
                delete this._timers[id];
            },

            pause(id) {
                const item = this.items.find((i) => i.id === id);
                if (item) item.paused = true;
            },

            resume(id) {
                const item = this.items.find((i) => i.id === id);
                if (item) item.paused = false;
            },

            dismiss(id) {
                const item = this.items.find((i) => i.id === id);
                if (!item) return;
                this._stopTimer(id);
                item.show = false;
                setTimeout(() => {
                    this.items = this.items.filter((i) => i.id !== id);
                }, 200);
            },
        });
    });
</script>