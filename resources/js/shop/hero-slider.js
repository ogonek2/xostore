const pendingSliders = new Set();

function setupHeroSlider(slider) {
    const track = slider.querySelector('[data-hero-track]');

    if (! track) {
        return;
    }

    const slides = [...track.children];

    if (slides.length <= 1) {
        return;
    }

    const prev = slider.querySelector('[data-hero-prev]');
    const next = slider.querySelector('[data-hero-next]');
    const dots = [...slider.querySelectorAll('[data-hero-dot]')];
    const total = slides.length;
    let current = 0;
    let isAnimating = false;

    const slideWidth = () => slides[0]?.getBoundingClientRect().width ?? 0;

    const clampIndex = (index) => Math.max(0, Math.min(index, total - 1));

    const syncDots = () => {
        dots.forEach((dot, index) => {
            dot.dataset.active = index === current ? 'true' : 'false';
        });
    };

    const update = (animate = true) => {
        const width = slideWidth();

        if (width <= 0) {
            return;
        }

        track.style.transition = animate ? 'transform 500ms ease-out' : 'none';
        track.style.transform = `translate3d(-${current * width}px, 0, 0)`;
        syncDots();
    };

    const goTo = (index) => {
        const nextIndex = clampIndex(index);

        if (nextIndex === current || isAnimating) {
            return;
        }

        isAnimating = true;
        current = nextIndex;
        update();

        window.setTimeout(() => {
            isAnimating = false;
        }, 500);
    };

    prev?.addEventListener('click', () => {
        goTo(current === 0 ? total - 1 : current - 1);
    });

    next?.addEventListener('click', () => {
        goTo(current === total - 1 ? 0 : current + 1);
    });

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            goTo(Number(dot.dataset.index || 0));
        });
    });

    let touchStartX = 0;
    let touchDeltaX = 0;

    track.addEventListener('touchstart', (event) => {
        if (event.touches.length !== 1) {
            return;
        }

        touchStartX = event.touches[0].clientX;
        touchDeltaX = 0;
        track.style.transition = 'none';
    }, { passive: true });

    track.addEventListener('touchmove', (event) => {
        if (event.touches.length !== 1) {
            return;
        }

        touchDeltaX = event.touches[0].clientX - touchStartX;
        const width = slideWidth();

        if (width <= 0) {
            return;
        }

        track.style.transform = `translate3d(${-(current * width) + touchDeltaX}px, 0, 0)`;
    }, { passive: true });

    track.addEventListener('touchend', () => {
        const width = slideWidth();
        const threshold = Math.min(60, width * 0.18);

        if (touchDeltaX <= -threshold) {
            goTo(current === total - 1 ? 0 : current + 1);
        } else if (touchDeltaX >= threshold) {
            goTo(current === 0 ? total - 1 : current - 1);
        } else {
            update();
        }

        touchDeltaX = 0;
    });

    const resizeObserver = new ResizeObserver(() => {
        update(false);
    });

    resizeObserver.observe(slider);

    update(false);
}

function initHeroSlider(slider) {
    if (slider.dataset.heroInitialized === 'true') {
        return;
    }

    if (slider.getBoundingClientRect().width <= 0) {
        pendingSliders.add(slider);

        return;
    }

    slider.dataset.heroInitialized = 'true';
    pendingSliders.delete(slider);
    setupHeroSlider(slider);
}

function initPendingHeroSliders() {
    [...pendingSliders].forEach(initHeroSlider);
}

export function initHeroSliders() {
    document.querySelectorAll('[data-hero-slider]').forEach(initHeroSlider);
    initPendingHeroSliders();
}

document.addEventListener('DOMContentLoaded', initHeroSliders);
window.addEventListener('resize', initPendingHeroSliders);
