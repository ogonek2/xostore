<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const open = ref(false);

function toggle() {
    open.value = !open.value;
}

function onKeydown(event) {
    if (event.key === 'Escape') open.value = false;
}

onMounted(() => {
    window.addEventListener('cart:open', toggle);
    document.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('cart:open', toggle);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 sm:items-center"
            @click.self="open = false"
        >
            <div
                class="bg-surface w-full max-w-lg rounded-t-2xl p-6 shadow-xl sm:rounded-2xl"
                role="dialog"
                aria-modal="true"
                aria-label="Koszyk"
            >
                <slot />
            </div>
        </div>
    </Teleport>
</template>
