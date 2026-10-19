<script setup>
import { ref } from 'vue';

const props = defineProps({
    images: { type: Array, default: () => [] },
    title: { type: String, default: '' },
    compact: { type: Boolean, default: false },
});

const activeIndex = ref(null);

function openAt(index) {
    activeIndex.value = index;
}

function close() {
    activeIndex.value = null;
}

function prev() {
    if (activeIndex.value === null || props.images.length < 2) {
        return;
    }

    activeIndex.value = (activeIndex.value - 1 + props.images.length) % props.images.length;
}

function next() {
    if (activeIndex.value === null || props.images.length < 2) {
        return;
    }

    activeIndex.value = (activeIndex.value + 1) % props.images.length;
}
</script>

<template>
    <section v-if="images?.length" :class="compact ? 'mt-3' : 'mt-10'">
        <h2
            v-if="!compact && title"
            class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted"
        >
            {{ title }}
        </h2>

        <div
            class="flex gap-2 overflow-x-auto"
            :class="compact ? 'mt-0' : 'mt-4'"
            role="list"
        >
            <button
                v-for="(image, index) in images"
                :key="`${image.thumb_url}-${index}`"
                type="button"
                role="listitem"
                class="shrink-0 overflow-hidden border border-border-DEFAULT bg-[#eceae6] transition hover:border-primary-DEFAULT"
                :class="compact ? 'size-10 rounded-md' : 'size-20 rounded-lg sm:size-24'"
                @click.prevent="openAt(index)"
            >
                <img
                    :src="image.thumb_url || image.url"
                    :alt="image.alt || title"
                    class="size-full object-cover"
                    loading="lazy"
                >
            </button>
        </div>

        <Teleport to="body">
            <div
                v-if="activeIndex !== null"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-black/80 p-4"
                role="dialog"
                aria-modal="true"
                @click.self="close"
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 rounded-full bg-white/90 px-3 py-1 text-sm"
                    @click="close"
                >
                    ✕
                </button>
                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 px-3 py-2"
                    @click="prev"
                >
                    ‹
                </button>
                <img
                    :src="images[activeIndex].url"
                    :alt="images[activeIndex].alt || title"
                    class="max-h-[85vh] max-w-full object-contain"
                >
                <button
                    v-if="images.length > 1"
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 px-3 py-2"
                    @click="next"
                >
                    ›
                </button>
            </div>
        </Teleport>
    </section>
</template>
