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
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Kanban Pipeline</h1>
            <div class="flex gap-2">
                <Link href="/app/opportunities">
                    <Button label="Liste" icon="pi pi-list" severity="secondary" outlined />
                </Link>
                <Link href="/app/opportunities/create">
                    <Button label="Nouvelle opportunité" icon="pi pi-plus" />
                </Link>
            </div>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 70vh">
            <div
                v-for="column in columns"
                :key="column.id"
                class="flex-shrink-0 w-72 bg-surface-100 dark:bg-surface-800 rounded-xl flex flex-col"
            >
                <!-- Column header -->
                <div class="p-3 border-b border-surface-200 dark:border-surface-700">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-semibold text-sm text-surface-900 dark:text-surface-0">
                            {{ column.name }}
                        </h3>
                        <Tag :value="String(column.items.length)" severity="secondary" rounded />
                    </div>
                    <p class="text-xs text-surface-500">{{ formatCurrency(columnTotal(column.items)) }}</p>
                </div>

                <!-- Cards -->
                <draggable
                    v-model="column.items"
                    group="opportunities"
                    item-key="id"
                    class="flex-1 p-2 space-y-2 overflow-y-auto"
                    ghost-class="opacity-30"
                    @end="onDragEnd(column.id)"
                >
                    <template #item="{ element }">
                        <div class="bg-surface-0 dark:bg-surface-900 rounded-lg p-3 shadow-sm border border-surface-200 dark:border-surface-700 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-2">
                                <Link :href="`/app/opportunities/${element.id}/edit`" class="font-medium text-sm text-surface-900 dark:text-surface-0 hover:text-primary">
                                    {{ element.name }}
                                </Link>
                                <Tag
                                    :value="`${element.days_open}j`"
                                    :severity="daysBadgeColor(element.days_open)"
                                    size="small"
                                    rounded
                                />
                            </div>
                            <p class="text-sm font-bold text-green-600 mb-2">{{ formatCurrency(element.value) }}</p>
                            <div class="space-y-1 text-xs text-surface-500">
                                <div v-if="element.company" class="flex items-center gap-1">
                                    <i class="pi pi-building"></i>
                                    <span>{{ element.company.name }}</span>
                                </div>
                                <div v-if="element.contact" class="flex items-center gap-1">
                                    <i class="pi pi-user"></i>
                                    <span>{{ element.contact.name }}</span>
                                </div>
                                <div v-if="element.expected_close_date" class="flex items-center gap-1">
                                    <i class="pi pi-calendar"></i>
                                    <span :class="{ 'text-red-500': new Date(element.expected_close_date) < new Date() }">
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
