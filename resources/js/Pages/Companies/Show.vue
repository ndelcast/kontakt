<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
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
            <Link href="/app/companies"><Button icon="pi pi-arrow-left" text rounded size="small" /></Link>
            <div class="flex-1">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ company.name }}</h1>
                <p v-if="company.industry" class="text-sm text-gray-400 mt-0.5">{{ company.industry }}</p>
            </div>
            <Link :href="`/app/companies/${company.id}/edit`">
                <Button label="Modifier" icon="pi pi-pencil" outlined size="small" />
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
            <div class="lg:col-span-1 panel">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Informations</h3>
                </div>
                <div class="p-5 space-y-4 text-sm">
                    <div v-if="company.website">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Site web</span>
                        <a :href="company.website" target="_blank" class="block text-[#4A6CF7] dark:text-[#7B93FA] hover:underline mt-0.5">{{ company.website }}</a>
                    </div>
                    <div v-if="company.phone">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Telephone</span>
                        <p class="mt-0.5 text-gray-700 dark:text-gray-300">{{ company.phone }}</p>
                    </div>
                    <div v-if="company.address">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Adresse</span>
                        <p class="mt-0.5 text-gray-700 dark:text-gray-300">{{ company.address }}</p>
                    </div>
                    <div v-if="company.notes">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Notes</span>
                        <p class="mt-0.5 whitespace-pre-line text-gray-700 dark:text-gray-300">{{ company.notes }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-5">
                <div class="panel">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Contacts</h3>
                        <span class="text-xs font-semibold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">{{ company.contacts.length }}</span>
                    </div>
                    <div class="p-5 pt-0">
                        <DataTable :value="company.contacts" class="text-sm">
                            <Column field="name" header="Nom">
                                <template #body="{ data }">
                                    <Link :href="`/app/contacts/${data.id}/edit`" class="text-[#4A6CF7] dark:text-[#7B93FA] hover:underline font-semibold">{{ data.name }}</Link>
                                </template>
                            </Column>
                            <Column field="email" header="Email">
                                <template #body="{ data }">
                                    <span class="text-gray-500">{{ data.email }}</span>
                                </template>
                            </Column>
                            <Column field="phone" header="Telephone">
                                <template #body="{ data }">
                                    <span class="text-gray-500">{{ data.phone }}</span>
                                </template>
                            </Column>
                            <Column field="position" header="Poste">
                                <template #body="{ data }">
                                    <Tag v-if="data.position" :value="data.position" severity="secondary" />
                                </template>
                            </Column>
                            <template #empty>
                                <div class="empty-state">
                                    <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                                        <i class="pi pi-users"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-400">Aucun contact</p>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>

                <div class="panel">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Opportunites</h3>
                        <span class="text-xs font-semibold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">{{ company.opportunities.length }}</span>
                    </div>
                    <div class="p-5 pt-0">
                        <DataTable :value="company.opportunities" class="text-sm">
                            <Column field="name" header="Nom">
                                <template #body="{ data }">
                                    <Link :href="`/app/opportunities/${data.id}/edit`" class="text-[#4A6CF7] dark:text-[#7B93FA] hover:underline font-semibold">{{ data.name }}</Link>
                                </template>
                            </Column>
                            <Column field="stage" header="Etape">
                                <template #body="{ data }">
                                    <Tag :value="data.stage" :style="{ backgroundColor: data.stage_color || '#6366f1', color: 'white' }" rounded />
                                </template>
                            </Column>
                            <Column field="value" header="Valeur">
                                <template #body="{ data }">
                                    <span class="font-bold text-teal-600 dark:text-teal-400">{{ formatCurrency(data.value) }}</span>
                                </template>
                            </Column>
                            <Column field="expected_close_date" header="Cloture prevue">
                                <template #body="{ data }">
                                    <span class="text-gray-500">{{ formatDate(data.expected_close_date) }}</span>
                                </template>
                            </Column>
                            <template #empty>
                                <div class="empty-state">
                                    <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                                        <i class="pi pi-chart-bar"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-400">Aucune opportunite</p>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
