import Lenis from "lenis";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export function initSmoothScroll() {
    // =====================================
    // Guard: Lenis hanya boleh aktif di halaman landing.
    // Ditandai lewat attribute data-page="landing" di <body>
    // (lihat resources/views/layouts/landing.blade.php).
    // Kalau attribute-nya tidak ada, berarti bukan halaman landing
    // -> jangan inisialisasi Lenis sama sekali.
    // =====================================
    const isLandingPage = document.body?.dataset?.page === "landing";

    if (!isLandingPage) {
        return null;
    }

    const lenis = new Lenis({
        duration: 1.5,          // durasi transisi tiap scroll event (detik)
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,      // aktifkan smooth untuk scroll mouse/trackpad
        wheelMultiplier: 0.7,   // sensitivitas per "tick" scroll mouse
        touchMultiplier: 1.7,   // sensitivitas untuk swipe di touchscreen
        lerp: 0.1,               // alternatif ke duration/easing (lihat catatan di bawah)
    });

    // Sinkronkan Lenis dengan requestAnimationFrame GSAP,
    // supaya semua ScrollTrigger yang sudah ada tetap akurat
    const onLenisScroll = () => ScrollTrigger.update();
    lenis.on("scroll", onLenisScroll);

    const rafCallback = (time) => {
        lenis.raf(time * 1000);
    };
    gsap.ticker.add(rafCallback);

    gsap.ticker.lagSmoothing(0);

    // =====================================
    // Cleanup — jaga-jaga kalau nanti ada navigasi
    // tanpa full reload (Livewire/Turbo/dsb), supaya
    // Lenis dari halaman landing tidak "nyangkut"
    // dan tetap mempengaruhi scroll di halaman lain.
    // =====================================
    lenis.destroy = ((originalDestroy) => () => {
        gsap.ticker.remove(rafCallback);
        lenis.off("scroll", onLenisScroll);
        originalDestroy.call(lenis);
    })(lenis.destroy.bind(lenis));

    return lenis;
}