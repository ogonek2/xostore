<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    endpoint: { type: String, required: true },
    perPage: { type: Number, default: 24 },
});

const items = ref([]);
const page = ref(1);
const loading = ref(false);
const hasMore = ref(true);

async function loadMore() {
    if (loading.value || !hasMore.value) return;

    loading.value = true;

    try {
        const url = new URL(props.endpoint, window.location.origin);
        url.searchParams.set('page', String(page.value));
        url.searchParams.set('per_page', String(props.perPage));

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error('Failed to load catalog');

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
        window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 400;

    if (nearBottom) loadMore();
}

onMounted(() => {
    loadMore();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <div class="catalog-infinite">
        <slot :items="items" :loading="loading" />
        <p v-if="loading" class="text-text-muted py-8 text-center text-sm">Ładowanie…</p>
    </div>
</template>
