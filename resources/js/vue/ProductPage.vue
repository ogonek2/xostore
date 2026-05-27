<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    product: { type: Object, required: true },
    labels: { type: Object, required: true },
    routes: { type: Object, required: true },
});

const selectedColorId = ref(props.product.selected_color_id);
const selectedVariantId = ref(props.product.default_variant_id);
const quantity = ref(1);
const activeImage = ref(props.product.images[0]?.url ?? '');
const activeTab = ref('details');
const adding = ref(false);
const error = ref('');

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

function selectImage(url) {
    activeImage.value = url;
}

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
        <div class="space-y-4">
            <div class="relative aspect-[4/5] overflow-hidden rounded-2xl bg-[#eceae6]">
                <img :src="activeImage" :alt="product.name" class="size-full object-cover object-center">
                <span
                    v-if="product.is_new"
                    class="absolute left-4 top-4 bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]"
                >
                    {{ labels.new_badge }}
                </span>
            </div>
            <div v-if="product.images.length > 1" class="flex gap-2 overflow-x-auto pb-1">
                <button
                    v-for="(image, index) in product.images"
                    :key="index"
                    type="button"
                    class="h-20 w-16 shrink-0 overflow-hidden rounded-lg border-2 bg-[#eceae6] transition"
                    :class="activeImage === image.url ? 'border-primary-DEFAULT' : 'border-transparent'"
                    @click="selectImage(image.url)"
                >
                    <img :src="image.url" :alt="image.alt" class="size-full object-cover">
                </button>
            </div>
        </div>

        <div class="lg:py-2">
            <p v-if="product.brand" class="text-sm uppercase tracking-[0.16em] text-text-muted">{{ product.brand }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight lg:text-[2rem]">{{ product.name }}</h1>

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
                    type="button"
                    class="min-h-[3.25rem] flex-1 bg-primary-DEFAULT px-8 text-sm font-medium text-text-inverse transition hover:bg-primary-hover disabled:opacity-60"
                    :disabled="adding"
                    @click="addToCart"
                >
                    {{ adding ? labels.adding : labels.add_to_cart }}
                </button>
                <a
                    :href="product.consultation_url"
                    class="inline-flex min-h-[3.25rem] flex-1 items-center justify-center border border-border-DEFAULT px-8 text-sm font-medium transition hover:border-primary-DEFAULT"
                >
                    {{ labels.consultation }}
                </a>
            </div>

            <div class="mt-12 border-t border-border-DEFAULT pt-8">
                <div class="flex gap-6 border-b border-border-DEFAULT">
                    <button
                        v-for="tab in ['details', 'fit', 'fabric']"
                        :key="tab"
                        type="button"
                        class="pb-3 text-sm font-medium uppercase tracking-[0.14em] transition"
                        :class="activeTab === tab ? 'border-b-2 border-primary-DEFAULT text-primary-DEFAULT' : 'text-text-muted'"
                        @click="activeTab = tab"
                    >
                        {{ labels['tab_' + tab] }}
                    </button>
                </div>
                <div class="prose prose-sm mt-6 max-w-none text-text-muted">
                    <div v-show="activeTab === 'details'" v-html="product.description || labels.no_details" />
                    <div v-show="activeTab === 'fit'" v-html="product.fit_description || labels.no_details" />
                    <div v-show="activeTab === 'fabric'" v-html="product.fabric_description || labels.no_details" />
                </div>
            </div>
        </div>
    </div>
</template>
