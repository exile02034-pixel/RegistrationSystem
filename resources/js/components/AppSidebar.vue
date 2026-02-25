<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Folder, File, LayoutGrid, Users, HomeIcon } from 'lucide-vue-next';
import { computed } from 'vue';

import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const dashboardHref = computed(() =>
    user.value?.role === 'admin' ? '/admin/dashboard' : '/user/dashboard'
);

const mainNavItems = computed<NavItem[]>(() => {
    const role = user.value?.role;

    if (role === 'admin') {
        return [
            { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
            { title: 'Users', href: '/admin/user', icon: Users },
            { title: 'Drive', href: '/admin/folders', icon: Folder },
            { title: 'Registration', href: '/admin/registration', icon: Folder },

        ];
    }
    return [
        { title: 'Dashboard', href: '/user/dashboard', icon: LayoutGrid },
        { title: 'Registration', href: '/user/home', icon: HomeIcon },
    ];
});


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>
        <SidebarFooter>

            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
