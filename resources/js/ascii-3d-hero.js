/**
 * ascii-3d-hero.js
 * -----------------------------------------------------------------------
 * Merender model .glb/.gltf sebagai ASCII art 3D (mirip contoh referensi),
 * dioptimasi supaya ringan:
 *   - resolusi internal render sengaja kecil (efek ASCII tidak butuh detail
 *     tinggi), jadi GPU load-nya jauh lebih murah daripada render normal.
 *   - devicePixelRatio di-cap ke 1, retina tidak perlu buat efek karakter.
 *   - render loop otomatis PAUSE saat elemen keluar viewport
 *     (IntersectionObserver) dan saat tab tidak aktif.
 *   - menghormati prefers-reduced-motion (auto-rotate dimatikan).
 *   - resize di-debounce lewat ResizeObserver.
 *   - semua geometry/material/texture di-dispose lewat fungsi destroy().
 *   - rotasi & skala terjadi di sekitar TITIK TENGAH objek yang sebenarnya
 *     (bukan pivot asli dari Blender), lewat pola "pivot group" — lihat
 *     komentar di bagian load model.
 *
 * Install dulu:
 *   npm install three
 *
 * (AsciiEffect & GLTFLoader ikut terbundle di paket "three" pada folder
 *  three/examples/jsm, jadi tidak perlu install tambahan.)
 * -----------------------------------------------------------------------
 */

import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";
import { AsciiEffect } from "three/examples/jsm/effects/AsciiEffect.js";

/**
 * @param {Object} opts
 * @param {string} opts.containerSelector - selector elemen pembungkus (harus punya width/height, mis. lewat CSS)
 * @param {string} opts.modelUrl          - path ke file .glb / .gltf
 * @param {string} [opts.characters]      - urutan karakter dari gelap->terang, default mirip referensi
 * @param {number} [opts.resolution]      - 0–1, makin kecil makin ringan & makin kasar (default 0.16)
 * @param {number} [opts.modelScale]      - skala tambahan model setelah auto-fit (default 1)
 * @param {boolean} [opts.autoRotate]     - putar otomatis pelan (default true; dimatikan otomatis kalau tiltCursor aktif atau reduced-motion)
 * @param {number} [opts.rotateSpeed]     - radian/detik (default 0.25)
 * @param {boolean} [opts.tiltCursor]     - efek 3D tilt parallax mengikuti posisi kursor (default true, off jika reduced-motion)
 * @param {"window"|"container"} [opts.cursorSource] - area yang dipantau untuk posisi kursor (default "window")
 * @param {number} [opts.tiltDamping]     - 0–1, makin besar makin cepat/snappy mengikuti kursor (default 0.06)
 * @param {number} [opts.tiltMaxAngle]    - sudut miring maksimum dalam radian, dipakai untuk sumbu X & Y (default 0.28 ≈ 16°)
 * @param {number} [opts.parallaxAmount]  - seberapa jauh model ikut geser (unit dunia three.js) mengikuti kursor, buat kesan kedalaman (default 0.15, isi 0 untuk matikan)
 * @param {number} [opts.frontOffsetX]    - koreksi rotasi X (radian) kalau "depan" model dari Blender miring/kebalik di sumbu X (default 0)
 * @param {number} [opts.frontOffsetY]    - koreksi rotasi Y (radian) kalau "depan" model dari Blender tidak menghadap +Z (default 0)
 * @param {number} [opts.frontOffsetZ]    - koreksi rotasi Z (radian) kalau "depan" model dari Blender terguling di sumbu Z (default 0)
 * @returns {{ destroy: () => void }}
 */
