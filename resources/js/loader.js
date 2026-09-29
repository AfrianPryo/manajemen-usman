import { gsap } from "gsap";
// landing-animations.js & ascii-3d-hero.js (+ Three.js) SENGAJA di-import
// dinamis di dalam initPageLoader(), bukan statis di sini. Sebelumnya import
// statis di file ini menyeret Three.js + GLTFLoader + AsciiEffect ke bundle
// utama (dan membatalkan lazy-load di app.js) untuk SEMUA halaman, padahal
// initPageLoader() hanya berjalan jika #page-loader ada (landing page).

const COUNT_DURATION = 2.5; 
const PAUSE_BEFORE_WIPE = 0.4; 

export function initPageLoader() {
    const loaderEl = document.getElementById("page-loader");
    const screenEl = document.getElementById("loader-screen");
    const counterEl = document.getElementById("loader-counter");
    const curtain1El = document.getElementById("loader-curtain-1");
    const curtain2El = document.getElementById("loader-curtain-2");
    const mainContent = document.getElementById("main-content");

    if (!loaderEl || !counterEl || !screenEl || !curtain1El || !curtain2El) return;

    // Pre-load modul animasi hero di awal (jauh sebelum tirai terangkat
    // ~3 detik kemudian), supaya playHeroAnimations() siap tanpa jeda.
    const heroAnimationsPromise = import("./landing-animations.js");

    // Kunci Scroll
    document.body.style.overflow = "hidden";

    let countReached = false;
    let windowLoaded = false;
    let modelLoaded = false;
    let isDone = false;


    const checkWindowLoad = () => {
        if (document.readyState === "complete") {
            windowLoaded = true;
            tryFinish();
        } else {
            window.addEventListener("load", () => {
                windowLoaded = true;
                tryFinish();
            }, { once: true });
        }
    };

    // PENTING: di mobile (< 1024px), app.js SENGAJA tidak memanggil
    // initAsciiHero() sama sekali (lihat guard isDesktopViewport di
    // initGlobalScripts), sehingga resolveModelLoaded() di dalam
    // ascii-3d-hero.js tidak pernah terpanggil dan modelLoadedPromise
    // tidak akan pernah resolve. Tanpa guard viewport yang SAMA di sini,
    // loader akan menunggu promise itu SELAMANYA di mobile -> halaman
    // stuck di loading screen dan #main-content tidak pernah dimunculkan.
    const isDesktopViewport = window.matchMedia("(min-width: 1024px)").matches;
    const has3DHero = isDesktopViewport &&
        (document.querySelector("#ascii-3d-container") || document.querySelector("#ascii-hero-container"));
    if (has3DHero) {
        // Modul ascii-3d-hero (chunk Three.js) di-load dinamis; instance modul
        // yang sama dipakai app.js (loadAsciiHeroModule), jadi modelLoadedPromise
        // tetap di-resolve oleh initAsciiHero() persis seperti sebelumnya.
        import("./ascii-3d-hero.js")
            .then(({ modelLoadedPromise }) => modelLoadedPromise)
            .catch((err) => {
                // Jika chunk gagal dimuat, jangan tahan loader selamanya.
                console.warn("[loader] ascii-3d-hero gagal dimuat:", err);
            })
            .then(() => {
                modelLoaded = true;
                tryFinish();
            });
    } else {
        modelLoaded = true;
    }

    checkWindowLoad();

    // Counter Animation
    const proxy = { value: 0 };
    gsap.to(proxy, {
        value: 100,
        duration: COUNT_DURATION,
        ease: "power2.inOut",
        onUpdate: () => {
            counterEl.textContent = `[${Math.round(proxy.value)}]`;
        },
        onComplete: () => {
            countReached = true;
            tryFinish();
        },
    });

    function tryFinish() {
        if (countReached && windowLoaded && modelLoaded && !isDone) {
            isDone = true;
            runOutroSequence();
        }
    }

    // Outro Timeline Sequencer
    function runOutroSequence() {
        const tl = gsap.timeline({
            onComplete: () => {
                document.body.style.overflow = "";
                loaderEl.remove();
            },
        });

        tl
        // 1. Tahan sejenak di angka [100]
        .to({}, { duration: PAUSE_BEFORE_WIPE })

        // 2. Tirai 1 (Gold) meluncur NAIK dari bawah (100% -> 0%)
        .fromTo(
            curtain1El,
            { yPercent: 100 },
            { yPercent: 0, duration: 0.6, ease: "power4.inOut" }
        )

        // 3. Tirai 2 (Hitam) meluncur NAIK menyusul (100% -> 0%)
        .fromTo(
            curtain2El,
            { yPercent: 100 },
            { yPercent: 0, duration: 0.6, ease: "power4.inOut" },
            "-=0.4"
        )

        // 🔴 4. SAAT LAYAR TERTUTUP TOTAL OLEH TIRAI HITAM:
        .add(() => {
            screenEl.style.display = "none"; // Sembunyikan layer counter biru di belakang
            if (mainContent) {
                mainContent.classList.add("is-ready"); // Buka kuncian CSS Guard !important
                gsap.set(mainContent, { autoAlpha: 1 }); // Munculkan landing page tepat di balik tirai
            }
        })

        // 5. Kedua tirai meluncur NAIK keluar layar ke atas (-100%) menyingkap landing page
        .to(
            [curtain1El, curtain2El],
            {
                yPercent: -100,
                duration: 0.75,
                stagger: 0.08,
                ease: "power4.inOut",
                onStart: () => {
                    // Animasi elemen hero baru berjalan saat tirai terangkat
                    heroAnimationsPromise
                        .then(({ playHeroAnimations }) => playHeroAnimations())
                        .catch((err) => console.warn("[loader] playHeroAnimations gagal:", err));
                    if (window.ScrollTrigger) window.ScrollTrigger.refresh();
                },
            },
            "+=0.05"
        );
    }
}