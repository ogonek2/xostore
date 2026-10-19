<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import ProductGallery from './ProductGallery.vue';
import ProductCharacteristics from './ProductCharacteristics.vue';
import ProductSimilarProducts from './ProductSimilarProducts.vue';
import ProductSizeChart from './ProductSizeChart.vue';
import ProductReviews from './ProductReviews.vue';
import ProductCommerceInfo from './ProductCommerceInfo.vue';
import { productCartLines } from '../shop/cart-badges';
import { addVariantToCart } from '../shop/cart-api';
import { viewItem } from '../shop/ga4-ecommerce';

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

const variants = computed(() => props.product.variants ?? []);
const colorProducts = computed(() => props.product.color_products ?? []);
const hasMultipleColorProducts = computed(() => colorProducts.value.length > 1);

const sizes = computed(() => {
    if (hasMultipleColorProducts.value || (props.product.colors?.length ?? 0) <= 1) {
        return props.product.sizes ?? [];
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
    variants.value.find((v) => v.id === selectedVariantId.value) ?? null,
);

const currentColorProduct = computed(() =>
    colorProducts.value.find((c) => c.is_current) ?? null,
);

const inCart = computed(() => cartLines.value.length > 0);

const cartTotalQty = computed(() =>
    cartLines.value.reduce((sum, line) => sum + line.quantity, 0),
);

const selectedVariantCartLine = computed(() =>
    cartLines.value.find((line) => line.variant_id === selectedVariantId.value) ?? null,
);

const hasDetailsSection = computed(() =>
    (props.product.detail_items?.length ?? 0) > 0
    || props.product.description
    || props.product.fit_description
    || props.product.fabric_description
    || props.product.size_chart
    || (props.product.review_images?.length ?? 0) > 0,
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

    const variant = selectedVariant.value;
    viewItem({
        product_id: props.product.id,
        name: props.product.display_name || props.product.name,
        brand: props.product.brand,
        category: props.product.category,
        price: variant?.price ?? props.product.variants?.[0]?.price ?? 0,
        variant_label: variant?.size || variant?.sku || undefined,
        quantity: 1,
    });
});

onUnmounted(() => {
    window.removeEventListener('cart:updated', onCartUpdated);
});

watch(selectedColorId, (colorId) => {
    if (hasMultipleColorProducts.value) {
        return;
    }

    const color = props.product.colors?.find((c) => c.id === colorId);
    if (color) {
        const url = new URL(window.location.href);
        url.searchParams.set('color', color.code);
        history.replaceState({}, '', url);
    }

    const first = variants.value.find((v) => v.color_id === colorId);
    if (first) {
        selectedVariantId.value = first.id;
    }
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
        const data = await addVariantToCart(selectedVariantId.value, {
            quantity: quantity.value,
            openDrawer: true,
        });
        applyCartItems(data.items ?? []);
    } catch (err) {
        error.value = err?.message ?? props.labels.error;
    } finally {
        adding.value = false;
    }
}
</script>

<template>
    <div class="grid min-w-0 gap-10 lg:grid-cols-2 lg:gap-14">
        <ProductGallery
            :images="product.images"
            :product-name="product.name"
            :is-new="product.is_new"
            :new-badge="labels.new_badge"
            :labels="labels"
        />

        <div class="lg:py-2">
            <p v-if="product.brand" class="text-sm uppercase tracking-[0.16em] text-text-muted">{{ product.brand }}</p>
            <p v-if="product.category" class="mt-1 text-xs uppercase tracking-[0.14em] text-text-muted">
                {{ product.category }}
            </p>
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

            <ul v-if="product.highlights?.length" class="mt-3 flex flex-wrap gap-2">
                <li
                    v-for="badge in product.highlights"
                    :key="badge.key"
                    class="rounded-full border border-border-DEFAULT px-3 py-1 text-[11px] font-medium uppercase tracking-[0.12em] text-text-muted"
                >
                    {{ badge.label }}
                </li>
            </ul>

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

            <div v-if="hasMultipleColorProducts" class="mt-8">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                    {{ labels.colors }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a
                        v-for="color in colorProducts"
                        :key="color.id"
                        :href="color.url"
                        class="size-9 rounded-full border-2 transition hover:scale-105"
                        :class="color.is_current
                            ? 'border-primary-DEFAULT ring-2 ring-primary-DEFAULT ring-offset-2'
                            : 'border-border-DEFAULT'"
                        :style="{ backgroundColor: color.hex || '#e5e5e5' }"
                        :title="color.label"
                        :aria-current="color.is_current ? 'true' : undefined"
                    />
                </div>
                <p v-if="currentColorProduct" class="mt-2 text-sm text-text-muted">
                    {{ labels.color }}: {{ currentColorProduct.label }}
                </p>
            </div>

            <div v-else-if="product.colors?.length" class="mt-8">
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
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">{{ labels.size }}</p>
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
                    v-if="product.whatsapp_url"
                    :href="product.whatsapp_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    data-whatsapp-cta
                    data-whatsapp-location="product_page"
                    :data-product-id="product.id"
                    :data-product-name="product.display_name || product.name"
                    class="inline-flex min-h-[3.25rem] flex-1 items-center justify-center gap-2 border border-[#25D366] bg-[#25D366] px-8 text-sm font-medium text-white transition hover:brightness-95 sm:max-w-[16rem]"
                >
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12.04 2c-5.5 0-9.96 4.45-9.96 9.94 0 1.75.46 3.45 1.34 4.95L2 22l5.27-1.38a9.93 9.93 0 0 0 4.77 1.21h.01c5.49 0 9.95-4.45 9.95-9.94C22 6.45 17.53 2 12.04 2zm5.79 14.22c-.24.68-1.4 1.25-1.93 1.33-.52.08-1.03.12-3.33-.71-2.78-1-4.56-3.61-4.7-3.78-.13-.17-1.12-1.49-1.12-2.84 0-1.35.71-2.02.96-2.3.25-.27.54-.34.72-.34h.52c.17 0 .4-.06.62.47.24.58.81 2 .88 2.15.07.15.12.32.02.52-.1.2-.15.32-.3.5-.15.17-.31.38-.44.51-.15.15-.3.31-.13.6.17.3.76 1.25 1.63 2.02 1.12 1 2.07 1.31 2.36 1.46.3.15.47.12.64-.07.17-.2.74-.86.94-1.16.2-.3.4-.25.67-.15.27.1 1.72.81 2.01.96.3.15.5.22.57.34.08.12.08.7-.16 1.38z" />
                    </svg>
                    {{ labels.whatsapp }}
                </a>
                <a
                    v-else
                    :href="product.consultation_url"
                    class="inline-flex min-h-[3.25rem] flex-1 items-center justify-center border border-border-DEFAULT px-8 text-sm font-medium transition hover:border-primary-DEFAULT sm:max-w-[14rem]"
                >
                    {{ labels.consultation }}
                </a>
            </div>

            <ProductCommerceInfo
                :commerce="product.commerce"
                :labels="labels"
            />
        </div>
    </div>

    <div
        v-if="product.similar_products?.length || hasDetailsSection"
        class="mt-12 space-y-12"
    >
        <ProductReviews
            :images="product.review_images ?? []"
            :title="labels.reviews"
        />
        <ProductCharacteristics
            v-if="product.detail_items?.length || product.description || product.fit_description || product.fabric_description"
            :product="product"
            :labels="labels"
        />
        <ProductSizeChart
            v-if="product.size_chart"
            :chart="product.size_chart"
            :title="labels.size_chart_title"
        />
        <ProductSimilarProducts
            :products="product.similar_products ?? []"
            :labels="labels"
        />
    </div>
</template>
