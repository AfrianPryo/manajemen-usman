{{--
    Komponen form 2-langkah untuk modal dengan field yang banyak.

    Field di kedua tab TETAP ada di DOM, jadi wire:model, validasi Livewire,
    dan file upload tetap berfungsi walau tabnya sedang tersembunyi.

    Ukuran container tetap: kedua panel ditumpuk di satu sel grid dan yang
    tidak aktif hanya di-invisible, jadi tinggi form = tinggi panel terpanjang
    dan tidak "melompat" saat pindah tab.

    Footer (Lanjut / Kembali / Simpan) ada di dalam komponen ini:
      - Tab 1 : [Lanjut]              -> validasi field wajib di tab 1, lalu pindah
      - Tab 2 : [Kembali] [Simpan]    -> Kembali ke tab 1, Simpan menyimpan form
    Tidak ada tombol Batal; menutup form lewat ikon X di header modal.
    Tombol submit TIDAK dirender di tab 1, dan Enter di input tab 1 juga melewati
    pemeriksaan field wajib sebelum berpindah ke tab 2.

    Field yang memang wajib ditandai parent dengan data-form-required="true".
    Pemeriksaan di sini hanya mencegah perpindahan tab ketika field wajib masih
    kosong atau sedang berada pada error state; validasi server Livewire tetap
    menjadi sumber kebenaran saat form disimpan.

    Kalau simpan gagal validasi, komponen membaca nama field (wire:model) di
    tiap tab lalu mencocokkannya dengan $errors: tab yang berisi error diberi
    titik merah dan, kalau tab yang sedang dibuka tidak punya error, otomatis
    pindah ke tab yang punya. Tidak perlu daftar field manual dari parent.

    Props:
      tab1-label / tab2-label : judul tab
      cancel                  : (tidak dipakai lagi, boleh dihapus dari pemanggil)
      compact                 : true untuk ukuran tombol kecil (text-xs)
      rounded                 : kelas radius tombol (default rounded-sm)
      active                  : (opsional) paksa tab awal 1 / 2

    Pemakaian:
        <x-form-tabs tab1-label="Data Utama" tab2-label="Detail">
            <x-slot:tab1> ... </x-slot:tab1>
            <x-slot:tab2> ... </x-slot:tab2>
            <x-slot:submit>
                <button type="submit" wire:loading.attr="disabled" class="...">Simpan</button>
            </x-slot:submit>
        </x-form-tabs>
--}}
@props([
    'tab1Label' => 'Detail',
    'tab2Label' => 'Lainnya',
    'cancel' => null, // tidak dipakai lagi; penutupan lewat ikon X di header modal
    'compact' => false,
    'rounded' => 'rounded-sm',
    'active' => null,
])

@php
    // Nama field per tab, diambil dari atribut wire:model (termasuk .live/.blur/dst).
    // Induk key array ikut dihitung ("items.0.qty" -> "items.0", "items") supaya
    // error level array (misal key 'items') juga menandai tab-nya.
    $modelsIn = function ($slot) {
        if (! preg_match_all('/wire:model(?:\.[\w-]+)*\s*=\s*"([^"]+)"/', (string) $slot, $m)) {
            return [];
        }
        $keys = [];
        foreach ($m[1] as $name) {
            $parts = explode('.', $name);
            for ($i = 1, $n = count($parts); $i <= $n; $i++) {
                $keys[] = implode('.', array_slice($parts, 0, $i));
            }
        }
        return array_values(array_unique($keys));
    };

    $tab1Errors = $errors->hasAny($modelsIn($tab1));
    $tab2Errors = $errors->hasAny($modelsIn($tab2));

    $startTab = $active !== null
        ? ((int) $active === 2 ? 2 : 1)
        : ($tab2Errors && ! $tab1Errors ? 2 : 1);

    $btnBack = $compact
        ? "px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 {$rounded} hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer"
        : "px-4 py-2.5 border border-neutral-200 dark:border-slate-700 {$rounded} text-sm font-semibold hover:bg-neutral-50 dark:hover:bg-slate-700 dark:text-white transition-colors cursor-pointer";

    $btnNext = $compact
        ? "inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 {$rounded} transition-all shadow-sm cursor-pointer"
        : "inline-flex items-center gap-1.5 px-5 py-2.5 text-sm font-semibold text-white bg-blue-900 hover:bg-blue-950 {$rounded} transition-colors shadow-sm shadow-blue-900/20 cursor-pointer";
