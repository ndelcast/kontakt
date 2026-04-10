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
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Contacts</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ contacts.total ?? contacts.data?.length ?? 0 }} contacts</p>
            </div>
            <Link href="/app/contacts/create">
                <Button label="Nouveau contact" icon="pi pi-plus" size="small" />
            </Link>
        </div>

        <div class="panel">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="search-box w-full sm:w-64">
                        <i class="pi pi-search search-icon"></i>
                        <InputText v-model="search" placeholder="Rechercher..." class="w-full" />
                    </div>
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
            </div>

            <div class="px-4 pb-2">
                <DataTable :value="contacts.data" :rows="15" class="text-sm">
                    <Column field="name" header="Nom" sortable>
                        <template #body="{ data }">
                            <Link :href="`/app/contacts/${data.id}/edit`" class="font-semibold text-[#4A6CF7] dark:text-[#7B93FA] hover:text-[#3451D1]">
                                {{ data.name }}
                            </Link>
                            <span v-if="data.email" class="block text-xs text-gray-400 mt-0.5">{{ data.email }}</span>
                        </template>
                    </Column>
                    <Column field="phone" header="Telephone">
                        <template #body="{ data }">
                            <span v-if="data.phone" class="text-gray-600 dark:text-gray-300">{{ data.phone }}</span>
                        </template>
                    </Column>
                    <Column field="company" header="Entreprise">
                        <template #body="{ data }">
                            <span v-if="data.company" class="text-gray-600 dark:text-gray-300">{{ data.company.name }}</span>
                        </template>
                    </Column>
                    <Column field="position" header="Poste">
                        <template #body="{ data }">
                            <span v-if="data.position" class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-800 text-xs font-medium text-gray-600 dark:text-gray-400">{{ data.position }}</span>
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
                        <div class="empty-state">
                            <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                                <i class="pi pi-users"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-400">Aucun contact trouve</p>
                        </div>
                    </template>
                </DataTable>
            </div>
        </div>

        <div v-if="contacts.links?.length > 3" class="flex justify-center gap-1 mt-5">
            <template v-for="link in contacts.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    :class="['pg-link', link.active ? 'bg-[#4A6CF7] text-white' : 'bg-white dark:bg-gray-800 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700']"
                    v-html="link.label"
                    preserve-state
                />
                <span v-else class="pg-link text-gray-300 dark:text-gray-600" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
