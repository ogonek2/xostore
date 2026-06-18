<script setup>
import { computed, nextTick, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { refreshCartProductBadges } from '../shop/cart-badges';
import CatalogFilterForm from './CatalogFilterForm.vue';

const props = defineProps({
    endpoint: { type: String, required: true },
    baseQuery: { type: Object, default: () => ({}) },
    initialFilters: { type: Object, default: () => ({}) },
    initialItems: { type: Array, default: () => [] },
    initialTotal: { type: Number, default: 0 },
    facets: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    labels: { type: Object, required: true },
    locale: { type: String, default: 'pl' },
    perPage: { type: Number, default: 24 },
});

const filters = reactive({
    sort: props.initialFilters.sort ?? 'newest',
    q: props.initialFilters.q ?? '',
    brands: [...(props.initialFilters.brands ?? [])],
    sizes: [...(props.initialFilters.sizes ?? [])],
    colors: [...(props.initialFilters.colors ?? [])],
    price_min: props.initialFilters.price_min ?? '',
    price_max: props.initialFilters.price_max ?? '',
    new: Boolean(props.initialFilters.new),
    sale: Boolean(props.initialFilters.sale),
});

const items = ref([...props.initialItems]);
const total = ref(props.initialTotal);
const facetsState = ref({ ...(props.facets ?? {}) });

watch(items, () => {
    nextTick(() => refreshCartProductBadges());
});

function inCartLabel(name) {
    return (props.labels.in_cart_label || '').replace(':name', name);
}
const page = ref(props.initialItems.length > 0 ? 2 : 1);
const loadingMore = ref(false);
const filtering = ref(false);
const hasMore = ref(props.initialItems.length >= props.perPage);
const mobileFiltersOpen = ref(false);
const expandedParents = ref([]);
const currentPath = ref(typeof window !== 'undefined' ? window.location.pathname : '');

const hasActiveFilters = computed(() => {
    return (
        filters.brands.length > 0
        || filters.sizes.length > 0
        || filters.colors.length > 0
        || filters.price_min !== ''
        || filters.price_max !== ''
        || filters.new
        || filters.sale
        || filters.sort !== 'newest'
    );
});

const sizeGroups = computed(() => facetsState.value.size_groups ?? []);
const hasMultipleSizeGroups = computed(() => sizeGroups.value.length > 1);

const productsCountLabel = computed(() => formatCount(total.value));

function formatCount(count) {
    if (props.locale === 'en') {
        if (count === 0) return 'No products';
        if (count === 1) return '1 product';

        return `${count} products`;
    }

    if (count === 0) return 'Brak produktów';
    if (count === 1) return '1 produkt';
    if (count >= 2 && count <= 4) return `${count} produkty`;

    return `${count} produktów`;
}

function buildQueryObject() {
    const query = { ...props.baseQuery, sort: filters.sort };

    if (filters.q.trim()) query.q = filters.q.trim();
    if (filters.brands.length) query.brands = [...filters.brands];
    if (filters.sizes.length) query.sizes = [...filters.sizes];
    if (filters.colors.length) query.colors = [...filters.colors];
    if (filters.price_min !== '' && filters.price_min !== null) query.price_min = Number(filters.price_min);
    if (filters.price_max !== '' && filters.price_max !== null) query.price_max = Number(filters.price_max);
    if (filters.new) query.new = true;
    if (filters.sale) query.sale = true;

    return query;
}

function appendQueryToUrl(url, query) {
    Object.entries(query).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') return;

        if (Array.isArray(value)) {
            value.forEach((v) => url.searchParams.append(`${key}[]`, String(v)));
        } else if (typeof value === 'boolean') {
            if (value) url.searchParams.set(key, '1');
        } else {
            url.searchParams.set(key, String(value));
        }
    });
}

function buildFetchUrl(pageNum) {
    const url = new URL(props.endpoint, window.location.origin);
    appendQueryToUrl(url, buildQueryObject());
    url.searchParams.set('page', String(pageNum));
    url.searchParams.set('per_page', String(props.perPage));

    return url.toString();
}

