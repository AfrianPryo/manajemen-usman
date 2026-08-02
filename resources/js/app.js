import { initLandingAnimations } from './landing-animations.js';
import { initSmoothScroll } from "./smooth-scroll";
import { initAsciiHero } from "./ascii-3d-hero.js";

document.addEventListener('DOMContentLoaded', () => {
    initSmoothScroll();
    initLandingAnimations();
    initAsciiHero({
        containerSelector: "#ascii-3d-container",
        modelUrl: "/models/hero.glb",
    });
});