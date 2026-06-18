import './bootstrap';
import './shop/trending-swiper';
import './shop/new-arrivals-swiper';
import { initCartBadges } from './shop/cart-badges';
import { initProductCardAddButtons } from './shop/cart-api';
import { initShopAnalytics } from './shop/analytics';
import { initHeaderNav } from './shop/header-nav';
import { initNewsletterForms } from './shop/newsletter';

initCartBadges();
initProductCardAddButtons();
initShopAnalytics();
    initHeaderNav();
    initNewsletterForms();
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

    if (component === 'cart-drawer') {
        import('./vue/CartDrawer.vue').then(({ default: CartDrawer }) => {
            createApp(CartDrawer, {
                locale: el.dataset.locale,
                labels: JSON.parse(el.dataset.labels || '{}'),
                routes: JSON.parse(el.dataset.routes || '{}'),
            }).mount(el);
        });
    }

    if (component === 'product-page') {
        import('./vue/ProductPage.vue').then(({ default: ProductPage }) => {
            createApp(ProductPage, {
                product: JSON.parse(el.dataset.product || '{}'),
                labels: JSON.parse(el.dataset.labels || '{}'),
                routes: JSON.parse(el.dataset.routes || '{}'),
            }).mount(el);
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
                categories: JSON.parse(el.dataset.categories || '[]'),
                labels: JSON.parse(el.dataset.labels || '{}'),
                locale: el.dataset.locale || 'pl',
                perPage: Number(el.dataset.perPage || 24),
            }).mount(el);
        });
    }
});
