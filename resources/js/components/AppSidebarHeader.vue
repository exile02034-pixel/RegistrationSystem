<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { getInitials } from '@/composables/useInitials';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const profileInitials = getInitials(page.props.auth?.user?.name);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-[#E2E8F0] bg-[#FFFFFF] px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 dark:border-[#1E3A5F] dark:bg-[#0B1F3A] md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <AppearanceTabs />
            <Link
                :href="editProfile()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
            >
                <span class="text-xs font-semibold">
                    {{ profileInitials || 'U' }}
                </span>
                <span class="sr-only">Profile</span>
            </Link>
            <Link
                :href="editAppearance()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
            >
                <Settings class="h-4 w-4" />
                <span class="sr-only">Settings</span>
            </Link>
        </div>
    </header>
</template>
