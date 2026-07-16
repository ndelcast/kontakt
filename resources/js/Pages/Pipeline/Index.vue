<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import draggable from 'vuedraggable';
import axios from 'axios';

defineOptions({ layout: AppLayout });

const props = defineProps({ stages: { type: [Array, Object] } });
const confirm = useConfirm();

const stagesList = ref(Array.isArray(props.stages) ? [...props.stages] : Object.values(props.stages));

const onReorder = async () => {
    const ids = stagesList.value.map(s => s.id);
    await axios.post('/app/pipeline/reorder', { ids });
};

const deleteStage = (id) => {
    confirm.require({
        message: 'Supprimer cette etape ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(`/app/pipeline/${id}`),
    });
};
</script>

<template>
    <div>
        <ConfirmDialog />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Etapes du Pipeline</h1>
                <p class="text-sm text-gray-400 mt-0.5">{{ stagesList.length }} etapes</p>
            </div>
            <Link href="/app/pipeline/create">
                <Button label="Nouvelle etape" icon="pi pi-plus" size="small" />
            </Link>
        </div>

        <div class="panel">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
                <p class="text-xs text-gray-400">Glissez-deposez pour reorganiser les etapes</p>
            </div>
            <div class="p-4">
                <draggable
                    v-model="stagesList"
                    item-key="id"
                    handle=".drag-handle"
                    ghost-class="opacity-30"
                    @end="onReorder"
                >
                    <template #item="{ element }">
                        <div class="flex items-center gap-4 p-4 mb-2 rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <i class="pi pi-bars drag-handle cursor-grab text-gray-300 hover:text-gray-500 transition-colors"></i>
                            <div
                                class="w-3 h-3 rounded-full shrink-0"
                                :style="{ backgroundColor: element.color || '#6366f1' }"
                            ></div>
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ element.name }}</span>
                                <span class="text-xs text-gray-400 ml-2">({{ element.slug }})</span>
                            </div>
                            <Tag :value="`${element.probability}%`"
                                :severity="element.probability >= 75 ? 'success' : element.probability >= 40 ? 'warn' : 'secondary'"
                                rounded
                            />
                            <Tag v-if="element.is_won" value="Won" severity="success" rounded />
                            <Tag v-if="element.is_lost" value="Lost" severity="danger" rounded />
                            <Tag :value="`${element.opportunities_count} opps`" severity="info" rounded />
                            <div class="flex gap-1">
                                <Link :href="`/app/pipeline/${element.id}/edit`">
                                    <Button icon="pi pi-pencil" text rounded size="small" />
                                </Link>
                                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteStage(element.id)" />
                            </div>
                        </div>
                    </template>
                </draggable>
                <div v-if="stagesList.length === 0" class="empty-state">
                    <div class="empty-icon bg-[#EEF1FE] dark:bg-[#4A6CF7]/10 text-[#4A6CF7] mx-auto">
                        <i class="pi pi-sliders-h"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Aucune etape configuree</p>
                </div>
            </div>
        </div>
    </div>
</template>
