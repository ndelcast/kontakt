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
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Entreprises</h1>
            <Link href="/app/companies/create">
                <Button label="Nouvelle entreprise" icon="pi pi-plus" />
            </Link>
        </div>

        <div class="mb-4">
            <InputText v-model="search" placeholder="Rechercher..." class="w-full sm:w-64" />
        </div>

        <DataTable :value="companies.data" :rows="15" stripedRows class="text-sm">
            <Column field="name" header="Nom" sortable>
                <template #body="{ data }">
                    <Link :href="`/app/companies/${data.id}`" class="font-medium text-primary hover:underline">{{ data.name }}</Link>
                </template>
            </Column>
            <Column field="industry" header="Secteur">
                <template #body="{ data }">
                    <Tag v-if="data.industry" :value="data.industry" severity="secondary" />
                </template>
            </Column>
            <Column field="phone" header="Téléphone" />
            <Column field="contacts_count" header="Contacts">
                <template #body="{ data }">
                    <Tag :value="String(data.contacts_count)" severity="info" />
                </template>
            </Column>
            <Column field="opportunities_count" header="Opportunités">
                <template #body="{ data }">
                    <Tag :value="String(data.opportunities_count)" severity="success" />
                </template>
            </Column>
            <Column field="created_at" header="Créé" />
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
                <div class="text-center py-8 text-surface-500">Aucune entreprise trouvée</div>
            </template>
        </DataTable>

        <div v-if="companies.links?.length > 3" class="flex justify-center gap-1 mt-4">
            <template v-for="link in companies.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-primary text-white' : 'bg-surface-100 dark:bg-surface-800 hover:bg-surface-200']" v-html="link.label" preserve-state />
                <span v-else class="px-3 py-1 rounded text-sm text-surface-400" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
