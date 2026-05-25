import './bootstrap';
import './shop/trending-swiper';
import './shop/new-arrivals-swiper';
import { createApp } from 'vue';

const vueMounts = document.querySelectorAll('[data-vue]');

vueMounts.forEach((el) => {
    const component = el.dataset.vue;

    if (component === 'catalog-infinite') {
        import('./vue/CatalogInfinite.vue').then(({ default: CatalogInfinite }) => {
            createApp(CatalogInfinite, {
                endpoint: el.dataset.endpoint,
                perPage: Number(el.dataset.perPage || 24),
            }).mount(el);
        });
    }

    if (component === 'cart-modal') {
        import('./vue/CartModal.vue').then(({ default: CartModal }) => {
            createApp(CartModal).mount(el);
        });
    }

    if (component === 'categories-swiper') {
        import('./vue/CategoriesSwiper.vue').then(({ default: CategoriesSwiper }) => {
            createApp(CategoriesSwiper, {
                cards: JSON.parse(el.dataset.cards || '[]'),
                title: el.dataset.title,
                viewAll: el.dataset.viewAll,
            }).mount(el);
        });
    }

    if (component === 'catalog-page') {
        import('./vue/CatalogPage.vue').then(({ default: CatalogPage }) => {
            createApp(CatalogPage, {
                endpoint: el.dataset.endpoint,
                baseQuery: JSON.parse(el.dataset.baseQuery || '{}'),
                initialFilters: JSON.parse(el.dataset.initialFilters || '{}'),
                initialItems: JSON.parse(el.dataset.initialItems || '[]'),
                initialTotal: Number(el.dataset.initialTotal || 0),
                facets: JSON.parse(el.dataset.facets || '{}'),
                labels: JSON.parse(el.dataset.labels || '{}'),
                locale: el.dataset.locale || 'pl',
                perPage: Number(el.dataset.perPage || 24),
            }).mount(el);
        });
    }
});
