<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

defineOptions({ layout: AppLayout });

const props = defineProps({ members: Array, invitations: Array, isAdmin: Boolean, team: Object });
const confirm = useConfirm();

const inviteDialog = ref(false);
const inviteEmail = ref('');
const inviteRole = ref('member');

const roleOptions = [
    { value: 'admin', label: 'Admin' },
    { value: 'member', label: 'Membre' },
];

const submitInvite = () => {
    router.post('/app/team/invite', { email: inviteEmail.value, role: inviteRole.value }, {
        onSuccess: () => {
            inviteDialog.value = false;
            inviteEmail.value = '';
            inviteRole.value = 'member';
        },
    });
};

const cancelInvitation = (id) => {
    router.delete(`/app/team/invitations/${id}`);
};

const copyLink = (url) => {
    navigator.clipboard.writeText(url);
};

const changeRole = (userId, newRole) => {
    confirm.require({
        message: `Changer le rôle en "${newRole}" ?`,
        header: 'Confirmation',
        accept: () => router.put(`/app/team/${props.team.id}/members/${userId}/role`, { role: newRole }),
    });
};

const removeMember = (userId) => {
    confirm.require({
        message: 'Retirer ce membre de l\'équipe ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => router.delete(`/app/team/${props.team.id}/members/${userId}`),
    });
};
</script>

<template>
    <div>
        <ConfirmDialog />
        <Dialog v-model:visible="inviteDialog" header="Inviter un membre" :style="{ width: '25rem' }" modal>
            <form @submit.prevent="submitInvite" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email *</label>
                    <InputText v-model="inviteEmail" type="email" class="w-full" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Rôle</label>
                    <Select v-model="inviteRole" :options="roleOptions" optionLabel="label" optionValue="value" class="w-full" />
                </div>
                <div class="flex justify-end gap-3">
                    <Button label="Annuler" severity="secondary" outlined @click="inviteDialog = false" />
                    <Button label="Inviter" type="submit" />
                </div>
            </form>
        </Dialog>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0">Membres de l'équipe</h1>
            <Button v-if="isAdmin" label="Inviter" icon="pi pi-user-plus" @click="inviteDialog = true" />
        </div>

        <!-- Pending invitations -->
        <Card v-if="invitations.length > 0" class="!shadow-sm mb-6">
            <template #title>Invitations en attente</template>
            <template #content>
                <div class="space-y-2">
                    <div v-for="inv in invitations" :key="inv.id" class="flex items-center gap-3 p-3 rounded-lg border border-surface-200 dark:border-surface-700">
                        <i class="pi pi-envelope text-primary"></i>
                        <div class="flex-1">
                            <span class="font-medium text-sm">{{ inv.email }}</span>
                            <Tag :value="inv.role" severity="secondary" size="small" class="ml-2" />
                        </div>
                        <span class="text-xs text-surface-500">Expire le {{ inv.expires_at }}</span>
                        <Button icon="pi pi-copy" text rounded size="small" @click="copyLink(inv.url)" v-tooltip="'Copier le lien'" />
                        <Button icon="pi pi-times" text rounded size="small" severity="danger" @click="cancelInvitation(inv.id)" v-tooltip="'Annuler'" />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Members -->
        <Card class="!shadow-sm">
            <template #content>
                <DataTable :value="members" class="text-sm">
                    <Column field="name" header="Nom">
                        <template #body="{ data }">
                            <span class="font-medium">{{ data.name }}</span>
                        </template>
                    </Column>
                    <Column field="email" header="Email" />
                    <Column field="role" header="Rôle">
                        <template #body="{ data }">
                            <Tag :value="data.role" :severity="data.role === 'admin' ? 'warn' : 'secondary'" />
                        </template>
                    </Column>
                    <Column field="joined_at" header="Rejoint le" />
                    <Column v-if="isAdmin" header="Actions" style="width: 150px">
                        <template #body="{ data }">
                            <div class="flex gap-1">
                                <Button
                                    v-if="data.role === 'member'"
                                    label="Admin"
                                    size="small"
                                    severity="warn"
                                    outlined
                                    @click="changeRole(data.id, 'admin')"
                                />
                                <Button
                                    v-if="data.role === 'admin'"
                                    label="Membre"
                                    size="small"
                                    severity="secondary"
                                    outlined
                                    @click="changeRole(data.id, 'member')"
                                />
                                <Button
                                    icon="pi pi-user-minus"
                                    text
                                    rounded
                                    size="small"
                                    severity="danger"
                                    @click="removeMember(data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>
    </div>
</template>
