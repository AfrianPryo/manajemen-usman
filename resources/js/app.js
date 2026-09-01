import { initLandingAnimations } from './landing-animations.js';
import { initSmoothScroll } from "./smooth-scroll";
import { initPageLoader } from "./loader.js"; // 👈 Import module loader

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

function initGlobalScripts() {
    // 1. Inisialisasi Smooth Scroll
    initSmoothScroll();

    // 2. Inisialisasi Page Loader (Hanya jika elemen #page-loader ada di halaman)
    const pageLoader = document.querySelector("#page-loader");
    if (pageLoader) {
        initPageLoader();
    }

    // 3. Inisialisasi Animasi Landing Page (Aman dipanggil karena ada pengecekan DOM di dalamnya)
    initLandingAnimations();

    // 4. Inisialisasi Model 3D ASCII Hero (Khusus Landing Page) — modul
    // di-load secara dinamis, hanya jika container-nya memang ada di halaman.
    const landingContainer = document.querySelector("#ascii-3d-container");
    if (landingContainer) {
        loadAsciiHeroModule().then(({ initAsciiHero }) => {
            // Guard tambahan: pada SPA navigation (livewire:navigated), container
            // bisa saja sudah tidak ada lagi di DOM pada saat chunk selesai di-fetch.
            if (!document.querySelector("#ascii-3d-container")) {
                return;
            }

            initAsciiHero({
                containerSelector: "#ascii-3d-container",
                modelUrl: "/models/hero.glb",
            });
        });
    }
}

// Inisialisasi saat load pertama kali
document.addEventListener('DOMContentLoaded', initGlobalScripts);

// Inisialisasi saat navigasi Livewire v3 SPA Navigation
document.addEventListener('livewire:navigated', initGlobalScripts);