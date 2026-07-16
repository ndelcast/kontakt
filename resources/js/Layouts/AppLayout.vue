<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import Avatar from 'primevue/avatar';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';

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

const sidebarCollapsed = ref(true);
const mobileOpen = ref(false);

const currentUrl = computed(() => page.url);

const isActive = (path) => {
    if (path === '/app') return currentUrl.value === '/app';
    return currentUrl.value.startsWith(path);
};

const navItems = [
    { label: 'Dashboard', icon: 'pi pi-objects-column', href: '/app' },
    { label: 'Ma Journee', icon: 'pi pi-sun', href: '/app/tasks/my-day' },
    { label: 'Taches', icon: 'pi pi-check-square', href: '/app/tasks' },
    { label: 'Marché', icon: 'pi pi-shopping-bag', href: '/app/market' },
    { label: 'Opportunites', icon: 'pi pi-chart-bar', href: '/app/opportunities' },
    { label: 'Kanban', icon: 'pi pi-th-large', href: '/app/opportunities/kanban' },
    { label: 'Etapes', icon: 'pi pi-sliders-h', href: '/app/pipeline' },
    { label: 'Contacts', icon: 'pi pi-users', href: '/app/contacts' },
    { label: 'Entreprises', icon: 'pi pi-building', href: '/app/companies' },
    { label: 'Membres', icon: 'pi pi-user-plus', href: '/app/team/members' },
    { label: 'Parametres', icon: 'pi pi-cog', href: '/app/team/edit' },
];

const userMenuRef = ref();
const userMenuItems = ref([
    { label: 'Profil', icon: 'pi pi-user', command: () => router.visit('/app/profile') },
    { separator: true },
    { label: 'Deconnexion', icon: 'pi pi-sign-out', command: () => router.post('/logout') },
]);

const toggleUserMenu = (event) => {
    userMenuRef.value.toggle(event);
};

const switchTeam = (teamId) => {
    router.post(`/app/team/switch/${teamId}`);
};

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Bonjour';
    if (hour < 18) return 'Bon apres-midi';
    return 'Bonsoir';
});
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-950">
        <Toast />

        <!-- Mobile overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="mobileOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 z-50 h-screen flex flex-col transition-all duration-200',
                'bg-[#1e1f3b]',
                sidebarCollapsed ? 'w-[64px]' : 'w-60',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 shrink-0">
                <Link href="/app" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#4A6CF7] flex items-center justify-center shrink-0">
                        <span class="text-white text-sm font-bold">K</span>
                    </div>
                    <span v-if="!sidebarCollapsed" class="text-base font-bold text-white">
                        Kontak
                    </span>
                </Link>
            </div>

            <!-- Team switcher (expanded only) -->
            <div v-if="!sidebarCollapsed && teams.length > 1" class="px-3 pb-3">
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
            <nav class="flex-1 overflow-y-auto px-2.5 pb-3" :class="sidebarCollapsed ? 'flex flex-col items-center gap-1 pt-2' : ''">
                <template v-if="sidebarCollapsed">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="['sidebar-nav-item', isActive(item.href) ? 'is-active' : '']"
                        :title="item.label"
                        @click="mobileOpen = false"
                    >
                        <i :class="item.icon" class="text-[1rem]"></i>
                    </Link>
                </template>
                <template v-else>
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        :class="['sidebar-nav-expanded mb-0.5', isActive(item.href) ? 'is-active' : '']"
                        @click="mobileOpen = false"
                    >
                        <i :class="item.icon" class="text-[0.9rem] w-5 text-center"></i>
                        <span>{{ item.label }}</span>
                    </Link>
                </template>
            </nav>

            <!-- User section -->
            <div class="shrink-0 border-t border-white/10 p-2.5">
                <button
                    @click="toggleUserMenu"
                    :class="[
                        'w-full flex items-center gap-2.5 p-2 rounded-xl text-sm transition-colors hover:bg-white/8',
                        sidebarCollapsed ? 'justify-center' : '',
                    ]"
                >
                    <Avatar
                        :label="user?.name?.charAt(0)?.toUpperCase()"
                        shape="circle"
                        class="bg-[#4A6CF7] text-white shrink-0"
                        style="width: 2rem; height: 2rem; font-size: 0.7rem;"
                    />
                    <div v-if="!sidebarCollapsed" class="flex-1 min-w-0 text-left">
                        <p class="font-semibold text-white truncate text-[0.8rem]">{{ user?.name }}</p>
                        <p class="text-[0.7rem] text-white/40 truncate">{{ user?.email }}</p>
                    </div>
                </button>
                <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" />
            </div>
        </aside>

        <!-- Main content -->
        <div :class="['transition-all duration-200', sidebarCollapsed ? 'lg:ml-[64px]' : 'lg:ml-60']">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 flex items-center justify-between h-14 px-5 sm:px-8 bg-white dark:bg-gray-900 border-b border-gray-200/50 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <button
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        <i class="pi pi-bars text-sm"></i>
                    </button>
                    <button
                        @click="mobileOpen = !mobileOpen"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        <i class="pi pi-bars text-sm"></i>
                    </button>
                    <div class="hidden sm:block search-box">
                        <i class="pi pi-search search-icon"></i>
                        <InputText placeholder="Rechercher..." class="w-72 !bg-[#F5F6FA] !border-gray-200/50 !rounded-xl" size="small" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span v-if="currentTeam" class="text-xs font-semibold text-[#4A6CF7] bg-[#EEF1FE] px-3 py-1.5 rounded-lg">
                        {{ currentTeam.name }}
                    </span>
                    <button class="relative flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <i class="pi pi-comment text-sm"></i>
                    </button>
                    <button class="relative flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <i class="pi pi-bell text-sm"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#F59E0B] rounded-full"></span>
                    </button>
                    <Avatar
                        :label="user?.name?.charAt(0)?.toUpperCase()"
                        shape="circle"
                        class="bg-[#4A6CF7] text-white cursor-pointer"
                        style="width: 2.2rem; height: 2.2rem; font-size: 0.75rem;"
                        @click="toggleUserMenu"
                    />
                </div>
            </header>

            <!-- Page content -->
            <main class="p-5 sm:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
