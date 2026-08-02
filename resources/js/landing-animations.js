import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

function initThemeToggle() {
    console.log("🔧 initThemeToggle() dipanggil");

    const toggleBtn = document.getElementById("theme-toggle");
    const sunIcon = document.getElementById("theme-icon-sun");
    const moonIcon = document.getElementById("theme-icon-moon");

    console.log("🔧 toggleBtn:", toggleBtn);

    if (!toggleBtn) {
        console.log("❌ toggleBtn tidak ditemukan, keluar dari fungsi");
        return;
    }

    const applyIcon = () => {
        const isDark = document.documentElement.classList.contains("dark");
        sunIcon.classList.toggle("hidden", !isDark);
        moonIcon.classList.toggle("hidden", isDark);
    };

    applyIcon();

    toggleBtn.addEventListener("click", () => {
        console.log("✅ Tombol theme-toggle diklik!");
        document.documentElement.classList.toggle("dark");
        const isDark = document.documentElement.classList.contains("dark");
        localStorage.setItem("sims-theme", isDark ? "dark" : "light");
        applyIcon();
    });

    console.log("🔧 Event listener berhasil dipasang");
}

function initNavIndicator() {
    const navContainer = document.getElementById("nav-pill");
    const indicator = document.getElementById("nav-indicator");

    if (!navContainer || !indicator) return;

    const navLinks = [...navContainer.querySelectorAll(".nav-link")];
    if (!navLinks.length) return;

    // =====================================
    // Hover Text Animation
    // =====================================

    navLinks.forEach((link) => {
        const track = link.querySelector(".nav-track");

        if (!track) return;

        const textHeight = track.children[0].offsetHeight;

        const tl = gsap.timeline({
            paused: true,
            defaults: {
                duration: 0.32,
                ease: "power4.out",
            },
        });

        tl.to(track, {
            y: -textHeight,
        });

        link.addEventListener("mouseenter", () => tl.play());
        link.addEventListener("mouseleave", () => tl.reverse());
    });

    // =====================================
    // Sections
    // =====================================

    const sections = navLinks
        .map((link) => {
            const href = link.getAttribute("href");

            if (!href.startsWith("#")) return null;

            return document.querySelector(href);
        })
        .filter(Boolean);

    let activeLink = navLinks[0];

    // =====================================
    // Indicator (langsung pindah)
    // =====================================

    const moveIndicatorTo = (link) => {
        if (!link) return;

        indicator.style.transform = `translateX(${link.offsetLeft}px)`;
        indicator.style.width = `${link.offsetWidth}px`;
    };

    // =====================================
    // Active Text Color
    // =====================================

    const setActiveStyles = (link) => {
        navLinks.forEach((item) => {
            item.querySelectorAll(".nav-track span").forEach((span) => {
                span.classList.remove(
                    "text-blue-950",
                    "dark:text-blue-900",
                    "font-semibold"
                );

                span.classList.add("text-white");
            });
        });

        link.querySelectorAll(".nav-track span").forEach((span) => {
            span.classList.remove("text-white");

            span.classList.add(
                "text-blue-950",
                "dark:text-blue-900",
                "font-semibold"
            );
        });
    };

    // =====================================
    // Initial
    // =====================================

    requestAnimationFrame(() => {
        moveIndicatorTo(activeLink);
        setActiveStyles(activeLink);
    });

    ScrollTrigger.refresh();

    sections.forEach((section) => {

        ScrollTrigger.create({
            trigger: section,
            start: "top center",
            end: "bottom center",

            onEnter: () => {
                const link = navLinks.find(
                    l => l.getAttribute("href") === `#${section.id}`
                );

                if (!link) return;

                activeLink = link;
                moveIndicatorTo(activeLink);
                setActiveStyles(activeLink);
            },

            onEnterBack: () => {
                const link = navLinks.find(
                    l => l.getAttribute("href") === `#${section.id}`
                );

                if (!link) return;

                activeLink = link;
                moveIndicatorTo(activeLink);
                setActiveStyles(activeLink);
            }

        });

    });

    // =====================================
    // Resize
    // =====================================

    window.addEventListener("resize", () => {
        moveIndicatorTo(activeLink);
    });
}


function initLoginButtonHover() {
    const buttons = document.querySelectorAll('a[href="/login"]');

    if (!buttons.length) return;

    buttons.forEach((button) => {
        const textEl = button.querySelector(".login-text");
        if (!textEl) return;

        const label = textEl.dataset.text || textEl.textContent.trim();

        textEl.innerHTML = "";
        const tracks = [];

        [...label].forEach((char) => {
            const display = char === " " ? "\u00A0" : char;

            const mask = document.createElement("span");
            mask.className = "letter-mask";

            const track = document.createElement("span");
            track.className = "letter-track";
            track.innerHTML = `
                <span class="letter">${display}</span>
                <span class="letter">${display}</span>
            `;

            mask.appendChild(track);
            textEl.appendChild(mask);
            tracks.push(track);
        });

        const tl = gsap.timeline({
            paused: true,
            defaults: {
                duration: 0.22,
                ease: "expo.out",
            },
        });

        // yPercent relatif terhadap tinggi track sendiri (200%),
        // jadi -50% = tepat 1x tinggi mask, tanpa perlu ukur pixel sama sekali
        tl.to(tracks, {
            yPercent: -50,
            stagger: 0.01,
        });

        button.addEventListener("mouseenter", () => tl.play());
        button.addEventListener("mouseleave", () => tl.reverse());
    });
}

