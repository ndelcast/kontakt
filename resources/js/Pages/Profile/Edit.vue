<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Card from 'primevue/card';

defineOptions({ layout: AppLayout });

const props = defineProps({ user: Object });

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    locale: props.user.locale,
    current_password: '',
    password: '',
    password_confirmation: '',
});

const localeOptions = [
    { value: 'en', label: 'English' },
    { value: 'fr', label: 'Français' },
    { value: 'es', label: 'Español' },
];

const submit = () => form.put('/app/profile');
</script>

<template>
    <div class="max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Profil</h1>
        </div>

        <Card class="mb-6">
            <template #title>Informations personnelles</template>
            <template #content>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom *</label>
                        <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                        <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email *</label>
                        <InputText v-model="form.email" type="email" class="w-full" :invalid="!!form.errors.email" />
                        <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Langue</label>
                        <Select v-model="form.locale" :options="localeOptions" optionLabel="label" optionValue="value" class="w-full" />
                    </div>

                    <div class="border-t pt-4 mt-4">
                        <h3 class="text-sm font-semibold mb-3">Changer le mot de passe</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Mot de passe actuel</label>
                                <InputText v-model="form.current_password" type="password" class="w-full" :invalid="!!form.errors.current_password" />
                                <small v-if="form.errors.current_password" class="text-red-500">{{ form.errors.current_password }}</small>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Nouveau mot de passe</label>
                                <InputText v-model="form.password" type="password" class="w-full" :invalid="!!form.errors.password" />
                                <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Confirmer le mot de passe</label>
                                <InputText v-model="form.password_confirmation" type="password" class="w-full" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <Button label="Enregistrer" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>
