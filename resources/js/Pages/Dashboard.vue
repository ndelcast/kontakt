<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
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
    {
        label: 'Leads actifs',
        value: props.pipelineStats.active_count,
        icon: 'pi pi-users',
        iconColor: 'text-[#4A6CF7]',
        iconBg: 'bg-[#EEF1FE]',
    },
    {
        label: 'Valeur Pipeline',
        value: formatCurrency(props.pipelineStats.pipeline_value),
        icon: 'pi pi-chart-line',
        iconColor: 'text-teal-500',
        iconBg: 'bg-teal-50',
    },
    {
        label: 'Valeur Ponderee',
        value: formatCurrency(props.pipelineStats.weighted_value),
        icon: 'pi pi-percentage',
        iconColor: 'text-amber-500',
        iconBg: 'bg-amber-50',
    },
    {
        label: 'Gagnes ce mois',
        value: `${props.pipelineStats.won_this_month}`,
        subValue: formatCurrency(props.pipelineStats.won_this_month_value),
        icon: 'pi pi-trophy',
        iconColor: 'text-rose-500',
        iconBg: 'bg-rose-50',
    },
]);

const conversionCards = computed(() => [
    { label: 'Taux de conversion', value: `${props.conversionStats.win_rate}%`, icon: 'pi pi-chart-pie', color: 'text-[#4A6CF7] dark:text-[#7B93FA]' },
    { label: 'Taille moy. deal', value: formatCurrency(props.conversionStats.avg_deal_size), icon: 'pi pi-wallet', color: 'text-teal-600 dark:text-teal-400' },
    { label: 'Jours moy. pour closer', value: `${props.conversionStats.avg_days_to_close}j`, icon: 'pi pi-clock', color: 'text-amber-600 dark:text-amber-400' },
]);

const shortLabels = (labels) => labels.map(l => {
    const d = new Date(l);
    return d.toLocaleDateString('fr-FR', { month: 'short' });
});

const leadsChartData = computed(() => ({
    labels: shortLabels(props.incomingLeads.labels),
    datasets: [{
        label: 'Leads entrants',
        data: props.incomingLeads.data,
        borderColor: '#4A6CF7',
        backgroundColor: 'rgba(74,108,247,0.06)',
        fill: true, tension: 0.4, borderWidth: 2.5,
        pointRadius: 0, pointHoverRadius: 5,
        pointBackgroundColor: '#4A6CF7',
    }],
}));

const revenueChartData = computed(() => ({
    labels: shortLabels(props.revenueOverTime.labels),
    datasets: [{
        label: 'Revenu',
        data: props.revenueOverTime.data,
        borderColor: '#0d9488',
        backgroundColor: 'rgba(13,148,136,0.06)',
        fill: true, tension: 0.4, borderWidth: 2.5,
        pointRadius: 0, pointHoverRadius: 5,
        pointBackgroundColor: '#0d9488',
    }],
}));

const wonLostChartData = computed(() => ({
    labels: shortLabels(props.wonLostOverTime.labels),
    datasets: [
        { label: 'Gagnes', data: props.wonLostOverTime.won, backgroundColor: '#10b981', borderRadius: 8, barThickness: 18 },
        { label: 'Perdus', data: props.wonLostOverTime.lost, backgroundColor: '#f43f5e', borderRadius: 8, barThickness: 18 },
    ],
}));

const pipelineByStageData = computed(() => ({
    labels: props.pipelineByStage.labels,
    datasets: [{
        data: props.pipelineByStage.data,
        backgroundColor: props.pipelineByStage.colors.map(c => c || '#4A6CF7'),
        borderWidth: 0, spacing: 3,
    }],
}));

const baseScales = {
    x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 11, weight: '500' }, color: '#9ca3af' } },
    y: { grid: { color: '#f3f4f6', drawBorder: false }, border: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
};

const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: baseScales };
const barChartOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 12, weight: '500' }, padding: 20 } } },
    scales: baseScales,
};
const doughnutOptions = {
    responsive: true, maintainAspectRatio: false, cutout: '70%',
    plugins: { legend: { position: 'right', labels: { usePointStyle: true, pointStyle: 'circle', font: { size: 11, weight: '500' }, padding: 14 } } },
};
</script>

