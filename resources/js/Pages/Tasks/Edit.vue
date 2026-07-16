<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';

defineOptions({ layout: AppLayout });

const props = defineProps({ task: Object, taskTypes: Array, opportunities: Array, contacts: Array, companies: Array });

const form = useForm({ ...props.task });

const priorityOptions = [
    { value: 0, label: 'Normal' },
    { value: 1, label: 'High' },
    { value: 2, label: 'Urgent' },
];

const submit = () => form.put(`/app/tasks/${props.task.id}`);
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/tasks"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Modifier la tache</h1>
        </div>

        <div class="panel">
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Type *</label>
                        <Select v-model="form.type" :options="taskTypes" optionLabel="label" optionValue="value" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Priorite</label>
                        <Select v-model="form.priority" :options="priorityOptions" optionLabel="label" optionValue="value" class="w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Titre *</label>
                    <InputText v-model="form.title" class="w-full" :invalid="!!form.errors.title" />
                    <small v-if="form.errors.title" class="text-rose-500 mt-1">{{ form.errors.title }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Description</label>
                    <Textarea v-model="form.description" rows="3" class="w-full" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date d'echeance</label>
                        <InputText v-model="form.due_date" type="date" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Heure</label>
                        <InputText v-model="form.due_time" type="time" class="w-full" />
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800 pt-5">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">Associer a</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Opportunite</label>
                            <Select v-model="form.opportunity_id" :options="opportunities" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Contact</label>
                            <Select v-model="form.contact_id" :options="contacts" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Entreprise</label>
                            <Select v-model="form.company_id" :options="companies" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                        </div>
                    </div>
                </div>

                <div v-if="form.completed_at" class="border-t border-gray-100 dark:border-gray-800 pt-5">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-4">Completion</h3>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Resultat</label>
                        <Textarea v-model="form.outcome" rows="3" class="w-full" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <Link href="/app/tasks"><Button label="Annuler" severity="secondary" outlined size="small" /></Link>
                    <Button label="Enregistrer" type="submit" :loading="form.processing" size="small" />
                </div>
            </form>
        </div>
    </div>
</template>
