<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';

defineOptions({ layout: GuestLayout });

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => form.post('/login');
</script>

<template>
    <div>
        <h1 class="text-xl font-bold text-gray-900 text-center mb-1">Connexion</h1>
        <p class="text-sm text-gray-400 text-center mb-6">
            ou <Link href="/register" class="text-[#4A6CF7] font-semibold hover:text-[#3451D1]">creer un compte</Link>
        </p>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email *</label>
                <InputText v-model="form.email" type="email" class="w-full" :invalid="!!form.errors.email" autofocus />
                <small v-if="form.errors.email" class="text-rose-500 mt-1">{{ form.errors.email }}</small>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Mot de passe *</label>
                <InputText v-model="form.password" type="password" class="w-full" :invalid="!!form.errors.password" />
                <small v-if="form.errors.password" class="text-rose-500 mt-1">{{ form.errors.password }}</small>
            </div>
            <div class="flex items-center gap-2">
                <Checkbox v-model="form.remember" :binary="true" inputId="remember" />
                <label for="remember" class="text-sm text-gray-600">Se souvenir de moi</label>
            </div>
            <Button label="Se connecter" type="submit" :loading="form.processing" class="w-full" />
        </form>
    </div>
</template>