@endphp

<div x-data="{
        formTab: {{ $startTab }},
        armed: false,
        nextBlocked: false,

        fieldIsInvalid(field) {
            if (!field || field.disabled) return false;

            if (field.matches('[data-form-validate=email]') || field.type === 'email') {
                return String(field.value ?? '').trim() !== '' && !field.checkValidity();
            }

            if (field.type === 'checkbox') {
                return !field.checked;
            }

            if (field.type === 'radio') {
                const name = field.getAttribute('name');
                if (!name) return !field.checked;

                const panel = this.$refs.tab1Panel;
                const radios = panel
                    ? [...panel.querySelectorAll('input[type=radio][name]')].filter(radio => !radio.disabled && radio.name === name)
                    : [];
                return radios.length > 0 && !radios.some(radio => radio.checked);
            }

            return String(field.value ?? '').trim() === '';
        },

        fieldLabel(field) {
            let box = field.parentElement;
            let label = null;
            for (let i = 0; i < 4 && box && !label; i++) {
                label = box.querySelector(':scope > label');
                box = box.parentElement;
            }
            const text = label
                ? [...label.childNodes].filter(n => n.nodeType === 3).map(n => n.textContent).join(' ')
                : '';
            return text.replace(/\*/g, '').replace(/\([^)]*\)/g, '').replace(/\s+/g, ' ').trim()
                || field.getAttribute('aria-label')
                || 'Kolom ini';
        },

        fieldMessage(field) {
            const label = this.fieldLabel(field);
            const isEmail = field.matches('[data-form-validate=email]') || field.type === 'email';
            if (isEmail && String(field.value ?? '').trim() !== '') {
                return label + ' tidak valid. Gunakan format seperti nama@contoh.com.';
            }
            if (field.tagName === 'SELECT' || field.type === 'checkbox') {
                return label + ' wajib dipilih.';
            }
            return label + ' wajib diisi.';
        },

        showFieldMessage(field, invalid) {
            if (field.type === 'radio') return;
            if (field._clientError) {
                field._clientError.remove();
                field._clientError = null;
            }
            if (!invalid) return;

            const p = document.createElement('p');
            p.className = 'field-error mt-1 flex items-start gap-1 text-[11px] leading-snug font-medium text-rose-600 dark:text-rose-400';
            p.setAttribute('role', 'alert');
            p.setAttribute('data-client-error', '');

            const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('class', 'mt-px size-3 shrink-0');
            icon.setAttribute('viewBox', '0 0 20 20');
            icon.setAttribute('fill', 'currentColor');
            icon.setAttribute('aria-hidden', 'true');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('fill-rule', 'evenodd');
            path.setAttribute('clip-rule', 'evenodd');
            path.setAttribute('d', 'M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z');
            icon.appendChild(path);

            const span = document.createElement('span');
            span.textContent = this.fieldMessage(field);

            p.appendChild(icon);
            p.appendChild(span);
            field.insertAdjacentElement('afterend', p);
            field._clientError = p;
        },

        markRequiredField(field, invalid) {
            if (!field) return;
            field.toggleAttribute('data-required-invalid', invalid);
            field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
            field.classList.toggle('!border-rose-400', invalid);
            field.classList.toggle('!focus:border-rose-500', invalid);
            field.classList.toggle('!focus:ring-rose-500/10', invalid);
            this.showFieldMessage(field, invalid);
        },

        clearRequiredError(field) {
            if (!field?.matches('[data-form-required=true], [aria-required=true], [required], [data-form-validate]')) return;

            if (field.type === 'radio' && field.name) {
                const panel = this.$refs.tab1Panel?.contains(field) ? this.$refs.tab1Panel : this.$refs.tab2Panel;
                const radios = panel
                    ? [...panel.querySelectorAll('input[type=radio][name]')].filter(radio => !radio.disabled && radio.name === field.name)
                    : [];
                if (radios.some(radio => radio.checked)) {
                    radios.forEach(radio => this.markRequiredField(radio, false));
                }
            } else if (!this.fieldIsInvalid(field)) {
                this.markRequiredField(field, false);
            }

            this.nextBlocked = this.tabHasRequiredErrors(1);
        },

        tabHasRequiredErrors(tab) {
            const panel = tab === 1 ? this.$refs.tab1Panel : this.$refs.tab2Panel;
            if (!panel) return false;

            const fields = [...panel.querySelectorAll('[data-form-required=true], [aria-required=true], [required], [data-form-validate]')]
                .filter(field => !field.disabled);
            const radioNames = new Set();

            for (const field of fields) {
                if (field.type === 'radio' && field.name) {
                    radioNames.add(field.name);
                    continue;
                }
                if (this.fieldIsInvalid(field) || field.getAttribute('aria-invalid') === 'true') return true;
            }

            for (const name of radioNames) {
                const radios = fields.filter(field => field.type === 'radio' && field.name === name);
                if (!radios.some(radio => radio.checked)) return true;
            }

            return false;
        },

        canLeaveTab(tab) {
            if (tab !== 1) return true;

            const panel = this.$refs.tab1Panel;
            if (!panel) return true;

            const fields = [...panel.querySelectorAll('[data-form-required=true], [aria-required=true], [required], [data-form-validate]')]
                .filter(field => !field.disabled);
            const invalid = [];
            const radioNames = new Set();

            fields.forEach(field => {
                if (field.type === 'radio' && field.name) {
                    radioNames.add(field.name);
                    return;
                }
                const bad = this.fieldIsInvalid(field) || field.getAttribute('aria-invalid') === 'true';
                this.markRequiredField(field, bad);
                if (bad) invalid.push(field);
            });

            radioNames.forEach(name => {
                const radios = fields.filter(field => field.type === 'radio' && field.name === name);
                const checked = radios.some(field => field.checked);
                radios.forEach(field => this.markRequiredField(field, !checked));
                if (!checked && radios[0]) invalid.push(radios[0]);
            });

            this.nextBlocked = invalid.length > 0;

            if (invalid.length) {
                const first = invalid[0];
                requestAnimationFrame(() => {
                    first.focus({ preventScroll: true });
                    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                return false;
            }

            return true;
        },

        goToNextTab() {
            if (this.canLeaveTab(1)) {
                this.nextBlocked = false;
                this.formTab = 2;
            }
        }
     }"
     data-err="{{ ($tab1Errors ? '1' : '') . ($tab2Errors ? '2' : '') }}"
     x-init="window.__formTabsSync ||= (window.Livewire && Livewire.hook('commit', ({ succeed }) => succeed(() => setTimeout(() => window.dispatchEvent(new CustomEvent('form-tabs-synced')), 0))), true)"
     x-on:keydown.enter="if (formTab === 1 && $event.target.tagName === 'INPUT') { $event.preventDefault(); goToNextTab() }"
     x-on:input.capture="clearRequiredError($event.target)"
     x-on:change.capture="clearRequiredError($event.target)"
     x-on:form-tabs-synced.window="if (armed) { armed = false; const e = $el.dataset.err; if (e && !e.includes(formTab)) formTab = +e[0] }">

    {{-- Tab Switcher --}}
    <div class="flex items-center gap-5 border-b border-neutral-100 dark:border-slate-700">
        <button type="button" wire:key="form-tab-1" @click="formTab = 1; nextBlocked = false"
                :class="formTab === 1
                    ? 'text-blue-900 dark:text-blue-400 border-blue-900 dark:border-blue-400'
                    : 'text-neutral-400 dark:text-neutral-500 border-transparent hover:text-neutral-600 dark:hover:text-neutral-300'"
                class="flex items-center gap-1.5 pb-2.5 -mb-px text-xs font-bold border-b-2 transition-colors cursor-pointer">
            <span :class="formTab === 1
                    ? 'bg-blue-900 dark:bg-blue-400 text-white dark:text-slate-900'
                    : 'bg-neutral-200 dark:bg-slate-700 text-neutral-500 dark:text-neutral-400'"
                  class="w-4 h-4 rounded-full text-[10px] font-bold flex items-center justify-center transition-colors shrink-0">1</span>
            <span>{{ $tab1Label }}</span>
            @if($tab1Errors)
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0" title="Ada isian yang perlu diperbaiki"></span>
            @endif
        </button>
        <button type="button" wire:key="form-tab-2" @click="canLeaveTab(1) && (formTab = 2)"
                :class="formTab === 2
                    ? 'text-blue-900 dark:text-blue-400 border-blue-900 dark:border-blue-400'
                    : 'text-neutral-400 dark:text-neutral-500 border-transparent hover:text-neutral-600 dark:hover:text-neutral-300'"
                class="flex items-center gap-1.5 pb-2.5 -mb-px text-xs font-bold border-b-2 transition-colors cursor-pointer">
            <span :class="formTab === 2
                    ? 'bg-blue-900 dark:bg-blue-400 text-white dark:text-slate-900'
                    : 'bg-neutral-200 dark:bg-slate-700 text-neutral-500 dark:text-neutral-400'"
                  class="w-4 h-4 rounded-full text-[10px] font-bold flex items-center justify-center transition-colors shrink-0">2</span>
            <span>{{ $tab2Label }}</span>
            @if($tab2Errors)
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0" title="Ada isian yang perlu diperbaiki"></span>
            @endif
        </button>
    </div>

    {{-- Isi tab: x-show mengatur panel aktif. Style awal dari Blade mencegah
         kedua panel sempat tampil bersamaan sebelum Alpine selesai inisialisasi.
         Field tetap ada di DOM agar wire:model dan validasi Livewire tetap berfungsi. --}}
    <div class="min-w-0">
        <div x-ref="tab1Panel"
             x-show="formTab === 1"
             style="{{ $startTab === 1 ? '' : 'display: none;' }}"
             class="min-w-0 space-y-4 pt-4"
             :aria-hidden="formTab !== 1">
            {{ $tab1 }}
        </div>

        <div x-ref="tab2Panel"
             x-show="formTab === 2"
             style="{{ $startTab === 2 ? '' : 'display: none;' }}"
             class="min-w-0 space-y-4 pt-4"
             :aria-hidden="formTab !== 2">
            {{ $tab2 }}
        </div>
    </div>

    {{-- Footer: tab 1 = [Lanjut], tab terakhir = [Kembali] [Simpan].
         Membatalkan/menutup form lewat ikon X di header modal (di luar komponen ini). --}}
    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
        <div x-show="nextBlocked && formTab === 1" style="display: none;" class="mr-auto text-[11px] text-rose-500 dark:text-rose-400 leading-relaxed">
            Masih ada isian yang belum lengkap atau belum sesuai. Periksa kolom bertanda merah sebelum melanjutkan.
        </div>

        <button type="button" x-show="formTab === 2" style="{{ $startTab === 2 ? '' : 'display: none;' }}" @click="formTab = 1; nextBlocked = false" class="inline-flex items-center gap-1.5 {{ $btnBack }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            Kembali
        </button>

        <button type="button" x-show="formTab === 1" style="{{ $startTab === 1 ? '' : 'display: none;' }}" @click="goToNextTab()" class="{{ $btnNext }}">
            Lanjut
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </button>

        <div x-show="formTab === 2" style="{{ $startTab === 2 ? '' : 'display: none;' }}" @click.capture="armed = true" class="contents">
            {{ $submit }}
        </div>
    </div>
</div>

