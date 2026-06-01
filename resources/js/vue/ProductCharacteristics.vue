<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    product: { type: Object, required: true },
    labels: { type: Object, required: true },
});

const activeTab = ref('details');

const tabs = computed(() => [
    { id: 'details', label: props.labels.tab_details, content: props.product.description },
    { id: 'fit', label: props.labels.tab_fit, content: props.product.fit_description },
    { id: 'fabric', label: props.labels.tab_fabric, content: props.product.fabric_description },
].filter((tab) => tab.content));

const hasDetailItems = computed(() => (props.product.detail_items?.length ?? 0) > 0);

const hasTabs = computed(() => tabs.value.length > 0);

const hasContent = computed(() => hasDetailItems.value || hasTabs.value);
</script>

<template>
    <section v-if="hasContent" class="rounded-2xl border border-border-DEFAULT bg-surface-DEFAULT p-6 lg:p-8">
        <h2 class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
            {{ labels.characteristics }}
        </h2>

        <dl v-if="hasDetailItems" class="mt-6 divide-y divide-border-DEFAULT border-t border-border-DEFAULT">
            <div
                v-for="(item, index) in product.detail_items"
                :key="index"
                class="grid gap-2 py-4 sm:grid-cols-[minmax(8rem,12rem)_1fr] sm:gap-6"
            >
                <dt class="text-sm font-medium text-text-DEFAULT">{{ item.label }}</dt>
                <dd class="text-sm leading-relaxed text-text-muted" v-html="item.description" />
            </div>
        </dl>

        <div v-if="hasTabs" :class="hasDetailItems ? 'mt-10' : 'mt-6'">
            <div class="flex flex-wrap gap-4 border-b border-border-DEFAULT">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="pb-3 text-sm font-medium uppercase tracking-[0.14em] transition"
                    :class="activeTab === tab.id
                        ? 'border-b-2 border-primary-DEFAULT text-primary-DEFAULT'
                        : 'text-text-muted hover:text-text-DEFAULT'"
                    @click="activeTab = tab.id"
                >
                    {{ tab.label }}
                </button>
            </div>
            <div class="prose prose-sm mt-6 max-w-none text-text-muted">
                <div
                    v-for="tab in tabs"
                    :key="tab.id"
                    v-show="activeTab === tab.id"
                    v-html="tab.content"
                />
            </div>
        </div>

        <p v-else-if="!hasDetailItems" class="mt-6 text-sm text-text-muted">{{ labels.no_details }}</p>
    </section>
</template>
