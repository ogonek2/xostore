<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import ProductGallery from './ProductGallery.vue';
import ProductCharacteristics from './ProductCharacteristics.vue';
import ProductSimilarProducts from './ProductSimilarProducts.vue';
import { productCartLines } from '../shop/cart-badges';

const props = defineProps({
    product: { type: Object, required: true },
    labels: { type: Object, required: true },
    routes: { type: Object, required: true },
});

const selectedColorId = ref(props.product.selected_color_id);
const selectedVariantId = ref(props.product.default_variant_id);
const quantity = ref(1);
const adding = ref(false);
const error = ref('');
const cartLines = ref(props.product.cart?.lines ?? []);

const variants = computed(() => props.product.variants);

const sizes = computed(() => {
    if (!selectedColorId.value) {
        return props.product.sizes;
    }

    return variants.value
        .filter((v) => v.color_id === selectedColorId.value)
        .map((v) => ({
            variant_id: v.id,
            label: v.size ?? v.sku,
            value: v.size_value ?? v.sku,
        }));
});

const selectedVariant = computed(() =>
    variants.value.find((v) => v.id === selectedVariantId.value) ?? null
);

const inCart = computed(() => cartLines.value.length > 0);

const cartTotalQty = computed(() =>
    cartLines.value.reduce((sum, line) => sum + line.quantity, 0),
);

const selectedVariantCartLine = computed(() =>
    cartLines.value.find((line) => line.variant_id === selectedVariantId.value) ?? null,
);

function applyCartItems(items) {
    cartLines.value = productCartLines({ items }, props.product.id);
}

function openCart() {
    window.dispatchEvent(new Event('cart:open'));
}

function formatLabel(template, replacements) {
    return Object.entries(replacements).reduce(
        (text, [key, value]) => text.replace(`:${key}`, String(value)),
        template ?? '',
    );
}

function onCartUpdated(event) {
    applyCartItems(event.detail?.items ?? []);
}

onMounted(() => {
    window.addEventListener('cart:updated', onCartUpdated);
});

onUnmounted(() => {
    window.removeEventListener('cart:updated', onCartUpdated);
});

watch(selectedColorId, (colorId) => {
    const color = props.product.colors.find((c) => c.id === colorId);
    if (color) {
        const url = new URL(window.location.href);
        url.searchParams.set('color', color.code);
        history.replaceState({}, '', url);
    }

    const first = variants.value.find((v) => v.color_id === colorId);
    if (first) selectedVariantId.value = first.id;
});

watch(sizes, (list) => {
    if (!list.find((s) => s.variant_id === selectedVariantId.value) && list[0]) {
        selectedVariantId.value = list[0].variant_id;
    }
}, { immediate: true });

async function addToCart() {
    if (!selectedVariantId.value) {
        error.value = props.labels.select_size;
        return;
    }

    adding.value = true;
    error.value = '';

    try {
        const response = await fetch(props.routes.cartStore, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                variant_id: selectedVariantId.value,
                quantity: quantity.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            error.value = Object.values(data.errors ?? {}).flat()[0] ?? props.labels.error;
            return;
        }

        const { dispatchCartState } = await import('../shop/cart-badges');
        dispatchCartState(data);
        applyCartItems(data.items ?? []);
        window.dispatchEvent(new Event('cart:open'));
    } catch {
        error.value = props.labels.error;
    } finally {
        adding.value = false;
    }
}
</script>

