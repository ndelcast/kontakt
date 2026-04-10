<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import Card from 'primevue/card';

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
            <Link href="/app/contacts"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Nouveau contact</h1>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom *</label>
                        <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                        <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Entreprise</label>
                        <Select v-model="form.company_id" :options="companies" optionLabel="name" optionValue="id" placeholder="Sélectionner..." showClear filter class="w-full" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <InputText v-model="form.email" type="email" class="w-full" :invalid="!!form.errors.email" />
                            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Téléphone</label>
                            <InputText v-model="form.phone" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Poste</label>
                        <InputText v-model="form.position" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Notes</label>
                        <Textarea v-model="form.notes" rows="3" class="w-full" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <Link href="/app/contacts"><Button label="Annuler" severity="secondary" outlined /></Link>
                        <Button label="Créer" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>
