<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';

defineOptions({ layout: AppLayout });

const props = defineProps({ company: Object });

const form = useForm({ ...props.company });

const submit = () => form.put(`/app/companies/${props.company.id}`);
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/companies"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Modifier l'entreprise</h1>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom *</label>
                        <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                        <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Secteur</label>
                            <InputText v-model="form.industry" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Site web</label>
                            <InputText v-model="form.website" class="w-full" placeholder="https://" />
                            <small v-if="form.errors.website" class="text-red-500">{{ form.errors.website }}</small>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone</label>
                        <InputText v-model="form.phone" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Adresse</label>
                        <Textarea v-model="form.address" rows="2" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Notes</label>
                        <Textarea v-model="form.notes" rows="3" class="w-full" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <Link href="/app/companies"><Button label="Annuler" severity="secondary" outlined /></Link>
                        <Button label="Enregistrer" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>
