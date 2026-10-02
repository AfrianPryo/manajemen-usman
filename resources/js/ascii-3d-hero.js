/**
 * ascii-3d-hero.js (Fixed & Optimized)
 * -----------------------------------------------------------------------
 * FIXED:
 *   - Bug baseQuat dipanggil sebelum deklarasi (ReferenceError).
 *   - IndexSizeError dari AsciiEffect saat container display:none.
 *   - Render loop tetap jalan di container 0-dimensi.
 * -----------------------------------------------------------------------
 */

import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";
import { AsciiEffect } from "three/examples/jsm/effects/AsciiEffect.js";

// 🔴 TAMBAHKAN DUA BARIS INI:
let resolveModelLoaded;
export const modelLoadedPromise = new Promise((resolve) => {
    resolveModelLoaded = resolve;
});

const originalGetContext = HTMLCanvasElement.prototype.getContext;
HTMLCanvasElement.prototype.getContext = function (type, attributes) {
    if (type === '2d') {
        attributes = Object.assign({}, attributes, { willReadFrequently: true });
    }
    return originalGetContext.call(this, type, attributes);
};

/**
 * Render gambar (logo/foto unggahan admin) sebagai teks ASCII di dalam
 * container, sebagai pengganti model 3D. Dipakai initAsciiHero() saat
 * opsi imageUrl diisi. onFail dipanggil jika gambar gagal dimuat/dibaca
 * supaya pemanggil bisa kembali ke model 3D bawaan.
 */
