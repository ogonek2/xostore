<script setup>
import { computed } from 'vue';

const props = defineProps({
    commerce: { type: Object, default: null },
    labels: { type: Object, default: () => ({}) },
    compact: { type: Boolean, default: false },
});

const delivery = computed(() => props.commerce?.delivery ?? null);
const payment = computed(() => props.commerce?.payment ?? null);

const hasContent = computed(() => Boolean(delivery.value || payment.value));
</script>

<template>
    <section v-if="hasContent" :class="compact ? 'mt-3 space-y-2' : 'mt-8 space-y-4 border-t border-border-DEFAULT pt-6'">
        <div v-if="delivery">
            <p
                class="font-medium text-primary-DEFAULT"
                :class="compact ? 'text-[11px] uppercase tracking-[0.14em] text-text-muted' : 'text-xs uppercase tracking-[0.18em] text-text-muted'"
            >
                {{ delivery.title || labels.delivery_title }}
            </p>
            <p
                v-if="delivery.summary"
                class="mt-1 text-text-DEFAULT"
                :class="compact ? 'text-xs leading-snug text-text-muted' : 'text-sm'"
            >
                {{ delivery.summary }}
            </p>
            <ul
                v-if="!compact && delivery.lines?.length"
                class="mt-2 space-y-1 text-sm text-text-muted"
            >
                <li v-for="(line, index) in delivery.lines" :key="index">
                    {{ line }}
                </li>
            </ul>
            <p
                v-else-if="compact && commerce?.shipping?.line"
                class="mt-1 text-xs text-text-muted"
            >
                {{ commerce.shipping.line }}
            </p>
        </div>

        <div v-if="payment">
            <p
                class="font-medium text-primary-DEFAULT"
                :class="compact ? 'text-[11px] uppercase tracking-[0.14em] text-text-muted' : 'text-xs uppercase tracking-[0.18em] text-text-muted'"
            >
                {{ payment.title || labels.payment_title }}
            </p>
            <p
                v-if="payment.summary"
                class="mt-1 text-text-DEFAULT"
                :class="compact ? 'text-xs leading-snug text-text-muted' : 'text-sm'"
            >
                {{ payment.summary }}
            </p>
            <p
                v-if="payment.methods?.length"
                class="mt-1 text-text-muted"
                :class="compact ? 'text-xs' : 'text-sm'"
            >
                {{ payment.methods.join(' · ') }}
            </p>
        </div>
    </section>
</template>
