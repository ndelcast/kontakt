<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
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
        message: 'Supprimer cette étape ?',
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
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Étapes du Pipeline</h1>
            <Link href="/app/pipeline/create">
                <Button label="Nouvelle étape" icon="pi pi-plus" />
            </Link>
        </div>

        <Card>
            <template #content>
                <p class="text-sm text-surface-500 mb-4">Glissez-déposez pour réorganiser les étapes</p>
                <draggable
                    v-model="stagesList"
                    item-key="id"
                    handle=".drag-handle"
                    ghost-class="opacity-30"
                    @end="onReorder"
                >
                    <template #item="{ element }">
                        <div class="flex items-center gap-4 p-4 mb-2 rounded-lg border border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-900">
                            <i class="pi pi-bars drag-handle cursor-grab text-surface-400"></i>
                            <div
                                class="w-4 h-4 rounded-full shrink-0"
                                :style="{ backgroundColor: element.color || '#6366f1' }"
                            ></div>
                            <div class="flex-1 min-w-0">
                                <span class="font-medium text-surface-900 dark:text-surface-0">{{ element.name }}</span>
                                <span class="text-xs text-surface-500 ml-2">({{ element.slug }})</span>
                            </div>
                            <Tag :value="`${element.probability}%`"
                                :severity="element.probability >= 75 ? 'success' : element.probability >= 40 ? 'warn' : 'secondary'"
                            />
                            <Tag v-if="element.is_won" value="Won" severity="success" />
                            <Tag v-if="element.is_lost" value="Lost" severity="danger" />
                            <Tag :value="`${element.opportunities_count} opps`" severity="info" />
                            <div class="flex gap-1">
                                <Link :href="`/app/pipeline/${element.id}/edit`">
                                    <Button icon="pi pi-pencil" text rounded size="small" />
                                </Link>
                                <Button icon="pi pi-trash" text rounded size="small" severity="danger" @click="deleteStage(element.id)" />
                            </div>
                        </div>
                    </template>
                </draggable>
                <div v-if="stagesList.length === 0" class="text-center py-8 text-surface-500">
                    Aucune étape configurée
                </div>
            </template>
        </Card>
    </div>
</template>
