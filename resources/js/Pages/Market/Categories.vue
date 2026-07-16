<script setup>
import { ref, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';

defineOptions({ layout: AppLayout });

const props = defineProps({
    categories: Array,
});

const form = useForm({
    active: props.categories.filter((c) => c.is_active).map((c) => c.id),
});

const search = ref('');

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    return q ? props.categories.filter((c) => c.name.toLowerCase().includes(q)) : props.categories;
});

const selectAll = () => (form.active = props.categories.map((c) => c.id));
const selectNone = () => (form.active = []);

const submit = () => form.put('/app/market/categories', { preserveScroll: true });

const dirty = computed(() => {
    const initial = props.categories.filter((c) => c.is_active).map((c) => c.id).sort().join(',');
    return [...form.active].sort().join(',') !== initial;
});
</script>

<template>
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Catégories du marché</h1>
                <p class="text-sm text-gray-400 mt-0.5">
                    {{ form.active.length }} active(s) sur {{ categories.length }}
                </p>
            </div>
            <Link href="/app/market">
                <Button label="Retour au marché" icon="pi pi-arrow-left" size="small" severity="secondary" outlined />
            </Link>
        </div>

        <p class="text-sm text-gray-500 mb-6 max-w-2xl">
            Seules les offres portant au moins une catégorie active apparaissent dans le marché.
            Toutes les offres restent stockées : cocher une catégorie révèle immédiatement son
            historique. Le compteur indique ce que chaque catégorie ferait apparaître.
        </p>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <InputText v-model="search" placeholder="Filtrer les catégories…" class="w-full sm:w-64" />
            <Button label="Tout cocher" size="small" text severity="secondary" @click="selectAll" />
            <Button label="Tout décocher" size="small" text severity="secondary" @click="selectNone" />
            <div class="flex-1" />
            <Button label="Enregistrer" icon="pi pi-check" size="small" :disabled="!dirty"
                :loading="form.processing" @click="submit" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-1">
            <label v-for="c in filtered" :key="c.id"
                class="flex items-center gap-2.5 py-1.5 px-2 rounded hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                <Checkbox v-model="form.active" :value="c.id" :inputId="`cat-${c.id}`" />
                <span class="text-sm text-gray-700 dark:text-gray-200 flex-1 truncate">{{ c.name }}</span>
                <span class="text-xs tabular-nums"
                    :class="c.offers_count > 0 ? 'text-gray-500' : 'text-gray-300 dark:text-gray-600'">
                    {{ c.offers_count }}
                </span>
            </label>
        </div>

        <p v-if="filtered.length === 0" class="text-center text-gray-400 py-6 text-sm">
            Aucune catégorie ne correspond.
        </p>
    </div>
</template>