function renderAsciiImage(container, imageUrl, characters, onFail, tilt) {
    const FONT_SIZE = 6; // px; sel karakter monospace ~0.6 x 1 em
    const CELL_W = FONT_SIZE * 0.6;
    const SS = 4; // supersampling per sel supaya hasil downscale halus

    const pre = document.createElement("pre");
    pre.style.cssText =
        "margin:0;padding:0;width:100%;height:100%;display:flex;align-items:center;" +
        "justify-content:center;overflow:hidden;white-space:pre;font-family:monospace;" +
        `font-size:${FONT_SIZE}px;line-height:${FONT_SIZE}px;letter-spacing:0;` +
        "pointer-events:none;user-select:none;background:transparent;";
    const inner = document.createElement("span");
    pre.appendChild(inner);

    let densities = null; // [rows][cols] -> {a, l} (alpha & luminance 0..1)
    let cols = 0;
    let rows = 0;
    let destroyed = false;
    let themeObserver = null;
    let tiltCleanup = null;

    const paint = () => {
        if (!densities) return;
        const isDark = document.documentElement.classList.contains("dark");
        pre.style.color = isDark ? "#e2e8f0" : "#0f172a";
        const ramp = characters;
        const last = ramp.length - 1;
        let out = "";
        for (let y = 0; y < rows; y++) {
            for (let x = 0; x < cols; x++) {
                const { a, l } = densities[y][x];
                if (a < 0.08) {
                    out += " ";
                    continue;
                }
                // Gelap-di-terang / terang-di-gelap mengikuti tema, dengan
                // batas bawah supaya bagian logo yang warnanya sama dengan
                // latar tetap terlihat.
                const tone = isDark ? l : 1 - l;
                const d = a * Math.max(0.35, tone);
                out += ramp[Math.max(1, Math.min(last, Math.round(d * last)))];
            }
            out += "\n";
        }
        inner.textContent = out;
    };

    const img = new Image();
    img.decoding = "async";
    img.onload = () => {
        if (destroyed) return;
        try {
            const W = container.clientWidth || 180;
            const H = container.clientHeight || 180;
            cols = Math.max(8, Math.floor(W / CELL_W));
            rows = Math.max(8, Math.floor(H / FONT_SIZE));

            // Gambar dimuat 'contain' di dalam kotak container, lalu
            // diskalakan ke grid sel (dengan koreksi rasio sel yang tinggi).
            const iw = img.naturalWidth || 1;
            const ih = img.naturalHeight || 1;
            const s = Math.min(W / iw, H / ih);
            const dwCells = (iw * s) / CELL_W;
            const dhCells = (ih * s) / FONT_SIZE;
            const offX = (cols - dwCells) / 2;
            const offY = (rows - dhCells) / 2;

            const canvas = document.createElement("canvas");
            canvas.width = cols * SS;
            canvas.height = rows * SS;
            const ctx = canvas.getContext("2d", { willReadFrequently: true });
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = "high";
            ctx.drawImage(img, offX * SS, offY * SS, dwCells * SS, dhCells * SS);
            const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

            densities = [];
            for (let y = 0; y < rows; y++) {
                const row = [];
                for (let x = 0; x < cols; x++) {
                    let aSum = 0, lSum = 0;
                    for (let sy = 0; sy < SS; sy++) {
                        for (let sx = 0; sx < SS; sx++) {
                            const i = (((y * SS + sy) * canvas.width) + (x * SS + sx)) * 4;
                            const a = data[i + 3] / 255;
                            aSum += a;
                            // Luminansi hanya dibobotkan oleh piksel yang terlihat.
                            lSum += a * ((0.2126 * data[i] + 0.7152 * data[i + 1] + 0.0722 * data[i + 2]) / 255);
                        }
                    }
                    const n = SS * SS;
                    row.push({ a: aSum / n, l: aSum > 0 ? lSum / aSum : 0 });
                }
                densities.push(row);
            }

            container.appendChild(pre);
            paint();

            themeObserver = new MutationObserver(paint);
            themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["class"],
            });

            // Tilt parallax mengikuti kursor -- meniru perilaku model 3D
            // (sudut maksimal, damping, dan geser parallax yang sama), tapi
            // lewat CSS 3D transform karena gambar ini berupa teks, bukan mesh.
            if (tilt && tilt.enabled) {
                const target = { x: 0, y: 0 };
                const current = { x: 0, y: 0 };
                let mouseX = 0, mouseY = 0;
                let rafId = null;
                const EPSILON = 0.0005;
                // 1 satuan dunia 3D ~ (tinggi container / 3.78) px pada kamera hero.
                const parallaxPx = (tilt.parallax || 0) * ((container.clientHeight || 180) / 3.78);

                const step = () => {
                    rafId = null;
                    if (destroyed) return;
                    current.x += (target.x - current.x) * tilt.damping;
                    current.y += (target.y - current.y) * tilt.damping;
                    const settled =
                        Math.abs(target.x - current.x) < EPSILON &&
                        Math.abs(target.y - current.y) < EPSILON;
                    if (settled) {
                        current.x = target.x;
                        current.y = target.y;
                    }
                    pre.style.transform =
                        `perspective(700px) translate(${(current.x * parallaxPx).toFixed(2)}px, ${(-current.y * parallaxPx).toFixed(2)}px) ` +
                        `rotateX(${(current.y * tilt.maxAngle).toFixed(4)}rad) rotateY(${(current.x * tilt.maxAngle).toFixed(4)}rad)`;
                    if (!settled) rafId = requestAnimationFrame(step);
                };
                const kick = () => {
                    if (rafId === null) rafId = requestAnimationFrame(step);
                };

                const onMove = (e) => {
                    mouseX = e.clientX;
                    mouseY = e.clientY;
                    let nx, ny;
                    if (tilt.cursorSource === "container") {
                        const rect = container.getBoundingClientRect();
                        nx = ((mouseX - rect.left) / rect.width) * 2 - 1;
                        ny = -((mouseY - rect.top) / rect.height) * 2 + 1;
                    } else {
                        nx = (mouseX / window.innerWidth) * 2 - 1;
                        ny = -(mouseY / window.innerHeight) * 2 + 1;
                    }
                    target.x = Math.max(-1, Math.min(1, nx));
                    target.y = Math.max(-1, Math.min(1, ny));
                    kick();
                };
                const onLeave = () => {
                    target.x = 0;
                    target.y = 0;
                    kick();
                };

                pre.style.willChange = "transform";
                const src = tilt.cursorSource === "container" ? container : window;
                src.addEventListener("mousemove", onMove);
                if (tilt.cursorSource === "container") container.addEventListener("mouseleave", onLeave);

                tiltCleanup = () => {
                    src.removeEventListener("mousemove", onMove);
                    container.removeEventListener("mouseleave", onLeave);
                    if (rafId !== null) cancelAnimationFrame(rafId);
                    rafId = null;
                };
            }

            resolveModelLoaded?.();
        } catch (err) {
            console.warn("[ascii-3d-hero] gagal memproses gambar, kembali ke model 3D:", err);
            if (!destroyed) onFail();
        }
    };
    img.onerror = () => {
        console.warn("[ascii-3d-hero] gagal memuat gambar, kembali ke model 3D");
        if (!destroyed) onFail();
    };
    img.src = imageUrl;

    return {
        destroy: () => {
            destroyed = true;
            themeObserver?.disconnect();
            tiltCleanup?.();
            pre.remove();
        },
    };
}