<template>
    <div>
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
            <div
                v-for="stat in statCards"
                :key="stat.label"
                class="stat-card panel p-5"
            >
                <div class="flex items-center gap-3 mb-3">
                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center', stat.iconBg]">
                        <i :class="[stat.icon, stat.iconColor]" class="text-sm"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ stat.label }}</p>
                </div>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</p>
                <p v-if="stat.subValue" class="text-sm text-gray-400 mt-0.5">{{ stat.subValue }}</p>
            </div>
        </div>

        <!-- Conversion KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
            <div
                v-for="stat in conversionCards"
                :key="stat.label"
                class="panel p-5 flex items-center gap-4"
            >
                <div class="w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-800 flex items-center justify-center shrink-0">
                    <i :class="[stat.icon, stat.color]" class="text-base"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ stat.label }}</p>
                    <p :class="['text-xl font-bold mt-0.5', stat.color]">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="panel p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-5">Leads entrants</h3>
                <div style="height: 220px">
                    <Chart type="line" :data="leadsChartData" :options="chartOptions" />
                </div>
            </div>
            <div class="panel p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-5">Revenu dans le temps</h3>
                <div style="height: 220px">
                    <Chart type="line" :data="revenueChartData" :options="chartOptions" />
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
            <div class="panel p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-5">Gagnes vs Perdus</h3>
                <div style="height: 220px">
                    <Chart type="bar" :data="wonLostChartData" :options="barChartOptions" />
                </div>
            </div>
            <div class="panel p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-5">Pipeline par etape</h3>
                <div style="height: 220px">
                    <Chart type="doughnut" :data="pipelineByStageData" :options="doughnutOptions" />
                </div>
            </div>
        </div>

        <!-- Bottom row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Latest opportunities -->
            <div class="panel">
                <div class="flex items-center justify-between px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Dernieres opportunites</h3>
                    <Link href="/app/opportunities" class="text-xs font-bold text-[#4A6CF7] dark:text-[#7B93FA] hover:text-[#3451D1] transition-colors">
                        Voir tout &rarr;
                    </Link>
                </div>
                <div class="px-6 pb-4">
                    <DataTable :value="latestOpportunities" :rows="5" class="text-sm">
                        <Column field="name" header="Nom">
                            <template #body="{ data }">
                                <div>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ data.name }}</span>
                                    <span v-if="data.company" class="block text-xs text-gray-400 mt-0.5">{{ data.company }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="stage" header="Etape">
                            <template #body="{ data }">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold text-white"
                                    :style="{ backgroundColor: data.stage_color || '#4A6CF7' }"
                                >{{ data.stage }}</span>
                            </template>
                        </Column>
                        <Column field="value" header="Valeur" class="text-right">
                            <template #body="{ data }">
                                <span class="font-bold text-teal-600 dark:text-teal-400">{{ formatCurrency(data.value) }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>

            <!-- Today tasks -->
            <div class="panel">
                <div class="flex items-center justify-between px-6 py-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Taches du jour</h3>
                    <Link href="/app/tasks/my-day" class="text-xs font-bold text-[#4A6CF7] dark:text-[#7B93FA] hover:text-[#3451D1] transition-colors">
                        Ma journee &rarr;
                    </Link>
                </div>
                <div class="px-6 pb-5">
                    <div v-if="todayTasks.length === 0" class="empty-state">
                        <div class="empty-icon bg-green-50 dark:bg-green-950/30 text-green-500 mx-auto">
                            <i class="pi pi-check-circle"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-400">Aucune tache pour aujourd'hui</p>
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="task in todayTasks"
                            :key="task.id"
                            :class="[
                                'flex items-center gap-3 p-3 rounded-xl transition-colors',
                                task.is_overdue
                                    ? 'bg-rose-50 dark:bg-rose-950/20'
                                    : 'bg-gray-50 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60',
                            ]"
                        >
                            <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-sm truncate text-gray-900 dark:text-white">{{ task.title }}</p>
                                <p v-if="task.due_time" class="text-xs text-gray-400 mt-0.5">{{ task.due_time }}</p>
                            </div>
                            <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
