<script setup>
import { computed, onMounted, ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Chart from 'primevue/chart';
import { Link } from '@inertiajs/vue3';
import { useFormatters } from '@/Composables/useFormatters';

defineOptions({ layout: AppLayout });

const props = defineProps({
    pipelineStats: Object,
    conversionStats: Object,
    incomingLeads: Object,
    revenueOverTime: Object,
    wonLostOverTime: Object,
    pipelineByStage: Object,
    latestOpportunities: Array,
    todayTasks: Array,
});

const { formatCurrency, formatDate, priorityColor, priorityLabel, taskTypeColor } = useFormatters();

const statCards = computed(() => [
    { label: 'Leads & Opportunités actifs', value: props.pipelineStats.active_count, icon: 'pi pi-users' },
    { label: 'Valeur Pipeline', value: formatCurrency(props.pipelineStats.pipeline_value), icon: 'pi pi-chart-line' },
    { label: 'Valeur Pondérée', value: formatCurrency(props.pipelineStats.weighted_value), icon: 'pi pi-percentage' },
    { label: 'Gagnés ce mois', value: `${props.pipelineStats.won_this_month} (${formatCurrency(props.pipelineStats.won_this_month_value)})`, icon: 'pi pi-trophy' },
]);

const conversionCards = computed(() => [
    { label: 'Taux de conversion', value: `${props.conversionStats.win_rate}%`, icon: 'pi pi-chart-pie' },
    { label: 'Taille moy. deal', value: formatCurrency(props.conversionStats.avg_deal_size), icon: 'pi pi-wallet' },
    { label: 'Jours moy. pour closer', value: `${props.conversionStats.avg_days_to_close}j`, icon: 'pi pi-clock' },
]);

const shortLabels = (labels) => labels.map(l => {
    const d = new Date(l);
    return d.toLocaleDateString('fr-FR', { month: 'short' });
});

const leadsChartData = computed(() => ({
    labels: shortLabels(props.incomingLeads.labels),
    datasets: [{ label: 'Leads entrants', data: props.incomingLeads.data, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', fill: true, tension: 0.4 }],
}));

const revenueChartData = computed(() => ({
    labels: shortLabels(props.revenueOverTime.labels),
    datasets: [{ label: 'Revenu', data: props.revenueOverTime.data, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 }],
}));

const wonLostChartData = computed(() => ({
    labels: shortLabels(props.wonLostOverTime.labels),
    datasets: [
        { label: 'Gagnés', data: props.wonLostOverTime.won, backgroundColor: '#10b981' },
        { label: 'Perdus', data: props.wonLostOverTime.lost, backgroundColor: '#ef4444' },
    ],
}));

const pipelineByStageData = computed(() => ({
    labels: props.pipelineByStage.labels,
    datasets: [{
        data: props.pipelineByStage.data,
        backgroundColor: props.pipelineByStage.colors.map(c => c || '#6366f1'),
    }],
}));

const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
const barChartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } };
const doughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } };
</script>

<template>
    <div>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Dashboard</h1>
        </div>

        <!-- Pipeline Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card v-for="stat in statCards" :key="stat.label" class="!shadow-sm">
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-surface-500 mb-1">{{ stat.label }}</p>
                            <p class="text-xl font-bold text-surface-900 dark:text-surface-0">{{ stat.value }}</p>
                        </div>
                        <i :class="stat.icon" class="text-2xl text-primary"></i>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Conversion Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <Card v-for="stat in conversionCards" :key="stat.label" class="!shadow-sm">
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-surface-500 mb-1">{{ stat.label }}</p>
                            <p class="text-xl font-bold text-surface-900 dark:text-surface-0">{{ stat.value }}</p>
                        </div>
                        <i :class="stat.icon" class="text-2xl text-primary"></i>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card class="!shadow-sm">
                <template #title>Leads entrants</template>
                <template #content>
                    <div style="height: 250px">
                        <Chart type="line" :data="leadsChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>
            <Card class="!shadow-sm">
                <template #title>Revenu dans le temps</template>
                <template #content>
                    <div style="height: 250px">
                        <Chart type="line" :data="revenueChartData" :options="chartOptions" />
                    </div>
                </template>
            </Card>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <Card class="!shadow-sm">
                <template #title>Gagnés vs Perdus</template>
                <template #content>
                    <div style="height: 250px">
                        <Chart type="bar" :data="wonLostChartData" :options="barChartOptions" />
                    </div>
                </template>
            </Card>
            <Card class="!shadow-sm">
                <template #title>Pipeline par étape</template>
                <template #content>
                    <div style="height: 250px">
                        <Chart type="doughnut" :data="pipelineByStageData" :options="doughnutOptions" />
                    </div>
                </template>
            </Card>
        </div>

        <!-- Bottom row: Latest opportunities + Today tasks -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card class="!shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>Dernières opportunités</span>
                        <Link href="/app/opportunities" class="text-sm font-normal text-primary">Voir tout</Link>
                    </div>
                </template>
                <template #content>
                    <DataTable :value="latestOpportunities" :rows="5" class="text-sm">
                        <Column field="name" header="Nom">
                            <template #body="{ data }">
                                <div>
                                    <span class="font-medium">{{ data.name }}</span>
                                    <span v-if="data.company" class="block text-xs text-surface-500">{{ data.company }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="stage" header="Étape">
                            <template #body="{ data }">
                                <Tag :value="data.stage" :style="{ backgroundColor: data.stage_color || '#6366f1', color: 'white' }" />
                            </template>
                        </Column>
                        <Column field="value" header="Valeur" class="text-right">
                            <template #body="{ data }">
                                <span class="font-semibold text-green-600">{{ formatCurrency(data.value) }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <Card class="!shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>Tâches du jour</span>
                        <Link href="/app/tasks/my-day" class="text-sm font-normal text-primary">Ma journée</Link>
                    </div>
                </template>
                <template #content>
                    <div v-if="todayTasks.length === 0" class="text-center py-8 text-surface-500">
                        <i class="pi pi-check-circle text-4xl mb-2"></i>
                        <p>Aucune tâche pour aujourd'hui</p>
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="task in todayTasks"
                            :key="task.id"
                            :class="[
                                'flex items-center gap-3 p-3 rounded-lg border',
                                task.is_overdue ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-950' : 'border-surface-200 dark:border-surface-700',
                            ]"
                        >
                            <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm truncate">{{ task.title }}</p>
                                <p v-if="task.due_time" class="text-xs text-surface-500">{{ task.due_time }}</p>
                            </div>
                            <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
