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
    const navBar = document.querySelector("nav");
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
    // Sections (urut sesuai urutan link di navbar,
    // yang otomatis sama dengan urutan DOM section)
    // =====================================

    const sections = navLinks
        .map((link) => {
            const href = link.getAttribute("href");
            if (!href || !href.startsWith("#") || href.length === 1) return null;

            try {
                const el = document.querySelector(href);
                return el ? { link, el } : null;
            } catch (e) {
                return null;
            }
        })
        .filter(Boolean);

    if (!sections.length) return;

    let activeLink = sections[0].link;

    // =====================================
    // Indicator — pindah dengan efek BLINK
    // (fade out cepat -> snap ke posisi baru -> fade in cepat)
    // =====================================

    const blinkTween = { current: null };

    const moveIndicatorTo = (link, animate = true) => {
        if (!link) return;

        const x = link.offsetLeft;
        const width = link.offsetWidth;

        if (blinkTween.current) {
            blinkTween.current.kill();
            blinkTween.current = null;
        }

        if (animate) {
            blinkTween.current = gsap
                .timeline({ overwrite: "auto" })
                .to(indicator, { opacity: 0, duration: 0.09, ease: "power1.in" })
                .set(indicator, { x, width })
                .to(indicator, { opacity: 1, duration: 0.09, ease: "power1.out" });
        } else {
            // Langsung snap tanpa animasi apapun (dipakai untuk initial state & resize)
            gsap.set(indicator, { x, width, opacity: 1 });
        }
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

    const setActive = (link, animate = true) => {
        if (!link || link === activeLink) return;

        activeLink = link;
        moveIndicatorTo(link, animate);
        setActiveStyles(link);
        navLinks.forEach((l) => l.classList.toggle("is-active", l === link));
    };

    // =====================================
    // Scrollspy — cari section aktif berdasarkan reference line
    // =====================================

    const getReferenceLine = () => {
        const navHeight = navBar?.offsetHeight || 64;
        return navHeight + window.innerHeight * 0.35;
    };

    const updateActiveSection = () => {
        // Paling atas halaman -> selalu Home
        if (window.scrollY < 10) {
            setActive(sections[0].link);
            return;
        }

        // Sudah mentok bawah -> paksa section terakhir aktif.
        const atBottom =
            Math.ceil(window.innerHeight + window.scrollY) >=
            document.documentElement.scrollHeight - 2;

        if (atBottom) {
            setActive(sections[sections.length - 1].link);
            return;
        }

        const refLine = getReferenceLine();
        let current = sections[0].link;

        for (const { link, el } of sections) {
            const top = el.getBoundingClientRect().top;
            if (top <= refLine) {
                current = link;
            } else {
                break;
            }
        }

        setActive(current);
    };

    // Throttle via requestAnimationFrame biar smooth & hemat performa
    let ticking = false;
    window.addEventListener(
        "scroll",
        () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    updateActiveSection();
                    ticking = false;
                });
                ticking = true;
            }
        },
        { passive: true }
    );

    // Resize: posisi/lebar link berubah + tinggi section bisa berubah
    window.addEventListener("resize", () => {
        moveIndicatorTo(activeLink, false);
        updateActiveSection();
    });

    // =====================================
    // Initial state — DUA TAHAP untuk menghindari bug
    // indikator "meluber" saat refresh di section bawah (mis. FAQ):
    //
    // 1) Snap cepat begitu DOM siap, biar tidak ada indikator kosong/loncat.
    // 2) Setelah font web & seluruh halaman benar-benar termuat,
    //    ukur ulang & snap lagi — karena offsetLeft/offsetWidth link
    //    bisa salah dihitung kalau diukur sebelum font asli ter-swap
    //    (masih pakai font fallback yang lebar/pendeknya beda).
    // =====================================

    const syncIndicatorNow = () => {
        updateActiveSection();
        moveIndicatorTo(activeLink, false);
        setActiveStyles(activeLink);
    };

    // Tahap 1: snap secepatnya
    requestAnimationFrame(syncIndicatorNow);

    // Tahap 2: snap ulang setelah font & load event selesai
    const fontsReady = document.fonts ? document.fonts.ready : Promise.resolve();
    const pageLoaded = new Promise((resolve) => {
        if (document.readyState === "complete") {
            resolve();
        } else {
            window.addEventListener("load", resolve, { once: true });
        }
    });

    Promise.all([fontsReady, pageLoaded]).then(() => {
        requestAnimationFrame(syncIndicatorNow);
    });
}

