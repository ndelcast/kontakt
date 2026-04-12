<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import ColorPicker from 'primevue/colorpicker';
import ToggleSwitch from 'primevue/toggleswitch';

defineOptions({ layout: AppLayout });

const props = defineProps({ stage: Object });

const form = useForm({ ...props.stage });

const submit = () => form.put(`/app/pipeline/${props.stage.id}`);
</script>

<template>
    <div class="max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/pipeline"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Modifier l'etape</h1>
        </div>

        <div class="panel">
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom *</label>
                    <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Slug *</label>
                    <InputText v-model="form.slug" class="w-full" :invalid="!!form.errors.slug" />
                    <small v-if="form.errors.slug" class="text-rose-500 mt-1">{{ form.errors.slug }}</small>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Couleur</label>
                        <div class="flex items-center gap-2">
                            <ColorPicker v-model="form.color" format="hex" />
                            <InputText v-model="form.color" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Probabilite (%)</label>
                        <InputNumber v-model="form.probability" :min="0" :max="100" suffix="%" class="w-full" />
                    </div>
                </div>
                <div class="flex gap-8">
                    <div class="flex items-center gap-2">
                        <ToggleSwitch v-model="form.is_won" />
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Etape "Gagne"</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <ToggleSwitch v-model="form.is_lost" />
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Etape "Perdu"</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <Link href="/app/pipeline"><Button label="Annuler" severity="secondary" outlined size="small" /></Link>
                    <Button label="Enregistrer" type="submit" :loading="form.processing" size="small" />
                </div>
            </form>
        </div>
    </div>
</template>
