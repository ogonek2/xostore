<script setup>
const props = defineProps({
    nav: {
        type: Object,
        default: () => ({
            all_products: { label: '', url: '' },
            parent: null,
            links: [],
        }),
    },
    labels: { type: Object, required: true },
    isLinkActive: { type: Function, required: true },
});

const hasLinks = () => (props.nav.links?.length ?? 0) > 0 || props.nav.parent;
</script>

<template>
    <section v-if="hasLinks()" class="py-3">
        <p class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
            {{ labels.categories }}
        </p>
        <nav class="catalog-filters-scroll max-h-52 space-y-1 overflow-y-auto pr-1">
            <a
                :href="nav.all_products?.url"
                class="block rounded-md px-2.5 py-2 text-sm transition-colors"
                :class="isLinkActive(nav.all_products?.url)
                    ? 'bg-primary-DEFAULT/5 font-semibold text-primary-DEFAULT'
                    : 'text-text-muted hover:bg-surface-muted hover:text-primary-DEFAULT'"
            >
                {{ nav.all_products?.label }}
            </a>
            <a
                v-if="nav.parent"
                :href="nav.parent.url"
                class="block rounded-md px-2.5 py-2 text-sm transition-colors"
                :class="isLinkActive(nav.parent.url)
                    ? 'bg-primary-DEFAULT/5 font-semibold text-primary-DEFAULT'
                    : 'text-text-muted hover:bg-surface-muted hover:text-primary-DEFAULT'"
            >
                {{ nav.parent.label }}
            </a>
            <a
                v-for="link in nav.links"
                :key="link.url"
                :href="link.url"
                class="block rounded-md px-2.5 py-2 text-sm transition-colors"
                :class="isLinkActive(link.url)
                    ? 'bg-primary-DEFAULT/5 font-semibold text-primary-DEFAULT'
                    : 'text-text-DEFAULT hover:bg-surface-muted hover:text-primary-DEFAULT'"
            >
                {{ link.label }}
            </a>
        </nav>
    </section>
</template>