document.fonts.ready.then(() => {
    initLoginButtonHover();
});

function initNavFade() {

    const items = document.querySelectorAll(".nav-fade-item");

    items.forEach(item => {

        item.addEventListener("mouseenter", () => {

            items.forEach(other => {

                gsap.to(other, {
                    opacity: other === item ? 1 : 0.45,
                    duration: 0.25,
                    ease: "power2.out"
                });

            });

        });

        item.addEventListener("mouseleave", () => {

            gsap.to(items, {
                opacity: 1,
                duration: 0.25,
                ease: "power2.out"
            });

        });

    });

}

function initPixelDivider() {
    const dividers = document.querySelectorAll(".pixel-divider");

    if (!dividers.length) return;

    const cols = 24;
    const rows = 7;
    const accentChance = 0.06; // ~6% pixel jadi warna aksen
    const totalDuration = 3;
    const bandPerRow = totalDuration / rows;
    const wildcardChance = 0.35;

    // Fallback default = warna yang sudah ada sekarang (Hero -> Bento),
    // supaya divider lama yang belum diberi data-attribute tetap identik.
    const DEFAULTS = {
        sectionLight: "#172554",
        sectionDark: "rgb(2, 6, 23)",
        accentLight: "rgb(37, 91, 157)",
        accentDark: "rgb(59, 130, 246)",
        prevLight: "#f8fafc",
        prevDark: "rgb(2, 6, 23)",
    };

    dividers.forEach((divider, index) => {
        // =====================================
        // Ambil konfigurasi warna per-instance
        // dari data-attribute, fallback ke DEFAULTS
        // =====================================
        const config = {
            sectionLight: divider.dataset.sectionLight || DEFAULTS.sectionLight,
            sectionDark: divider.dataset.sectionDark || DEFAULTS.sectionDark,
            accentLight: divider.dataset.accentLight || DEFAULTS.accentLight,
            accentDark: divider.dataset.accentDark || DEFAULTS.accentDark,
            prevLight: divider.dataset.prevLight || DEFAULTS.prevLight,
            prevDark: divider.dataset.prevDark || DEFAULTS.prevDark,
        };

        divider.style.gridTemplateColumns = `repeat(${cols}, minmax(0,1fr))`;
        divider.style.gridTemplateRows = `repeat(${rows}, 1fr)`;

        divider.innerHTML = "";

        const getSectionColor = () => {
            const isDark = document.documentElement.classList.contains("dark");
            return isDark ? config.sectionDark : config.sectionLight;
        };

        const getAccentColor = () => {
            const isDark = document.documentElement.classList.contains("dark");
            return isDark ? config.accentDark : config.accentLight;
        };

        const getPrevColor = () => {
            const isDark = document.documentElement.classList.contains("dark");
            return isDark ? config.prevDark : config.prevLight;
        };

        const pixels = [];

        for (let i = 0; i < cols * rows; i++) {
            const pixel = document.createElement("div");

            const isAccent = Math.random() < accentChance;

            pixel.className = "scale-0 origin-center";
            pixel.dataset.accent = isAccent ? "true" : "false";
            pixel.style.backgroundColor = isAccent ? getAccentColor() : getSectionColor();

            divider.appendChild(pixel);
            pixels.push(pixel);
        }

        // =====================================
        // Warna dasar container: samakan dengan
        // warna akhir gradient section sebelumnya,
        // supaya sebelum animasi mulai tidak ada seam
        // =====================================
        divider.style.backgroundColor = getPrevColor();

        // =====================================
        // Set tinggi divider dinamis supaya
        // tiap kotak benar-benar 1:1 (persegi)
        // =====================================
        const syncSquareHeight = () => {
            const cellWidth = divider.offsetWidth / cols;
            divider.style.height = `${cellWidth * rows}px`;
            ScrollTrigger.refresh();
        };

        syncSquareHeight();
        window.addEventListener("resize", syncSquareHeight);

        // Update warna pixel saat theme di-toggle
        // (status accent/non-accent tetap dipertahankan, tidak di-random ulang)
        const refreshPixelColors = () => {
            const base = getSectionColor();
            const accent = getAccentColor();

            pixels.forEach((pixel) => {
                const isAccent = pixel.dataset.accent === "true";
                pixel.style.backgroundColor = isAccent ? accent : base;
            });

            // container background juga ikut disinkronkan
            divider.style.backgroundColor = getPrevColor();
        };

        const themeObserver = new MutationObserver(refreshPixelColors);
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });

        // =====================================
        // Animasi: blink instan, random tapi
        // condong bertahap dari bawah ke atas,
        // dengan banyak pixel "nyempil" acak,
        // dan bisa pause saat scroll berhenti (scrub)
        // =====================================
        gsap.to(divider.children, {
            scale: 1,
            ease: "steps(1)",

            stagger: (index) => {
                const row = Math.floor(index / cols);
                const rowFromBottom = rows - 1 - row;

                if (Math.random() < wildcardChance) {
                    return Math.random() * totalDuration * 0.8;
                }

                const baseDelay = rowFromBottom * bandPerRow * 0.4;
                const jitter = Math.random() * bandPerRow * 1.8;

                return baseDelay + jitter;
            },

            scrollTrigger: {
                id: `pixel-divider-${index}`,
                trigger: divider,
                start: "top 90%",     // mulai saat 10% bagian atas divider masuk layar
                end: "bottom 10%",    // selesai saat divider hampir sepenuhnya lewat
                scrub: 1,
                invalidateOnRefresh: true,
            },
        });
    });
}
function initHorizontalScroll() {
    const wrapper = document.getElementById("horizontal-wrapper");
    const track = document.getElementById("horizontal-track");

    if (!wrapper || !track) return;

    const cards = [...track.querySelectorAll(".howitworks-card")];

    ScrollTrigger.matchMedia({

        "(min-width: 1024px)": function () {

            // Tambah elemen spacer kosong di akhir track sebagai trailing gap.
            // Pendekatan ini lebih reliable daripada paddingRight karena
            // overflow:hidden / will-change pada track kadang mengabaikan padding.
            let spacer = track.querySelector(".howitworks-trail-spacer");
            if (!spacer) {
                spacer = document.createElement("div");
                spacer.className = "howitworks-trail-spacer";
                spacer.style.cssText = "flex-shrink:0; width:10px; height:1px; pointer-events:none;";
                track.appendChild(spacer);
            }

            const getScrollDistance = () => track.scrollWidth - wrapper.offsetWidth;

            const tween = gsap.to(track, {
                x: () => -getScrollDistance(),
                ease: "none",
            });

            const updateCardScale = () => {
                const wrapperRect = wrapper.getBoundingClientRect();
                const centerX = wrapperRect.left + wrapperRect.width / 2;

                cards.forEach((card) => {
                    const cardRect = card.getBoundingClientRect();
                    const cardCenterX = cardRect.left + cardRect.width / 2;
                    const distance = Math.abs(centerX - cardCenterX);
                    const maxDistance = wrapperRect.width / 2;
                    const proximity = Math.max(0, 1 - distance / maxDistance);

                    gsap.set(card, { scale: 0.92 + proximity * 0.18 });
                });
            };

            ScrollTrigger.create({
                trigger: wrapper,
                start: "top top",
                end: () => `+=${getScrollDistance()}`,
                pin: true,
                animation: tween,
                scrub: 1,
                invalidateOnRefresh: true,
                onUpdate: updateCardScale,
                onRefresh: updateCardScale,
            });

            updateCardScale();
        },

        "(max-width: 1023px)": function () {
            // Hapus spacer jika ada
            const spacer = track.querySelector(".howitworks-trail-spacer");
            if (spacer) spacer.remove();

            gsap.set(track, { x: 0, clearProps: "transform" });
            gsap.set(cards, { scale: 1, clearProps: "transform" });
        },

    });
}

