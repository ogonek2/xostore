import Swiper from 'swiper';
import { FreeMode, Mousewheel } from 'swiper/modules';
import 'swiper/css';

document.querySelectorAll('.new-arrivals-swiper').forEach((element) => {
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
        breakpoints: {
            640: { spaceBetween: 24 },
            1024: { spaceBetween: 28 },
        },
    });
});