function syncBrowserUrl() {
    const url = new URL(window.location.href);

    ['sort', 'q', 'price_min', 'price_max', 'sale', 'new', 'page', 'per_page'].forEach((key) => {
        url.searchParams.delete(key);
    });

    [...url.searchParams.keys()].forEach((key) => {
        if (key.startsWith('brands') || key.startsWith('sizes') || key.startsWith('colors')) {
            url.searchParams.delete(key);
        }
    });

    appendQueryToUrl(url, buildQueryObject());

    const next = `${url.pathname}${url.search}`;
    history.replaceState({}, '', next);
}

async function fetchProducts(pageNum, append = false) {
    const response = await fetch(buildFetchUrl(pageNum), {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!response.ok) throw new Error('Failed to load products');

    return response.json();
}

async function applyFilters() {
    filtering.value = true;
    page.value = 1;

    try {
        const data = await fetchProducts(1, false);
        items.value = data.data ?? [];
        total.value = data.meta?.total ?? 0;
        hasMore.value = data.meta?.has_more ?? false;
        facetsState.value = { ...(data.meta?.facets ?? facetsState.value) };
        page.value = 2;
        syncBrowserUrl();
        syncSearchInputs();
        emitCount();
    } finally {
        filtering.value = false;
    }
}

function emitCount() {
    const host = document.querySelector('[data-vue="catalog-page"]');
    host?.dispatchEvent(new CustomEvent('catalog:count', { detail: productsCountLabel.value }));
}

async function loadMore() {
    if (loadingMore.value || filtering.value || !hasMore.value) return;

    loadingMore.value = true;

    try {
        const data = await fetchProducts(page.value, true);
        items.value.push(...(data.data ?? []));
        hasMore.value = data.meta?.has_more ?? false;
        page.value += 1;
    } finally {
        loadingMore.value = false;
    }
}

function onScroll() {
    const nearBottom =
        window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 500;

    if (nearBottom) loadMore();
}

function toggleInArray(array, value) {
    const index = array.indexOf(value);
    if (index === -1) array.push(value);
    else array.splice(index, 1);
}

function toggleBrand(id) {
    toggleInArray(filters.brands, id);
    applyFilters();
}

function toggleSize(key) {
    toggleInArray(filters.sizes, key);
    applyFilters();
}

function toggleColor(id) {
    toggleInArray(filters.colors, id);
    applyFilters();
}

function clearFilters() {
    filters.sort = 'newest';
    filters.q = '';
    filters.brands = [];
    filters.sizes = [];
    filters.colors = [];
    filters.price_min = '';
    filters.price_max = '';
    filters.new = false;
    filters.sale = false;
    syncSearchInputs();
    applyFilters();
}

function normalizePath(url) {
    try {
        const parsed = new URL(url, window.location.origin);
        return parsed.pathname.replace(/\/+$/, '');
    } catch (error) {
        return '';
    }
}

function isCategoryActive(url) {
    const targetPath = normalizePath(url);
    const activePath = (currentPath.value || '').replace(/\/+$/, '');
    return targetPath !== '' && targetPath === activePath;
}

function isParentActive(category) {
    if (isCategoryActive(category.url)) return true;
    return (category.children ?? []).some((child) => isCategoryActive(child.url));
}

function isParentExpanded(categoryUrl) {
    return expandedParents.value.includes(categoryUrl);
}

function toggleParent(categoryUrl) {
    const index = expandedParents.value.indexOf(categoryUrl);
    if (index === -1) expandedParents.value.push(categoryUrl);
    else expandedParents.value.splice(index, 1);
}

function openMobileFilters() {
    mobileFiltersOpen.value = true;
    document.body.classList.add('overflow-hidden');
}

function closeMobileFilters() {
    mobileFiltersOpen.value = false;
    document.body.classList.remove('overflow-hidden');
}

function syncSearchInputs() {}

onMounted(() => {
    const host = document.querySelector('[data-vue="catalog-page"]');

    host?.addEventListener('catalog:search', (event) => {
        filters.q = event.detail?.q ?? '';
        applyFilters();
    });

    if (items.value.length === 0) {
        applyFilters();
    } else {
        emitCount();
    }

    watch(total, emitCount);

    window.addEventListener('scroll', onScroll, { passive: true });

    nextTick(() => refreshCartProductBadges());

    expandedParents.value = props.categories
        .filter((category) => isParentActive(category))
        .map((category) => category.url);
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    document.body.classList.remove('overflow-hidden');
});
</script>

<template>
    <div>
        <div class="mb-5 flex items-center justify-between gap-3 lg:hidden">
            <p class="text-sm text-text-muted">{{ productsCountLabel }}</p>
            <button
                type="button"
                class="inline-flex min-h-[2.75rem] items-center gap-2 border border-border-DEFAULT px-4 text-sm font-medium text-text-DEFAULT"
                @click="openMobileFilters"
            >
                <span aria-hidden="true">☰</span>
                {{ labels.open_filters || labels.filters }}
            </button>
        </div>

        <div v-if="mobileFiltersOpen" class="fixed inset-0 z-[110] lg:hidden">
            <button
                type="button"
                class="absolute inset-0 bg-black/45"
                aria-label="Close filters"
                @click="closeMobileFilters"
            />

            <aside class="relative z-[1] h-full w-[86%] max-w-[24rem] overflow-y-auto bg-surface-DEFAULT p-5 shadow-2xl">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold uppercase tracking-[0.12em] text-primary-DEFAULT">
                        {{ labels.filters }}
                    </p>
                    <button
                        type="button"
                        class="inline-flex min-h-[2.5rem] items-center border border-border-DEFAULT px-3 text-sm"
                        @click="closeMobileFilters"
                    >
                        {{ labels.close }}
                    </button>
                </div>

                <div v-if="categories.length" class="mb-8">
                    <p class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.categories }}
                    </p>
                    <ul class="space-y-2">
                        <li v-for="category in categories" :key="category.url">
                            <div class="rounded-lg border border-border-DEFAULT/70">
                                <div class="flex items-stretch">
                                    <a
                                        :href="category.url"
                                        class="min-w-0 flex-1 px-3 py-2.5 text-sm transition-colors"
                                        :class="isParentActive(category) ? 'font-semibold text-primary-DEFAULT' : 'font-medium text-text-DEFAULT hover:text-primary-DEFAULT'"
                                    >
                                        {{ category.label }}
                                    </a>
                                    <button
                                        v-if="category.children?.length"
                                        type="button"
                                        class="inline-flex w-11 items-center justify-center border-l border-border-DEFAULT/70 text-text-muted transition-colors hover:text-primary-DEFAULT"
                                        :aria-expanded="isParentExpanded(category.url)"
                                        :aria-label="`Toggle ${category.label}`"
                                        @click="toggleParent(category.url)"
                                    >
                                        <svg class="size-4 transition-transform" :class="{ 'rotate-180': isParentExpanded(category.url) }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>

                                <ul
                                    v-if="category.children?.length && isParentExpanded(category.url)"
                                    class="space-y-0.5 border-t border-border-DEFAULT/70 bg-surface-muted/40 px-2 py-2"
                                >
                                    <li v-for="child in category.children" :key="child.url">
                                        <a
                                            :href="child.url"
                                            class="block rounded-md px-2.5 py-2 text-sm transition-colors"
                                            :class="isCategoryActive(child.url) ? 'font-semibold text-primary-DEFAULT bg-primary-DEFAULT/5' : 'text-text-muted hover:bg-surface-muted hover:text-primary-DEFAULT'"
                                        >
                                            {{ child.label }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>

                <CatalogFilterForm
                    :filters="filters"
                    :facets-state="facetsState"
                    :labels="labels"
                    :size-groups="sizeGroups"
                    :has-multiple-size-groups="hasMultipleSizeGroups"
                    :has-active-filters="hasActiveFilters"
                    @apply="applyFilters"
                    @clear="clearFilters"
                    @toggle-brand="toggleBrand"
                    @toggle-size="toggleSize"
                    @toggle-color="toggleColor"
                />
            </aside>
        </div>

        <div class="grid gap-10 lg:grid-cols-[260px_minmax(0,1fr)] lg:gap-6">
            <aside class="hidden lg:sticky lg:top-24 lg:z-20 lg:block lg:self-start">
                <div class="flex max-h-[calc(100dvh-14rem)] flex-col overflow-hidden">
                    <div class="shrink-0 border-b border-border-DEFAULT/60 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-DEFAULT">
                            {{ labels.filters }}
                        </p>
                    </div>
                    <div class="catalog-filters-scroll min-h-0 flex-1 overflow-y-auto overscroll-contain py-4 pr-6">
                        <CatalogFilterForm
                            :filters="filters"
                            :facets-state="facetsState"
                            :labels="labels"
                            :size-groups="sizeGroups"
                            :has-multiple-size-groups="hasMultipleSizeGroups"
                            :has-active-filters="hasActiveFilters"
                            @apply="applyFilters"
                            @clear="clearFilters"
                            @toggle-brand="toggleBrand"
                            @toggle-size="toggleSize"
                            @toggle-color="toggleColor"
                        />
                    </div>
                </div>
            </aside>

            <div class="relative min-h-[200px]">
            <div
                v-if="filtering"
                class="absolute inset-0 z-10 flex items-start justify-center bg-surface-DEFAULT/60 pt-16 backdrop-blur-[1px]"
            >
                <p class="text-sm text-text-muted">{{ labels.loading }}</p>
            </div>

            <div v-if="items.length === 0 && !filtering" class="py-20 text-center text-sm text-text-muted">
                {{ labels.empty }}
            </div>

            <div
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4"
                :class="{ 'opacity-50 pointer-events-none': filtering }"
            >
                <article
                    v-for="(product, index) in items"
                    :key="`${product.url}-${index}`"
                    :data-product-id="product.product_id"
                    data-product-card
                    :data-in-cart-label="inCartLabel(product.name)"
                    class="group/product-card flex w-full flex-col"
                >
                    <a :href="product.url" class="flex flex-col">
                        <div class="relative aspect-[4/5] overflow-hidden rounded-t-2xl bg-[#eceae6] ring-inset ring-1 ring-transparent group-[.is-in-cart]/product-card:ring-primary-DEFAULT">
                            <img
                                :src="product.image"
                                :alt="product.alt || product.name"
                                class="size-full object-cover object-center transition-transform duration-700 ease-out group-hover/product-card:scale-[1.02]"
                                loading="lazy"
                            >
                            <span
                                v-if="product.is_new"
                                class="absolute left-3 top-3 z-[1] bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-DEFAULT"
                            >
                                {{ labels.new_badge }}
                            </span>
                            <span
                                class="pointer-events-none absolute bottom-3 left-3 right-3 z-[2] hidden items-center justify-center gap-1.5 bg-primary-DEFAULT px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-text-inverse group-[.is-in-cart]/product-card:flex"
                            >
                                <svg class="size-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                {{ labels.in_cart }}
                            </span>
                            <button
                                type="button"
                                class="absolute right-3 top-3 z-[2] flex size-9 items-center justify-center rounded-full bg-white text-primary-DEFAULT shadow-sm transition-transform hover:scale-105 group-[.is-in-cart]/product-card:hidden"
                                :aria-label="labels.cart"
                                @click.prevent.stop="window.dispatchEvent(new Event('cart:open'))"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M6 6h15l-1.5 9h-12L6 6z" stroke-linejoin="round" />
                                    <path d="M6 6L5 3H2" stroke-linecap="round" />
                                    <circle cx="9" cy="20" r="1" fill="currentColor" stroke="none" />
                                    <circle cx="18" cy="20" r="1" fill="currentColor" stroke="none" />
                                </svg>
                            </button>
                            <span
                                class="absolute right-3 top-3 z-[2] hidden size-9 items-center justify-center rounded-full bg-primary-DEFAULT text-text-inverse group-[.is-in-cart]/product-card:flex"
                                aria-hidden="true"
                            >
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>
                        <div class="pt-4">
                            <h3 class="text-[0.95rem] font-semibold leading-snug tracking-tight text-primary-DEFAULT">
                                {{ product.name }}
                            </h3>
                            <p v-if="product.category" class="mt-1 text-sm text-text-muted">{{ product.category }}</p>
                            <p v-if="product.price_formatted" class="mt-2 text-[0.95rem] font-semibold text-primary-DEFAULT">
                                {{ product.price_formatted }}
                            </p>
                        </div>
                    </a>
                    <div
                        v-if="product.colors?.length"
                        class="mt-3 flex flex-wrap items-center gap-2"
                        role="list"
                        :aria-label="labels.colors"
                    >
                        <span
                            v-for="(hex, ci) in product.colors"
                            :key="ci"
                            role="listitem"
                            class="size-5 rounded-full border border-border-DEFAULT"
                            :style="{ backgroundColor: hex }"
                        />
                    </div>
                </article>
            </div>

            <p v-if="loadingMore" class="py-10 text-center text-sm text-text-muted">{{ labels.loading }}</p>
            </div>
        </div>
    </div>
</template>
