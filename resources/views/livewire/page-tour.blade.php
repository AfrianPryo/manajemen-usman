{{--
    Tutorial kontekstual per halaman/aksi (lihat App\Livewire\PageTour dan
    App\Support\PageTours). Gayanya mengikuti components/onboarding-tour: menyorot
    elemen ASLI di halaman dengan kartu petunjuk di sebelahnya. Selama tutorial,
    HANYA elemen yang disorot dan kartu petunjuk yang bisa diklik. Target yang tidak ada / tidak terlihat (mis. tab form yang sedang
    tersembunyi, atau sidebar tertutup di HP) => kartu tampil di tengah layar.

    Posisi sorotan KONSTAN, tidak mengejar target: selama tutorial semua transisi/animasi CSS
    dimatikan (class tour-frozen) sehingga target langsung di posisi akhirnya, lalu posisi sorotan
    + kartu diukur SEKALI dan dipatok (tanpa loop per frame / pengawas). Gulir halaman dikunci;
    hanya elemen yang disorot yang bisa dipakai.
--}}
<div
    x-data="{
        steps: @js($steps),
        tour: @js($tour),
        open: false,
        step: 1,
        ready: false,
        popId: 'tour-pop-{{ $tour }}',
        holeId: 'tour-hole-{{ $tour }}',
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
            // Jangan pernah memilih elemen milik tutorial sendiri (kartu petunjuk & penghalang klik).
            // Tombol CTA di kartu (mis. tautan 'Kelola Template') punya href yang sama dengan target
            // di halaman dan posisinya lebih dulu di DOM, sehingga tanpa filter ini sorotan menempel
            // ke tombol kartu itu sendiri, bukan ke kartu aksi yang sebenarnya.
            const ui = [this.$refs.pop, this.$refs.blocks];
            const own = (e) => ui.some((u) => u && u.contains(e));
            const el = list.find((e) => !own(e) && this.visible(e)) || null;
            if (el !== this._el) this._parents = el ? this.scrollParents(el) : [];
            this._el = el;
            this._elStep = this.step;
            return el;
        },
        // Posisi target saat ini: kotak asli + bagian yang benar-benar terlihat (null bila tidak ada).
        probe() {
            const el = this.find();
            if (!el) return null;
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
            this.$nextTick(() => this.settle());
        },
        // Jadwalkan satu kali pemasangan (menunggu render Alpine / Livewire selama 2 frame).
        settle() {
            if (!this.open) return;
            if (this._raf) cancelAnimationFrame(this._raf);
            this._n = 0;
            this._raf = requestAnimationFrame(() => { this._raf = requestAnimationFrame(() => this.tick()); });
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
                // Tombol yang membuka form/modal (langkah ber-CTA 'click') ditekan, baik lewat tombol di
                // kartu maupun langsung di halaman: tutup tutorial halaman ini supaya kartu & sorotannya
                // tidak tertinggal di belakang modal. Modal yang baru terbuka boleh punya tutorialnya sendiri.
                click: (e) => {
                    if (!this.open || !this.cur.click || !this._el) return;
                    if (e.target instanceof Node && this._el.contains(e.target)) setTimeout(() => this.finish(), 0);
                },
                resize: () => { clearTimeout(this._rt); this._rt = setTimeout(() => { this._snap = null; this.settle(); }, 120); },
            };
            window.addEventListener('wheel', h.wheel, { capture: true, passive: false });
            window.addEventListener('touchmove', h.touch, { capture: true, passive: false });
            window.addEventListener('keydown', h.key, true);
            window.addEventListener('click', h.click, true);
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
            window.removeEventListener('click', h.click, true);
            window.removeEventListener('scroll', h.scroll, true);
            window.removeEventListener('resize', h.resize);
            if (window.visualViewport) window.visualViewport.removeEventListener('resize', h.resize);
            clearTimeout(this._rt);
            this.freeze(false);
            this._on = null;
        },
        stop() {
            this.open = false;
            this._el = null;
            this._snap = null;
            this.unlisten();
            if (this._raf) { cancelAnimationFrame(this._raf); this._raf = null; }
        },
        destroy() { this.stop(); },
        start() {
            if (!this.steps.length) return;
            window.dispatchEvent(new CustomEvent('tour-opened', { detail: { tour: this.tour } }));
            this.step = 1;
            this.reset();
            this.ready = false;
            this.open = true;
            this.listen();
            this.$nextTick(() => this.settle());
        },
        finish() {
            if (!this.open) return;
            this.stop();
            this.$wire.complete();
        },
        clickTarget() {
            const el = this.find();
            if (el) el.click();
        },
    }"
    x-init="
        @if ($autoStart) start(); @endif
        $watch('step', () => { reset(); $nextTick(() => settle()); });
    "
    @start-tour.window="if ($event.detail.tour === tour) start()"
    @tour-opened.window="if ($event.detail.tour !== tour) finish()"
    @keydown.escape.window="finish()"
>
    <template x-if="open">
        {{-- wire:ignore: cegah morph Livewire menghapus style inline sorotan & kartu. --}}
        <div wire:ignore>
            {{-- Penghalang klik: 4 panel transparan di sekeliling sorotan. Area gelap tidak bisa diklik;
                 hanya elemen yang disorot (lubang di tengah) dan kartu petunjuk (z lebih tinggi) yang bisa.
                 Gulir (roda mouse / sentuhan / keyboard) juga dikunci selama tutorial berjalan. --}}
            <div x-ref="blocks" @wheel.prevent @touchmove.prevent>
                <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none;width:100%;height:100%"></div>
                <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none"></div>
                <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none"></div>
                <div data-tour-block class="fixed top-0 left-0 z-[70]" style="touch-action:none"></div>
            </div>

            {{-- Sorotan: lubang di lapisan gelap tepat di atas elemen target.
                 pointer-events-none supaya elemen di bawahnya (yang disorot) tetap bisa diklik. --}}
            <div id="tour-hole-{{ $tour }}" x-ref="hole" class="fixed top-0 left-0 z-[70] pointer-events-none rounded-md"></div>

            {{-- Kartu petunjuk --}}
            <div id="tour-pop-{{ $tour }}" x-ref="pop" role="dialog" aria-label="{{ $title }}"
                 class="fixed top-0 left-0 z-[71] w-[calc(100vw-1rem)] max-w-xs bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-md shadow-xl transition-opacity duration-150 motion-reduce:transition-none"
                 :class="ready ? 'opacity-100' : 'opacity-0'">

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

                    <button type="button" x-show="cur.cta && cur.click" x-cloak @click="clickTarget()"
                            class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-900 dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all cursor-pointer"
                            x-text="cur.cta"></button>
                    <a x-show="cur.cta && cur.href" x-cloak :href="cur.href"
                       class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-900 dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-sm hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all cursor-pointer"
                       x-text="cur.cta"></a>
                </div>

                <div class="px-4 py-2.5 border-t border-neutral-100 dark:border-slate-700 bg-neutral-50/50 dark:bg-slate-900/50 rounded-b-md flex items-center justify-between gap-2">
                    <button type="button" @click="finish()"
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
                        <button type="button" x-show="step === steps.length" x-cloak @click="finish()"
                                class="px-3 py-1 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-sm transition-all cursor-pointer">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>