export function initAsciiHero({
    containerSelector,
    modelUrl,
    characters = " -+01@",
    resolution = 0.3,
    modelScale = 1.2,
    autoRotate = true,
    rotateSpeed = 0.25,
    tiltCursor = true,
    cursorSource = "window",
    tiltDamping = 0.06,
    tiltMaxAngle = 0.5,
    parallaxAmount = 0.1,
    frontOffsetX = Math.PI / 2,
    frontOffsetY = 0,
    frontOffsetZ = 0,
    imageUrl = null,
} = {}) {
    // Simpan opsi asli untuk fallback (lihat blok imageUrl di bawah).
    const originalOptions = arguments[0] || {};
    const container = document.querySelector(containerSelector);
    if (!container) {
        console.warn(`[ascii-3d-hero] container "${containerSelector}" tidak ditemukan`);
        resolveModelLoaded?.(); // <-- Tambahkan baris ini
        return { destroy: () => {} };
    }

    if (container.clientWidth === 0 || container.clientHeight === 0) {
        console.info("[ascii-3d-hero] container hidden (mobile), skip inisialisasi");
        resolveModelLoaded?.(); // <-- Tambahkan baris ini
        return { destroy: () => {} };
    }

    // Logo/foto custom dari admin: tampil sebagai ASCII menggantikan model 3D.
    // Jika gambar gagal dimuat, otomatis kembali ke model 3D bawaan.
    if (imageUrl) {
        let fallback = null;
        let destroyedEarly = false;
        const imageHandle = renderAsciiImage(container, imageUrl, characters, () => {
            if (destroyedEarly || fallback) return;
            fallback = initAsciiHero({ ...originalOptions, imageUrl: null });
        }, {
            enabled: tiltCursor && !window.matchMedia("(prefers-reduced-motion: reduce)").matches,
            cursorSource,
            damping: tiltDamping,
            maxAngle: tiltMaxAngle,
            parallax: parallaxAmount,
        });
        return {
            destroy: () => {
                destroyedEarly = true;
                imageHandle.destroy();
                fallback?.destroy();
            },
        };
    }

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (prefersReducedMotion) {
        autoRotate = false;
        tiltCursor = false;
    }
    if (tiltCursor) autoRotate = false;

    // =====================================================
    // Scene / Camera / Renderer
    // =====================================================
    const scene = new THREE.Scene();

    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(0, 0, 6);

    const ambient = new THREE.AmbientLight(0xffffff, 2); // Dari 0.6 ke 1.0

    // Turunkan sedikit kontras key light
    const key = new THREE.DirectionalLight(0xffffff, 1.0); // Dari 1.4 ke 1.0
    key.position.set(3, 4, 5);

    const fill = new THREE.DirectionalLight(0xffffff, 0.6);
    fill.position.set(-4, -2, -3);

    scene.add(ambient, key, fill);

    const renderer = new THREE.WebGLRenderer({ 
        alpha: true, 
        antialias: false,
        powerPreference: "high-performance" 
    });
    renderer.setPixelRatio(1);
    renderer.setClearColor(0x000000, 1);

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
    // Load Model
    // =====================================================
    let model = null;
    const pivot = new THREE.Group();
    scene.add(pivot);

    // 🔴 FIX #1: Deklarasikan baseQuat DI SINI (sebelum dipakai)
    const baseQuat = new THREE.Quaternion();
    const basePosition = new THREE.Vector3();

    const loader = new GLTFLoader();
    let forceRender = true;

    loader.load(
        modelUrl,
        (gltf) => {
            model = gltf.scene;
            const box = new THREE.Box3().setFromObject(model);
            const size = new THREE.Vector3();
            const center = new THREE.Vector3();
            box.getSize(size);
            box.getCenter(center);

            model.position.sub(center);

            const maxDim = Math.max(size.x, size.y, size.z) || 1;
            const fitScale = (2.4 / maxDim) * modelScale;

            pivot.scale.setScalar(fitScale);
            if (frontOffsetX !== 0 || frontOffsetY !== 0 || frontOffsetZ !== 0) {
                pivot.rotation.set(frontOffsetX, frontOffsetY, frontOffsetZ, "XYZ");
            }
            
            // Sekarang aman karena baseQuat sudah dideklarasikan di atas
            baseQuat.copy(pivot.quaternion);

            pivot.add(model);
            forceRender = true;

            resolveModelLoaded?.();
        },
        undefined,
        (err) => {
            console.error("[ascii-3d-hero] gagal load model:", err);
            resolveModelLoaded?.(); // <-- Tambahkan ini di callback error
        }
    );
    // =====================================================
    // Tilt Parallax
    // =====================================================
    const mouseNdc = new THREE.Vector2(0, 0);
    const tiltQuat = new THREE.Quaternion();
    const targetQuat = new THREE.Quaternion();
    const euler = new THREE.Euler();
    const targetPos = new THREE.Vector3();

    let mouseX = 0, mouseY = 0;
    let hasNewMousePos = false;

    const onMouseMove = (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        hasNewMousePos = true;
    };

    const onMouseLeave = () => {
        mouseNdc.set(0, 0);
        hasNewMousePos = false;
        forceRender = true;
    };

    if (tiltCursor) {
        const targetElement = cursorSource === "container" ? container : window;
        targetElement.addEventListener("mousemove", onMouseMove);
        if (cursorSource === "container") {
            container.addEventListener("mouseleave", onMouseLeave);
        }
    }

    const applyTiltParallax = () => {
        if (!model) return false;

        const nx = THREE.MathUtils.clamp(mouseNdc.x, -1, 1);
        const ny = THREE.MathUtils.clamp(mouseNdc.y, -1, 1);

        euler.set(-ny * tiltMaxAngle, nx * tiltMaxAngle, 0, "XYZ");
        tiltQuat.setFromEuler(euler);
        targetQuat.copy(tiltQuat).multiply(baseQuat);

        let isMoving = false;
        const EPSILON = 0.0005;

        const angleDiff = pivot.quaternion.angleTo(targetQuat);
        if (angleDiff > EPSILON) {
            pivot.quaternion.slerp(targetQuat, tiltDamping);
            isMoving = true;
        } else {
            pivot.quaternion.copy(targetQuat);
        }

        if (parallaxAmount > 0) {
            targetPos.set(
                basePosition.x + nx * parallaxAmount,
                basePosition.y + ny * parallaxAmount,
                basePosition.z
            );
            const posDiff = pivot.position.distanceTo(targetPos);
            if (posDiff > EPSILON) {
                pivot.position.lerp(targetPos, tiltDamping);
                isMoving = true;
            } else {
                pivot.position.copy(targetPos);
            }
        }

        return isMoving;
    };

    // =====================================================
    // Resize (dengan proteksi width/height 0)
    // =====================================================
    const resize = () => {
        const w = container.clientWidth;
        const h = container.clientHeight;
        
        // 🔴 FIX #2: Skip jika container disembunyikan
        if (w === 0 || h === 0) return;
        
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        effect.setSize(w, h);
        forceRender = true;
    };

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);
    resize();

    // =====================================================
    // Render Loop (dengan proteksi)
    // =====================================================
    let isVisible = false;
    let rafId = null;
    const clock = new THREE.Clock();

    const renderFrame = () => {
        rafId = requestAnimationFrame(renderFrame);
        const dt = clock.getDelta();
        let needsRender = forceRender;
        forceRender = false;

        // 🔴 FIX #3: Skip render jika container tidak terlihat (display:none)
        if (container.clientWidth === 0 || container.clientHeight === 0) {
            return;
        }

        if (model && autoRotate) {
            pivot.rotation.y += rotateSpeed * dt;
            needsRender = true;
        }

        if (hasNewMousePos) {
            if (cursorSource === "container") {
                const rect = container.getBoundingClientRect();
                mouseNdc.x = ((mouseX - rect.left) / rect.width) * 2 - 1;
                mouseNdc.y = -((mouseY - rect.top) / rect.height) * 2 + 1;
            } else {
                mouseNdc.x = (mouseX / window.innerWidth) * 2 - 1;
                mouseNdc.y = -(mouseY / window.innerHeight) * 2 + 1;
            }
            hasNewMousePos = false;
            needsRender = true;
        }

        if (model && tiltCursor) {
            const moving = applyTiltParallax();
            if (moving) needsRender = true;
        }

        if (needsRender) {
            // Double-check sebelum render (mencegah IndexSizeError)
            if (container.clientWidth > 0 && container.clientHeight > 0) {
                effect.render(scene, camera);
            }
        }
    };

    const startLoop = () => {
        if (rafId === null) {
            clock.getDelta(); 
            forceRender = true;
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
    // Cleanup
    // =====================================================
    const destroy = () => {
        stopLoop();
        resizeObserver.disconnect();
        intersectionObserver.disconnect();
        themeObserver.disconnect();
        document.removeEventListener("visibilitychange", onVisibilityChange);
        
        const targetElement = cursorSource === "container" ? container : window;
        targetElement.removeEventListener("mousemove", onMouseMove);
        if (cursorSource === "container") {
            container.removeEventListener("mouseleave", onMouseLeave);
        }

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