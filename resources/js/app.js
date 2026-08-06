import { initLandingAnimations } from './landing-animations.js';
import { initSmoothScroll } from "./smooth-scroll";
import { initAsciiHero } from "./ascii-3d-hero.js";
import { initPageLoader } from "./loader.js"; // 👈 Import module loader

// Export ke window agar bisa dipanggil spesifik dari Blade (seperti di login.blade.php)
window.initAsciiHero = initAsciiHero;

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

    // 4. Inisialisasi Model 3D ASCII Hero (Khusus Landing Page)
    const landingContainer = document.querySelector("#ascii-3d-container");
    if (landingContainer) {
        initAsciiHero({
            containerSelector: "#ascii-3d-container",
            modelUrl: "/models/hero.glb",
        });
    }
}

// Inisialisasi saat load pertama kali
document.addEventListener('DOMContentLoaded', initGlobalScripts);

// Inisialisasi saat navigasi Livewire v3 SPA Navigation
document.addEventListener('livewire:navigated', initGlobalScripts);