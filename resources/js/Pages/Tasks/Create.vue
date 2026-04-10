<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Card from 'primevue/card';

defineOptions({ layout: AppLayout });

const props = defineProps({ taskTypes: Array, opportunities: Array, contacts: Array, companies: Array });

const form = useForm({
    type: 'call',
    title: '',
    description: '',
    due_date: new Date().toISOString().split('T')[0],
    due_time: '',
    priority: 0,
    opportunity_id: null,
    contact_id: null,
    company_id: null,
});

const priorityOptions = [
    { value: 0, label: 'Normal' },
    { value: 1, label: 'High' },
    { value: 2, label: 'Urgent' },
];

const submit = () => form.post('/app/tasks');
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/tasks"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Nouvelle tâche</h1>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Type *</label>
                            <Select v-model="form.type" :options="taskTypes" optionLabel="label" optionValue="value" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Priorité</label>
                            <Select v-model="form.priority" :options="priorityOptions" optionLabel="label" optionValue="value" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Titre *</label>
                        <InputText v-model="form.title" class="w-full" :invalid="!!form.errors.title" />
                        <small v-if="form.errors.title" class="text-red-500">{{ form.errors.title }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <Textarea v-model="form.description" rows="3" class="w-full" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Date d'échéance</label>
                            <InputText v-model="form.due_date" type="date" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Heure</label>
                            <InputText v-model="form.due_time" type="time" class="w-full" />
                        </div>
                    </div>

                    <div class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold mb-3">Associer à</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Opportunité</label>
                                <Select v-model="form.opportunity_id" :options="opportunities" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Contact</label>
                                <Select v-model="form.contact_id" :options="contacts" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Entreprise</label>
                                <Select v-model="form.company_id" :options="companies" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Link href="/app/tasks"><Button label="Annuler" severity="secondary" outlined /></Link>
                        <Button label="Créer" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>
