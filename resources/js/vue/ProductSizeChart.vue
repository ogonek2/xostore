<script setup>
defineProps({
    chart: { type: Object, required: true },
    title: { type: String, required: true },
});
</script>

<template>
    <section class="rounded-2xl border border-border-DEFAULT bg-surface-DEFAULT p-6 lg:p-8">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ title }}
            </h2>
            <p v-if="chart.unit" class="text-xs text-text-muted">{{ chart.unit }}</p>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-border-DEFAULT text-xs uppercase tracking-[0.12em] text-text-muted">
                        <th class="px-3 py-2 font-medium">{{ chart.headers.size }}</th>
                        <th class="px-3 py-2 font-medium">{{ chart.headers.chest }}</th>
                        <th class="px-3 py-2 font-medium">{{ chart.headers.waist }}</th>
                        <th class="px-3 py-2 font-medium">{{ chart.headers.hips }}</th>
                        <th class="px-3 py-2 font-medium">{{ chart.headers.inseam }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, index) in chart.rows"
                        :key="index"
                        class="border-b border-border-DEFAULT/60 last:border-0"
                    >
                        <td class="px-3 py-2.5 font-medium text-text-DEFAULT">{{ row.size || '—' }}</td>
                        <td class="px-3 py-2.5 text-text-muted">{{ row.chest || '—' }}</td>
                        <td class="px-3 py-2.5 text-text-muted">{{ row.waist || '—' }}</td>
                        <td class="px-3 py-2.5 text-text-muted">{{ row.hips || '—' }}</td>
                        <td class="px-3 py-2.5 text-text-muted">{{ row.inseam || '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
