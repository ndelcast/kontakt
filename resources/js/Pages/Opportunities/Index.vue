<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { useFormatters } from '@/Composables/useFormatters';

defineOptions({ layout: AppLayout });

const props = defineProps({ opportunities: Object, stages: Array, companies: Array, filters: Object });
const confirm = useConfirm();
const { formatCurrency, formatDate } = useFormatters();

const search = ref(props.filters?.search ?? '');
const stageFilter = ref(props.filters?.stage_id ?? null);
const companyFilter = ref(props.filters?.company_id ?? null);

const applyFilters = () => {
    router.get('/app/opportunities', {
        search: search.value || undefined,
        stage_id: stageFilter.value || undefined,
        company_id: companyFilter.value || undefined,
    }, { preserveState: true, replace: true });
};

let timeout;
watch(search, () => { clearTimeout(timeout); timeout = setTimeout(applyFilters, 300); });
watch([stageFilter, companyFilter], applyFilters);

const deleteOpportunity = (id) => {
    confirm.require({
        message: 'Supprimer cette opportunite ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(`/app/opportunities/${id}`),
    });
};

const rowClass = (data) => {
    if (data.won_at) return 'bg-green-50 dark:bg-green-950/30';
    if (data.lost_at) return 'bg-red-50 dark:bg-red-950/30';
    return '';
};
</script>

<template>
    <div>
        <ConfirmDialog />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Opportunites</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ opportunities.total ?? opportunities.data?.length ?? 0 }} opportunites</p>
            </div>
            <div class="flex gap-2">
                <Link href="/app/opportunities/kanban">
                    <Button label="Kanban" icon="pi pi-th-large" severity="secondary" outlined size="small" />
                </Link>
                <Link href="/app/opportunities/create">
                    <Button label="Nouvelle opportunite" icon="pi pi-plus" size="small" />
                </Link>
            </div>
        </div>

        <div class="panel">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="search-box w-full sm:w-64">
                        <i class="pi pi-search search-icon"></i>
                        <InputText v-model="search" placeholder="Rechercher..." class="w-full" />
                    </div>
                    <Select v-model="stageFilter" :options="stages" optionLabel="name" optionValue="id" placeholder="Toutes les etapes" showClear class="w-full sm:w-48" />
                    <Select v-model="companyFilter" :options="companies" optionLabel="name" optionValue="id" placeholder="Toutes les entreprises" showClear class="w-full sm:w-48" />
                </div>
            </div>

            <div class="px-4 pb-2">
                <DataTable :value="opportunities.data" :rows="15" class="text-sm" :rowClass="rowClass">
                    <Column field="name" header="Nom" sortable>
                        <template #body="{ data }">
                            <Link :href="`/app/opportunities/${data.id}/edit`" class="font-semibold text-[#4A6CF7] dark:text-[#7B93FA] hover:text-[#3451D1]">{{ data.name }}</Link>
                            <span v-if="data.stage" class="block text-xs text-gray-400 mt-0.5">{{ data.stage.name }}</span>
                        </template>
                    </Column>
                    <Column field="stage" header="Etape">
                        <template #body="{ data }">
                            <Tag v-if="data.stage" :value="data.stage.name"
                                :severity="data.stage.is_won ? 'success' : data.stage.is_lost ? 'danger' : 'info'"
                                rounded
                            />
                        </template>
                    </Column>
                    <Column field="company" header="Entreprise">
                        <template #body="{ data }">
                            <span v-if="data.company" class="text-gray-600 dark:text-gray-300">
                                <i class="pi pi-building text-xs mr-1 text-gray-400"></i>{{ data.company.name }}
                            </span>
                        </template>
                    </Column>
                    <Column field="contact" header="Contact">
                        <template #body="{ data }">
                            <span v-if="data.contact" class="text-gray-600 dark:text-gray-300">
                                <i class="pi pi-user text-xs mr-1 text-gray-400"></i>{{ data.contact.name }}
                            </span>
                        </template>
                    </Column>
                    <Column field="value" header="Valeur" class="text-right">
                        <template #body="{ data }">
                            <span class="font-bold text-teal-600 dark:text-teal-400">{{ formatCurrency(data.value) }}</span>
                        </template>
                    </Column>
                    <Column field="started_at" header="Debut">
                        <template #body="{ data }">
                            <span class="text-gray-500">{{ formatDate(data.started_at) }}</span>
                        </template>
                    </Column>
                    <Column field="expected_close_date" header="Cloture prevue">
                        <template #body="{ data }">
                            <span :class="data.expected_close_date && new Date(data.expected_close_date) < new Date() ? 'text-rose-500 font-semibold' : 'text-gray-500'">
                                {{ formatDate(data.expected_close_date) }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 100px">
                        <template #body="{ data }">
                            <div class="flex gap-1">
                                <Link :href="`/app/opportunities/${data.id}/edit`"><Button icon="pi pi-pencil" text rounded size="small" /></Link>
                                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteOpportunity(data.id)" />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="empty-state">
                            <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                                <i class="pi pi-chart-bar"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-400">Aucune opportunite trouvee</p>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <div v-if="opportunities.links?.length > 3" class="flex justify-center gap-1 mt-5">
            <template v-for="link in opportunities.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['pg-link', link.active ? 'bg-[#4A6CF7] text-white' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700']" v-html="link.label" preserve-state />
                <span v-else class="pg-link text-gray-300 dark:text-gray-600" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
