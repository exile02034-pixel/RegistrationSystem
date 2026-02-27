<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, Settings, Maximize2, Minimize2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { edit as editAppearance } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const isFullscreen = ref(false);
const canToggleFullscreen = ref(false);
const page = usePage();

const notifications = computed(() => {
    const value = page.props.notifications;

    if (
        value &&
        typeof value === 'object' &&
        'recent' in value &&
        'unreadCount' in value
    ) {
        return value as {
            unreadCount: number;
            recent: Array<{
                id: number;
                title: string;
                message: string | null;
                action_url: string | null;
                read_at: string | null;
                created_at: string | null;
            }>;
        };
    }

    return {
        unreadCount: 0,
        recent: [],
    };
});

const formatDate = (value: string | null) => {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const openNotification = (id: number, actionUrl: string | null) => {
    const target = actionUrl || '/notifications';

    router.patch(
        `/notifications/${id}/read`,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                router.visit(target);
            },
        },
    );
};

const syncFullscreenState = () => {
    isFullscreen.value = Boolean(document.fullscreenElement);
};

const toggleFullscreen = async () => {
    if (!canToggleFullscreen.value) {
        return;
    }

    if (document.fullscreenElement) {
        await document.exitFullscreen();
        return;
    }

    await document.documentElement.requestFullscreen();
};

onMounted(() => {
    canToggleFullscreen.value =
        Boolean(document.fullscreenEnabled) &&
        typeof document.documentElement.requestFullscreen === 'function';
    syncFullscreenState();
    document.addEventListener('fullscreenchange', syncFullscreenState);
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', syncFullscreenState);
});
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
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button
                        type="button"
                        class="relative inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
                    >
                        <Bell class="size-4" />
                        <span
                            v-if="notifications.unreadCount > 0"
                            class="absolute -right-1 -top-1 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-[#2563EB] px-1 text-[10px] font-semibold text-white"
                        >
                            {{ notifications.unreadCount > 9 ? '9+' : notifications.unreadCount }}
                        </span>
                        <span class="sr-only">Notifications</span>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    align="end"
                    class="w-80 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-0 dark:border-[#1E3A5F] dark:bg-[#12325B]"
                >
                    <div class="border-b border-[#E2E8F0] px-4 py-3 dark:border-[#1E3A5F]">
                        <p class="text-sm font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
                            Notifications
                        </p>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <div
                            v-for="item in notifications.recent"
                            :key="item.id"
                            class="border-b border-[#E2E8F0] px-4 py-3 dark:border-[#1E3A5F]"
                        >
                            <button
                                type="button"
                                class="block w-full text-left"
                                @click="openNotification(item.id, item.action_url)"
                            >
                                <p class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">
                                    {{ item.title }}
                                </p>
                                <p class="mt-1 line-clamp-2 text-xs text-[#64748B] dark:text-[#9FB3C8]">
                                    {{ item.message || 'No details available.' }}
                                </p>
                                <p class="mt-1 text-[11px] text-[#94A3B8] dark:text-[#9FB3C8]">
                                    {{ formatDate(item.created_at) }}
                                </p>
                            </button>
                        </div>
                        <p
                            v-if="!notifications.recent.length"
                            class="px-4 py-5 text-center text-xs text-[#64748B] dark:text-[#9FB3C8]"
                        >
                            No notifications yet.
                        </p>
                    </div>
                    <div class="px-4 py-3">
                        <Link
                            href="/notifications"
                            class="text-xs font-semibold text-[#2563EB] hover:underline dark:text-[#60A5FA]"
                        >
                            View all my notifications
                        </Link>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
            <AppearanceTabs />
            <button
                v-if="canToggleFullscreen"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
                @click="toggleFullscreen"
            >
                <component :is="isFullscreen ? Minimize2 : Maximize2" class="size-4" />
                <span class="sr-only">
                    {{
                        isFullscreen ? 'Exit full screen' : 'Enter full screen'
                    }}
                </span>
            </button>
            <Link
                :href="editAppearance()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
            >
                <Settings class="size-4" />
                <span class="sr-only">Settings</span>
            </Link>
        </div>
    </header>
</template>
