/**
 * ascii-3d-hero.js (Optimized)
 * -----------------------------------------------------------------------
 * Merender model .glb/.gltf sebagai ASCII art 3D.
 * Optimasi tambahan:
 *   - Mencegah Layout Thrashing (kalkulasi posisi kursor di-throttle ke frame).
 *   - Idle Frame Skipping (tidak merender ulang AsciiEffect jika model 
 *     sedang diam/tidak berputar, sangat menghemat CPU/Baterai).
 *   - High-performance context untuk WebGL.
 * -----------------------------------------------------------------------
 */

import * as THREE from "three";
import { GLTFLoader } from "three/examples/jsm/loaders/GLTFLoader.js";
import { AsciiEffect } from "three/examples/jsm/effects/AsciiEffect.js";

export function initAsciiHero({
    containerSelector,
    modelUrl,
    characters = " -+01@",
    resolution = 0.3,
    modelScale = 0.8,
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

    const ambient = new THREE.AmbientLight(0xffffff, 0.6);
    const key = new THREE.DirectionalLight(0xffffff, 1.4);
    key.position.set(3, 4, 5);
    const fill = new THREE.DirectionalLight(0xffffff, 0.5);
    fill.position.set(-4, -2, -3);
    scene.add(ambient, key, fill);

    // Optimasi: Memberitahu browser untuk memprioritaskan performa
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

    let basePosition = new THREE.Vector3();
    const loader = new GLTFLoader();
    let forceRender = true; // Trigger render saat model baru dimuat

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
            baseQuat.copy(pivot.quaternion);

            pivot.add(model);
            forceRender = true; // Paksa render ulang agar model tampil
        },
        undefined,
        (err) => console.error("[ascii-3d-hero] gagal load model:", err)
    );

    // =====================================================
    // Tilt Parallax (Optimized)
    // =====================================================
    const mouseNdc = new THREE.Vector2(0, 0);
    const baseQuat = new THREE.Quaternion();
    const tiltQuat = new THREE.Quaternion();
    const targetQuat = new THREE.Quaternion();
    const euler = new THREE.Euler();
    const targetPos = new THREE.Vector3();

    // Optimasi: Simpan posisi kursor sementara. 
    // Jangan lakukan getBoundingClientRect() di sini (mencegah Layout Thrashing).
    let mouseX = 0, mouseY = 0;
    let hasNewMousePos = false;

    const onMouseMove = (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        hasNewMousePos = true;
    };

    const onMouseLeave = () => {
        mouseNdc.set(0, 0);
        hasNewMousePos = false; // Batalkan perhitungan posisi baru
        forceRender = true;     // Pastikan render dipanggil agar kembali ke titik (0,0)
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
        const EPSILON = 0.0005; // Ambang batas berhenti

        // Kalkulasi jarak putaran saat ini dengan target
        const angleDiff = pivot.quaternion.angleTo(targetQuat);
        if (angleDiff > EPSILON) {
            pivot.quaternion.slerp(targetQuat, tiltDamping);
            isMoving = true;
        } else {
            pivot.quaternion.copy(targetQuat); // Snap jika sudah sangat dekat
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

        return isMoving; // True jika masih proses interpolasi bergerak
    };

    // =====================================================
    // Resize
    // =====================================================
    const resize = () => {
        const w = container.clientWidth || 1;
        const h = container.clientHeight || 1;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        effect.setSize(w, h);
        forceRender = true;
    };

    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);
    resize();

    // =====================================================
    // Render Loop
    // =====================================================
    let isVisible = false;
    let rafId = null;
    const clock = new THREE.Clock();

    const renderFrame = () => {
        rafId = requestAnimationFrame(renderFrame);
        const dt = clock.getDelta();
        let needsRender = forceRender;
        forceRender = false; // Reset setelah dipicu

        if (model && autoRotate) {
            pivot.rotation.y += rotateSpeed * dt;
            needsRender = true; // Selalu butuh render kalau berputar otomatis
        }

        // Kalkulasi posisi mouse yang sesungguhnya di dalam frame (efisien)
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

        // Optimasi Terbesar: Hanya eksekusi render jika ada yang berubah
        if (needsRender) {
            effect.render(scene, camera);
        }
    };

    const startLoop = () => {
        if (rafId === null) {
            clock.getDelta(); 
            forceRender = true; // Paksa render saat baru terlihat di layar
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