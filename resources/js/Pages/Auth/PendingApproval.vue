<script setup>
import { router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from 'primevue/button';

defineOptions({ layout: GuestLayout });

const logout = () => router.post('/logout');
const refresh = () => router.reload();
</script>

<template>
    <div class="text-center">
        <div class="w-14 h-14 rounded-2xl bg-[#EEF1FE] flex items-center justify-center mx-auto mb-5">
            <i class="pi pi-clock text-[#4A6CF7] text-2xl"></i>
        </div>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Compte en attente d'approbation</h1>
        <p class="text-sm text-gray-400 mb-8">
            Votre compte a ete cree avec succes. Un administrateur doit approuver votre acces avant que vous puissiez continuer.
        </p>
        <div class="flex gap-3 justify-center">
            <Button label="Verifier" icon="pi pi-refresh" severity="secondary" outlined size="small" @click="refresh" />
            <Button label="Se deconnecter" icon="pi pi-sign-out" severity="secondary" size="small" @click="logout" />
        </div>
    </div>
</template>
