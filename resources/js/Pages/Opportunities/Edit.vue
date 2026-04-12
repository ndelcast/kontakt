<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';

defineOptions({ layout: AppLayout });

const props = defineProps({ opportunity: Object, stages: Array, companies: Array, contacts: Array });

const form = useForm({ ...props.opportunity });

const filteredContacts = computed(() => {
    if (!form.company_id) return props.contacts;
    return props.contacts.filter(c => c.company_id === form.company_id);
});

const submit = () => form.put(`/app/opportunities/${props.opportunity.id}`);
</script>

<template>
    <div class="max-w-2xl">
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/opportunities"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Modifier l'opportunite</h1>
        </div>

        <div class="panel">
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nom *</label>
                    <InputText v-model="form.name" class="w-full" :invalid="!!form.errors.name" />
                    <small v-if="form.errors.name" class="text-rose-500 mt-1">{{ form.errors.name }}</small>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Etape *</label>
                    <Select v-model="form.pipeline_stage_id" :options="stages" optionLabel="name" optionValue="id" class="w-full" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Entreprise</label>
                        <Select v-model="form.company_id" :options="companies" optionLabel="name" optionValue="id" showClear filter class="w-full" @change="form.contact_id = null" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Contact</label>
                        <Select v-model="form.contact_id" :options="filteredContacts" optionLabel="name" optionValue="id" showClear filter class="w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Valeur (EUR)</label>
                    <InputNumber v-model="form.value" mode="currency" currency="EUR" locale="fr-FR" class="w-full" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date de debut</label>
                        <InputText v-model="form.started_at" type="date" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Cloture prevue</label>
                        <InputText v-model="form.expected_close_date" type="date" class="w-full" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Date de gain</label>
                        <InputText v-model="form.won_at" type="date" class="w-full" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                    <Textarea v-model="form.notes" rows="3" class="w-full" />
                </div>
                <div class="flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                    <Link href="/app/opportunities"><Button label="Annuler" severity="secondary" outlined size="small" /></Link>
                    <Button label="Enregistrer" type="submit" :loading="form.processing" size="small" />
                </div>
            </form>
        </div>
    </div>
</template>
