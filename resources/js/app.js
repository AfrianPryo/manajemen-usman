import { initSmoothScroll } from "./smooth-scroll";
import { initPageLoader } from "./loader.js"; // 👈 Import module loader
import "./phone-input.js"; // 👈 Alpine component untuk <x-phone-input>
import "./form-validation.js"; // 👈 Penanda error state field form (lihat <x-form-error>)

// 👇 Lazy-load module ascii-3d-hero.js (dan Three.js di dalamnya) HANYA saat
// dibutuhkan, alih-alih di-import secara statis di atas. Three.js adalah
// dependency yang cukup besar, jadi men-static-import-nya membuat bundle
// app.js membengkak untuk SEMUA halaman meski hanya landing page & login
// yang benar-benar memakainya. Dengan dynamic import(), Vite otomatis
// memecah ascii-3d-hero.js (+three) menjadi chunk terpisah yang baru
// di-fetch saat fungsi ini pertama kali dipanggil.
let asciiHeroModulePromise = null;
function loadAsciiHeroModule() {
    if (!asciiHeroModulePromise) {
        asciiHeroModulePromise = import("./ascii-3d-hero.js");
    }
    return asciiHeroModulePromise;
}

// Export ke window agar bisa dipanggil spesifik dari Blade (seperti di login.blade.php).
// PERUBAHAN PERILAKU YANG PERLU DIPERHATIKAN PEMANGGIL: karena loading modul
// sekarang async, window.initAsciiHero juga jadi ASYNC (mengembalikan Promise
// yang resolve ke instance, bukan instance langsung). Signature argumen
// (object options) tidak berubah. Lihat login.blade.php untuk versi
// pemanggilan yang sudah di-`await`.
window.initAsciiHero = async function (options) {
    const { initAsciiHero } = await loadAsciiHeroModule();
    return initAsciiHero(options);
};

// 👇 Lazy-load landing-animations.js (GSAP ScrollTrigger + 8 fungsi animasi
// landing page, ~860 baris) HANYA di halaman landing. Sebelumnya di-import
// statis sehingga ikut masuk bundle utama dan dieksekusi di SEMUA halaman
// (dashboard Master/Unit, login, dsb.) padahal semua elemen targetnya
// (#nav-pill, #theme-toggle, .faq-item, dst.) hanya ada di landing.blade.php.
// Penanda halaman landing: <body data-page="landing"> (layouts/landing.blade.php)
// + fallback ke elemen khas landing, jaga-jaga kalau body diganti saat SPA navigate.
let landingAnimationsModulePromise = null;
function loadLandingAnimationsModule() {
    if (!landingAnimationsModulePromise) {
        landingAnimationsModulePromise = import("./landing-animations.js");
    }
    return landingAnimationsModulePromise;
}

function isLandingPage() {
    return !!document.querySelector(
        '[data-page="landing"], #page-loader, #nav-pill, #theme-toggle, #theme-toggle-mobile'
    );
}

function initGlobalScripts() {
    // 1. Inisialisasi Smooth Scroll
    initSmoothScroll();

    // 2. Inisialisasi Page Loader (Hanya jika elemen #page-loader ada di halaman)
    const pageLoader = document.querySelector("#page-loader");
    if (pageLoader) {
        initPageLoader();
    }

    // 3. Inisialisasi Animasi Landing Page -- hanya di halaman landing, modul
    // di-load dinamis (lihat isLandingPage() di atas).
    if (isLandingPage()) {
        loadLandingAnimationsModule().then(({ initLandingAnimations }) => {
            initLandingAnimations();
        });
    }

    // 4. Inisialisasi Model 3D ASCII Hero (Khusus Landing Page) — modul
    // di-load secara dinamis, hanya jika container-nya memang ada di halaman
    // DAN lebar layar saat ini >= breakpoint lg (1024px). Elemen container
    // sendiri sudah disembunyikan lewat class "hidden lg:block" di Blade,
    // tapi tanpa guard ini Three.js chunk tetap di-fetch & dijalankan
    // sia-sia di mobile (di belakang display:none), memboroskan bandwidth
    // dan CPU/battery pengguna mobile yang tidak pernah melihat elemennya.
    const landingContainer = document.querySelector("#ascii-3d-container");
    const isDesktopViewport = window.matchMedia("(min-width: 1024px)").matches;

    if (landingContainer && isDesktopViewport) {
        loadAsciiHeroModule().then(({ initAsciiHero }) => {
            // Guard tambahan: pada SPA navigation (livewire:navigated), container
            // bisa saja sudah tidak ada lagi di DOM pada saat chunk selesai di-fetch.
            if (!document.querySelector("#ascii-3d-container")) {
                return;
            }

            initAsciiHero({
                containerSelector: "#ascii-3d-container",
                modelUrl: "/models/hero.glb",
                // Logo/foto custom dari Pengaturan > Landing Page (kosong = model 3D).
                imageUrl: landingContainer.dataset.asciiImage || null,
            });
        });
    }
}

// ================= Pengaman Input Angka =================
// Sebagian field di form (No. Telepon, NIP, Harga, Stok, dst) secara alami
// hanya boleh berisi angka. Atribut HTML seperti type="number" atau
// inputmode="numeric" saja TIDAK cukup: type="number" masih meloloskan
// karakter "e", "+", "-", dan keyboard fisik tetap bisa mengetik huruf pada
// field bertipe text. Dua helper berikut dipasang lewat atribut oninput pada
// field terkait di Blade, sehingga karakter selain angka (dan satu titik
// desimal untuk onlyDecimal) langsung dibuang saat event input terjadi --
// baik dari ketikan, paste, maupun autofill. wire:model Livewire tetap
// membaca event yang sama setelah nilai dibersihkan, jadi hasil yang
// tersimpan ke server juga sudah bersih.
window.onlyDigits = function (event) {
    const el = event.target;
    const cleaned = el.value.replace(/[^0-9]/g, '');
    if (cleaned !== el.value) {
        el.value = cleaned;
    }
};

window.onlyDecimal = function (event) {
    const el = event.target;
    let cleaned = el.value.replace(/[^0-9.]/g, '');
    // Hanya izinkan satu titik desimal; sisanya dibuang.
    const firstDot = cleaned.indexOf('.');
    if (firstDot !== -1) {
        cleaned = cleaned.slice(0, firstDot + 1) + cleaned.slice(firstDot + 1).replace(/\./g, '');
    }
    if (cleaned !== el.value) {
        el.value = cleaned;
    }
};

// Inisialisasi saat load pertama kali
document.addEventListener('DOMContentLoaded', initGlobalScripts);

// Inisialisasi saat navigasi Livewire v3 SPA Navigation
document.addEventListener('livewire:navigated', initGlobalScripts);

// ================= Pesan Sesi Kedaluwarsa (Livewire 419) =================
// Bawaan Livewire menampilkan confirm() berbahasa Inggris ("This page has
// expired...") saat token CSRF/sesi habis. Diganti dengan toast Indonesia
// lalu halaman dimuat ulang otomatis. Status selain 419 tidak disentuh.
document.addEventListener('livewire:init', () => {
    if (!window.Livewire || typeof window.Livewire.hook !== 'function') {
        return;
    }

    window.Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status !== 419) {
                return;
            }

            preventDefault();

            const pesan = 'Sesi halaman telah berakhir. Halaman akan dimuat ulang.';
            try {
                window.Alpine?.store('toast')?.push('error', pesan);
            } catch (e) {}

            setTimeout(() => window.location.reload(), 1500);
        });
    });
});
