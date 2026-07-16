<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Message from 'primevue/message';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AppLayout });

const confirm = useConfirm();

const props = defineProps({
    offers: Object,
    filters: Object,
    categories: Array,
    statuses: Array,
    activeCategoryCount: Number,
});

const search = ref(props.filters?.search ?? '');
const status = ref(props.filters?.status ?? null);
const category = ref(props.filters?.category ?? null);
const budgetMin = ref(props.filters?.budget_min ?? null);
const allCategories = ref(Boolean(props.filters?.all_categories));

const budgetOptions = [
    { label: 'Tous les budgets', value: null },
    { label: '500 € et plus', value: 500 },
    { label: '1 000 € et plus', value: 1000 },
    { label: '10 000 € et plus', value: 10000 },
];

const reload = () => {
    router.get('/app/market', {
        search: search.value || undefined,
        status: status.value || undefined,
        category: category.value || undefined,
        budget_min: budgetMin.value || undefined,
        all_categories: allCategories.value || undefined,
    }, { preserveState: true, replace: true });
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(reload, 300);
});
watch([status, category, budgetMin, allCategories], reload);

const setStatus = (offer, value) => {
    router.put(`/app/market/offers/${offer.id}/status`, { status: value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// L'import est synchrone et dure une seconde ou deux : l'état de chargement
// n'est pas cosmétique, il évite les clics répétés pendant l'attente.
const importing = ref(false);

const runImport = () => {
    router.post('/app/market/import', {}, {
        preserveScroll: true,
        onStart: () => (importing.value = true),
        onFinish: () => (importing.value = false),
    });
};

const convert = (offer) => {
    confirm.require({
        message: `Créer une opportunité à partir de « ${offer.title} » ?`,
        header: 'Envoyer au pipeline',
        icon: 'pi pi-arrow-right',
        acceptLabel: 'Envoyer',
        rejectLabel: 'Annuler',
        accept: () => router.post(`/app/market/offers/${offer.id}/convert`),
    });
};

// « il y a 2 h » se lit mieux qu'une date absolue pour trier une inbox :
// la fraîcheur d'une annonce compte plus que sa date exacte.
const age = (iso) => {
    if (!iso) return '—';
    const minutes = Math.round((Date.now() - new Date(iso)) / 60000);
    if (minutes < 60) return `il y a ${Math.max(minutes, 1)} min`;
    const hours = Math.round(minutes / 60);
    if (hours < 24) return `il y a ${hours} h`;
    const days = Math.round(hours / 24);
    return `il y a ${days} j`;
};

const severity = (color) => ({
    info: 'info', danger: 'danger', success: 'success', gray: 'secondary',
}[color] ?? 'secondary');

const noActiveCategories = computed(() => props.activeCategoryCount === 0 && !allCategories.value);
</script>

<template>
    <div>
        <ConfirmDialog />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Marché</h1>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ offers.total ?? offers.data?.length ?? 0 }} offres · Codeur.com
                </p>
            </div>
            <div class="flex gap-2">
                <Button label="Importer" icon="pi pi-download" size="small" :loading="importing"
                    @click="runImport" />
                <Link href="/app/market/categories">
                    <Button label="Catégories" icon="pi pi-sliders-h" size="small" severity="secondary" outlined />
                </Link>
            </div>
        </div>

        <Message v-if="noActiveCategories" severity="warn" :closable="false" class="mb-4">
            Aucune catégorie n'est active : la liste est donc vide.
            <Link href="/app/market/categories" class="font-semibold underline">Choisir des catégories</Link>
            ou cocher « toutes catégories » ci-dessous.
        </Message>

        <div class="flex flex-wrap gap-3 mb-4">
            <InputText v-model="search" placeholder="Rechercher…" class="w-full sm:w-64" />
            <Select v-model="status" :options="statuses" optionLabel="label" optionValue="value"
                placeholder="Tous les statuts" showClear class="w-full sm:w-48" />
            <Select v-model="category" :options="categories" placeholder="Toutes les catégories actives"
                showClear filter class="w-full sm:w-56" />
            <Select v-model="budgetMin" :options="budgetOptions" optionLabel="label" optionValue="value"
                placeholder="Tous les budgets" class="w-full sm:w-48" />
            <Button :label="allCategories ? 'Filtre catégories désactivé' : 'Voir toutes catégories'"
                :icon="allCategories ? 'pi pi-eye' : 'pi pi-eye-slash'" size="small"
                :severity="allCategories ? 'contrast' : 'secondary'" text
                @click="allCategories = !allCategories" />
        </div>

        <DataTable :value="offers.data" stripedRows>
            <Column field="title" header="Offre">
                <template #body="{ data }">
                    <a :href="data.url" target="_blank" rel="noopener"
                        class="font-medium text-gray-900 dark:text-white hover:text-[#4A6CF7]">
                        {{ data.title }}
                        <i class="pi pi-external-link text-xs ml-1 opacity-40" />
                    </a>
                    <p v-if="data.description" class="text-xs text-gray-400 mt-0.5 line-clamp-1">
                        {{ data.description }}
                    </p>
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        <span v-for="c in data.categories" :key="c"
                            class="text-[10px] uppercase tracking-wide text-gray-500 bg-gray-100 dark:bg-gray-800 rounded px-1.5 py-0.5">
                            {{ c }}
                        </span>
                    </div>
                </template>
            </Column>
            <Column field="budget_raw" header="Budget" style="width: 12rem">
                <template #body="{ data }">
                    <span class="text-sm">{{ data.budget_raw ?? '—' }}</span>
                </template>
            </Column>
            <Column field="published_at" header="Âge" style="width: 8rem">
                <template #body="{ data }">
                    <span class="text-sm text-gray-500">{{ age(data.published_at) }}</span>
                </template>
            </Column>
            <Column field="status" header="Statut" style="width: 8rem">
                <template #body="{ data }">
                    <Tag :value="data.status_label" :severity="severity(data.status_color)" />
                </template>
            </Column>
            <Column header="" style="width: 13rem">
                <template #body="{ data }">
                    <div class="flex gap-1 justify-end items-center">
                        <!-- Une offre déjà convertie mène à son opportunité plutôt
                             que de proposer une conversion impossible. -->
                        <Link v-if="data.converted_opportunity_id"
                            :href="`/app/opportunities/${data.converted_opportunity_id}/edit`">
                            <Button v-tooltip.top="'Voir l\'opportunité'" icon="pi pi-external-link"
                                label="Pipeline" size="small" text severity="success" />
                        </Link>
                        <template v-else>
                            <Button v-tooltip.top="'Marquer comme vue'" icon="pi pi-eye" size="small" text
                                severity="secondary" @click="setStatus(data, 'seen')" />
                            <Button v-tooltip.top="'Chaud'" icon="pi pi-bolt" size="small" text
                                severity="danger" @click="setStatus(data, 'hot')" />
                            <Button v-tooltip.top="'Ignorer'" icon="pi pi-times" size="small" text
                                severity="secondary" @click="setStatus(data, 'ignored')" />
                            <Button v-tooltip.top="'Envoyer au pipeline'" icon="pi pi-arrow-right"
                                size="small" text @click="convert(data)" />
                        </template>
                    </div>
                </template>
            </Column>
            <template #empty>
                <p class="text-center text-gray-400 py-6 text-sm">Aucune offre.</p>
            </template>
        </DataTable>
    </div>
</template>
