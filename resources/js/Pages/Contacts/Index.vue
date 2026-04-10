<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AppLayout });

const props = defineProps({
    contacts: Object,
    companies: Array,
    filters: Object,
});

const confirm = useConfirm();
const search = ref(props.filters?.search ?? '');
const companyFilter = ref(props.filters?.company_id ?? null);

let searchTimeout;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/app/contacts', { search: val || undefined, company_id: companyFilter.value || undefined }, { preserveState: true, replace: true });
    }, 300);
});

watch(companyFilter, (val) => {
    router.get('/app/contacts', { search: search.value || undefined, company_id: val || undefined }, { preserveState: true, replace: true });
});

const deleteContact = (id) => {
    confirm.require({
        message: 'Supprimer ce contact ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(`/app/contacts/${id}`),
    });
};
</script>

<template>
    <div>
        <ConfirmDialog />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Contacts</h1>
            <Link href="/app/contacts/create">
                <Button label="Nouveau contact" icon="pi pi-plus" />
            </Link>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <InputText v-model="search" placeholder="Rechercher..." class="w-full sm:w-64" />
            <Select
                v-model="companyFilter"
                :options="companies"
                optionLabel="name"
                optionValue="id"
                placeholder="Toutes les entreprises"
                showClear
                class="w-full sm:w-64"
            />
        </div>

        <DataTable
            :value="contacts.data"
            :rows="15"
            stripedRows
            class="text-sm"
        >
            <Column field="name" header="Nom" sortable>
                <template #body="{ data }">
                    <Link :href="`/app/contacts/${data.id}/edit`" class="font-medium text-primary hover:underline">
                        {{ data.name }}
                    </Link>
                    <span v-if="data.email" class="block text-xs text-surface-500">{{ data.email }}</span>
                </template>
            </Column>
            <Column field="phone" header="Téléphone">
                <template #body="{ data }">
                    <span v-if="data.phone"><i class="pi pi-phone text-xs mr-1"></i>{{ data.phone }}</span>
                </template>
            </Column>
            <Column field="company" header="Entreprise">
                <template #body="{ data }">
                    <span v-if="data.company"><i class="pi pi-building text-xs mr-1"></i>{{ data.company.name }}</span>
                </template>
            </Column>
            <Column field="position" header="Poste">
                <template #body="{ data }">
                    <Tag v-if="data.position" :value="data.position" severity="secondary" />
                </template>
            </Column>
            <Column header="Actions" style="width: 100px">
                <template #body="{ data }">
                    <div class="flex gap-1">
                        <Link :href="`/app/contacts/${data.id}/edit`">
                            <Button icon="pi pi-pencil" text rounded size="small" />
                        </Link>
                        <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteContact(data.id)" />
                    </div>
                </template>
            </Column>
            <template #empty>
                <div class="text-center py-8 text-surface-500">Aucun contact trouvé</div>
            </template>
        </DataTable>

        <!-- Pagination -->
        <div v-if="contacts.links?.length > 3" class="flex justify-center gap-1 mt-4">
            <template v-for="link in contacts.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    :class="['px-3 py-1 rounded text-sm', link.active ? 'bg-primary text-white' : 'bg-surface-100 dark:bg-surface-800 hover:bg-surface-200']"
                    v-html="link.label"
                    preserve-state
                />
                <span v-else class="px-3 py-1 rounded text-sm text-surface-400" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
