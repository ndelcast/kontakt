<script setup>
import { ref } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import { useFormatters } from '@/Composables/useFormatters';

defineOptions({ layout: AppLayout });

const props = defineProps({ overdue: Array, today: Array, upcoming: Array, taskTypes: Array });
const { formatDate, priorityColor, priorityLabel, taskTypeColor } = useFormatters();

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

const quickAddDialog = ref(false);
const quickForm = useForm({
    type: 'call',
    title: '',
    due_date: new Date().toISOString().split('T')[0],
    due_time: '',
    priority: 0,
    description: '',
    opportunity_id: null,
    contact_id: null,
    company_id: null,
});

const submitQuickAdd = () => {
    quickForm.post('/app/tasks', {
        onSuccess: () => {
            quickAddDialog.value = false;
            quickForm.reset();
        },
    });
};

const priorityOptions = [
    { value: 0, label: 'Normal' },
    { value: 1, label: 'High' },
    { value: 2, label: 'Urgent' },
];

const totalTasks = props.overdue.length + props.today.length + props.upcoming.length;
</script>

<template>
    <div>
        <Dialog v-model:visible="completeDialog" header="Completer la tache" :style="{ width: '30rem' }" modal>
            <div class="space-y-4">
                <p class="text-sm text-gray-500">Tache : <strong class="text-gray-900 dark:text-white">{{ completingTask?.title }}</strong></p>
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

        <Dialog v-model:visible="quickAddDialog" header="Nouvelle tache" :style="{ width: '30rem' }" modal>
            <form @submit.prevent="submitQuickAdd" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Type</label>
                    <Select v-model="quickForm.type" :options="taskTypes" optionLabel="label" optionValue="value" class="w-full" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Titre *</label>
                    <InputText v-model="quickForm.title" class="w-full" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date</label>
                        <InputText v-model="quickForm.due_date" type="date" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Priorite</label>
                        <Select v-model="quickForm.priority" :options="priorityOptions" optionLabel="label" optionValue="value" class="w-full" />
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button label="Annuler" severity="secondary" outlined @click="quickAddDialog = false" />
                    <Button label="Creer" type="submit" :loading="quickForm.processing" />
                </div>
            </form>
        </Dialog>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Ma Journee</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ totalTasks }} taches au programme</p>
            </div>
            <div class="flex gap-2">
                <Link href="/app/tasks">
                    <Button label="Toutes les taches" icon="pi pi-list" severity="secondary" outlined size="small" />
                </Link>
                <Button label="Nouvelle tache" icon="pi pi-plus" size="small" @click="quickAddDialog = true" />
            </div>
        </div>

        <!-- Overdue -->
        <div v-if="overdue.length > 0" class="mb-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-lg bg-rose-500 flex items-center justify-center">
                    <i class="pi pi-exclamation-triangle text-white text-[0.6rem]"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">En retard</h3>
                <span class="text-xs font-bold text-rose-500 bg-rose-50 dark:bg-rose-950/30 px-2 py-0.5 rounded-md">{{ overdue.length }}</span>
            </div>
            <div class="space-y-2">
                <div v-for="task in overdue" :key="task.id" class="panel flex items-center gap-3 p-3.5 !border-rose-200 dark:!border-rose-900/30 bg-rose-50/50 dark:bg-rose-950/10">
                    <Button icon="pi pi-check" rounded size="small" severity="success" @click="openCompleteDialog(task)" />
                    <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                    <div class="flex-1 min-w-0">
                        <Link :href="`/app/tasks/${task.id}/edit`" class="font-semibold text-sm hover:text-[#4A6CF7] text-gray-900 dark:text-white">{{ task.title }}</Link>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ [task.opportunity?.name, task.contact?.name, task.company?.name].filter(Boolean).join(' · ') }}
                        </p>
                    </div>
                    <span class="text-xs text-rose-500 font-bold">{{ formatDate(task.due_date) }}</span>
                    <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                </div>
            </div>
        </div>

        <!-- Today -->
        <div class="mb-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-lg bg-[#4A6CF7] flex items-center justify-center">
                    <i class="pi pi-sun text-white text-[0.6rem]"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Aujourd'hui</h3>
                <span class="text-xs font-bold text-[#4A6CF7] bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 px-2 py-0.5 rounded-md">{{ today.length }}</span>
            </div>
            <div v-if="today.length === 0" class="panel p-8 text-center">
                <div class="empty-icon bg-green-50 dark:bg-green-950/30 text-green-500 mx-auto inline-flex items-center justify-center w-12 h-12 rounded-xl mb-3">
                    <i class="pi pi-check-circle text-xl"></i>
                </div>
                <p class="text-sm font-medium text-gray-400">Aucune tache pour aujourd'hui</p>
            </div>
            <div v-else class="space-y-2">
                <div v-for="task in today" :key="task.id" class="panel flex items-center gap-3 p-3.5">
                    <Button icon="pi pi-check" rounded size="small" severity="success" @click="openCompleteDialog(task)" />
                    <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                    <div class="flex-1 min-w-0">
                        <Link :href="`/app/tasks/${task.id}/edit`" class="font-semibold text-sm hover:text-[#4A6CF7] text-gray-900 dark:text-white">{{ task.title }}</Link>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ [task.opportunity?.name, task.contact?.name, task.company?.name].filter(Boolean).join(' · ') }}
                        </p>
                    </div>
                    <span v-if="task.due_time" class="text-xs text-gray-400 font-medium">{{ task.due_time }}</span>
                    <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                </div>
            </div>
        </div>

        <!-- Upcoming -->
        <div v-if="upcoming.length > 0">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <i class="pi pi-clock text-gray-500 text-[0.6rem]"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">A venir</h3>
                <span class="text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">{{ upcoming.length }}</span>
            </div>
            <div class="space-y-2">
                <div v-for="task in upcoming" :key="task.id" class="panel flex items-center gap-3 p-3.5">
                    <Button icon="pi pi-check" rounded size="small" severity="success" @click="openCompleteDialog(task)" />
                    <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                    <div class="flex-1 min-w-0">
                        <Link :href="`/app/tasks/${task.id}/edit`" class="font-semibold text-sm hover:text-[#4A6CF7] text-gray-900 dark:text-white">{{ task.title }}</Link>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ [task.opportunity?.name, task.contact?.name, task.company?.name].filter(Boolean).join(' · ') }}
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 font-medium">{{ formatDate(task.due_date) }}</span>
                    <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                </div>
            </div>
        </div>
    </div>
</template>
