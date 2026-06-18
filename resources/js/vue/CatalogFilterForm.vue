<script setup>
import { onMounted, reactive, watch } from 'vue';

const props = defineProps({
    filters: { type: Object, required: true },
    facetsState: { type: Object, required: true },
    labels: { type: Object, required: true },
    sizeGroups: { type: Array, default: () => [] },
    hasMultipleSizeGroups: { type: Boolean, default: false },
    hasActiveFilters: { type: Boolean, default: false },
});

const emit = defineEmits([
    'apply',
    'clear',
    'toggle-brand',
    'toggle-size',
    'toggle-color',
]);

const sectionOpen = reactive({
    brands: false,
    sizes: true,
    colors: false,
    price: true,
});

function toggleSection(key) {
    sectionOpen[key] = !sectionOpen[key];
}

function isSizeSelected(key) {
    return props.filters.sizes.includes(key);
}

function sectionBadge(key) {
    if (key === 'brands' && props.filters.brands.length) {
        return props.filters.brands.length;
    }

    if (key === 'sizes' && props.filters.sizes.length) {
        return props.filters.sizes.length;
    }

    if (key === 'colors' && props.filters.colors.length) {
        return props.filters.colors.length;
    }

    if (key === 'price' && (props.filters.price_min !== '' || props.filters.price_max !== '')) {
        return '•';
    }

    return null;
}

function syncSectionDefaults() {
    const brandCount = props.facetsState.brands?.length ?? 0;
    const colorCount = props.facetsState.colors?.length ?? 0;

    if (props.filters.brands.length) {
        sectionOpen.brands = true;
    } else if (! sectionOpen.brands && brandCount > 6) {
        sectionOpen.brands = false;
    } else if (brandCount > 0 && brandCount <= 6) {
        sectionOpen.brands = true;
    }

    if (props.filters.sizes.length) {
        sectionOpen.sizes = true;
    } else if (! props.hasMultipleSizeGroups) {
        sectionOpen.sizes = true;
    }

    if (props.filters.colors.length) {
        sectionOpen.colors = true;
    } else if (! sectionOpen.colors && colorCount > 6) {
        sectionOpen.colors = false;
    } else if (colorCount > 0 && colorCount <= 6) {
        sectionOpen.colors = true;
    }

    if (props.filters.price_min !== '' || props.filters.price_max !== '') {
        sectionOpen.price = true;
    }
}

onMounted(syncSectionDefaults);

watch(
    () => [
        props.filters.brands.length,
        props.filters.sizes.length,
        props.filters.colors.length,
        props.filters.price_min,
        props.filters.price_max,
    ],
    () => {
        if (props.filters.brands.length) {
            sectionOpen.brands = true;
        }

        if (props.filters.sizes.length) {
            sectionOpen.sizes = true;
        }

        if (props.filters.colors.length) {
            sectionOpen.colors = true;
        }

        if (props.filters.price_min !== '' || props.filters.price_max !== '') {
            sectionOpen.price = true;
        }
    },
);
</script>

