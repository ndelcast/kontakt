<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    invitation: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    name: '',
    email: props.invitation?.email || '',
    password: '',
    password_confirmation: '',
    team_name: '',
});

const submit = () => form.post('/register');
</script>

<template>
    <div>
        <h1 class="text-xl font-bold text-gray-900 text-center mb-1">Creer un compte</h1>
        <p class="text-sm text-gray-400 text-center mb-6">
            ou <Link href="/login" class="text-[#4A6CF7] font-semibold hover:text-[#3451D1]">se connecter</Link>
        </p>

        <!-- Invitation banner -->
        <div v-if="invitation" class="rounded-xl bg-[#EEF1FE] p-4 mb-6">
            <div class="flex items-start gap-3">
                <i class="pi pi-users text-[#4A6CF7] text-lg mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        Vous avez ete invite a rejoindre <strong>{{ invitation.team_name }}</strong>
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">Creez un compte pour accepter l'invitation.</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom *</label>
                <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" autofocus />
                <small v-if="form.errors.name" class="text-rose-500 mt-1">{{ form.errors.name }}</small>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Email *</label>
                <InputText v-model="form.email" type="email" class="w-full" :invalid="!!form.errors.email" :disabled="!!invitation" />
                <small v-if="form.errors.email" class="text-rose-500 mt-1">{{ form.errors.email }}</small>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Mot de passe *</label>
                <InputText v-model="form.password" type="password" class="w-full" :invalid="!!form.errors.password" />
                <small v-if="form.errors.password" class="text-rose-500 mt-1">{{ form.errors.password }}</small>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Confirmer le mot de passe *</label>
                <InputText v-model="form.password_confirmation" type="password" class="w-full" />
            </div>
            <div v-if="!invitation">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom de l'equipe *</label>
                <InputText v-model="form.team_name" class="w-full" :invalid="!!form.errors.team_name" placeholder="Votre entreprise ou equipe" />
                <small v-if="form.errors.team_name" class="text-rose-500 mt-1">{{ form.errors.team_name }}</small>
            </div>
            <Button label="Creer le compte" type="submit" :loading="form.processing" class="w-full" />
        </form>
    </div>
</template>
