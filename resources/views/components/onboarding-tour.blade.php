{{--
    Walkthrough setup awal Master Admin (kontekstual): menyorot elemen ASLI di
    halaman (tombol header, menu akun, menu Tanda Tangan) dengan kartu petunjuk
    di sebelahnya, bukan modal layar penuh. Selama tutorial, HANYA elemen yang
    disorot dan kartu petunjuk yang bisa diklik (area gelap dihalangi), jadi
    tombol yang disorot bisa langsung ditekan tanpa risiko salah klik.

    Logika langkah TIDAK ada di sini -- tetap di App\Livewire\Master\Dashboard
    ($showOnboarding, determineOnboardingStep(), completeOnboarding()). Komponen
    ini hanya menampilkan langkah yang diberikan server lewat prop $step.

    Target disorot lewat selector CSS, dan yang disorot SELALU elemen tempat aksinya
    benar-benar dilakukan (tombol Unit Usaha, tombol Admin Baru, menu Pengaturan Sistem,
    menu Tanda Tangan). Kalau target tidak ada / tidak terlihat, kartu tampil di tengah
    layar tanpa sorotan. Selector [data-tour="..."] dipasang di dashboard.blade.php
    (add-unit, add-admin) dan layouts/app.blade.php (user-menu, settings-link).

    Langkah yang targetnya ada di sidebar / popup menu akun memakai 'prep': komponen ini
    membukakan popup menu akun (langkah 3) dan sidebar mobile (langkah 3 & 4) lebih dulu
    supaya elemen targetnya benar-benar tampil & bisa diklik, lalu menutupnya lagi saat
    berpindah ke langkah lain atau tutorial selesai.

    Posisi sorotan KONSTAN, tidak mengejar target: selama tutorial semua transisi/animasi CSS
    dimatikan (class tour-frozen) sehingga target langsung di posisi akhirnya, lalu posisi sorotan
    + kartu diukur SEKALI dan dipatok. Tidak ada loop per frame, pengawas, atau listener scroll
    penghitung posisi; hanya resize layar yang memicu pengukuran ulang. Gulir halaman dikunci
    (roda mouse, sentuhan, tombol keyboard) dan klik di luar sorotan diblokir.
--}}
@props(['step' => 1])

@php
    // Menu sidebar yang benar-benar dirender layout (config/menu.php), supaya target langkah 4
    // menunjuk ke menu yang memang ada: menu Tanda Tangan langsung bila ada di sidebar, kalau
    // tidak ada maka menu Dokumen Resmi (tempat kartu Tanda Tangan berada).
    $menuRoutes = collect(config('menu', []))
        ->flatMap(fn ($m) => array_merge([$m['route'] ?? null], array_column($m['children'] ?? [], 'route')))
        ->filter()
        ->values()
        ->all();

    $signRoute = 'master.documents.signature';
    $docsRoute = 'master.documents.index';
    $linkTo = fn (string $name) => 'a[href$="' . parse_url(route($name), PHP_URL_PATH) . '"]';

    $signTarget = null;
    $signBody   = 'Profil Tanda Tangan (nama, jabatan, gambar) dipakai untuk membubuhkan tanda tangan digital pada Dokumen Resmi yang Anda terbitkan.';

    if (in_array($signRoute, $menuRoutes, true)) {
        $signTarget = $linkTo($signRoute);
        $signBody   = 'Buka menu Tanda Tangan (disorot) untuk mengisi nama, jabatan, dan gambar tanda tangan. ' . $signBody;
    } elseif (Route::has($docsRoute) && in_array($docsRoute, $menuRoutes, true)) {
        $signTarget = $linkTo($docsRoute);
        $signBody   = 'Buka menu Dokumen Resmi (disorot), lalu pilih kartu Tanda Tangan. ' . $signBody;
    }

    $steps = [
        [
            'target' => '[data-tour="add-unit"]',
            'side'   => 'bottom',
            'title'  => 'Tambahkan Unit Usaha pertama',
            'body'   => 'Unit Usaha (mis. Kantin, Koperasi, Percetakan) adalah dasar sistem ini: transaksi, stok, dan admin unit semuanya terhubung ke sebuah unit.',
            'cta'    => 'Tambah Unit Usaha',
            'call'   => 'openCreateUnitModal',
        ],
        [
            'target' => '[data-tour="add-admin"]',
            'side'   => 'bottom',
            'title'  => 'Tambahkan Admin Unit',
            'body'   => 'Admin Unit mengelola operasional harian unitnya. Kredensial login dibuat otomatis dan dikirim ke nomor WhatsApp admin.',
            'cta'    => 'Tambah Admin',
            'call'   => 'openCreateAdminModal',
        ],
        [
            'target' => '[data-tour="settings-link"]',
            'prep'   => 'account',
            'side'   => 'right',
            'title'  => 'Sambungkan Fonnte (WhatsApp)',
            'body'   => 'Fonnte mengirim kredensial admin baru, kode OTP, dan notifikasi otomatis lewat WhatsApp. Buka Pengaturan Sistem (disorot), pilih tab Fitur & Modul, aktifkan Notifikasi WhatsApp, lalu isi nomor pengirim dan API key Fonnte.',
            'cta'    => 'Buka Pengaturan',
            'href'   => route('master.settings.index'),
        ],
        [
            'target' => $signTarget,
            'prep'   => 'sidebar',
            'side'   => 'right',
            'title'  => 'Atur tanda tangan pejabat',
            'body'   => $signBody,
            'cta'    => 'Atur Tanda Tangan',
            'href'   => route('master.documents.signature'),
        ],
    ];
