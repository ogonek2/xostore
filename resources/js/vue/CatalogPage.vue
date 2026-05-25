<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

const props = defineProps({
    endpoint: { type: String, required: true },
    baseQuery: { type: Object, default: () => ({}) },
    initialFilters: { type: Object, default: () => ({}) },
    initialItems: { type: Array, default: () => [] },
    initialTotal: { type: Number, default: 0 },
    facets: { type: Object, required: true },
    labels: { type: Object, required: true },
    locale: { type: String, default: 'pl' },
    perPage: { type: Number, default: 24 },
});

const filters = reactive({
    sort: props.initialFilters.sort ?? 'newest',
    q: props.initialFilters.q ?? '',
    brands: [...(props.initialFilters.brands ?? [])],
    colors: [...(props.initialFilters.colors ?? [])],
    price_min: props.initialFilters.price_min ?? '',
    price_max: props.initialFilters.price_max ?? '',
    new: Boolean(props.initialFilters.new),
    sale: Boolean(props.initialFilters.sale),
});

const items = ref([...props.initialItems]);
const total = ref(props.initialTotal);
const page = ref(props.initialItems.length > 0 ? 2 : 1);
const loadingMore = ref(false);
const filtering = ref(false);
const hasMore = ref(props.initialItems.length >= props.perPage);

const hasActiveFilters = computed(() => {
    return (
        filters.brands.length > 0
        || filters.colors.length > 0
        || filters.price_min !== ''
        || filters.price_max !== ''
        || filters.new
        || filters.sale
        || filters.sort !== 'newest'
    );
});

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
        if (key.startsWith('brands') || key.startsWith('colors')) {
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

function toggleColor(id) {
    toggleInArray(filters.colors, id);
    applyFilters();
}

function clearFilters() {
    filters.sort = 'newest';
    filters.q = '';
    filters.brands = [];
    filters.colors = [];
    filters.price_min = '';
    filters.price_max = '';
    filters.new = false;
    filters.sale = false;
    syncSearchInputs();
    applyFilters();
}

function syncSearchInputs() {
    document.querySelectorAll('[data-listing-search] input[name="q"], #catalog-search-mobile').forEach((input) => {
        input.value = filters.q;
    });
}

let searchTimeout = null;

function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
}

function onSearchSubmit(event) {
    event.preventDefault();
    clearTimeout(searchTimeout);
    applyFilters();
}

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
});

onUnmounted(() => {
    window.removeEventListener('scroll', onScroll);
    clearTimeout(searchTimeout);
});
</script>

