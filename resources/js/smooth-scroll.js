import Lenis from "lenis";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

export function initSmoothScroll() {
    const lenis = new Lenis({
        duration: 1.5,          // durasi transisi tiap scroll event (detik)
        easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
        smoothWheel: true,      // aktifkan smooth untuk scroll mouse/trackpad
        wheelMultiplier: 0.7,     // sensitivitas per "tick" scroll mouse
        touchMultiplier: 1.5,   // sensitivitas untuk swipe di touchscreen
        lerp: 0.1,               // alternatif ke duration/easing (lihat catatan di bawah)
    });

    // Sinkronkan Lenis dengan requestAnimationFrame GSAP,
    // supaya semua ScrollTrigger yang sudah ada tetap akurat
    lenis.on("scroll", ScrollTrigger.update);

    gsap.ticker.add((time) => {
        lenis.raf(time * 1000);
    });

    gsap.ticker.lagSmoothing(0);

    return lenis;
}