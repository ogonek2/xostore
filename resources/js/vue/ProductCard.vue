<script setup>
defineProps({
    product: { type: Object, required: true },
    labels: { type: Object, required: true },
});

function openCart(event) {
    event.preventDefault();
    event.stopPropagation();
    window.dispatchEvent(new Event('cart:open'));
}
</script>

<template>
    <article
        :data-product-id="product.product_id"
        data-product-card
        class="group/product-card flex min-w-0 max-w-full flex-col"
    >
        <a :href="product.url" class="flex flex-col">
            <div
                class="relative aspect-[4/5] overflow-hidden rounded-t-2xl bg-[#eceae6] ring-inset ring-1 ring-transparent group-[.is-in-cart]/product-card:ring-primary-DEFAULT"
            >
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
                    class="absolute left-3 top-3 z-[2] flex size-9 items-center justify-center rounded-full bg-white text-primary-DEFAULT shadow-sm transition-transform hover:scale-105 group-[.is-in-cart]/product-card:hidden"
                    :aria-label="labels.cart_label"
                    @click="openCart"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M6 6h15l-1.5 9h-12L6 6z" stroke-linejoin="round" />
                        <path d="M6 6L5 3H2" stroke-linecap="round" />
                        <circle cx="9" cy="20" r="1" fill="currentColor" stroke="none" />
                        <circle cx="18" cy="20" r="1" fill="currentColor" stroke="none" />
                    </svg>
                </button>

                <span
                    class="absolute left-3 top-3 z-[2] hidden size-9 items-center justify-center rounded-full bg-primary-DEFAULT text-text-inverse group-[.is-in-cart]/product-card:flex"
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
                <p v-if="product.category" class="mt-1 text-sm text-text-muted">
                    {{ product.category }}
                </p>
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
                v-for="(hex, index) in product.colors"
                :key="index"
                role="listitem"
                class="size-5 rounded-full border border-border-DEFAULT"
                :style="{ backgroundColor: hex }"
                :title="hex"
            />
        </div>
    </article>
</template>