@endphp

{{-- wire:ignore: seluruh isi komponen dikendalikan Alpine. Tanpa ini, setiap re-render
     Livewire pada Dashboard (filter, wire:loading, dll) me-morph DOM-nya dan menghapus
     style inline sorotan & kartu, sehingga keduanya berkedip / melompat ke pojok layar. --}}
<div
    wire:ignore
    x-data="{
        steps: @js($steps),
        step: {{ (int) $step }},
        open: true,
        ready: false,
        popId: 'onboarding-tour-pop',
        holeId: 'onboarding-tour-hole',
        _parents: [],
        _key: '',
        _on: null,
        _side: '',
        _el: null,
        _elStep: 0,
        _raf: null,
        _rt: null,
        _n: 0,
        _scrolled: false,
        _snap: null,
        _late: null,
        _wm: false,
        _fz: null,
        get cur() { return this.steps[this.step - 1] || {}; },
        visible(e) {
            if (!e || !e.isConnected) return false;
            const r = e.getBoundingClientRect();
            return e.getClientRects().length > 0
                && getComputedStyle(e).visibility !== 'hidden'
                && r.width > 0 && r.height > 0 && r.right > 0 && r.left < window.innerWidth;
        },
        // Ancestor yang bisa memotong (overflow) elemen target, mis. <main> yang scroll
        // atau panel modal, supaya sorotan ikut terpotong bila target tersembunyi sebagian.
        scrollParents(e) {
            const out = [];
            let p = e.parentElement;
            while (p && p !== document.body && p !== document.documentElement) {
                const s = getComputedStyle(p);
                if (/(auto|scroll|hidden|clip)/.test(s.overflowX + ' ' + s.overflowY)) out.push(p);
                p = p.parentElement;
            }
            return out;
        },
        // Irisan kotak dengan viewport dan semua ancestor pemotong.
        clipRect(r) {
            let t = Math.max(r.top, 0), l = Math.max(r.left, 0);
            let b = Math.min(r.bottom, window.innerHeight), rt = Math.min(r.right, window.innerWidth);
            for (const p of this._parents) {
                const pr = p.getBoundingClientRect();
                if (!pr.width && !pr.height) continue;
                t = Math.max(t, pr.top); l = Math.max(l, pr.left);
                b = Math.min(b, pr.bottom); rt = Math.min(rt, pr.right);
            }
            return { t, l, w: Math.max(0, rt - l), h: Math.max(0, b - t) };
        },
        // Ancestor pemotong yang TIDAK bisa digulir pengguna (overflow hidden/clip). Posisi gulirnya
        // disimpan sebelum scrollIntoView dan dikembalikan sesudahnya, karena scrollIntoView
        // ikut menggeser kontainer overflow-hidden (sidebar, layout utama) dan membuat layout melompat.
        lockedParents() {
            return this._parents.filter((p) => !/(auto|scroll)/.test(getComputedStyle(p).overflowX + ' ' + getComputedStyle(p).overflowY));
        },
        // Tulis kotak penghalang klik langsung ke elemennya.
        box(e, t, l, w, h) {
            e.style.top = t + 'px';
            e.style.left = l + 'px';
            e.style.width = Math.max(0, w) + 'px';
            e.style.height = Math.max(0, h) + 'px';
        },
        // Reset status per langkah (dipanggil saat langkah berganti / tutorial dimulai).
        reset() {
            this._el = null;
            this._parents = [];
            this._side = '';
            this._key = '';
            this._scrolled = false;
            this._snap = null;
        },
        // Elemen target dibuat 'lengket': selama masih ada di DOM dan terlihat, elemen yang sama
        // terus dipakai, supaya sorotan tidak meloncat ke elemen lain yang kebetulan cocok dengan selector.
        find() {
            if (!this.cur.target) return null;
            if (this._el && this._elStep === this.step && this.visible(this._el)) return this._el;
            let list = [];
            try { list = [...document.querySelectorAll(this.cur.target)]; } catch (e) { return null; }
            const el = list.find((e) => this.visible(e)) || null;
            if (el !== this._el) this._parents = el ? this.scrollParents(el) : [];
            this._el = el;
            this._elStep = this.step;
            return el;
        },
        // Posisi target saat ini: kotak asli + bagian yang benar-benar terlihat (null bila tidak ada).
        // Target di dalam popup menu akun baru dianggap siap bila popup sudah selesai animasi masuk
        // (opacity penuh & tanpa transform). Tanpa ini, sorotan terukur saat popup masih mengecil
        // (scale-95 + translate) sehingga ukuran/posisinya meleset.
        settled(el) {
            const host = el.closest('[x-show]');
            if (!host) return true;
            const cs = getComputedStyle(host);
            return parseFloat(cs.opacity) >= 0.99
                && (cs.transform === 'none' || cs.transform === 'matrix(1, 0, 0, 1, 0, 0)');
        },
        probe() {
            const el = this.find();
            if (!el) return null;
            if (this.cur.prep === 'account' && !this.settled(el)) return null;
            const r = el.getBoundingClientRect();
            return { r, v: this.clipRect(r) };
        },
        // Tulis sorotan, penghalang klik, dan kartu SEKALI. Posisinya konstan sampai langkah
        // berganti atau ukuran layar berubah; tidak ada pelacakan target dan tidak ada loop.
        apply(box, r, pw, ph) {
            const pop = this.$refs.pop, hl = this.$refs.hole;
            if (!pop || !hl) return;
            const vw = window.innerWidth, vh = window.innerHeight, g = 12, p = 6;
            let hole, top, left;
            if (!box) {
                // Tanpa target (atau target tidak terlihat): kartu di tengah layar, tanpa sorotan.
                hole = { t: vh / 2, l: vw / 2, w: 0, h: 0 };
                top = (vh - ph) / 2;
                left = (vw - pw) / 2;
                this._side = '';
            } else {
                hole = this.clipRect({ top: r.top - p, left: r.left - p, bottom: r.bottom + p, right: r.right + p });
                const bl = box.l, bt = box.t, br = box.l + box.w, bb = box.t + box.h;
                const fits = {
                    right: br + p + g + pw <= vw - 8,
                    top: bt - p - g - ph >= 8,
                    bottom: bb + p + g + ph <= vh - 8,
                };
                // Sisi kartu dibuat 'lengket' per langkah: hanya diganti kalau sudah tidak muat.
                let side = this._side;
                if (!side || !fits[side]) {
                    const pref = this.cur.side || 'bottom';
                    side = fits[pref] ? pref : (fits.bottom ? 'bottom' : (fits.top ? 'top' : (fits.right ? 'right' : pref)));
                    this._side = side;
                }
                if (side === 'right') {
                    left = br + p + g;
                    top = bt + box.h / 2 - ph / 2;
                } else if (side === 'top') {
                    left = bl + box.w / 2 - pw / 2;
                    top = bt - p - g - ph;
                } else {
                    left = bl + box.w / 2 - pw / 2;
                    top = bb + p + g;
                }
                top = Math.max(8, Math.min(top, vh - ph - 8));
                left = Math.max(8, Math.min(left, vw - pw - 8));
            }
            const ht = Math.round(hole.t), hlf = Math.round(hole.l), hw = Math.round(hole.w), hh = Math.round(hole.h);
            top = Math.round(top); left = Math.round(left);
            const key = [ht, hlf, hw, hh, top, left, vw, vh].join(',');
            const bs = this.$refs.blocks ? this.$refs.blocks.children : [];
            // Cek juga style DOM: kalau style inline hilang, tulis ulang.
            if (key !== this._key || !hl.style.top || !pop.style.top || (bs.length === 4 && !bs[1].style.top)) {
                this._key = key;
                // Empat penghalang klik mengelilingi sorotan (lubangnya dibiarkan kosong supaya elemen
                // yang disorot tetap bisa diklik). Tanpa sorotan, seluruh layar tertutup.
                if (bs.length === 4) {
                    const rt = hlf + hw, bm = ht + hh;
                    this.box(bs[0], 0, 0, vw, ht);
                    this.box(bs[1], bm, 0, vw, vh - bm + 200);
                    this.box(bs[2], ht, 0, hlf, hh);
                    this.box(bs[3], ht, rt, vw - rt + 200, hh);
                }
                hl.style.top = ht + 'px';
                hl.style.left = hlf + 'px';
                hl.style.width = hw + 'px';
                hl.style.height = hh + 'px';
                hl.style.boxShadow = (hw ? '0 0 0 2px rgb(96 165 250), ' : '') + '0 0 0 9999px rgba(15, 23, 42, 0.55)';
                pop.style.top = top + 'px';
                pop.style.left = left + 'px';
            }
            // Kunci posisi gulir: apa pun yang mencoba menggulir halaman akan dikembalikan (lihat guard).
            this._snap = { x: window.scrollX, y: window.scrollY, els: this._parents.map((q) => [q, q.scrollTop, q.scrollLeft]) };
            if (!this.ready) this.ready = true;
        },
        // Ukur target SEKALI lalu pasang sorotan + kartu. Karena semua transisi/animasi dimatikan
        // selama tutorial (lihat freeze), sidebar / modal / tab sudah di posisi akhir, jadi hasil
        // ukurannya akurat tanpa perlu menunggu atau mendeteksi gerakan. Bila elemen target belum
        // ada di DOM (mis. modal baru saja dibuka), dicoba lagi maksimal 20 frame, lalu kartu di tengah.
        tick() {
            this._raf = null;
            if (!this.open) return;
            try {
                this.measure();
            } catch (e) {
                console.error('tour:', e);
                try {
                    const pop = this.$refs.pop;
                    if (pop) this.apply(null, null, pop.offsetWidth, pop.offsetHeight);
                } catch (e2) { /* biarkan */ }
            }
        },
        measure() {
            const pop = this.$refs.pop;
            if (!pop) return;
            const pw = pop.offsetWidth, ph = pop.offsetHeight;
            if (!pw || !ph) { if (++this._n < 20) this._raf = requestAnimationFrame(() => this.tick()); return; }
            if (!this.cur.target) { this.apply(null, null, pw, ph); return; }
            let m = this.probe();
            if (!m) {
                // Popup menu akun bisa saja tertutup / belum tampil: pastikan terbuka lagi lalu coba ulang.
                if (this.cur.prep) this.prepare();
                if (++this._n < 20) { this._raf = requestAnimationFrame(() => this.tick()); return; }
                this.apply(null, null, pw, ph);
                return;
            }
            // Gulung SEKALI ke target bila belum terlihat penuh (termasuk yang sepenuhnya di luar
            // layar / di luar kontainer gulir), lalu ukur ulang.
            if (!this._scrolled && (m.v.w < m.r.width - 1 || m.v.h < m.r.height - 1)) {
                this._scrolled = true;
                const locked = this.lockedParents().map((q) => [q, q.scrollTop, q.scrollLeft]);
                this._el.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' });
                locked.forEach(([q, st, sl]) => { q.scrollTop = st; q.scrollLeft = sl; });
                m = this.probe();
                if (!m) { this.apply(null, null, pw, ph); return; }
            }
            const box = m.v.w >= 4 && m.v.h >= 4 ? m.v : null;
            this.apply(box, box ? m.r : null, pw, ph);
        },
        // Pindah langkah: dipanggil langsung oleh tombol Lanjut / Kembali (tidak bergantung pada
        // $watch), lalu pemasangan posisi dijadwalkan ulang untuk langkah yang baru.
        go(d) {
            const n = this.step + d;
            if (n < 1 || n > this.steps.length) return;
            this.step = n;
            this.reset();
            // Simpan langkah aktif ke server (renderless) supaya tutorial melanjutkan dari sini
            // setelah user pindah halaman lewat tombol CTA atau membuka lalu menutup modal.
            try { this.$wire.setOnboardingStep(n); } catch (e) { /* tidak fatal */ }
            this.$nextTick(() => this.settle());
        },
        // Buka / tutup popup menu akun dan sidebar mobile (state Alpine milik layout) lewat
        // Alpine.$data, supaya target yang ada di dalamnya benar-benar tampil sebelum diukur.
        // Hanya menulis bila nilainya berbeda, jadi aman dipanggil berulang.
        layout(want) {
            try {
                const A = window.Alpine;
                const btn = document.querySelector('[data-tour=\'user-menu\']');
                const host = btn ? btn.closest('[x-data]') : null;
                if (!A || !host) return;
                const d = A.$data(host);
                if (typeof d.userMenuOpen === 'boolean' && d.userMenuOpen !== want.menu) d.userMenuOpen = want.menu;
                if (typeof d.mobileSidebarOpen === 'boolean' && d.mobileSidebarOpen !== want.side) d.mobileSidebarOpen = want.side;
            } catch (e) { console.error('tour layout:', e); }
        },
        // Siapkan tampilan layout sesuai kebutuhan langkah ini: langkah 3 butuh popup menu akun
        // terbuka, langkah 3 & 4 butuh sidebar terbuka di layar kecil (di desktop sidebar selalu tampil).
        prepare() {
            const need = this.cur.prep || '';
            const small = window.innerWidth < 768;
            if (need === 'account') this.watchMenu();
            this.layout({ menu: need === 'account', side: small && (need === 'account' || need === 'sidebar') });
        },
        // Selama langkah yang membutuhkan popup menu akun, popup tidak boleh tertutup oleh hal lain
        // (mis. klik-di-luar bawaan popup): begitu ditutup, dibuka lagi dan sorotan diukur ulang.
        // Dipasang sekali; hanya bereaksi pada perubahan status popup, bukan loop.
        watchMenu() {
            if (this._wm) return;
            try {
                const A = window.Alpine;
                const btn = document.querySelector('[data-tour=\'user-menu\']');
                const host = btn ? btn.closest('[x-data]') : null;
                if (!A || !host) return;
                this._wm = true;
                A.evaluate(host, '$watch(\'userMenuOpen\', (v) => window.dispatchEvent(new CustomEvent(\'tour-menu-changed\', { detail: v })))');
            } catch (e) { console.error('tour watch:', e); }
        },
        // Jadwalkan satu kali pemasangan (menunggu render Alpine / Livewire selama 2 frame).
        settle() {
            if (!this.open) return;
            this.prepare();
            if (this._raf) cancelAnimationFrame(this._raf);
            this._n = 0;
            this._raf = requestAnimationFrame(() => { this._raf = requestAnimationFrame(() => this.tick()); });
            // Langkah yang targetnya di popup menu akun: pastikan popup masih terbuka & ukur ulang sekali
            // lagi setelah animasinya pasti selesai (posisi baru hanya ditulis bila memang berubah).
            clearTimeout(this._late);
            if (this.cur.prep === 'account') {
                this._late = setTimeout(() => {
                    if (!this.open) return;
                    this.prepare();
                    this._n = 0;
                    this.tick();
                }, 350);
            }
        },
        // Matikan semua transisi & animasi CSS selama tutorial supaya target tidak bergerak sama sekali.
        freeze(on) {
            if (on && !this._fz) {
                const s = document.createElement('style');
                s.textContent = '.tour-frozen *, .tour-frozen *::before, .tour-frozen *::after { transition: none !important; animation: none !important; scroll-behavior: auto !important; }';
                document.head.appendChild(s);
                document.documentElement.classList.add('tour-frozen');
                this._fz = s;
            } else if (!on && this._fz) {
                document.documentElement.classList.remove('tour-frozen');
                this._fz.remove();
                this._fz = null;
            }
        },
        // Kunci interaksi di luar sorotan: gulir (roda mouse, sentuhan, tombol keyboard) dihentikan
        // total, dan posisi gulir halaman + kontainer target dikembalikan bila ada yang menggesernya
        // (mis. fokus Tab). Hanya perubahan ukuran layar yang memicu pengukuran ulang.
        keys(e) {
            if (!this.open || e.ctrlKey || e.metaKey || e.altKey) return;
            const k = e.key;
            // Esc menutup sidebar mobile (layout) -- tahan selama langkah yang membutuhkannya
            // supaya sorotan tidak tiba-tiba kosong.
            if (k === 'Escape') { if (this.cur.prep) { e.preventDefault(); e.stopPropagation(); } return; }
            const scrollKey = [' ', 'PageUp', 'PageDown', 'Home', 'End', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(k);
            if (!scrollKey && k !== 'Enter') return;
            const t = e.target, pop = this.$refs.pop;
            if (pop && t instanceof Node && pop.contains(t)) return;
            const inEl = !!this._el && t instanceof Node && this._el.contains(t);
            const interactive = t instanceof Element && t.closest('input, textarea, select, [contenteditable], button, a, summary, [role=button], [role=tab], [role=option], [role=menuitem]');
            if (inEl && interactive) return;
            if (!inEl || scrollKey) { e.preventDefault(); e.stopPropagation(); }
        },
        guard(e) {
            const s = this._snap;
            if (!s || !this.open) return;
            const t = e.target;
            if (t === document || t === document.documentElement || t === document.body) {
                if (window.scrollX !== s.x || window.scrollY !== s.y) window.scrollTo(s.x, s.y);
                return;
            }
            for (const q of s.els) {
                if (q[0] === t) {
                    if (t.scrollTop !== q[1] || t.scrollLeft !== q[2]) { t.scrollTop = q[1]; t.scrollLeft = q[2]; }
                    return;
                }
            }
        },
        listen() {
            if (this._on) return;
            this.freeze(true);
            const h = {
                wheel: (e) => { if (this.open && !e.ctrlKey) e.preventDefault(); },
                touch: (e) => { if (this.open && e.touches.length < 2) e.preventDefault(); },
                key: (e) => this.keys(e),
                scroll: (e) => this.guard(e),
                resize: () => { clearTimeout(this._rt); this._rt = setTimeout(() => { this._snap = null; this.settle(); }, 120); },
            };
            window.addEventListener('wheel', h.wheel, { capture: true, passive: false });
            window.addEventListener('touchmove', h.touch, { capture: true, passive: false });
            window.addEventListener('keydown', h.key, true);
            window.addEventListener('scroll', h.scroll, { capture: true, passive: true });
            window.addEventListener('resize', h.resize, { passive: true });
            if (window.visualViewport) window.visualViewport.addEventListener('resize', h.resize);
            this._on = h;
        },
        unlisten() {
            const h = this._on;
            if (!h) return;
            window.removeEventListener('wheel', h.wheel, true);
            window.removeEventListener('touchmove', h.touch, true);
            window.removeEventListener('keydown', h.key, true);
            window.removeEventListener('scroll', h.scroll, true);
            window.removeEventListener('resize', h.resize);
            if (window.visualViewport) window.visualViewport.removeEventListener('resize', h.resize);
            clearTimeout(this._rt);
            this.freeze(false);
            this._on = null;
        },
        stop() {
            this.open = false;
            clearTimeout(this._late);
            this.layout({ menu: false, side: false });
            this._el = null;
            this._snap = null;
            this.unlisten();
            if (this._raf) { cancelAnimationFrame(this._raf); this._raf = null; }
        },
        destroy() { this.stop(); },
    }"
    @tour-menu-changed.window="if (open && cur.prep === 'account' && $event.detail === false) { $nextTick(() => { prepare(); settle(); }); }"
    x-init="
        listen();
        $nextTick(() => settle());
        $watch('step', () => { reset(); $nextTick(() => settle()); });
    "
