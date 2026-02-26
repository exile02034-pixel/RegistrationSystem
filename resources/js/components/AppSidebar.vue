<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Folder, LayoutGrid, Users, HomeIcon } from 'lucide-vue-next';
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
        { title: 'My Files', href: '/user/files', icon: HomeIcon },
    ];
});


</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="bg-[#FFFFFF] dark:bg-[#0B1F3A]"
    >
        <SidebarHeader
            class="border-b border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#0B1F3A]"
        >
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] hover:bg-[#EFF6FF] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:hover:bg-[#0F2747]"
                    >
                        <Link :href="dashboardHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent class="bg-[#FFFFFF] dark:bg-[#0B1F3A]">
            <NavMain :items="mainNavItems" />
        </SidebarContent>
    </Sidebar>
    <slot />
</template>