export function initAsciiHero({
    containerSelector,
    modelUrl,
    characters = " 01e+*#%@",
    resolution = 0.2,
    modelScale = 1,
    autoRotate = true,
    rotateSpeed = 0.25,
    tiltCursor = true,
    cursorSource = "window",
    tiltDamping = 0.06,
    tiltMaxAngle = 0.5,
    parallaxAmount = 0.1,
    frontOffsetX = 0,
    frontOffsetY = -Math.PI / 2,
    frontOffsetZ = 0,
} = {}) {
    const container = document.querySelector(containerSelector);
    if (!container) {
        console.warn(`[ascii-3d-hero] container "${containerSelector}" tidak ditemukan`);
        return { destroy: () => {} };
    }

    const prefersReducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;
    if (prefersReducedMotion) {
        autoRotate = false;
        tiltCursor = false;
    }
    // autoRotate & tiltCursor sama-sama memutar model, jadi tidak dipakai berbarengan
    if (tiltCursor) autoRotate = false;

    // =====================================================
    // Scene / Camera / Renderer
    // =====================================================
    const scene = new THREE.Scene();

    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(0, 0, 6);

    const ambient = new THREE.AmbientLight(0xffffff, 0.6);
    const key = new THREE.DirectionalLight(0xffffff, 1.4);
    key.position.set(3, 4, 5);
    const fill = new THREE.DirectionalLight(0xffffff, 0.5);
    fill.position.set(-4, -2, -3);
    scene.add(ambient, key, fill);

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: false });
    renderer.setPixelRatio(1); // ASCII tidak butuh retina, hemat GPU
    // alpha di sini 1 (opaque), bukan 0 — supaya background selalu terbaca
    // "gelap total" oleh AsciiEffect (luminance 0 -> spasi kosong). Transparansi
    // visual terhadap halaman tetap didapat lewat CSS background:transparent
    // pada effect.domElement di bawah, bukan dari alpha channel renderer.
    renderer.setClearColor(0x000000, 1);

    // AsciiEffect membungkus renderer & menulis karakter ke DOM (bukan canvas biasa)
    //
    // PENTING soal kombinasi invert + alpha di sini:
    // - alpha:false -> AsciiEffect memilih karakter berdasarkan LUMINANCE
    //   (terang/gelap hasil pencahayaan), bukan channel transparansi.
    // - Default (tanpa invert) piksel GELAP -> karakter PADAT, piksel TERANG -> spasi.
    //   Itu kebalik dari yang kita mau (background gelap harusnya kosong).
    // - invert:true membalik pemetaan itu: piksel TERANG (permukaan model yang
    //   kena cahaya) -> karakter padat, piksel GELAP (background) -> spasi kosong.
    // Kombinasi invert:true + alpha:false ini persis yang dipakai contoh resmi
    // AsciiEffect dari three.js untuk background gelap + objek bercahaya.
    const effect = new AsciiEffect(renderer, characters, {
        invert: true,
        resolution,
        scale: 1,
        color: false,
        alpha: false,
    });
    effect.domElement.style.color = "currentColor";
    effect.domElement.style.backgroundColor = "transparent";
    effect.domElement.style.fontFamily = "monospace";
    effect.domElement.style.lineHeight = "1";
    effect.domElement.style.letterSpacing = "-1px";
    effect.domElement.style.width = "100%";
    effect.domElement.style.height = "100%";
    effect.domElement.style.pointerEvents = "none";
    effect.domElement.style.userSelect = "none";

    container.appendChild(effect.domElement);

    // =====================================================
    // Warna ikut tema (dark / light) — ambil pola yang sama
    // seperti toggle tema di project ini (class "dark" di <html>)
    // =====================================================
    const applyThemeColor = () => {
        const isDark = document.documentElement.classList.contains("dark");
        effect.domElement.style.color = isDark ? "#e2e8f0" : "#0f172a";
    };
    applyThemeColor();

    const themeObserver = new MutationObserver(applyThemeColor);
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });

    // =====================================================
    // Load model .glb/.gltf, auto-center & auto-fit
    // -----------------------------------------------------
    // POLA "PIVOT GROUP" — supaya rotasi & skala selalu berputar tepat di
    // TENGAH objek, bukan di pivot asli dari file Blender:
    //
    //   pivot (THREE.Group)   <- SEMUA transform dinamis diterapkan di sini:
    //     └─ model (gltf.scene)   rotasi (frontOffset*, autoRotate, tiltCursor),
    //                              skala (modelScale), posisi (basePosition)
    //
    // "model" sendiri, SETELAH dipindah masuk ke pivot, cuma digeser SATU KALI
    // secara permanen (model.position.sub(center)) supaya titik tengah bounding
    // box-nya pas jatuh di local origin (0,0,0) milik pivot. Model TIDAK PERNAH
    // dirotasi/discale langsung lagi setelah ini.
    //
    // Karena local origin pivot sekarang selalu = titik tengah visual objek,
    // rotasi apa pun yang diterapkan ke pivot.rotation / pivot.quaternion akan
    // otomatis berputar di tengah objek, tidak peduli seberapa besar sudutnya.
    // (Sebelumnya rotasi diterapkan ke model.rotation langsung — pivot rotasinya
    // ikut pivot asli dari Blender, itu sebabnya terasa muter dari pojok.)
    // =====================================================
    let model = null;
    const pivot = new THREE.Group();
    scene.add(pivot);

    let basePosition = new THREE.Vector3(); // posisi dasar PIVOT (bukan model)
    const loader = new GLTFLoader();

    loader.load(
        modelUrl,
        (gltf) => {
            model = gltf.scene;

            // Bounding box dihitung sebelum model dipindah ke pivot / diskala,
            // jadi "center" & "size" di sini masih dalam skala asli model.
            const box = new THREE.Box3().setFromObject(model);
            const size = new THREE.Vector3();
            const center = new THREE.Vector3();
            box.getSize(size);
            box.getCenter(center);

            // Geser SATU KALI: titik tengah bounding box model dipindah supaya
            // pas jatuh di local origin pivot (0,0,0). Ini murni "recentering",
            // bukan transform dinamis — makanya dilakukan pada model, bukan pivot.
            model.position.sub(center);

            const maxDim = Math.max(size.x, size.y, size.z) || 1;
            const fitScale = (2.4 / maxDim) * modelScale;

            // Skala & rotasi koreksi-depan (frontOffset*) diterapkan ke PIVOT,
            // bukan ke model — supaya berputar di tengah, bukan di pivot Blender.
            pivot.scale.setScalar(fitScale);
            if (frontOffsetX !== 0 || frontOffsetY !== 0 || frontOffsetZ !== 0) {
                pivot.rotation.set(frontOffsetX, frontOffsetY, frontOffsetZ, "XYZ");
            }
            baseQuat.copy(pivot.quaternion);

            pivot.add(model);
        },
        undefined,
        (err) => {
            console.error("[ascii-3d-hero] gagal load model:", err);
        }
    );

    // =====================================================
    // Tilt parallax: pivot miring (rotasi X/Y) mengikuti posisi
    // kursor relatif ke tengah container, plus sedikit pergeseran
    // posisi (parallax) untuk kesan kedalaman 3D. Bukan lookAt —
    // ini efek "tilt card" yang lebih halus, dihaluskan dengan lerp
    // supaya tidak nyentak.
    // =====================================================
    const mouseNdc = new THREE.Vector2(0, 0); // (0,0) = tengah, netral
    const baseQuat = new THREE.Quaternion();  // orientasi awal pivot (sudah termasuk frontOffsetX/Y/Z)
    const tiltQuat = new THREE.Quaternion();
    const targetQuat = new THREE.Quaternion();
    const euler = new THREE.Euler();
    const targetPos = new THREE.Vector3();

    const updateMouseNdc = (clientX, clientY, rect) => {
        mouseNdc.x = ((clientX - rect.left) / rect.width) * 2 - 1;
        mouseNdc.y = -((clientY - rect.top) / rect.height) * 2 + 1;
    };

    const onWindowMouseMove = (e) => {
        const rect = container.getBoundingClientRect();
        updateMouseNdc(e.clientX, e.clientY, rect);
    };

    const onContainerMouseMove = (e) => {
        const rect = container.getBoundingClientRect();
        updateMouseNdc(e.clientX, e.clientY, rect);
    };

    const onMouseLeave = () => {
        // kursor keluar area -> balik netral (0,0) biar model kembali ke posisi awal
        mouseNdc.set(0, 0);
    };

    if (tiltCursor) {
        if (cursorSource === "container") {
            container.addEventListener("mousemove", onContainerMouseMove);
            container.addEventListener("mouseleave", onMouseLeave);
        } else {
            window.addEventListener("mousemove", onWindowMouseMove);
        }
    }

    const applyTiltParallax = () => {
        if (!model) return;

        // clamp jaga-jaga kalau mouseNdc sedikit meleset dari [-1, 1]
        const nx = THREE.MathUtils.clamp(mouseNdc.x, -1, 1);
        const ny = THREE.MathUtils.clamp(mouseNdc.y, -1, 1);

        // Rotasi: gerak mouse horizontal -> tilt sumbu Y, vertikal -> tilt sumbu X
        euler.set(-ny * tiltMaxAngle, nx * tiltMaxAngle, 0, "XYZ");
        tiltQuat.setFromEuler(euler);
        // PENTING: urutan perkalian ini sengaja tiltQuat * baseQuat (bukan
        // sebaliknya). Kalau dibalik (baseQuat * tiltQuat), rotasi tilt
        // dihitung dulu di ruang "asli" objek sebelum frontOffset, lalu
        // frontOffset ikut membelokkan arah sumbu tilt itu sendiri — hasilnya
        // tilt sumbu Y bisa terasa "berputar" alih-alih "menghadap" begitu
        // frontOffsetX/Y/Z dipakai untuk membetulkan orientasi model.
        // Dengan tiltQuat * baseQuat, frontOffset diterapkan LEBIH DULU untuk
        // membetulkan orientasi dasar, baru tilt diterapkan DI ATASNYA dalam
        // ruang dunia/kamera — jadi arah tilt selalu konsisten relatif ke
        // layar (atas/bawah/kiri/kanan), tidak peduli berapa pun frontOffset-nya.
        targetQuat.copy(tiltQuat).multiply(baseQuat);
        pivot.quaternion.slerp(targetQuat, tiltDamping);

        // Parallax posisi: sedikit geser mengikuti kursor untuk kesan kedalaman
        if (parallaxAmount > 0) {
            targetPos.set(
                basePosition.x + nx * parallaxAmount,
                basePosition.y + ny * parallaxAmount,
                basePosition.z
            );
            pivot.position.lerp(targetPos, tiltDamping);
        }
    };

    // =====================================================
    // Resize (debounced lewat ResizeObserver)
    // =====================================================
    const resize = () => {
        const w = container.clientWidth || 1;
        const h = container.clientHeight || 1;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        // renderer di dalam effect otomatis mengikuti setSize di bawah ini
        effect.setSize(w, h);
    };

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);
    resize();

    // =====================================================
    // Render loop — hanya jalan kalau elemen kelihatan di layar
    // dan tab sedang aktif. Ini bagian utama yang bikin ringan.
    // =====================================================
    let isVisible = false;
    let rafId = null;
    const clock = new THREE.Clock();

    const renderFrame = () => {
        rafId = requestAnimationFrame(renderFrame);

        const dt = clock.getDelta();

        if (model && autoRotate) {
            pivot.rotation.y += rotateSpeed * dt;
        }

        if (model && tiltCursor) {
            applyTiltParallax();
        }

        effect.render(scene, camera);
    };

    const startLoop = () => {
        if (rafId === null) {
            clock.getDelta(); // reset delta biar tidak lompat setelah pause
            renderFrame();
        }
    };

    const stopLoop = () => {
        if (rafId !== null) {
            cancelAnimationFrame(rafId);
            rafId = null;
        }
    };

    const intersectionObserver = new IntersectionObserver(
        (entries) => {
            isVisible = entries[0]?.isIntersecting ?? false;
            if (isVisible && document.visibilityState === "visible") {
                startLoop();
            } else {
                stopLoop();
            }
        },
        { threshold: 0.05 }
    );
    intersectionObserver.observe(container);

    const onVisibilityChange = () => {
        if (document.visibilityState === "visible" && isVisible) {
            startLoop();
        } else {
            stopLoop();
        }
    };
    document.addEventListener("visibilitychange", onVisibilityChange);

    // =====================================================
    // Cleanup — panggil ini kalau elemen di-unmount / SPA route change
    // =====================================================
    const destroy = () => {
        stopLoop();
        resizeObserver.disconnect();
        intersectionObserver.disconnect();
        themeObserver.disconnect();
        document.removeEventListener("visibilitychange", onVisibilityChange);
        window.removeEventListener("mousemove", onWindowMouseMove);
        container.removeEventListener("mousemove", onContainerMouseMove);
        container.removeEventListener("mouseleave", onMouseLeave);

        scene.traverse((obj) => {
            if (obj.geometry) obj.geometry.dispose();
            if (obj.material) {
                const materials = Array.isArray(obj.material) ? obj.material : [obj.material];
                materials.forEach((m) => {
                    Object.values(m).forEach((v) => {
                        if (v && v.isTexture) v.dispose();
                    });
                    m.dispose();
                });
            }
        });

        renderer.dispose();
        effect.domElement.remove();
    };

    return { destroy };
}