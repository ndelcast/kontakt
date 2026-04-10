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
        message: 'Supprimer cette opportunité ?',
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
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Opportunités</h1>
            <div class="flex gap-2">
                <Link href="/app/opportunities/kanban">
                    <Button label="Kanban" icon="pi pi-th-large" severity="secondary" outlined />
                </Link>
                <Link href="/app/opportunities/create">
                    <Button label="Nouvelle opportunité" icon="pi pi-plus" />
                </Link>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <InputText v-model="search" placeholder="Rechercher..." class="w-full sm:w-64" />
            <Select v-model="stageFilter" :options="stages" optionLabel="name" optionValue="id" placeholder="Toutes les étapes" showClear class="w-full sm:w-48" />
            <Select v-model="companyFilter" :options="companies" optionLabel="name" optionValue="id" placeholder="Toutes les entreprises" showClear class="w-full sm:w-48" />
        </div>

        <DataTable :value="opportunities.data" :rows="15" stripedRows class="text-sm" :rowClass="rowClass">
            <Column field="name" header="Nom" sortable>
                <template #body="{ data }">
                    <Link :href="`/app/opportunities/${data.id}/edit`" class="font-medium text-primary hover:underline">{{ data.name }}</Link>
                    <span v-if="data.stage" class="block text-xs text-surface-500">{{ data.stage.name }}</span>
                </template>
            </Column>
            <Column field="stage" header="Étape">
                <template #body="{ data }">
                    <Tag v-if="data.stage" :value="data.stage.name"
                        :severity="data.stage.is_won ? 'success' : data.stage.is_lost ? 'danger' : 'info'"
                    />
                </template>
            </Column>
            <Column field="company" header="Entreprise">
                <template #body="{ data }">
                    <span v-if="data.company"><i class="pi pi-building text-xs mr-1"></i>{{ data.company.name }}</span>
                </template>
            </Column>
            <Column field="contact" header="Contact">
                <template #body="{ data }">
                    <span v-if="data.contact"><i class="pi pi-user text-xs mr-1"></i>{{ data.contact.name }}</span>
                </template>
            </Column>
            <Column field="value" header="Valeur" class="text-right">
                <template #body="{ data }">
                    <span class="font-bold text-green-600">{{ formatCurrency(data.value) }}</span>
                </template>
            </Column>
            <Column field="started_at" header="Début">
                <template #body="{ data }">{{ formatDate(data.started_at) }}</template>
            </Column>
            <Column field="expected_close_date" header="Clôture prévue">
                <template #body="{ data }">
                    <span :class="{ 'text-red-500': data.expected_close_date && new Date(data.expected_close_date) < new Date() }">
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
            <template #empty><div class="text-center py-8 text-surface-500">Aucune opportunité trouvée</div></template>
        </DataTable>

        <div v-if="opportunities.links?.length > 3" class="flex justify-center gap-1 mt-4">
            <template v-for="link in opportunities.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-primary text-white' : 'bg-surface-100 dark:bg-surface-800 hover:bg-surface-200']" v-html="link.label" preserve-state />
                <span v-else class="px-3 py-1 rounded text-sm text-surface-400" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
