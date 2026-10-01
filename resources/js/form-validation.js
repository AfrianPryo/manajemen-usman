// ================= Error State Field Form =================
// Memberi tanda (border merah + aria-invalid) pada kolom input yang punya
// pesan error validasi Livewire, dan merapikan perilakunya saat user memperbaiki.
//
// Cara kerja: komponen <x-form-error> merender pesan dengan atribut
// data-error-for="<nama properti Livewire>". Setelah tiap respons Livewire
// diproses, modul ini mencocokkan nama itu dengan atribut wire:model pada
// input/select/textarea di komponen yang sama. Dengan begitu semua form
// mendapat tampilan error state yang seragam tanpa harus menambah class
// kondisional di tiap input satu per satu.
//
// Gaya visual "[aria-invalid=true]" ada di resources/css/app.css.
// Modul ini hanya menambah/menghapus atribut yang ia pasang sendiri
// (ditandai data-sync-invalid), jadi aria-invalid yang sudah dirender
// server (mis. di halaman login) maupun yang diatur form-tabs tidak ditimpa.

const COMPONENT_SELECTOR = '[wire\\:id]';
const CONTROL_SELECTOR = 'input, select, textarea';
const SKIP_TYPES = new Set(['hidden', 'checkbox', 'radio', 'file', 'range', 'submit', 'button', 'reset', 'image']);
const SYNC_MARK = 'data-sync-invalid';

function modelName(control) {
    for (const attr of control.attributes) {
        if (attr.name === 'wire:model' || attr.name.startsWith('wire:model.')) {
            return attr.value;
        }
    }
    return null;
}

// wire:model.live / .blur memicu request ke server, dan Livewire mempertahankan
// error lama sampai validasi berikutnya -- untuk field seperti itu tanda error
// jangan dihapus lebih dulu di sisi client agar tidak berkedip.
function syncsToServerWhileEditing(control) {
    for (const attr of control.attributes) {
        if (attr.name.startsWith('wire:model') && /\.(live|blur)(\.|$)/.test(attr.name)) {
            return true;
        }
    }
    return false;
}

function isVisible(el) {
    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
}

function errorNamesFor(root, cache) {
    if (cache.has(root)) {
        return cache.get(root);
    }

    const names = new Set();
    (root || document).querySelectorAll('[data-error-for]').forEach((el) => {
        if ((el.closest(COMPONENT_SELECTOR) || null) === root) {
            names.add(el.getAttribute('data-error-for'));
        }
    });
    cache.set(root, names);

    return names;
}

function syncInvalidState() {
    const cache = new Map();
    const newlyInvalid = [];

    document.querySelectorAll(CONTROL_SELECTOR).forEach((control) => {
        if (SKIP_TYPES.has(control.type)) {
            return;
        }

        const name = modelName(control);
        if (name === null) {
            return;
        }

        const root = control.closest(COMPONENT_SELECTOR) || null;
        const invalid = errorNamesFor(root, cache).has(name);

        if (invalid) {
            if (control.getAttribute('aria-invalid') !== 'true') {
                control.setAttribute('aria-invalid', 'true');
                control.setAttribute(SYNC_MARK, '');
                newlyInvalid.push(control);
            }
        } else if (control.hasAttribute(SYNC_MARK)) {
            control.removeAttribute(SYNC_MARK);
            control.removeAttribute('aria-invalid');
        }
    });

    return newlyInvalid;
}

// Setelah simpan gagal validasi: bawa user ke isian pertama yang bermasalah.
// Fokus hanya dipindah bila user tidak sedang mengetik di kolom lain, supaya
// validasi realtime (wire:model.live) tidak "merebut" kursor.
function revealFirstInvalid(controls) {
    const first = controls.find(isVisible);
    if (!first) {
        return;
    }

    const active = document.activeElement;
    const typing = active && active !== first && active.matches && active.matches(CONTROL_SELECTOR);
    if (!typing) {
        try {
            first.focus({ preventScroll: true });
        } catch (e) {}
    }

    const rect = first.getBoundingClientRect();
    if (rect.top < 0 || rect.bottom > (window.innerHeight || document.documentElement.clientHeight)) {
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function runSync(reveal) {
    const newlyInvalid = syncInvalidState();
    if (reveal && newlyInvalid.length) {
        revealFirstInvalid(newlyInvalid);
    }
}

// Saat user mulai memperbaiki isian, tanda error pada kolom itu langsung
// dihapus (pesan di bawahnya disembunyikan). Validasi server berikutnya
// akan memunculkannya lagi bila isiannya memang masih belum benar.
function clearOnEdit(event) {
    const control = event.target;
    if (!control || !control.matches || !control.matches(CONTROL_SELECTOR)) {
        return;
    }
    if (!control.hasAttribute(SYNC_MARK) || syncsToServerWhileEditing(control)) {
        return;
    }

    const name = modelName(control);
    control.removeAttribute(SYNC_MARK);
    control.removeAttribute('aria-invalid');

    if (name === null) {
        return;
    }

    const root = control.closest(COMPONENT_SELECTOR) || document;
    root.querySelectorAll('[data-error-for]').forEach((el) => {
        if (el.getAttribute('data-error-for') === name) {
            el.style.display = 'none';
        }
    });
}

document.addEventListener('input', clearOnEdit, true);
document.addEventListener('change', clearOnEdit, true);

document.addEventListener('livewire:init', () => {
    if (!window.Livewire || typeof window.Livewire.hook !== 'function') {
        return;
    }

    window.Livewire.hook('commit', ({ succeed }) => {
        succeed(() => setTimeout(() => runSync(true), 0));
    });
});

// Error yang sudah dirender server saat halaman pertama kali dibuka / navigasi SPA.
document.addEventListener('DOMContentLoaded', () => runSync(false));
document.addEventListener('livewire:navigated', () => runSync(false));