<template>
    <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
        <ProductGallery
            :images="product.images"
            :product-name="product.name"
            :is-new="product.is_new"
            :new-badge="labels.new_badge"
            :labels="labels"
        />

        <div class="lg:py-2">
            <p v-if="product.brand" class="text-sm uppercase tracking-[0.16em] text-text-muted">{{ product.brand }}</p>
            <div class="mt-2 flex flex-wrap items-start gap-3">
                <h1 class="text-2xl font-semibold tracking-tight lg:text-[2rem]">{{ product.name }}</h1>
                <span
                    v-if="inCart"
                    class="mt-1 inline-flex items-center gap-1.5 border border-primary-DEFAULT bg-primary-DEFAULT/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-primary-DEFAULT"
                >
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12l5 5L20 7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{ labels.in_cart }}
                </span>
            </div>

            <div
                v-if="inCart"
                class="mt-4 flex flex-col gap-2 rounded-lg border border-primary-DEFAULT/30 bg-primary-DEFAULT/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-sm text-text-DEFAULT">
                    <p class="font-medium">{{ labels.in_cart_notice }}</p>
                    <p v-if="selectedVariantCartLine" class="mt-1 text-text-muted">
                        {{ formatLabel(labels.in_cart_variant, {
                            label: selectedVariantCartLine.variant_label || labels.size,
                            count: selectedVariantCartLine.quantity,
                        }) }}
                    </p>
                    <p v-else-if="cartLines.length > 1" class="mt-1 text-text-muted">
                        {{ formatLabel(labels.in_cart_total, { count: cartTotalQty }) }}
                    </p>
                </div>
                <button
                    type="button"
                    class="shrink-0 text-sm font-medium text-primary-DEFAULT underline-offset-2 hover:underline"
                    @click="openCart"
                >
                    {{ labels.view_cart }}
                </button>
            </div>

            <div class="mt-4 flex items-baseline gap-3">
                <p class="text-2xl font-semibold">{{ selectedVariant?.price_formatted }}</p>
                <p v-if="selectedVariant?.compare_at_formatted" class="text-sm text-text-muted line-through">
                    {{ selectedVariant.compare_at_formatted }}
                </p>
            </div>

            <p v-if="product.short_description" class="mt-4 text-sm leading-relaxed text-text-muted">
                {{ product.short_description }}
            </p>

            <div v-if="product.colors.length" class="mt-8">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                    {{ labels.color }}<span v-if="selectedVariant?.color_label">: {{ selectedVariant.color_label }}</span>
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        v-for="color in product.colors"
                        :key="color.id"
                        type="button"
                        class="size-9 rounded-full border-2 transition hover:scale-105"
                        :class="selectedColorId === color.id ? 'border-primary-DEFAULT ring-2 ring-primary-DEFAULT ring-offset-2' : 'border-border-DEFAULT'"
                        :style="{ backgroundColor: color.hex }"
                        :title="color.label"
                        @click="selectedColorId = color.id"
                    />
                </div>
            </div>

            <div v-if="sizes.length" class="mt-8">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ labels.size }}</p>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        v-for="size in sizes"
                        :key="size.variant_id"
                        type="button"
                        class="min-w-[3rem] border px-4 py-2.5 text-sm transition"
                        :class="selectedVariantId === size.variant_id
                            ? 'border-primary-DEFAULT bg-primary-DEFAULT text-text-inverse'
                            : 'border-border-DEFAULT hover:border-primary-DEFAULT'"
                        @click="selectedVariantId = size.variant_id"
                    >
                        {{ size.label }}
                    </button>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-4">
                <label class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ labels.quantity }}</label>
                <div class="flex border border-border-DEFAULT">
                    <button type="button" class="px-3 py-2" @click="quantity = Math.max(1, quantity - 1)">−</button>
                    <span class="min-w-[2.5rem] px-3 py-2 text-center text-sm">{{ quantity }}</span>
                    <button type="button" class="px-3 py-2" @click="quantity++">+</button>
                </div>
            </div>

            <p v-if="error" class="mt-3 text-sm text-sale-DEFAULT">{{ error }}</p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <button
                    v-if="selectedVariantCartLine"
                    type="button"
                    class="min-h-[3.25rem] flex-1 border border-primary-DEFAULT bg-surface-DEFAULT px-8 text-sm font-medium text-primary-DEFAULT transition hover:bg-primary-DEFAULT/5"
                    @click="openCart"
                >
                    {{ labels.in_cart }}
                </button>
                <button
                    type="button"
                    class="min-h-[3.25rem] flex-1 px-8 text-sm font-medium transition disabled:opacity-60"
                    :class="selectedVariantCartLine
                        ? 'border border-border-DEFAULT bg-surface-DEFAULT text-text-DEFAULT hover:border-primary-DEFAULT'
                        : 'bg-primary-DEFAULT text-text-inverse hover:bg-primary-hover'"
                    :disabled="adding"
                    @click="addToCart"
                >
                    {{ adding ? labels.adding : (selectedVariantCartLine ? labels.add_another : labels.add_to_cart) }}
                </button>
                <a
                    :href="product.consultation_url"
                    class="inline-flex min-h-[3.25rem] flex-1 items-center justify-center border border-border-DEFAULT px-8 text-sm font-medium transition hover:border-primary-DEFAULT sm:max-w-[14rem]"
                >
                    {{ labels.consultation }}
                </a>
            </div>

        </div>
    </div>

    <div
        v-if="product.similar_products?.length || product.detail_items?.length || product.description || product.fit_description || product.fabric_description"
        class="mt-12 space-y-12"
    >
        <ProductCharacteristics
            v-if="product.detail_items?.length || product.description || product.fit_description || product.fabric_description"
            :product="product"
            :labels="labels"
        />
        <ProductSimilarProducts
            :products="product.similar_products ?? []"
            :labels="labels"
        />
    </div>
</template>
