<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { FreeMode, Mousewheel } from 'swiper/modules';
import 'swiper/css';
import ProductCard from './ProductCard.vue';
import { refreshCartProductBadges } from '../shop/cart-badges';

defineProps({
    products: { type: Array, default: () => [] },
    labels: { type: Object, required: true },
});

const modules = [FreeMode, Mousewheel];

const swiperInstance = ref(null);
const atStart = ref(true);
const atEnd = ref(false);

function syncEdges(swiper) {
    const instance = swiper ?? swiperInstance.value;
    if (!instance) {
        return;
    }

    atStart.value = instance.isBeginning;
    atEnd.value = instance.isEnd;
}

function onSwiper(swiper) {
    swiperInstance.value = swiper;
    syncEdges(swiper);
}

function goPrev() {
    swiperInstance.value?.slidePrev();
}

function goNext() {
    swiperInstance.value?.slideNext();
}

onMounted(() => {
    nextTick(() => refreshCartProductBadges());
});
</script>

<template>
    <section v-if="products.length" class="min-w-0">
        <div class="flex items-end justify-between gap-4 border-b border-border-DEFAULT pb-4">
            <h2 class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ labels.similar_products }}
            </h2>
            <div class="flex shrink-0 items-center gap-2">
                <button
                    type="button"
                    class="flex size-9 items-center justify-center border border-border-DEFAULT text-text-muted transition hover:border-primary-DEFAULT hover:text-primary-DEFAULT disabled:pointer-events-none disabled:opacity-30"
                    :disabled="atStart"
                    :aria-label="labels.prev"
                    @click="goPrev"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="flex size-9 items-center justify-center border border-border-DEFAULT text-text-muted transition hover:border-primary-DEFAULT hover:text-primary-DEFAULT disabled:pointer-events-none disabled:opacity-30"
                    :disabled="atEnd"
                    :aria-label="labels.next"
                    @click="goNext"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        <Swiper
            :modules="modules"
            :slides-per-view="'auto'"
            :space-between="20"
            :free-mode="{ enabled: true, momentum: true, momentumRatio: 0.35 }"
            :mousewheel="{ forceToAxis: true }"
            :observer="true"
            :observe-parents="true"
            :breakpoints="{
                640: { spaceBetween: 24 },
                1024: { spaceBetween: 28 },
            }"
            class="product-similar-swiper swiper mt-6 overflow-hidden"
            @swiper="onSwiper"
            @slide-change="syncEdges"
            @reach-beginning="syncEdges"
            @reach-end="syncEdges"
            @from-edge="syncEdges"
            @resize="syncEdges"
        >
            <SwiperSlide
                v-for="item in products"
                :key="item.product_id"
                class="!h-auto !w-[min(72vw,220px)] shrink-0 sm:!w-[240px] lg:!w-[260px]"
            >
                <ProductCard :product="item" :labels="labels" />
            </SwiperSlide>
        </Swiper>
    </section>
</template>

<style scoped>
.product-similar-swiper :deep(.swiper-wrapper) {
    align-items: stretch;
}
</style>
