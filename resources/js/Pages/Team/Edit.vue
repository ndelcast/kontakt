<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';

defineOptions({ layout: AppLayout });

const props = defineProps({ team: Object });

const form = useForm({ ...props.team });

const submit = () => form.put('/app/team');
</script>

<template>
    <div class="max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Paramètres de l'équipe</h1>
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
                        <label class="block text-sm font-medium mb-1">Slug</label>
                        <InputText v-model="form.slug" class="w-full" :invalid="!!form.errors.slug" />
                        <small v-if="form.errors.slug" class="text-red-500">{{ form.errors.slug }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <Textarea v-model="form.description" rows="3" class="w-full" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <Button label="Enregistrer" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>