>
    {{-- Penghalang klik: 4 panel transparan di sekeliling sorotan. Area gelap tidak bisa diklik;
         hanya elemen yang disorot (lubang di tengah) dan kartu petunjuk (z lebih tinggi) yang bisa.
         Gulir (roda mouse / sentuhan / keyboard) juga dikunci selama tutorial berjalan. --}}
    {{-- @click.stop: klik di area gelap tidak boleh sampai ke @click.outside popup menu akun
         (yang akan menutup popup dan mengosongkan sorotan langkah 3). --}}
    <div x-ref="blocks" @wheel.prevent @touchmove.prevent @click.stop>
        <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none;width:100%;height:100%"></div>
        <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none"></div>
        <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none"></div>
        <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none"></div>
    </div>

    {{-- Sorotan: lubang di lapisan gelap tepat di atas elemen target. pointer-events-none
         supaya elemen di bawahnya (yang disorot) tetap bisa diklik. --}}
    <div id="onboarding-tour-hole" x-ref="hole" class="fixed top-0 left-0 z-[70] pointer-events-none rounded-md"></div>

    {{-- Kartu petunjuk --}}
    <div id="onboarding-tour-pop" x-ref="pop" role="dialog" aria-label="Panduan setup awal"
         class="fixed top-0 left-0 z-[71] w-[calc(100vw-1rem)] max-w-xs bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-md shadow-xl transition-opacity duration-150 motion-reduce:transition-none"
         :class="ready ? 'opacity-100' : 'opacity-0'"
         @click.stop>

        <div class="flex gap-1 px-4 pt-3">
            <template x-for="i in steps.length" :key="i">
                <span class="h-1 flex-1 rounded-full transition-colors"
                      :class="i <= step ? 'bg-blue-900 dark:bg-sky-400' : 'bg-neutral-200 dark:bg-slate-600'"></span>
            </template>
        </div>

        <div class="px-4 pt-3 pb-4">
            <p class="text-[11px] font-semibold text-blue-800 dark:text-sky-400" x-text="'Langkah ' + step + ' dari ' + steps.length"></p>
            <h4 class="text-sm font-bold text-neutral-900 dark:text-white mt-1" x-text="cur.title"></h4>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1.5 leading-relaxed" x-text="cur.body"></p>

            <button type="button" x-show="cur.call" x-cloak @click="$wire.call(cur.call)"
                    class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-900 dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all cursor-pointer"
                    x-text="cur.cta"></button>
            <a x-show="cur.href" x-cloak :href="cur.href"
               class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-900 dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all cursor-pointer"
               x-text="cur.cta"></a>
        </div>

        <div class="px-4 py-2.5 border-t border-neutral-100 dark:border-slate-700 bg-neutral-50/50 dark:bg-slate-900/50 rounded-b-md flex items-center justify-between gap-2">
            <button type="button" wire:click="completeOnboarding"
                    class="text-[11px] font-semibold text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 cursor-pointer">
                Lewati
            </button>
            <div class="flex items-center gap-1.5">
                <button type="button" x-show="step > 1" x-cloak @click="go(-1)"
                        class="px-2.5 py-1 text-xs font-semibold text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 cursor-pointer">
                    Kembali
                </button>
                <button type="button" x-show="step < steps.length" x-cloak @click="go(1)"
                        class="px-3 py-1 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all cursor-pointer">
                    Lanjut
                </button>
                <button type="button" x-show="step === steps.length" x-cloak wire:click="completeOnboarding"
                        class="px-3 py-1 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all cursor-pointer">
                    Selesai
                </button>
            </div>
        </div>
    </div>
</div>