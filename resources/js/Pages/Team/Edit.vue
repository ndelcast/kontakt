<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

defineOptions({ layout: AppLayout });

const props = defineProps({ team: Object });

const form = useForm({ ...props.team });

const submit = () => form.put('/app/team');
</script>

<template>
    <div class="max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Parametres de l'equipe</h1>
        </div>

        <div class="panel">
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom *</label>
                    <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                    <small v-if="form.errors.name" class="text-rose-500 mt-1">{{ form.errors.name }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Slug</label>
                    <InputText v-model="form.slug" class="w-full" :invalid="!!form.errors.slug" />
                    <small v-if="form.errors.slug" class="text-rose-500 mt-1">{{ form.errors.slug }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Description</label>
                    <Textarea v-model="form.description" rows="3" class="w-full" />
                </div>
                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <Button label="Enregistrer" type="submit" :loading="form.processing" size="small" />
                </div>
            </form>
        </div>
    </div>
</template>
