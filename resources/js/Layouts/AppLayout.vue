<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Select from 'primevue/select';

const page = usePage();
const toast = useToast();
const user = computed(() => page.props.auth?.user);
const currentTeam = computed(() => page.props.currentTeam);
const teams = computed(() => page.props.teams);

const flash = computed(() => page.props.flash);
if (flash.value?.success) {
    toast.add({ severity: 'success', summary: 'Success', detail: flash.value.success, life: 3000 });
}
if (flash.value?.error) {
    toast.add({ severity: 'error', summary: 'Error', detail: flash.value.error, life: 5000 });
}

const sidebarCollapsed = ref(false);
const mobileOpen = ref(false);

const currentUrl = computed(() => page.url);

const isActive = (path) => {
    return currentUrl.value.startsWith(path);
};

const navGroups = [
    {
        label: 'Activités',
        items: [
            { label: 'Dashboard', icon: 'pi pi-home', href: '/app' },
            { label: 'Ma Journée', icon: 'pi pi-calendar', href: '/app/tasks/my-day' },
            { label: 'Tâches', icon: 'pi pi-check-square', href: '/app/tasks' },
        ],
    },
    {
        label: 'Pipeline',
        items: [
            { label: 'Opportunités', icon: 'pi pi-chart-bar', href: '/app/opportunities' },
            { label: 'Kanban', icon: 'pi pi-th-large', href: '/app/opportunities/kanban' },
            { label: 'Étapes', icon: 'pi pi-sliders-h', href: '/app/pipeline' },
        ],
    },
    {
        label: 'Contacts',
        items: [
            { label: 'Contacts', icon: 'pi pi-users', href: '/app/contacts' },
            { label: 'Entreprises', icon: 'pi pi-building', href: '/app/companies' },
        ],
    },
    {
        label: 'Équipe',
        items: [
            { label: 'Membres', icon: 'pi pi-user-plus', href: '/app/team/members' },
            { label: 'Paramètres', icon: 'pi pi-cog', href: '/app/team/edit' },
        ],
    },
];

const userMenuRef = ref();
const userMenuItems = ref([
    { label: 'Profil', icon: 'pi pi-user', command: () => router.visit('/app/profile') },
    { separator: true },
    { label: 'Déconnexion', icon: 'pi pi-sign-out', command: () => router.post('/logout') },
]);

const toggleUserMenu = (event) => {
    userMenuRef.value.toggle(event);
};

const switchTeam = (teamId) => {
    router.post(`/app/team/switch/${teamId}`);
};
</script>

<template>
    <div class="min-h-screen bg-surface-50 dark:bg-surface-950">
        <Toast />

        <!-- Mobile overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="mobileOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 z-50 h-screen transition-all duration-300 bg-surface-0 dark:bg-surface-900 border-r border-surface-200 dark:border-surface-700 flex flex-col',
                sidebarCollapsed ? 'w-16' : 'w-64',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-4 border-b border-surface-200 dark:border-surface-700 shrink-0">
                <Link href="/app" class="flex items-center gap-2">
                    <span v-if="!sidebarCollapsed" class="text-xl font-bold text-surface-900 dark:text-surface-0">
                        Kontak
                    </span>
                    <span v-else class="text-xl font-bold text-surface-900 dark:text-surface-0 mx-auto">K</span>
                </Link>
            </div>

            <!-- Team switcher -->
            <div v-if="!sidebarCollapsed && teams.length > 1" class="px-3 pt-3">
                <Select
                    :modelValue="currentTeam?.id"
                    @update:modelValue="switchTeam"
                    :options="teams"
                    optionLabel="name"
                    optionValue="id"
                    class="w-full"
                    size="small"
                />
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <template v-for="group in navGroups" :key="group.label">
                    <div v-if="!sidebarCollapsed" class="px-3 pt-4 pb-2 text-xs font-semibold text-surface-500 uppercase tracking-wider">
                        {{ group.label }}
                    </div>
                    <div v-else class="pt-3 mb-1 border-t border-surface-200 dark:border-surface-700 first:border-t-0 first:pt-0"></div>
                    <Link
                        v-for="item in group.items"
                        :key="item.label"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                            isActive(item.href) && (item.href === '/app' ? currentUrl === '/app' : true)
                                ? 'bg-primary/10 text-primary dark:text-primary-300'
                                : 'text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800',
                            sidebarCollapsed ? 'justify-center' : '',
                        ]"
                        @click="mobileOpen = false"
                    >
                        <i :class="item.icon" class="text-base"></i>
                        <span v-if="!sidebarCollapsed">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </aside>

        <!-- Main content -->
        <div :class="['transition-all duration-300', sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-64']">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-surface-0/80 dark:bg-surface-900/80 backdrop-blur-sm border-b border-surface-200 dark:border-surface-700">
                <div class="flex items-center gap-3">
                    <Button
                        icon="pi pi-bars"
                        text
                        rounded
                        severity="secondary"
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:flex"
                    />
                    <Button
                        icon="pi pi-bars"
                        text
                        rounded
                        severity="secondary"
                        @click="mobileOpen = !mobileOpen"
                        class="lg:hidden"
                    />
                    <span v-if="currentTeam" class="text-sm font-medium text-surface-500">{{ currentTeam.name }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        text
                        rounded
                        severity="secondary"
                        @click="toggleUserMenu"
                        class="flex items-center gap-2"
                    >
                        <Avatar
                            :label="user?.name?.charAt(0)?.toUpperCase()"
                            shape="circle"
                            class="bg-primary text-primary-contrast"
                            style="width: 2rem; height: 2rem;"
                        />
                        <span class="hidden sm:inline text-sm font-medium text-surface-700 dark:text-surface-300">
                            {{ user?.name }}
                        </span>
                    </Button>
                    <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" />
                </div>
            </header>

            <!-- Page content -->
            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