function initFaqAccordion() {
    const items = document.querySelectorAll(".faq-item");

    if (!items.length) return;

    items.forEach((item) => {
        const trigger = item.querySelector(".faq-trigger");

        if (!trigger) return;

        trigger.addEventListener("click", () => {
            const isOpen = item.classList.contains("is-open");

            // Tutup item lain (accordion single-open, sesuai referensi)
            items.forEach((i) => i.classList.remove("is-open"));

            if (!isOpen) {
                item.classList.add("is-open");
            }
        });
    });
}

export function initLandingAnimations() {
    console.log("🚀 initLandingAnimations() dipanggil");

    initThemeToggle();
    initNavIndicator();
    initLoginButtonHover();
    initNavFade();
    initHorizontalScroll();
    initFaqAccordion()
    initPixelDivider();

    ScrollTrigger.refresh();
    
    // ---------- Hero: fade-in & slide-up ----------
    const heroText = document.querySelector('[data-animate="hero-text"]');
    const heroVisual = document.querySelector('[data-animate="hero-visual"]');

    if (heroText) {
        gsap.from(heroText.children, {
            opacity: 0,
            y: 30,
            duration: 0.8,
            ease: "power2.out",
            stagger: 0.12,
        });
    }

    if (heroVisual) {
        gsap.from(heroVisual, {
            opacity: 0,
            y: 40,
            scale: 0.97,
            duration: 1,
            delay: 0.2,
            ease: "power2.out",
        });
    }
}