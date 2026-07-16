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
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import { useConfirm } from 'primevue/useconfirm';
import { useFormatters } from '@/Composables/useFormatters';

defineOptions({ layout: AppLayout });

const props = defineProps({ tasks: Object, taskTypes: Array, filters: Object });
const confirm = useConfirm();
const { formatDate, priorityColor, priorityLabel, taskTypeColor } = useFormatters();

const search = ref(props.filters?.search ?? '');
const typeFilter = ref(props.filters?.type ?? null);
const statusFilter = ref(props.filters?.status ?? null);
const priorityFilter = ref(props.filters?.priority ?? null);

const statusOptions = [
    { value: 'pending', label: 'En cours' },
    { value: 'completed', label: 'Terminees' },
    { value: 'overdue', label: 'En retard' },
];

const priorityOptions = [
    { value: 0, label: 'Normal' },
    { value: 1, label: 'High' },
    { value: 2, label: 'Urgent' },
];

const applyFilters = () => {
    router.get('/app/tasks', {
        search: search.value || undefined,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
        priority: priorityFilter.value !== null ? priorityFilter.value : undefined,
    }, { preserveState: true, replace: true });
};

let timeout;
watch(search, () => { clearTimeout(timeout); timeout = setTimeout(applyFilters, 300); });
watch([typeFilter, statusFilter, priorityFilter], applyFilters);

// Complete dialog
const completeDialog = ref(false);
const completingTask = ref(null);
const completionOutcome = ref('');

const openCompleteDialog = (task) => {
    completingTask.value = task;
    completionOutcome.value = '';
    completeDialog.value = true;
};

const submitComplete = () => {
    router.post(`/app/tasks/${completingTask.value.id}/complete`, { outcome: completionOutcome.value }, {
        onSuccess: () => { completeDialog.value = false; },
    });
};

const deleteTask = (id) => {
    confirm.require({
        message: 'Supprimer cette tache ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(`/app/tasks/${id}`),
    });
};

const rowClass = (data) => {
    if (data.completed_at) return 'opacity-50';
    if (data.is_overdue) return 'bg-red-50 dark:bg-red-950/30';
    if (data.is_today) return 'bg-amber-50 dark:bg-amber-950/30';
    return '';
};
</script>

<template>
    <div>
        <ConfirmDialog />
        <Dialog v-model:visible="completeDialog" header="Completer la tache" :style="{ width: '30rem' }" modal>
            <div class="space-y-4">
                <p class="text-sm text-gray-500">Tache : <strong>{{ completingTask?.title }}</strong></p>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Resultat (optionnel)</label>
                    <Textarea v-model="completionOutcome" rows="3" class="w-full" />
                </div>
                <div class="flex justify-end gap-3">
                    <Button label="Annuler" severity="secondary" outlined @click="completeDialog = false" />
                    <Button label="Completer" icon="pi pi-check" @click="submitComplete" />
                </div>
            </div>
        </Dialog>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Taches</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ tasks.total ?? tasks.data?.length ?? 0 }} taches</p>
            </div>
            <div class="flex gap-2">
                <Link href="/app/tasks/my-day">
                    <Button label="Ma Journee" icon="pi pi-sun" severity="secondary" outlined size="small" />
                </Link>
                <Link href="/app/tasks/create">
                    <Button label="Nouvelle tache" icon="pi pi-plus" size="small" />
                </Link>
            </div>
        </div>

        <div class="panel">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                    <div class="search-box w-full sm:w-64">
                        <i class="pi pi-search search-icon"></i>
                        <InputText v-model="search" placeholder="Rechercher..." class="w-full" />
                    </div>
                    <Select v-model="typeFilter" :options="taskTypes" optionLabel="label" optionValue="value" placeholder="Tous les types" showClear class="w-full sm:w-40" />
                    <Select v-model="statusFilter" :options="statusOptions" optionLabel="label" optionValue="value" placeholder="Tous les statuts" showClear class="w-full sm:w-40" />
                    <Select v-model="priorityFilter" :options="priorityOptions" optionLabel="label" optionValue="value" placeholder="Toutes priorites" showClear class="w-full sm:w-40" />
                </div>
            </div>

            <div class="px-4 pb-2">
                <DataTable :value="tasks.data" :rows="15" class="text-sm" :rowClass="rowClass">
                    <Column field="type" header="Type" style="width: 100px">
                        <template #body="{ data }">
                            <Tag :value="data.type_label" :severity="taskTypeColor(data.type)" size="small" />
                        </template>
                    </Column>
                    <Column field="title" header="Titre" sortable>
                        <template #body="{ data }">
                            <Link :href="`/app/tasks/${data.id}/edit`" class="font-semibold text-[#4A6CF7] dark:text-[#7B93FA] hover:text-[#3451D1]">{{ data.title }}</Link>
                            <span class="block text-xs text-gray-400 mt-0.5">
                                {{ [data.opportunity?.name, data.contact?.name, data.company?.name].filter(Boolean).join(' · ') }}
                            </span>
                        </template>
                    </Column>
                    <Column field="due_date" header="Echeance" sortable>
                        <template #body="{ data }">
                            <span :class="{ 'text-rose-500 font-semibold': data.is_overdue, 'text-amber-600': data.is_today }">
                                {{ formatDate(data.due_date) }}
                            </span>
                            <span v-if="data.due_time" class="block text-xs text-gray-400 mt-0.5">{{ data.due_time }}</span>
                        </template>
                    </Column>
                    <Column field="priority" header="Priorite" style="width: 100px">
                        <template #body="{ data }">
                            <Tag :value="priorityLabel(data.priority)" :severity="priorityColor(data.priority)" size="small" />
                        </template>
                    </Column>
                    <Column header="Statut" style="width: 80px">
                        <template #body="{ data }">
                            <i v-if="data.completed_at" class="pi pi-check-circle text-green-500"></i>
                            <i v-else class="pi pi-circle text-gray-300"></i>
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 140px">
                        <template #body="{ data }">
                            <div class="flex gap-1">
                                <Button v-if="!data.completed_at" icon="pi pi-check" text rounded size="small" severity="success" @click="openCompleteDialog(data)" v-tooltip="'Completer'" />
                                <Link :href="`/app/tasks/${data.id}/edit`"><Button icon="pi pi-pencil" text rounded size="small" /></Link>
                                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteTask(data.id)" />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="empty-state">
                            <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                                <i class="pi pi-check-square"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-400">Aucune tache trouvee</p>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <div v-if="tasks.links?.length > 3" class="flex justify-center gap-1 mt-5">
            <template v-for="link in tasks.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['pg-link', link.active ? 'bg-[#4A6CF7] text-white' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700']" v-html="link.label" preserve-state />
                <span v-else class="pg-link text-gray-300 dark:text-gray-600" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
