<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ColorPicker from 'primevue/colorpicker';
import ToggleSwitch from 'primevue/toggleswitch';
import Card from 'primevue/card';

defineOptions({ layout: AppLayout });

const form = useForm({
    name: '',
    slug: '',
    color: '#6366f1',
    probability: 50,
    is_won: false,
    is_lost: false,
});

const submit = () => form.post('/app/pipeline');
</script>

<template>
    <div class="max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/pipeline"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Nouvelle étape</h1>
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
                        <label class="block text-sm font-medium mb-1">Slug *</label>
                        <InputText v-model="form.slug" class="w-full" :invalid="!!form.errors.slug" />
                        <small v-if="form.errors.slug" class="text-red-500">{{ form.errors.slug }}</small>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Couleur</label>
                            <div class="flex items-center gap-2">
                                <ColorPicker v-model="form.color" format="hex" />
                                <InputText v-model="form.color" class="w-full" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Probabilité (%)</label>
                            <InputNumber v-model="form.probability" :min="0" :max="100" suffix="%" class="w-full" />
                        </div>
                    </div>
                    <div class="flex gap-8">
                        <div class="flex items-center gap-2">
                            <ToggleSwitch v-model="form.is_won" />
                            <label class="text-sm font-medium">Étape "Gagné"</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <ToggleSwitch v-model="form.is_lost" />
                            <label class="text-sm font-medium">Étape "Perdu"</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <Link href="/app/pipeline"><Button label="Annuler" severity="secondary" outlined /></Link>
                        <Button label="Créer" type="submit" :loading="form.processing" />
                    </div>
                </form>
            </template>
        </Card>
    </div>
</template>
