{{--
    Dialog konfirmasi kustom, pengganti confirm() bawaan browser & directive
    Livewire native `wire:confirm="..."` (yang di baliknya juga cuma
    memanggil window.confirm() bawaan browser).

    KENAPA DIGANTI: popup bawaan browser (confirm()/alert()) tidak bisa
    di-styling, tampilannya beda-beda tiap browser/OS, dan memblokir thread
    JS (blocking) sehingga terasa "murahan" dibanding UI Tailwind lain di
    aplikasi ini. Komponen ini menggantikan SEMUA pemakaian confirm()/
    wire:confirm di seluruh aplikasi dengan modal kustom yang konsisten.

    CARA PAKAI (di button/aksi manapun, di dalam Livewire component apapun):

        <button
            type="button"
            x-on:click.prevent="$store.confirmDialog.open({
                message: 'Apakah Anda yakin ingin menghapus data ini?',
                onConfirm: () => $wire.delete({{ $item->id }})
            })"
        >
            Hapus
        </button>

    Opsi yang didukung pada open({...}):
    - message    : string (wajib) - isi pertanyaan konfirmasi
    - title      : string (opsional) - default 'Konfirmasi'
    - confirmText: string (opsional) - default 'Ya, Lanjutkan'
    - cancelText : string (opsional) - default 'Batal'
    - variant    : 'danger' | 'default' (opsional) - default 'danger' (dipakai
                   hampir semua aksi hapus/reset di aplikasi ini). Pakai
                   'default' untuk aksi non-destruktif.
    - onConfirm  : function (wajib) - dijalankan saat tombol konfirmasi
                   ditekan. Karena fungsi ini didefinisikan langsung di
                   elemen pemanggil, closure `$wire`-nya otomatis merujuk ke
                   Livewire component TERDEKAT tempat tombol itu berada --
                   persis seperti wire:click biasa -- meski dialognya sendiri
                   dipasang satu kali secara global di layout (bukan di
                   dalam masing-masing komponen Livewire).

    Store Alpine ('confirmDialog') didaftarkan sekali lewat event
    'alpine:init' di bawah. Komponen ini cukup dipasang SEKALI per layout
    (lihat components/layouts/app.blade.php & components/layouts/unit.blade.php),
    tidak perlu diulang di tiap halaman/komponen Livewire.
--}}
<div
    x-data
    x-show="$store.confirmDialog.show"
    x-cloak
    x-trap.noscroll="$store.confirmDialog.show"
    @keydown.escape.window="$store.confirmDialog.cancel()"
    class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    role="alertdialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-neutral-900/50 backdrop-blur-[2px]"
        x-show="$store.confirmDialog.show"
        x-transition.opacity
        @click="$store.confirmDialog.cancel()"
    ></div>

    {{-- Panel --}}
    <div
        class="relative bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-sm border border-neutral-200 dark:border-slate-700 p-5 space-y-4"
        x-show="$store.confirmDialog.show"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="flex items-start gap-3">
            <div
                class="h-9 w-9 shrink-0 rounded-full flex items-center justify-center text-base"
                :class="$store.confirmDialog.variant === 'danger'
                    ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400'
                    : 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400'"
            >
                <span x-show="$store.confirmDialog.variant === 'danger'">⚠️</span>
                <span x-show="$store.confirmDialog.variant !== 'danger'">❓</span>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <h3 class="text-sm font-bold text-neutral-900 dark:text-white" x-text="$store.confirmDialog.title"></h3>
                <p class="mt-1 text-xs leading-relaxed text-neutral-500 dark:text-neutral-400" x-text="$store.confirmDialog.message"></p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-1">
            <button
                type="button"
                @click="$store.confirmDialog.cancel()"
                class="px-3.5 py-2 text-xs font-semibold rounded-md text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 hover:bg-neutral-200 dark:hover:bg-slate-600 transition-colors"
                x-text="$store.confirmDialog.cancelText"
            ></button>
            <button
                type="button"
                @click="$store.confirmDialog.confirm()"
                class="px-3.5 py-2 text-xs font-bold rounded-md text-white transition-colors shadow-sm"
                :class="$store.confirmDialog.variant === 'danger'
                    ? 'bg-rose-600 hover:bg-rose-700'
                    : 'bg-blue-900 hover:bg-blue-950'"
                x-text="$store.confirmDialog.confirmText"
            ></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        // Guard supaya store tidak didaftar ulang kalau komponen ini
        // ter-render lebih dari sekali dalam satu halaman (harusnya tidak,
        // tapi aman untuk navigasi SPA Livewire v3 / hot reload dev).
        if (Alpine.store('confirmDialog')) {
            return;
        }

        Alpine.store('confirmDialog', {
            show: false,
            title: 'Konfirmasi',
            message: '',
            confirmText: 'Ya, Lanjutkan',
            cancelText: 'Batal',
            variant: 'danger',
            onConfirm: null,

            open(options) {
                this.title = options.title ?? 'Konfirmasi';
                this.message = options.message ?? 'Apakah Anda yakin?';
                this.confirmText = options.confirmText ?? 'Ya, Lanjutkan';
                this.cancelText = options.cancelText ?? 'Batal';
                this.variant = options.variant ?? 'danger';
                this.onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
                this.show = true;
            },

            confirm() {
                const action = this.onConfirm;
                this.close();
                if (action) {
                    action();
                }
            },

            cancel() {
                this.close();
            },

            close() {
                this.show = false;
                this.onConfirm = null;
            },
        });
    });
</script>
