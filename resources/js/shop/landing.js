import Swiper from 'swiper';
import { FreeMode, Mousewheel } from 'swiper/modules';
import 'swiper/css';

function initLandingGalleries() {
    document.querySelectorAll('[data-landing-gallery]').forEach((section) => {
        const lightbox = section.querySelector('[data-landing-lightbox]');
        const image = section.querySelector('[data-landing-lightbox-image]');
        const closeBtn = section.querySelector('[data-landing-lightbox-close]');

        if (! lightbox || ! image) {
            return;
        }

        const open = (src) => {
            image.src = src;
            lightbox.hidden = false;
            document.body.style.overflow = 'hidden';
        };

        const close = () => {
            lightbox.hidden = true;
            image.src = '';
            document.body.style.overflow = '';
        };

        section.querySelectorAll('[data-landing-gallery-item]').forEach((button) => {
            button.addEventListener('click', () => {
                const src = button.dataset.full;

                if (src) {
                    open(src);
                }
            });
        });

        closeBtn?.addEventListener('click', close);
        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && ! lightbox.hidden) {
                close();
            }
        });
    });
}

function initLandingProductSwipers() {
    document.querySelectorAll('.landing-products-swiper').forEach((element) => {
        new Swiper(element, {
            modules: [FreeMode, Mousewheel],
            slidesPerView: 'auto',
            spaceBetween: 20,
            freeMode: {
                enabled: true,
                momentum: true,
                momentumRatio: 0.35,
            },
            mousewheel: {
                forceToAxis: true,
            },
            observer: true,
            observeParents: true,
            breakpoints: {
                640: { spaceBetween: 24 },
                1024: { spaceBetween: 28 },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLandingGalleries();
    initLandingProductSwipers();
});