<template>
    <div class="grid gap-10 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-12">
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <p class="mb-4 text-xs font-medium uppercase tracking-[0.18em] text-text-muted lg:hidden">
                {{ labels.filters }}
            </p>

            <form class="space-y-8" @submit.prevent>
                <div>
                    <label class="mb-3 block text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.sort }}
                    </label>
                    <select
                        v-model="filters.sort"
                        class="w-full border border-border-DEFAULT bg-surface-DEFAULT px-3 py-2.5 text-sm outline-none focus:border-primary-DEFAULT"
                        @change="applyFilters"
                    >
                        <option value="newest">{{ labels.sort_newest }}</option>
                        <option value="featured">{{ labels.sort_featured }}</option>
                        <option value="price_asc">{{ labels.sort_price_asc }}</option>
                        <option value="price_desc">{{ labels.sort_price_desc }}</option>
                    </select>
                </div>

                <fieldset>
                    <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.filters }}
                    </legend>
                    <div class="space-y-2">
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input v-model="filters.new" type="checkbox" class="size-4 border-border-DEFAULT" @change="applyFilters">
                            <span>{{ labels.only_new }}</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input v-model="filters.sale" type="checkbox" class="size-4 border-border-DEFAULT" @change="applyFilters">
                            <span>{{ labels.only_sale }}</span>
                        </label>
                    </div>
                </fieldset>

                <fieldset v-if="facets.brands?.length">
                    <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.brand }}
                    </legend>
                    <ul class="max-h-48 space-y-2 overflow-y-auto">
                        <li v-for="brand in facets.brands" :key="brand.id">
                            <label class="flex cursor-pointer items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    :checked="filters.brands.includes(brand.id)"
                                    class="size-4 border-border-DEFAULT"
                                    @change="toggleBrand(brand.id)"
                                >
                                <span>{{ brand.label }}</span>
                            </label>
                        </li>
                    </ul>
                </fieldset>

                <fieldset v-if="facets.colors?.length">
                    <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.color }}
                    </legend>
                    <ul class="flex flex-wrap gap-2">
                        <li v-for="color in facets.colors" :key="color.id">
                            <button
                                type="button"
                                class="size-8 rounded-full border-2 border-transparent ring-1 ring-border-DEFAULT transition hover:scale-105"
                                :class="{ 'ring-2 ring-primary-DEFAULT': filters.colors.includes(color.id) }"
                                :style="{ backgroundColor: color.hex || '#ccc' }"
                                :title="color.label"
                                :aria-label="color.label"
                                :aria-pressed="filters.colors.includes(color.id)"
                                @click="toggleColor(color.id)"
                            />
                        </li>
                    </ul>
                </fieldset>

                <fieldset>
                    <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.price }}
                    </legend>
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            v-model="filters.price_min"
                            type="number"
                            min="0"
                            step="1"
                            :placeholder="facets.price_min ? String(Math.round(facets.price_min)) : labels.from"
                            class="border border-border-DEFAULT px-3 py-2 text-sm outline-none focus:border-primary-DEFAULT"
                        >
                        <input
                            v-model="filters.price_max"
                            type="number"
                            min="0"
                            step="1"
                            :placeholder="facets.price_max ? String(Math.round(facets.price_max)) : labels.to"
                            class="border border-border-DEFAULT px-3 py-2 text-sm outline-none focus:border-primary-DEFAULT"
                        >
                    </div>
                    <button
                        type="button"
                        class="mt-3 w-full border border-primary-DEFAULT py-2 text-sm font-medium transition-colors hover:bg-primary-DEFAULT hover:text-text-inverse"
                        @click="applyFilters"
                    >
                        {{ labels.apply_price }}
                    </button>
                </fieldset>

                <button
                    v-if="hasActiveFilters"
                    type="button"
                    class="text-sm text-text-muted underline underline-offset-4 hover:text-primary-DEFAULT"
                    @click="clearFilters"
                >
                    {{ labels.clear_filters }}
                </button>
            </form>
        </aside>

        <div class="relative min-h-[200px]">
            <div
                v-if="filtering"
                class="absolute inset-0 z-10 flex items-start justify-center bg-surface-DEFAULT/60 pt-16 backdrop-blur-[1px]"
            >
                <p class="text-sm text-text-muted">{{ labels.loading }}</p>
            </div>

            <form class="mb-6 flex w-full gap-2 lg:hidden" @submit="onSearchSubmit">
                <label class="sr-only" for="catalog-search-mobile">{{ labels.search }}</label>
                <input
                    id="catalog-search-mobile"
                    v-model="filters.q"
                    type="search"
                    :placeholder="labels.search_placeholder"
                    class="min-h-[2.75rem] flex-1 border border-border-DEFAULT px-4 text-sm outline-none focus:border-primary-DEFAULT"
                    @input="onSearchInput"
                >
                <button type="submit" class="shrink-0 bg-primary-DEFAULT px-5 text-sm font-medium text-text-inverse hover:bg-primary-hover">
                    {{ labels.search }}
                </button>
            </form>

            <p class="mb-4 text-sm text-text-muted lg:hidden">{{ productsCountLabel }}</p>

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
                    class="group flex w-full flex-col"
                >
                    <a :href="product.url" class="flex flex-col">
                        <div class="relative aspect-[4/5] overflow-hidden rounded-t-2xl bg-[#eceae6]">
                            <img
                                :src="product.image"
                                :alt="product.alt || product.name"
                                class="size-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.02]"
                                loading="lazy"
                            >
                            <span
                                v-if="product.is_new"
                                class="absolute left-3 top-3 bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-DEFAULT"
                            >
                                {{ labels.new_badge }}
                            </span>
                            <button
                                type="button"
                                class="absolute right-3 top-3 flex size-9 items-center justify-center rounded-full bg-white text-primary-DEFAULT shadow-sm transition-transform hover:scale-105"
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
</template>
