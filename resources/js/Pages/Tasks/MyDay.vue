<script setup>
import { ref } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import { useFormatters } from '@/Composables/useFormatters';

defineOptions({ layout: AppLayout });

const props = defineProps({ overdue: Array, today: Array, upcoming: Array, taskTypes: Array });
const { formatDate, priorityColor, priorityLabel, taskTypeColor } = useFormatters();

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

// Quick add
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
</script>

<template>
    <div>
        <Dialog v-model:visible="completeDialog" header="Compléter la tâche" :style="{ width: '30rem' }" modal>
            <div class="space-y-4">
                <p class="text-sm text-surface-500">Tâche : <strong>{{ completingTask?.title }}</strong></p>
                <div>
                    <label class="block text-sm font-medium mb-1">Résultat (optionnel)</label>
                    <Textarea v-model="completionOutcome" rows="3" class="w-full" />
                </div>
                <div class="flex justify-end gap-3">
                    <Button label="Annuler" severity="secondary" outlined @click="completeDialog = false" />
                    <Button label="Compléter" icon="pi pi-check" @click="submitComplete" />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="quickAddDialog" header="Nouvelle tâche" :style="{ width: '30rem' }" modal>
            <form @submit.prevent="submitQuickAdd" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Type</label>
                    <Select v-model="quickForm.type" :options="taskTypes" optionLabel="label" optionValue="value" class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Titre *</label>
                    <InputText v-model="quickForm.title" class="w-full" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Date</label>
                        <InputText v-model="quickForm.due_date" type="date" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Priorité</label>
                        <Select v-model="quickForm.priority" :options="priorityOptions" optionLabel="label" optionValue="value" class="w-full" />
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <Button label="Annuler" severity="secondary" outlined @click="quickAddDialog = false" />
                    <Button label="Créer" type="submit" :loading="quickForm.processing" />
                </div>
            </form>
        </Dialog>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Ma Journée</h1>
            <div class="flex gap-2">
                <Link href="/app/tasks">
                    <Button label="Toutes les tâches" icon="pi pi-list" severity="secondary" outlined />
                </Link>
                <Button label="Nouvelle tâche" icon="pi pi-plus" @click="quickAddDialog = true" />
            </div>
        </div>

        <!-- Overdue -->
        <Card v-if="overdue.length > 0" class="!shadow-sm mb-4 !border-red-200 dark:!border-red-800">
            <template #title>
                <div class="flex items-center gap-2 text-red-600">
                    <i class="pi pi-exclamation-triangle"></i>
                    <span>En retard ({{ overdue.length }})</span>
                </div>
            </template>
            <template #content>
                <div class="space-y-2">
                    <div v-for="task in overdue" :key="task.id" class="flex items-center gap-3 p-3 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900">
                        <Button icon="pi pi-check" rounded size="small" severity="success" @click="openCompleteDialog(task)" />
                        <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                        <div class="flex-1 min-w-0">
                            <Link :href="`/app/tasks/${task.id}/edit`" class="font-medium text-sm hover:text-primary">{{ task.title }}</Link>
                            <p class="text-xs text-surface-500">
                                {{ [task.opportunity?.name, task.contact?.name, task.company?.name].filter(Boolean).join(' · ') }}
                            </p>
                        </div>
                        <span class="text-xs text-red-600 font-medium">{{ formatDate(task.due_date) }}</span>
                        <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Today -->
        <Card class="!shadow-sm mb-4">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="pi pi-calendar text-primary"></i>
                    <span>Aujourd'hui ({{ today.length }})</span>
                </div>
            </template>
            <template #content>
                <div v-if="today.length === 0" class="text-center py-6 text-surface-500">
                    <i class="pi pi-check-circle text-3xl mb-2"></i>
                    <p>Aucune tâche pour aujourd'hui</p>
                </div>
                <div v-else class="space-y-2">
                    <div v-for="task in today" :key="task.id" class="flex items-center gap-3 p-3 rounded-lg border border-surface-200 dark:border-surface-700">
                        <Button icon="pi pi-check" rounded size="small" severity="success" @click="openCompleteDialog(task)" />
                        <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                        <div class="flex-1 min-w-0">
                            <Link :href="`/app/tasks/${task.id}/edit`" class="font-medium text-sm hover:text-primary">{{ task.title }}</Link>
                            <p class="text-xs text-surface-500">
                                {{ [task.opportunity?.name, task.contact?.name, task.company?.name].filter(Boolean).join(' · ') }}
                            </p>
                        </div>
                        <span v-if="task.due_time" class="text-xs text-surface-500">{{ task.due_time }}</span>
                        <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Upcoming -->
        <Card v-if="upcoming.length > 0" class="!shadow-sm">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="pi pi-clock text-surface-500"></i>
                    <span>À venir ({{ upcoming.length }})</span>
                </div>
            </template>
            <template #content>
                <div class="space-y-2">
                    <div v-for="task in upcoming" :key="task.id" class="flex items-center gap-3 p-3 rounded-lg border border-surface-200 dark:border-surface-700">
                        <Button icon="pi pi-check" rounded size="small" severity="success" @click="openCompleteDialog(task)" />
                        <Tag :value="task.type_label" :severity="taskTypeColor(task.type)" size="small" />
                        <div class="flex-1 min-w-0">
                            <Link :href="`/app/tasks/${task.id}/edit`" class="font-medium text-sm hover:text-primary">{{ task.title }}</Link>
                            <p class="text-xs text-surface-500">
                                {{ [task.opportunity?.name, task.contact?.name, task.company?.name].filter(Boolean).join(' · ') }}
                            </p>
                        </div>
                        <span class="text-xs text-surface-500">{{ formatDate(task.due_date) }}</span>
                        <Tag :value="priorityLabel(task.priority)" :severity="priorityColor(task.priority)" size="small" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
