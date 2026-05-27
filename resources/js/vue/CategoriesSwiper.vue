<script setup>
import { ref } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { FreeMode, Mousewheel } from 'swiper/modules';
import 'swiper/css';

defineProps({
    cards: { type: Array, required: true },
    title: { type: String, required: true },
    viewAll: { type: String, required: true },
});

const modules = [FreeMode, Mousewheel];

const swiperInstance = ref(null);
const atStart = ref(true);
const atEnd = ref(false);

function syncEdges(swiper) {
    const instance = swiper ?? swiperInstance.value;
    if (!instance) return;

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
</script>

<template>
    <section class="overflow-x-clip bg-primary-DEFAULT text-text-inverse">
        <div class="mx-auto max-w-[90rem] px-5 lg:px-8">
            <div class="flex flex-col gap-6 border-b border-white/10 py-12 lg:flex-row lg:items-end lg:justify-between lg:py-16">
                <div>
                    <p class="mb-3 text-[10px] font-medium uppercase tracking-[0.4em] text-white/40">
                        XO Store
                    </p>
                    <h2 class="text-3xl font-light tracking-tight lg:text-[2.5rem] lg:leading-none">
                        {{ title }}
                    </h2>
                </div>

                <div class="flex items-center gap-6 lg:gap-8">
                    <a
                        href="#"
                        class="text-[10px] font-medium uppercase tracking-[0.28em] text-white/70 transition-colors hover:text-white"
                    >
                        {{ viewAll }}
                    </a>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="flex size-10 items-center justify-center border border-white/20 text-white/70 transition-colors hover:border-white/50 hover:text-white disabled:pointer-events-none disabled:opacity-25"
                            :disabled="atStart"
                            aria-label="Previous"
                            @click="goPrev"
                        >
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M15 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="flex size-10 items-center justify-center border border-white/20 text-white/70 transition-colors hover:border-white/50 hover:text-white disabled:pointer-events-none disabled:opacity-25"
                            :disabled="atEnd"
                            aria-label="Next"
                            @click="goNext"
                        >
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="py-10 lg:py-14">
                <Swiper
                    :modules="modules"
                    :slides-per-view="'auto'"
                    :space-between="20"
                    :free-mode="{ enabled: true, momentum: true, momentumRatio: 0.4 }"
                    :mousewheel="{ forceToAxis: true }"
                    :observer="true"
                    :observe-parents="true"
                    :breakpoints="{
                        640: { spaceBetween: 24 },
                        1024: { spaceBetween: 32 },
                    }"
                    class="categories-swiper overflow-hidden"
                    @swiper="onSwiper"
                    @slide-change="syncEdges"
                    @reach-beginning="syncEdges"
                    @reach-end="syncEdges"
                    @from-edge="syncEdges"
                    @resize="syncEdges"
                >
                    <SwiperSlide
                        v-for="(card, index) in cards"
                        :key="index"
                        class="!h-auto !w-[min(68vw,240px)] shrink-0 sm:!w-[260px] lg:!w-[300px]"
                    >
                        <a :href="card.url" class="group block min-w-0">
                            <div class="relative aspect-[4/5] overflow-hidden bg-white/[0.04]">
                                <img
                                    :src="card.image"
                                    :alt="card.alt"
                                    class="size-full object-cover object-top transition-transform duration-[1.4s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.03]"
                                    loading="lazy"
                                />
                            </div>

                            <div class="mt-5 flex items-end justify-between gap-4 border-t border-white/10 pt-4">
                                <div class="min-w-0">
                                    <p class="truncate text-[10px] uppercase tracking-[0.32em] text-white/45">
                                        {{ card.label }}
                                    </p>
                                    <p
                                        v-if="card.sublabel"
                                        class="mt-1.5 truncate text-sm font-light tracking-wide text-white/90"
                                    >
                                        {{ card.sublabel }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 text-sm text-white/30 transition-all duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-white/80"
                                    aria-hidden="true"
                                >↗</span>
                            </div>
                        </a>
                    </SwiperSlide>
                </Swiper>
            </div>
        </div>
    </section>
</template>

<style scoped>
.categories-swiper :deep(.swiper-wrapper) {
    align-items: stretch;
}
</style>
