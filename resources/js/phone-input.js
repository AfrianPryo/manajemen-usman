/**
 * Alpine component untuk <x-phone-input>.
 *
 * - Pemilih negara (bendera + kode) dengan pencarian, default Indonesia (+62)
 * - Format otomatis saat mengetik (812-3456-7890), caret tidak melompat
 * - Nol di depan otomatis dibuang (0812... -> 812...), paste "+62 812..." /
 *   "0812..." / "62812..." dikenali otomatis (termasuk negaranya)
 * - Nilai yang dikirim ke Livewire selalu E.164: "+6281234567890"
 */
const MAX_E164_DIGITS = 15;

export function registerPhoneInput(Alpine) {
    Alpine.data('phoneInput', ({ countries, initial = '', defaultIso = 'ID' }) => ({
        countries,
        iso: defaultIso,
        national: '', // hanya digit, TANPA kode negara & tanpa 0 di depan
        open: false,
        search: '',
        active: 0,

        init() {
            this.applyValue(initial, true);
            this.$watch('open', (isOpen) => {
                if (isOpen) {
                    this.search = '';
                    this.active = Math.max(0, this.countries.findIndex((c) => c.iso === this.iso));
                    this.$nextTick(() => this.$refs.search?.focus());
                }
            });
            this.$watch('search', () => (this.active = 0));
        },

        get country() {
            return this.countries.find((c) => c.iso === this.iso) ?? this.countries[0];
        },

        get filtered() {
            const q = this.search.trim().toLowerCase().replace(/^\+/, '');
            if (!q) return this.countries;
            return this.countries.filter(
                (c) => c.name.toLowerCase().includes(q) || c.iso.toLowerCase() === q || c.dial.startsWith(q)
            );
        },

        get maxNational() {
            return MAX_E164_DIGITS - this.country.dial.length;
        },

        get formatted() {
            const groups = ['US', 'CA'].includes(this.iso) ? [3, 3, 4] : [3, 4, 5];
            const parts = [];
            let i = 0;
            for (const size of groups) {
                if (i >= this.national.length) break;
                parts.push(this.national.slice(i, i + size));
                i += size;
            }
            return parts.join('-');
        },

        get placeholder() {
            if (this.iso === 'ID') return '812-3456-7890';
            if (['US', 'CA'].includes(this.iso)) return '201-555-0123';
            return '123-4567-890';
        },

        get value() {
            return this.national ? '+' + this.country.dial + this.national : '';
        },

        matchDial(digits) {
            return [...this.countries]
                .sort((a, b) => b.dial.length - a.dial.length)
                .find((c) => digits.startsWith(c.dial));
        },

        /** Baca nilai mentah (ketikan / paste / data lama) lalu isi iso + national. */
        applyValue(raw, pasted = false) {
            raw = String(raw ?? '').trim();
            let digits = raw.replace(/\D/g, '');

            if (!digits) {
                this.national = '';
                return;
            }

            if (raw.startsWith('+') || raw.startsWith('00')) {
                if (!raw.startsWith('+')) digits = digits.slice(2);
                const c = this.matchDial(digits);
                if (c) {
                    this.iso = c.iso;
                    this.national = digits.slice(c.dial.length);
                } else {
                    this.national = digits;
                }
            } else if (digits.startsWith('0')) {
                this.national = digits.replace(/^0+/, '');
            } else if (
                pasted &&
                digits.startsWith(this.country.dial) &&
                digits.length >= this.country.dial.length + 8
            ) {
                this.national = digits.slice(this.country.dial.length);
            } else {
                this.national = digits;
            }

            this.national = this.national.replace(/^0+/, '').slice(0, this.maxNational);
        },

        onInput(e) {
            const el = e.target;
            const raw = el.value;
            const pasted = e.inputType === 'insertFromPaste' || e.inputType === 'insertFromDrop';
            const special = /^\s*(\+|00)/.test(raw);
            const digitsBeforeCaret = raw.slice(0, el.selectionStart ?? raw.length).replace(/\D/g, '').length;

            this.applyValue(raw, pasted);
            el.value = this.formatted;

            // Kembalikan caret ke posisi yang benar setelah diformat ulang.
            let pos = el.value.length;
            if (!pasted && !special) {
                let seen = 0;
                pos = 0;
                while (pos < el.value.length && seen < digitsBeforeCaret) {
                    if (/\d/.test(el.value[pos])) seen++;
                    pos++;
                }
            }
            el.setSelectionRange(pos, pos);

            this.sync();
        },

        selectCountry(c) {
            if (!c) return;
            this.iso = c.iso;
            this.national = this.national.slice(0, this.maxNational);
            this.open = false;
            this.sync();
            this.$nextTick(() => this.$refs.input?.focus());
        },

        onListKey(e) {
            if (e.key === 'ArrowDown') this.active = Math.min(this.active + 1, this.filtered.length - 1);
            else if (e.key === 'ArrowUp') this.active = Math.max(this.active - 1, 0);
            else if (e.key === 'Enter') this.selectCountry(this.filtered[this.active]);
            this.$nextTick(() => this.$refs.list?.querySelector('[data-active="true"]')?.scrollIntoView({ block: 'nearest' }));
        },

        /** Kirim nilai E.164 ke properti Livewire lewat hidden input (wire:model). */
        sync() {
            const hidden = this.$refs.hidden;
            hidden.value = this.value;
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
        },
    }));
}

if (window.Alpine) {
    registerPhoneInput(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => registerPhoneInput(window.Alpine));
}