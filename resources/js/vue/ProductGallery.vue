<script setup>
import { computed, ref } from 'vue';
import ProductImageLightbox from './ProductImageLightbox.vue';

const props = defineProps({
    images: { type: Array, required: true },
    productName: { type: String, required: true },
    isNew: { type: Boolean, default: false },
    newBadge: { type: String, default: '' },
    labels: { type: Object, default: () => ({}) },
});

const activeIndex = ref(0);
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);
const zoomActive = ref(false);
const cursor = ref({ x: 0, y: 0 });
const stageRef = ref(null);

const ZOOM = 2.25;

const activeImage = computed(() => props.images[activeIndex.value] ?? props.images[0] ?? null);

const zoomStyle = computed(() => {
    if (!zoomActive.value || !activeImage.value || !stageRef.value) {
        return {};
    }

    const el = stageRef.value;
    const w = el.clientWidth;
    const h = el.clientHeight;

    return {
        backgroundImage: `url(${activeImage.value.url})`,
        backgroundSize: `${w * ZOOM}px ${h * ZOOM}px`,
        backgroundPosition: `${-cursor.value.x * (ZOOM - 1)}px ${-cursor.value.y * (ZOOM - 1)}px`,
    };
});

function selectImage(index) {
    activeIndex.value = index;
}

function onStageMove(event) {
    const rect = event.currentTarget.getBoundingClientRect();
    cursor.value = {
        x: Math.max(0, Math.min(event.clientX - rect.left, rect.width)),
        y: Math.max(0, Math.min(event.clientY - rect.top, rect.height)),
    };
}

function openLightbox(index = activeIndex.value) {
    lightboxIndex.value = index;
    lightboxOpen.value = true;
}
</script>

<template>
    <div class="w-full min-w-0 max-w-full">
        <div class="flex w-full min-w-0 max-w-full gap-3 sm:gap-4">
        <div
            v-if="images.length > 1"
            class="hidden shrink-0 flex-col gap-2 sm:flex"
        >
            <button
                v-for="(image, index) in images"
                :key="index"
                type="button"
                class="h-[4.5rem] w-[3.5rem] overflow-hidden rounded-lg border-2 bg-[#eceae6] transition"
                :class="activeIndex === index ? 'border-primary-DEFAULT' : 'border-transparent hover:border-border-DEFAULT'"
                :aria-label="`${labels.thumbnail ?? 'Photo'} ${index + 1}`"
                @click="selectImage(index)"
            >
                <img
                    :src="image.thumb_url ?? image.url"
                    :alt="image.alt"
                    loading="lazy"
                    decoding="async"
                    class="size-full object-cover object-center"
                >
            </button>
        </div>

        <div class="min-w-0 flex-1">
            <div
                ref="stageRef"
                class="group relative aspect-[4/5] cursor-zoom-in overflow-hidden rounded-2xl bg-[#eceae6]"
                @mouseenter="zoomActive = true"
                @mouseleave="zoomActive = false"
                @mousemove="onStageMove"
                @click="openLightbox()"
            >
                <img
                    :src="activeImage?.url"
                    :alt="activeImage?.alt ?? productName"
                    fetchpriority="high"
                    decoding="async"
                    class="size-full object-cover object-center transition-opacity duration-150"
                    :class="zoomActive ? 'opacity-0' : 'opacity-100'"
                    draggable="false"
                >
                <div
                    v-show="zoomActive"
                    class="pointer-events-none absolute inset-0 bg-[#eceae6] bg-no-repeat"
                    :style="zoomStyle"
                    aria-hidden="true"
                />
                <span
                    v-if="isNew"
                    class="pointer-events-none absolute left-4 top-4 z-10 bg-surface-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em]"
                >
                    {{ newBadge }}
                </span>
            </div>

            <div
                v-if="images.length > 1"
                class="mt-3 flex w-full max-w-full gap-2 overflow-x-auto overscroll-x-contain pb-1 [-webkit-overflow-scrolling:touch] sm:hidden"
            >
                <button
                    v-for="(image, index) in images"
                    :key="index"
                    type="button"
                    class="h-20 w-16 shrink-0 overflow-hidden rounded-lg border-2 bg-[#eceae6] transition"
                    :class="activeIndex === index ? 'border-primary-DEFAULT' : 'border-transparent'"
                    @click="selectImage(index)"
                >
                    <img
                        :src="image.thumb_url ?? image.url"
                        :alt="image.alt"
                        loading="lazy"
                        decoding="async"
                        class="size-full object-cover"
                    >
                </button>
            </div>
        </div>
        </div>
    </div>

    <ProductImageLightbox
        v-if="lightboxOpen"
        :images="images"
        :initial-index="lightboxIndex"
        :labels="labels"
        @close="lightboxOpen = false"
    />
</template>
