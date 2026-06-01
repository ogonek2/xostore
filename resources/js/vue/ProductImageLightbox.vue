<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    images: { type: Array, required: true },
    initialIndex: { type: Number, default: 0 },
    labels: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close']);

const index = ref(props.initialIndex);
const scale = ref(1);
const panX = ref(0);
const panY = ref(0);
const dragging = ref(false);
const dragStart = ref({ x: 0, y: 0, panX: 0, panY: 0 });

const current = computed(() => props.images[index.value] ?? null);
const canPrev = computed(() => index.value > 0);
const canNext = computed(() => index.value < props.images.length - 1);

const imageStyle = computed(() => ({
    transform: `translate(${panX.value}px, ${panY.value}px) scale(${scale.value})`,
}));

function resetTransform() {
    scale.value = 1;
    panX.value = 0;
    panY.value = 0;
}

function close() {
    emit('close');
}

function prev() {
    if (!canPrev.value) return;
    index.value -= 1;
    resetTransform();
}

function next() {
    if (!canNext.value) return;
    index.value += 1;
    resetTransform();
}

function zoomIn() {
    scale.value = Math.min(4, scale.value + 0.35);
}

function zoomOut() {
    scale.value = Math.max(1, scale.value - 0.35);
    if (scale.value === 1) {
        panX.value = 0;
        panY.value = 0;
    }
}

function onWheel(event) {
    event.preventDefault();
    if (event.deltaY < 0) {
        zoomIn();
    } else {
        zoomOut();
    }
}

function onPointerDown(event) {
    if (scale.value <= 1) return;
    dragging.value = true;
    dragStart.value = {
        x: event.clientX,
        y: event.clientY,
        panX: panX.value,
        panY: panY.value,
    };
}

function onPointerMove(event) {
    if (!dragging.value) return;
    panX.value = dragStart.value.panX + (event.clientX - dragStart.value.x);
    panY.value = dragStart.value.panY + (event.clientY - dragStart.value.y);
}

function onPointerUp() {
    dragging.value = false;
}

function onKeydown(event) {
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') prev();
    if (event.key === 'ArrowRight') next();
    if (event.key === '+' || event.key === '=') zoomIn();
    if (event.key === '-') zoomOut();
}

watch(() => props.initialIndex, (value) => {
    index.value = value;
    resetTransform();
});

onMounted(() => {
    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', onKeydown);
    window.addEventListener('pointerup', onPointerUp);
});

onUnmounted(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
    window.removeEventListener('pointerup', onPointerUp);
});
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 z-[100] flex flex-col bg-black/95"
            role="dialog"
            aria-modal="true"
            :aria-label="labels.gallery ?? 'Gallery'"
            @click.self="close"
        >
            <div class="flex items-center justify-between gap-4 px-4 py-3 text-white">
                <p class="text-sm tabular-nums text-white/80">
                    {{ index + 1 }} / {{ images.length }}
                </p>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="flex size-10 items-center justify-center rounded-full border border-white/25 transition hover:bg-white/10"
                        :aria-label="labels.zoom_out ?? 'Zoom out'"
                        @click.stop="zoomOut"
                    >
                        <span class="text-lg leading-none">−</span>
                    </button>
                    <button
                        type="button"
                        class="flex size-10 items-center justify-center rounded-full border border-white/25 transition hover:bg-white/10"
                        :aria-label="labels.zoom_in ?? 'Zoom in'"
                        @click.stop="zoomIn"
                    >
                        <span class="text-lg leading-none">+</span>
                    </button>
                    <button
                        type="button"
                        class="flex size-10 items-center justify-center rounded-full border border-white/25 transition hover:bg-white/10"
                        :aria-label="labels.close ?? 'Close'"
                        @click.stop="close"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="relative flex min-h-0 flex-1 items-center justify-center px-14">
                <button
                    v-if="canPrev"
                    type="button"
                    class="absolute left-2 top-1/2 z-10 flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 text-white transition hover:bg-white/10 lg:left-4"
                    :aria-label="labels.prev ?? 'Previous'"
                    @click.stop="prev"
                >
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="M14 6l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                <div
                    class="flex max-h-full max-w-full touch-none items-center justify-center overflow-hidden"
                    @wheel.prevent="onWheel"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                >
                    <img
                        v-if="current"
                        :src="current.url"
                        :alt="current.alt"
                        class="max-h-[calc(100vh-8rem)] max-w-[min(100vw-7rem,72rem)] select-none object-contain transition-transform duration-100"
                        :class="scale > 1 ? (dragging ? 'cursor-grabbing' : 'cursor-grab') : 'cursor-zoom-in'"
                        :style="imageStyle"
                        draggable="false"
                        @dblclick.stop="scale > 1 ? resetTransform() : (scale = 2)"
                        @click.stop
                    >
                </div>

                <button
                    v-if="canNext"
                    type="button"
                    class="absolute right-2 top-1/2 z-10 flex size-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 text-white transition hover:bg-white/10 lg:right-4"
                    :aria-label="labels.next ?? 'Next'"
                    @click.stop="next"
                >
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="M10 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div v-if="images.length > 1" class="flex justify-center gap-2 overflow-x-auto px-4 pb-4">
                <button
                    v-for="(image, i) in images"
                    :key="i"
                    type="button"
                    class="h-14 w-11 shrink-0 overflow-hidden rounded border-2 transition"
                    :class="i === index ? 'border-white' : 'border-white/20 opacity-70 hover:opacity-100'"
                    @click.stop="index = i; resetTransform()"
                >
                    <img :src="image.url" :alt="image.alt" class="size-full object-cover">
                </button>
            </div>
        </div>
    </Teleport>
</template>
