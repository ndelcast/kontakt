<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

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
    { value: 'fr', label: 'Francais' },
    { value: 'es', label: 'Espanol' },
];

const submit = () => form.put('/app/profile');
</script>

<template>
    <div class="max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Profil</h1>
        </div>

        <div class="panel">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Informations personnelles</h3>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom *</label>
                    <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                    <small v-if="form.errors.name" class="text-rose-500 mt-1">{{ form.errors.name }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email *</label>
                    <InputText v-model="form.email" type="email" class="w-full" :invalid="!!form.errors.email" />
                    <small v-if="form.errors.email" class="text-rose-500 mt-1">{{ form.errors.email }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Langue</label>
                    <Select v-model="form.locale" :options="localeOptions" optionLabel="label" optionValue="value" class="w-full" />
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800 pt-5 mt-5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Changer le mot de passe</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Mot de passe actuel</label>
                            <InputText v-model="form.current_password" type="password" class="w-full" :invalid="!!form.errors.current_password" />
                            <small v-if="form.errors.current_password" class="text-rose-500 mt-1">{{ form.errors.current_password }}</small>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nouveau mot de passe</label>
                            <InputText v-model="form.password" type="password" class="w-full" :invalid="!!form.errors.password" />
                            <small v-if="form.errors.password" class="text-rose-500 mt-1">{{ form.errors.password }}</small>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Confirmer le mot de passe</label>
                            <InputText v-model="form.password_confirmation" type="password" class="w-full" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <Button label="Enregistrer" type="submit" :loading="form.processing" size="small" />
                </div>
            </form>
        </div>
    </div>
</template>
