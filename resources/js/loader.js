import { gsap } from "gsap";
import { playHeroAnimations } from "./landing-animations.js";
import { modelLoadedPromise } from "./ascii-3d-hero.js";

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

    const has3DHero = document.querySelector("#ascii-3d-container") || document.querySelector("#ascii-hero-container");
    if (has3DHero && modelLoadedPromise) {
        modelLoadedPromise.then(() => {
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
                    playHeroAnimations(); // Animasi elemen hero baru berjalan saat tirai terangkat
                    if (window.ScrollTrigger) window.ScrollTrigger.refresh();
                },
            },
            "+=0.05"
        );
    }
}