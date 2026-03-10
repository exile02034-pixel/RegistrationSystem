<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Folder, LayoutGrid, Users, User } from 'lucide-vue-next';
import { computed } from 'vue';

import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
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
                        { title: 'Registration', href: '/admin/registration', icon: Folder },

            { title: 'Users', href: '/admin/user', icon: Users },
            

        ];
    }
    return [
        { title: 'Dashboard', href: '/user/dashboard', icon: LayoutGrid },
        { title: 'My Form', href: '/user/about-me', icon: User },
    ];
});


</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-sidebar">
        <SidebarHeader
            class="border-b bg-sidebar"
        >
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="rounded-xl border bg-background hover:bg-accent"
                    >
                        <Link :href="dashboardHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent class="bg-sidebar">
            <NavMain :items="mainNavItems" />
        </SidebarContent>
    </Sidebar>
    <slot />
</template>
