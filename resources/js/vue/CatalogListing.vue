<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    endpoint: { type: String, required: true },
    apiQuery: { type: Object, default: () => ({}) },
    initialItems: { type: Array, default: () => [] },
    cartLabel: { type: String, default: 'Cart' },
    colorsLabel: { type: String, default: 'Colors' },
    newBadge: { type: String, default: 'New' },
    perPage: { type: Number, default: 24 },
    emptyText: { type: String, default: 'No products found.' },
});

const items = ref([...props.initialItems]);
const page = ref(props.initialItems.length > 0 ? 2 : 1);
const loading = ref(false);
const hasMore = ref(props.initialItems.length >= props.perPage);

function buildUrl() {
    const url = new URL(props.endpoint, window.location.origin);

    Object.entries(props.apiQuery).forEach(([key, value]) => {
        if (value === null || value === undefined || value === '') return;

        if (Array.isArray(value)) {
            value.forEach((v) => url.searchParams.append(`${key}[]`, String(v)));
        } else if (typeof value === 'boolean') {
            if (value) url.searchParams.set(key, '1');
        } else {
            url.searchParams.set(key, String(value));
        }
    });

    url.searchParams.set('page', String(page.value));
    url.searchParams.set('per_page', String(props.perPage));

    return url.toString();
}

async function loadMore() {
    if (loading.value || !hasMore.value) return;

    loading.value = true;

    try {
        const response = await fetch(buildUrl(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error('Failed to load');

        const data = await response.json();
        items.value.push(...(data.data ?? []));
        hasMore.value = data.meta?.has_more ?? false;
        page.value += 1;
    } finally {
        loading.value = false;
    }
}

function onScroll() {
    const nearBottom =
        window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 500;

    if (nearBottom) loadMore();
}

onMounted(() => {
    if (items.value.length === 0) {
        page.value = 1;
        loadMore();
    }

    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <div>
        <div v-if="items.length === 0 && !loading" class="py-20 text-center text-sm text-text-muted">
            {{ emptyText }}
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
            <article
                v-for="(product, index) in items"
                :key="product.url + index"
                class="group flex w-full flex-col"
            >
                <a :href="product.url" class="flex flex-col">
                    <div class="relative aspect-[4/5] overflow-hidden rounded-t-2xl bg-[#eceae6]">
                        <img
                            :src="product.image"
                            :alt="product.alt || product.name"
                            class="size-full object-cover object-center transition-transform duration-700 ease-out group-hover:scale-[1.02]"
                            loading="lazy"
                        />
                        <span
                            v-if="product.is_new"
                            class="absolute left-3 top-3 bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-primary-DEFAULT"
                        >
                            {{ newBadge }}
                        </span>
                        <button
                            type="button"
                            class="absolute right-3 top-3 flex size-9 items-center justify-center rounded-full bg-white text-primary-DEFAULT shadow-sm transition-transform hover:scale-105"
                            :aria-label="cartLabel"
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
                    :aria-label="colorsLabel"
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

        <p v-if="loading" class="py-10 text-center text-sm text-text-muted">Ładowanie…</p>
    </div>
</template>
