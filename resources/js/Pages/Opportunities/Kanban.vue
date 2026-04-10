<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import draggable from 'vuedraggable';
import { useFormatters } from '@/Composables/useFormatters';
import axios from 'axios';

defineOptions({ layout: AppLayout });

const props = defineProps({
    stages: Array,
    opportunities: { type: [Array, Object] },
    companies: Array,
    contacts: Array,
    stagesList: Array,
});

const { formatCurrency, formatDate } = useFormatters();

const columns = ref(props.stages.map(stage => ({
    ...stage,
    items: (Array.isArray(props.opportunities) ? props.opportunities : Object.values(props.opportunities))
        .filter(o => o.pipeline_stage_id === stage.id),
})));

const onDragEnd = async (stageId) => {
    const column = columns.value.find(c => c.id === stageId);
    if (!column) return;

    for (let i = 0; i < column.items.length; i++) {
        const item = column.items[i];
        if (item.pipeline_stage_id !== stageId || item.position !== i) {
            item.pipeline_stage_id = stageId;
            item.position = i;

            await axios.post('/app/opportunities/kanban/move', {
                opportunity_id: item.id,
                pipeline_stage_id: stageId,
                position: i,
            });
        }
    }
};

const daysBadgeColor = (days) => {
    if (days > 90) return 'danger';
    if (days > 30) return 'warn';
    return 'info';
};

const columnTotal = (items) => {
    return items.reduce((sum, o) => sum + parseFloat(o.value || 0), 0);
};
</script>

<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Kanban Pipeline</h1>
            </div>
            <div class="flex gap-2">
                <Link href="/app/opportunities">
                    <Button label="Liste" icon="pi pi-list" severity="secondary" outlined size="small" />
                </Link>
                <Link href="/app/opportunities/create">
                    <Button label="Nouvelle opportunite" icon="pi pi-plus" size="small" />
                </Link>
            </div>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 70vh">
            <div
                v-for="column in columns"
                :key="column.id"
                class="kanban-col flex-shrink-0 w-72 rounded-2xl flex flex-col bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800"
            >
                <!-- Column header -->
                <div class="p-4">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-3 h-3 rounded-md shrink-0"
                                :style="{ backgroundColor: column.color || '#4f46e5' }"
                            ></div>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">
                                {{ column.name }}
                            </h3>
                        </div>
                        <span class="text-xs font-bold text-gray-400 bg-white dark:bg-gray-800 px-2 py-0.5 rounded-md">
                            {{ column.items.length }}
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 ml-[22px]">{{ formatCurrency(columnTotal(column.items)) }}</p>
                </div>

                <!-- Cards -->
                <draggable
                    v-model="column.items"
                    group="opportunities"
                    item-key="id"
                    class="flex-1 px-2 pb-2 space-y-2 overflow-y-auto"
                    ghost-class="opacity-30"
                    @end="onDragEnd(column.id)"
                >
                    <template #item="{ element }">
                        <div class="kanban-card bg-white dark:bg-gray-800 rounded-xl p-3.5 border border-gray-100 dark:border-gray-700 cursor-grab active:cursor-grabbing">
                            <div class="flex items-start justify-between mb-2">
                                <Link :href="`/app/opportunities/${element.id}/edit`" class="font-semibold text-sm text-gray-900 dark:text-white hover:text-[#4A6CF7] transition-colors leading-snug">
                                    {{ element.name }}
                                </Link>
                                <Tag
                                    :value="`${element.days_open}j`"
                                    :severity="daysBadgeColor(element.days_open)"
                                    size="small"
                                    rounded
                                />
                            </div>
                            <p class="text-sm font-bold text-teal-600 dark:text-teal-400 mb-3">{{ formatCurrency(element.value) }}</p>
                            <div class="space-y-1.5 text-xs text-gray-400">
                                <div v-if="element.company" class="flex items-center gap-1.5">
                                    <i class="pi pi-building text-[0.6rem]"></i>
                                    <span>{{ element.company.name }}</span>
                                </div>
                                <div v-if="element.contact" class="flex items-center gap-1.5">
                                    <i class="pi pi-user text-[0.6rem]"></i>
                                    <span>{{ element.contact.name }}</span>
                                </div>
                                <div v-if="element.expected_close_date" class="flex items-center gap-1.5">
                                    <i class="pi pi-calendar text-[0.6rem]"></i>
                                    <span :class="{ 'text-rose-500 font-semibold': new Date(element.expected_close_date) < new Date() }">
                                        {{ formatDate(element.expected_close_date) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>
    </div>
</template>
