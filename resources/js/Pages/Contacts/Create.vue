<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';

defineOptions({ layout: AppLayout });

const props = defineProps({ companies: Array });

const form = useForm({
    name: '',
    email: '',
    phone: '',
    position: '',
    company_id: null,
    notes: '',
});

const submit = () => form.post('/app/contacts');
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/contacts"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Nouveau contact</h1>
        </div>

        <div class="panel">
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom *</label>
                    <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                    <small v-if="form.errors.name" class="text-rose-500 mt-1">{{ form.errors.name }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Entreprise</label>
                    <Select v-model="form.company_id" :options="companies" optionLabel="name" optionValue="id" placeholder="Selectionner..." showClear filter class="w-full" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
                        <InputText v-model="form.email" type="email" class="w-full" :invalid="!!form.errors.email" />
                        <small v-if="form.errors.email" class="text-rose-500 mt-1">{{ form.errors.email }}</small>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Telephone</label>
                        <InputText v-model="form.phone" class="w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Poste</label>
                    <InputText v-model="form.position" class="w-full" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                    <Textarea v-model="form.notes" rows="3" class="w-full" />
                </div>
                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <Link href="/app/contacts"><Button label="Annuler" severity="secondary" outlined size="small" /></Link>
                    <Button label="Creer" type="submit" :loading="form.processing" size="small" />
                </div>
            </form>
        </div>
    </div>
</template>
