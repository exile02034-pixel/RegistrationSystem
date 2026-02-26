<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { type NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-2">
        <SidebarGroupLabel class="text-[#475569] dark:text-[#9FB3C8]"
            >Platform</SidebarGroupLabel
        >
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                    class="rounded-xl border border-transparent text-[#0B1F3A] hover:border-[#E2E8F0] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] data-[active=true]:border-[#60A5FA] data-[active=true]:bg-[#EFF6FF] data-[active=true]:text-[#2563EB] dark:text-[#E6F1FF] dark:hover:border-[#1E3A5F] dark:hover:bg-[#0F2747] dark:hover:text-[#E6F1FF] dark:data-[active=true]:border-[#2563EB] dark:data-[active=true]:bg-[#12325B] dark:data-[active=true]:text-[#E6F1FF]"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