<template>
    <form class="space-y-4" @submit.prevent>
        <div>
            <label class="mb-2 block text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ labels.sort }}
            </label>
            <select
                v-model="filters.sort"
                class="w-full py-2.5 text-sm outline-none focus:border-primary-DEFAULT"
                @change="emit('apply')"
            >
                <option value="newest">{{ labels.sort_newest }}</option>
                <option value="featured">{{ labels.sort_featured }}</option>
                <option value="price_asc">{{ labels.sort_price_asc }}</option>
                <option value="price_desc">{{ labels.sort_price_desc }}</option>
            </select>
        </div>

        <div class="space-y-2 pb-4">
            <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ labels.filters }}
            </p>
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input v-model="filters.new" type="checkbox" class="size-4 border-border-DEFAULT" @change="emit('apply')">
                <span>{{ labels.only_new }}</span>
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input v-model="filters.sale" type="checkbox" class="size-4 border-border-DEFAULT" @change="emit('apply')">
                <span>{{ labels.only_sale }}</span>
            </label>
        </div>

        <div class="divide-y divide-border-DEFAULT/50">
            <section v-if="facetsState.brands?.length" class="py-3">
                <button
                    type="button"
                    class="flex w-full items-center gap-2 text-left"
                    :aria-expanded="sectionOpen.brands"
                    @click="toggleSection('brands')"
                >
                    <span class="flex-1 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.brand }}
                    </span>
                    <span
                        v-if="sectionBadge('brands')"
                        class="rounded-full bg-primary-DEFAULT/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary-DEFAULT"
                    >
                        {{ sectionBadge('brands') }}
                    </span>
                    <svg
                        class="size-4 shrink-0 text-text-muted transition-transform"
                        :class="{ 'rotate-180': sectionOpen.brands }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <ul v-show="sectionOpen.brands" class="mt-3 max-h-40 space-y-1.5 overflow-y-auto pr-1 catalog-filters-scroll">
                    <li v-for="brand in facetsState.brands" :key="brand.id">
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                :checked="filters.brands.includes(brand.id)"
                                class="size-4 border-border-DEFAULT"
                                @change="emit('toggle-brand', brand.id)"
                            >
                            <span>{{ brand.label }}</span>
                        </label>
                    </li>
                </ul>
            </section>

            <section v-if="sizeGroups.length" class="py-3">
                <button
                    type="button"
                    class="flex w-full items-center gap-2 text-left"
                    :aria-expanded="sectionOpen.sizes"
                    @click="toggleSection('sizes')"
                >
                    <span class="flex-1 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.size }}
                    </span>
                    <span
                        v-if="sectionBadge('sizes')"
                        class="rounded-full bg-primary-DEFAULT/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary-DEFAULT"
                    >
                        {{ sectionBadge('sizes') }}
                    </span>
                    <svg
                        class="size-4 shrink-0 text-text-muted transition-transform"
                        :class="{ 'rotate-180': sectionOpen.sizes }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div v-show="sectionOpen.sizes" class="mt-3 space-y-3">
                    <p v-if="hasMultipleSizeGroups" class="text-xs leading-relaxed text-text-muted">
                        {{ labels.size_hint }}
                    </p>
                    <div v-for="group in sizeGroups" :key="group.id">
                        <p
                            v-if="hasMultipleSizeGroups"
                            class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-text-muted"
                        >
                            {{ group.label }}
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="size in group.sizes"
                                :key="size.key"
                                type="button"
                                class="inline-flex min-w-[2.25rem] items-center justify-center border px-2.5 py-1.5 text-xs transition-colors"
                                :class="isSizeSelected(size.key)
                                    ? 'border-primary-DEFAULT bg-primary-DEFAULT/5 font-semibold text-primary-DEFAULT'
                                    : 'border-border-DEFAULT text-text-DEFAULT hover:border-primary-DEFAULT/40'"
                                @click="emit('toggle-size', size.key)"
                            >
                                {{ size.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="facetsState.colors?.length" class="py-3">
                <button
                    type="button"
                    class="flex w-full items-center gap-2 text-left"
                    :aria-expanded="sectionOpen.colors"
                    @click="toggleSection('colors')"
                >
                    <span class="flex-1 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.color }}
                    </span>
                    <span
                        v-if="sectionBadge('colors')"
                        class="rounded-full bg-primary-DEFAULT/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary-DEFAULT"
                    >
                        {{ sectionBadge('colors') }}
                    </span>
                    <svg
                        class="size-4 shrink-0 text-text-muted transition-transform"
                        :class="{ 'rotate-180': sectionOpen.colors }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <ul v-show="sectionOpen.colors" class="mt-3 max-h-40 space-y-1.5 overflow-y-auto pr-1 catalog-filters-scroll">
                    <li v-for="color in facetsState.colors" :key="color.id">
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <input
                                type="checkbox"
                                :checked="filters.colors.includes(color.id)"
                                class="size-4 border-border-DEFAULT"
                                @change="emit('toggle-color', color.id)"
                            >
                            <span
                                class="size-5 shrink-0 rounded-full border border-border-DEFAULT ring-1 ring-inset ring-black/5"
                                :class="{ 'ring-2 ring-primary-DEFAULT': filters.colors.includes(color.id) }"
                                :style="{ backgroundColor: color.hex || '#e8e6e2' }"
                                :title="color.label"
                            />
                            <span :class="filters.colors.includes(color.id) ? 'font-semibold text-primary-DEFAULT' : 'text-text-DEFAULT'">
                                {{ color.label }}
                            </span>
                        </label>
                    </li>
                </ul>
            </section>

            <section class="py-3">
                <button
                    type="button"
                    class="flex w-full items-center gap-2 text-left"
                    :aria-expanded="sectionOpen.price"
                    @click="toggleSection('price')"
                >
                    <span class="flex-1 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                        {{ labels.price }}
                    </span>
                    <span
                        v-if="sectionBadge('price')"
                        class="rounded-full bg-primary-DEFAULT/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary-DEFAULT"
                    >
                        {{ sectionBadge('price') }}
                    </span>
                    <svg
                        class="size-4 shrink-0 text-text-muted transition-transform"
                        :class="{ 'rotate-180': sectionOpen.price }"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div v-show="sectionOpen.price" class="mt-3">
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            v-model="filters.price_min"
                            type="number"
                            min="0"
                            step="1"
                            :placeholder="facetsState.price_min ? String(Math.round(facetsState.price_min)) : labels.from"
                            class="border border-border-DEFAULT px-3 py-2 text-sm outline-none focus:border-primary-DEFAULT"
                        >
                        <input
                            v-model="filters.price_max"
                            type="number"
                            min="0"
                            step="1"
                            :placeholder="facetsState.price_max ? String(Math.round(facetsState.price_max)) : labels.to"
                            class="border border-border-DEFAULT px-3 py-2 text-sm outline-none focus:border-primary-DEFAULT"
                        >
                    </div>
                    <button
                        type="button"
                        class="mt-3 w-full border border-primary-DEFAULT py-2 text-sm font-medium transition-colors hover:bg-primary-DEFAULT hover:text-text-inverse"
                        @click="emit('apply')"
                    >
                        {{ labels.apply_price }}
                    </button>
                </div>
            </section>
        </div>

        <button
            v-if="hasActiveFilters"
            type="button"
            class="pt-1 text-sm text-text-muted underline underline-offset-4 hover:text-primary-DEFAULT"
            @click="emit('clear')"
        >
            {{ labels.clear_filters }}
        </button>
    </form>
</template>
