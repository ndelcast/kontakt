<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AppLayout });

const props = defineProps({ companies: Object, filters: Object });
const confirm = useConfirm();
const search = ref(props.filters?.search ?? '');

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/app/companies', { search: val || undefined }, { preserveState: true, replace: true });
    }, 300);
});

const deleteCompany = (id) => {
    confirm.require({
        message: 'Supprimer cette entreprise ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(`/app/companies/${id}`),
    });
};
</script>

<template>
    <div>
        <ConfirmDialog />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Entreprises</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ companies.total ?? companies.data?.length ?? 0 }} entreprises</p>
            </div>
            <Link href="/app/companies/create">
                <Button label="Nouvelle entreprise" icon="pi pi-plus" size="small" />
            </Link>
        </div>

        <div class="panel">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <div class="search-box w-full sm:w-64">
                    <i class="pi pi-search search-icon"></i>
                    <InputText v-model="search" placeholder="Rechercher..." class="w-full" />
                </div>
            </div>

            <div class="px-4 pb-2">
                <DataTable :value="companies.data" :rows="15" class="text-sm">
                    <Column field="name" header="Nom" sortable>
                        <template #body="{ data }">
                            <Link :href="`/app/companies/${data.id}`" class="font-semibold text-[#4A6CF7] dark:text-[#7B93FA] hover:text-[#3451D1]">{{ data.name }}</Link>
                        </template>
                    </Column>
                    <Column field="industry" header="Secteur">
                        <template #body="{ data }">
                            <Tag v-if="data.industry" :value="data.industry" severity="secondary" />
                        </template>
                    </Column>
                    <Column field="phone" header="Telephone">
                        <template #body="{ data }">
                            <span v-if="data.phone" class="text-gray-600 dark:text-gray-300">
                                <i class="pi pi-phone text-xs mr-1 text-gray-400"></i>{{ data.phone }}
                            </span>
                        </template>
                    </Column>
                    <Column field="contacts_count" header="Contacts">
                        <template #body="{ data }">
                            <Tag :value="String(data.contacts_count)" severity="info" rounded />
                        </template>
                    </Column>
                    <Column field="opportunities_count" header="Opportunites">
                        <template #body="{ data }">
                            <Tag :value="String(data.opportunities_count)" severity="success" rounded />
                        </template>
                    </Column>
                    <Column field="created_at" header="Cree">
                        <template #body="{ data }">
                            <span class="text-gray-500">{{ data.created_at }}</span>
                        </template>
                    </Column>
                    <Column header="Actions" style="width: 100px">
                        <template #body="{ data }">
                            <div class="flex gap-1">
                                <Link :href="`/app/companies/${data.id}/edit`">
                                    <Button icon="pi pi-pencil" text rounded size="small" />
                                </Link>
                                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteCompany(data.id)" />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="empty-state">
                            <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                                <i class="pi pi-building"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-400">Aucune entreprise trouvee</p>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <div v-if="companies.links?.length > 3" class="flex justify-center gap-1 mt-5">
            <template v-for="link in companies.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['pg-link', link.active ? 'bg-[#4A6CF7] text-white' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700']" v-html="link.label" preserve-state />
                <span v-else class="pg-link text-gray-300 dark:text-gray-600" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
