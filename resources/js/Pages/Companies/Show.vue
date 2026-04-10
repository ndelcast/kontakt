<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useFormatters } from '@/Composables/useFormatters';

defineOptions({ layout: AppLayout });

const props = defineProps({ company: Object });
const { formatCurrency, formatDate } = useFormatters();
</script>

<template>
    <div>
        <div class="flex items-center gap-3 mb-6">
            <Link href="/app/companies"><Button icon="pi pi-arrow-left" text rounded /></Link>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">{{ company.name }}</h1>
                <p v-if="company.industry" class="text-surface-500">{{ company.industry }}</p>
            </div>
            <Link :href="`/app/companies/${company.id}/edit`">
                <Button label="Modifier" icon="pi pi-pencil" outlined />
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <Card class="lg:col-span-1">
                <template #title>Informations</template>
                <template #content>
                    <div class="space-y-3 text-sm">
                        <div v-if="company.website">
                            <span class="text-surface-500">Site web</span>
                            <a :href="company.website" target="_blank" class="block text-primary hover:underline">{{ company.website }}</a>
                        </div>
                        <div v-if="company.phone">
                            <span class="text-surface-500">Téléphone</span>
                            <p>{{ company.phone }}</p>
                        </div>
                        <div v-if="company.address">
                            <span class="text-surface-500">Adresse</span>
                            <p>{{ company.address }}</p>
                        </div>
                        <div v-if="company.notes">
                            <span class="text-surface-500">Notes</span>
                            <p class="whitespace-pre-line">{{ company.notes }}</p>
                        </div>
                    </div>
                </template>
            </Card>

            <div class="lg:col-span-2 space-y-6">
                <Card>
                    <template #title>Contacts ({{ company.contacts.length }})</template>
                    <template #content>
                        <DataTable :value="company.contacts" class="text-sm">
                            <Column field="name" header="Nom">
                                <template #body="{ data }">
                                    <Link :href="`/app/contacts/${data.id}/edit`" class="text-primary hover:underline">{{ data.name }}</Link>
                                </template>
                            </Column>
                            <Column field="email" header="Email" />
                            <Column field="phone" header="Téléphone" />
                            <Column field="position" header="Poste" />
                            <template #empty>Aucun contact</template>
                        </DataTable>
                    </template>
                </Card>

                <Card>
                    <template #title>Opportunités ({{ company.opportunities.length }})</template>
                    <template #content>
                        <DataTable :value="company.opportunities" class="text-sm">
                            <Column field="name" header="Nom">
                                <template #body="{ data }">
                                    <Link :href="`/app/opportunities/${data.id}/edit`" class="text-primary hover:underline">{{ data.name }}</Link>
                                </template>
                            </Column>
                            <Column field="stage" header="Étape">
                                <template #body="{ data }">
                                    <Tag :value="data.stage" :style="{ backgroundColor: data.stage_color || '#6366f1', color: 'white' }" />
                                </template>
                            </Column>
                            <Column field="value" header="Valeur">
                                <template #body="{ data }">
                                    <span class="font-semibold text-green-600">{{ formatCurrency(data.value) }}</span>
                                </template>
                            </Column>
                            <Column field="expected_close_date" header="Clôture prévue">
                                <template #body="{ data }">{{ formatDate(data.expected_close_date) }}</template>
                            </Column>
                            <template #empty>Aucune opportunité</template>
                        </DataTable>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>
