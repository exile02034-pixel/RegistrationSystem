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
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
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

const isFullscreen = ref(false);
const canToggleFullscreen = ref(false);
const page = usePage();
const NOTIFICATION_POLL_INTERVAL_MS = 10000;
let notificationPollTimer: ReturnType<typeof setInterval> | null = null;

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
                id: string;
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

    return new Date(value).toLocaleDateString('en-PH', {
        timeZone: 'Asia/Manila',
        month: 'short',
        day: 'numeric',
    });
};

const openNotification = (id: string, actionUrl: string | null) => {
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

const reloadNotifications = () => {
    if (document.visibilityState !== 'visible') {
        return;
    }

    router.reload({
        only: ['notifications'],
        preserveState: true,
        preserveScroll: true,
    });
};

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        reloadNotifications();
    }
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
    reloadNotifications();
    notificationPollTimer = setInterval(
        reloadNotifications,
        NOTIFICATION_POLL_INTERVAL_MS,
    );

    document.addEventListener('fullscreenchange', syncFullscreenState);
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', syncFullscreenState);
    document.removeEventListener('visibilitychange', handleVisibilityChange);

    if (notificationPollTimer) {
        clearInterval(notificationPollTimer);
        notificationPollTimer = null;
    }
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b bg-sidebar px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
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
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        class="app-icon-button relative"
                    >
                        <Bell class="size-4" />
                        <span
                            v-if="notifications.unreadCount > 0"
                            class="bg-primary text-primary-foreground absolute -right-1 -top-1 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full px-1 text-[10px] font-semibold"
                        >
                            {{ notifications.unreadCount > 9 ? '9+' : notifications.unreadCount }}
                        </span>
                        <span class="sr-only">Notifications</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    align="end"
                    class="w-80 rounded-xl p-0"
                >
                    <div class="border-b px-4 py-3">
                        <p class="text-sm font-semibold text-foreground">
                            Notifications
                        </p>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <div
                            v-for="item in notifications.recent"
                            :key="item.id"
                            class="border-b px-4 py-3"
                        >
                            <button
                                type="button"
                                class="block w-full text-left"
                                @click="openNotification(item.id, item.action_url)"
                            >
                                <p class="text-sm font-medium text-foreground">
                                    {{ item.title }}
                                </p>
                                <p class="text-muted-foreground mt-1 line-clamp-2 text-xs">
                                    {{ item.message || 'No details available.' }}
                                </p>
                                <p class="text-muted-foreground mt-1 text-[11px]">
                                    {{ formatDate(item.created_at) }}
                                </p>
                            </button>
                        </div>
                        <p
                            v-if="!notifications.recent.length"
                            class="text-muted-foreground px-4 py-5 text-center text-xs"
                        >
                            No notifications yet.
                        </p>
                    </div>
                    <div class="px-4 py-3">
                        <Link
                            href="/notifications"
                            class="text-primary hover:text-primary/80 text-xs font-semibold hover:underline"
                        >
                            View all my notifications
                        </Link>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
            <AppearanceTabs />
            <Button
                v-if="canToggleFullscreen"
                type="button"
                variant="outline"
                size="icon"
                class="app-icon-button"
                @click="toggleFullscreen"
            >
                <component :is="isFullscreen ? Minimize2 : Maximize2" class="size-4" />
                <span class="sr-only">
                    {{
                        isFullscreen ? 'Exit full screen' : 'Enter full screen'
                    }}
                </span>
            </Button>
            <Button as-child variant="outline" size="icon" class="app-icon-button">
                <Link :href="editProfile()">
                    <Settings class="size-4" />
                    <span class="sr-only">Settings</span>
                </Link>
            </Button>
        </div>
    </header>
</template>
