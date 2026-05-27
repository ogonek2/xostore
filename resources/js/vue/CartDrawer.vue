<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { dispatchCartState } from '../shop/cart-badges';

const props = defineProps({
    locale: { type: String, required: true },
    labels: { type: Object, required: true },
    routes: { type: Object, required: true },
});

const open = ref(false);
const loading = ref(false);
const cart = ref({ items: [], count: 0, subtotal_formatted: '' });

async function fetchCart() {
    loading.value = true;
    try {
        const response = await fetch(props.routes.show, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (response.ok) cart.value = await response.json();
    } finally {
        loading.value = false;
        dispatchCartState(cart.value);
    }
}

async function updateQty(item, quantity) {
    loading.value = true;
    try {
        const response = await fetch(props.routes.update.replace('__ITEM__', item.id), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({ quantity }),
        });
        if (response.ok) cart.value = await response.json();
    } finally {
        loading.value = false;
        dispatchCartState(cart.value);
    }
}

async function removeItem(item) {
    loading.value = true;
    try {
        const response = await fetch(props.routes.destroy.replace('__ITEM__', item.id), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        });
        if (response.ok) cart.value = await response.json();
    } finally {
        loading.value = false;
        dispatchCartState(cart.value);
    }
}

function openDrawer() {
    open.value = true;
    document.body.style.overflow = 'hidden';
    fetchCart();
}

function closeDrawer() {
    open.value = false;
    document.body.style.overflow = '';
}

function onKeydown(event) {
    if (event.key === 'Escape') closeDrawer();
}

onMounted(() => {
    window.addEventListener('cart:open', openDrawer);
    window.addEventListener('cart:refresh', fetchCart);
    document.addEventListener('keydown', onKeydown);
    fetchCart();
});

onUnmounted(() => {
    window.removeEventListener('cart:open', openDrawer);
    window.removeEventListener('cart:refresh', fetchCart);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            class="fixed inset-0 z-[60] flex justify-end bg-black/40"
            @click.self="closeDrawer"
        >
            <aside
                class="flex h-full w-full max-w-md flex-col bg-surface-DEFAULT shadow-xl"
                role="dialog"
                aria-modal="true"
                :aria-label="labels.title"
            >
                <div class="flex items-center justify-between border-b border-border-DEFAULT px-6 py-5">
                    <h2 class="text-lg font-semibold">{{ labels.title }}</h2>
                    <button type="button" class="text-text-muted hover:text-primary-DEFAULT" @click="closeDrawer">✕</button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <p v-if="loading && !cart.items.length" class="text-sm text-text-muted">{{ labels.loading }}</p>
                    <p v-else-if="!cart.items.length" class="py-12 text-center text-sm text-text-muted">{{ labels.empty }}</p>

                    <ul v-else class="space-y-6">
                        <li v-for="item in cart.items" :key="item.id" class="flex gap-4">
                            <a :href="item.url" class="h-24 w-20 shrink-0 overflow-hidden bg-[#eceae6]">
                                <img :src="item.image" :alt="item.name" class="size-full object-cover">
                            </a>
                            <div class="min-w-0 flex-1">
                                <a :href="item.url" class="text-sm font-medium leading-snug hover:underline">{{ item.name }}</a>
                                <p v-if="item.variant_label" class="mt-1 text-xs text-text-muted">{{ item.variant_label }}</p>
                                <p class="mt-2 text-sm font-semibold">{{ item.price_formatted }}</p>
                                <div class="mt-3 flex items-center gap-3">
                                    <div class="flex border border-border-DEFAULT">
                                        <button type="button" class="px-2 py-1 text-sm" @click="updateQty(item, item.quantity - 1)">−</button>
                                        <span class="min-w-[2rem] px-2 py-1 text-center text-sm">{{ item.quantity }}</span>
                                        <button type="button" class="px-2 py-1 text-sm" @click="updateQty(item, item.quantity + 1)">+</button>
                                    </div>
                                    <button type="button" class="text-xs text-text-muted underline" @click="removeItem(item)">{{ labels.remove }}</button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div v-if="cart.items.length" class="border-t border-border-DEFAULT px-6 py-5">
                    <div class="mb-4 flex justify-between text-sm">
                        <span class="text-text-muted">{{ labels.subtotal }}</span>
                        <span class="font-semibold">{{ cart.subtotal_formatted }}</span>
                    </div>
                    <a
                        :href="routes.checkout"
                        class="mb-3 flex min-h-[3rem] w-full items-center justify-center bg-primary-DEFAULT text-sm font-medium text-text-inverse hover:bg-primary-hover"
                    >
                        {{ labels.checkout }}
                    </a>
                    <button type="button" class="w-full text-center text-sm text-text-muted underline" @click="closeDrawer">
                        {{ labels.continue }}
                    </button>
                </div>
            </aside>
        </div>
    </Teleport>
</template>