function initLoginButtonHover() {
    // Mencari semua elemen teks login
    const loginTexts = document.querySelectorAll(".login-text");

    if (!loginTexts.length) return;

    loginTexts.forEach((textEl) => {
        // Mendapatkan elemen induk 'a' terdekat dari .login-text
        const button = textEl.closest("a");
        if (!button) return;

        const label = textEl.dataset.text || textEl.textContent.trim();
        if (!label) return;

        // Reset konten HTML agar tidak terjadi duplikasi saat re-init
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
                duration: 0.3,
                ease: "expo.out",
            },
        });

        tl.to(tracks, {
            yPercent: -50,
            stagger: 0.01,
        });

        // Menambahkan listener mouseenter & mouseleave ke tombol induk (a)
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

    let mm = gsap.matchMedia();

    mm.add("(min-width: 1024px)", () => {
        // ==========================================
        // 1. LOGIKA DESKTOP (Jalan saat layar >= 1024px)
        // ==========================================
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

        // ==========================================
        // 2. CLEANUP (Jalan otomatis saat layar < 1024px)
        // ==========================================
        return () => {
            const currentSpacer = track.querySelector(".howitworks-trail-spacer");
            if (currentSpacer) currentSpacer.remove();

            gsap.set(track, { clearProps: "transform" });
            gsap.set(cards, { clearProps: "transform" });
        };
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

// ===========================================================
// Scroll Text Reveal — split per baris (DOM-safe) + play-once
// ===========================================================

function wrapWordsPreservingTags(el) {
    // simpan/kembalikan HTML asli agar aman di-split ulang (resize)
    if (!el.dataset.originalHtml) {
        el.dataset.originalHtml = el.innerHTML;
    } else {
        el.innerHTML = el.dataset.originalHtml;
    }

    const wrapTextNode = (node) => {
        const tokens = node.textContent.split(/(\s+)/); // pertahankan token spasi
        const frag = document.createDocumentFragment();

        tokens.forEach((token) => {
            if (token === "") return;

            if (/^\s+$/.test(token)) {
                frag.appendChild(document.createTextNode(token));
                return;
            }

            const span = document.createElement("span");
            span.className = "reveal-word";
            span.style.display = "inline-block";
            span.textContent = token;
            frag.appendChild(span);
        });

        node.replaceWith(frag);
    };

    // Telusuri child node asli. Elemen non-teks seperti <br> dibiarkan
    // apa adanya (tidak diubah jadi teks) — ini yang memperbaiki bug tag muncul.
    const walk = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) {
                if (child.textContent.trim() !== "") {
                    wrapTextNode(child);
                }
            } else if (child.nodeType === Node.ELEMENT_NODE && child.tagName !== "BR") {
                walk(child);
            }
            // <br> sengaja tidak disentuh: perannya sebagai pemaksa
            // line-break tetap berlaku saat kita ukur offsetTop di bawah
        });
    };

    walk(el);
}

function groupWordsIntoLines(el) {
    const words = Array.from(el.querySelectorAll(".reveal-word"));
    if (!words.length) return [];

    const lines = [];
    let currentTop = null;
    let currentLine = [];

    words.forEach((word) => {
        const top = word.offsetTop;
        if (currentTop === null || Math.abs(top - currentTop) < 2) {
            currentLine.push(word);
            currentTop = top;
        } else {
            lines.push(currentLine);
            currentLine = [word];
            currentTop = top;
        }
    });
    if (currentLine.length) lines.push(currentLine);

    el.innerHTML = "";
    const lineInners = [];

    lines.forEach((lineWords) => {
        const lineWrap = document.createElement("span");
        lineWrap.className = "split-line";
        
        // --- [ PERBAIKAN 1: CSS BUNGKUSAN ] ---
        // Membuat elemen jadi block (mengisi 1 baris penuh) 
        // dan overflow hidden agar teks yang turun ke bawah terpotong/hilang
        lineWrap.style.display = "block";
        lineWrap.style.overflow = "hidden";

        const lineInner = document.createElement("span");
        lineInner.className = "split-line-inner";
        
        // --- [ PERBAIKAN 2: CSS INNER TEKS ] ---
        // Elemen yang digerakkan (di-transform) wajib berupa inline-block atau block
        lineInner.style.display = "inline-block";
        lineInner.style.willChange = "transform, opacity";

        lineWords.forEach((word, i) => {
            lineInner.appendChild(word);
            if (i < lineWords.length - 1) {
                lineInner.appendChild(document.createTextNode(" "));
            }
        });

        lineWrap.appendChild(lineInner);
        el.appendChild(lineWrap);
        lineInners.push(lineInner);
    });

    // el.classList.add("reveal-ready");
    return lineInners;
}

function splitIntoLines(el) {
    wrapWordsPreservingTags(el);
    return groupWordsIntoLines(el);
}

function initScrollTextReveal() {
    const targets = document.querySelectorAll("[data-reveal-text]");
    if (!targets.length) return;

    // Pastikan tersembunyi sejak awal JS dieksekusi
    gsap.set(targets, { autoAlpha: 0 });

    let scrollTriggers = [];

    const build = () => {
        scrollTriggers.forEach((st) => st.kill());
        scrollTriggers = [];

        targets.forEach((el) => {
            const lines = splitIntoLines(el);
            if (!lines.length) return;

            // --- [ PERBAIKAN FOUC / KEDIP ] ---
            // 1. Set posisi awal (menghilang & di bawah) SECARA INSTAN ke tiap baris (lines)
            // SEBELUM wrapper utamanya dibuka. Ini akan dieksekusi dalam satu tick browser.
            gsap.set(lines, { yPercent: 110, opacity: 0 });

            // 2. Setelah isi barisnya aman di bawah, baru munculkan wrapper utamanya.
            gsap.set(el, { autoAlpha: 1 });
            el.classList.add("reveal-ready"); // Tambahkan class di sini jika dibutuhkan CSS

            const tl = gsap.timeline({
                scrollTrigger: {
                    trigger: el,
                    start: "top 57%", // Sedikit dinaikkan dari 50% agar lebih natural
                    toggleActions: "play none none reverse", 
                    invalidateOnRefresh: true,
                },
            });

            // 3. Gunakan .to() saja karena state/posisi awal sudah kita pasang lewat gsap.set()
            tl.to(lines, {
                yPercent: 0,
                opacity: 1,
                duration: 0.65, 
                ease: "power3.out",
                stagger: 0.02, 
            });

            scrollTriggers.push(tl.scrollTrigger);
        });
    };

    const pageFullyLoaded = () =>
        new Promise((resolve) => {
            if (document.readyState === "complete") {
                resolve();
            } else {
                window.addEventListener("load", resolve, { once: true });
            }
        });

    Promise.all([document.fonts.ready, pageFullyLoaded()]).then(() => {
        build();
        ScrollTrigger.refresh();
    });

    let resizeTimer;
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            build();
            ScrollTrigger.refresh();
        }, 200);
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
    initScrollTextReveal(); 

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