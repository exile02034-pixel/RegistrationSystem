<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, Maximize2, Minimize2, Settings } from 'lucide-vue-next';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
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

const isFullscreen = ref(false);
const isNotificationsOpen = ref(false);
const notificationRef = ref<HTMLElement | null>(null);

type HeaderNotification = {
    id: string;
    title: string;
    message: string;
    time: string | null;
    open_url: string;
};

const unreadCount = computed<number>(() => Number(page.props.notifications?.unread_count ?? 0));
const notifications = computed<HeaderNotification[]>(() => page.props.notifications?.latest ?? []);

const syncFullscreenState = () => {
    isFullscreen.value = Boolean(document.fullscreenElement);
};

const toggleFullscreen = async () => {
    if (!document.fullscreenElement) {
        await document.documentElement.requestFullscreen();
        return;
    }

    await document.exitFullscreen();
};

const toggleNotifications = () => {
    isNotificationsOpen.value = !isNotificationsOpen.value;
};

const closeNotificationsOnOutsideClick = (event: MouseEvent) => {
    const target = event.target as Node;
    if (!notificationRef.value?.contains(target)) {
        isNotificationsOpen.value = false;
    }
};

const markAllNotificationsRead = () => {
    router.post('/notifications/mark-all-read', {}, { preserveScroll: true });
};

const deleteNotification = (id: string) => {
    router.delete(`/notifications/${id}`, { preserveScroll: true });
};

const clearAllNotifications = () => {
    router.delete('/notifications', { preserveScroll: true });
};

onMounted(() => {
    syncFullscreenState();
    document.addEventListener('fullscreenchange', syncFullscreenState);
    document.addEventListener('click', closeNotificationsOnOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('fullscreenchange', syncFullscreenState);
    document.removeEventListener('click', closeNotificationsOnOutsideClick);
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
            <div ref="notificationRef" class="relative">
                <button
                    type="button"
                    class="relative inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
                    title="Notifications"
                    aria-label="Notifications"
                    @click.stop="toggleNotifications"
                >
                    <Bell class="h-4 w-4" />
                    <span v-if="unreadCount > 0" class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500" />
                </button>

                <div
                    v-if="isNotificationsOpen"
                    class="absolute right-0 top-11 z-50 w-80 rounded-xl border border-[#E2E8F0] bg-white p-3 shadow-lg dark:border-[#1E3A5F] dark:bg-[#12325B]"
                >
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Notifications</p>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="text-xs font-medium text-[#2563EB] hover:underline"
                                @click="markAllNotificationsRead"
                            >
                                Mark all as read
                            </button>
                            <button
                                type="button"
                                class="text-xs font-medium text-red-600 hover:underline"
                                @click="clearAllNotifications"
                            >
                                Delete all
                            </button>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="item in notifications"
                            :key="item.id"
                            class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-2 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <Link :href="item.open_url" class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ item.title }}</p>
                                    <p class="text-xs text-[#475569] dark:text-[#9FB3C8]">{{ item.message }}</p>
                                    <p class="mt-1 text-[11px] text-[#64748B] dark:text-[#94A3B8]">{{ item.time }}</p>
                                </Link>
                                <button
                                    type="button"
                                    class="text-xs text-red-600 hover:underline"
                                    @click.prevent="deleteNotification(item.id)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                        <p v-if="!notifications.length" class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] p-3 text-xs text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
                            No notifications yet.
                        </p>
                    </div>
                    <div class="mt-3 border-t border-[#E2E8F0] pt-2 dark:border-[#1E3A5F]">
                        <Link
                            href="/notifications"
                            class="text-sm font-medium text-[#2563EB] hover:underline"
                        >
                            View all my notification
                        </Link>
                    </div>
                </div>
            </div>
            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:hover:bg-[#0F2747]"
                @click="toggleFullscreen"
            >
                <Maximize2 v-if="!isFullscreen" class="h-4 w-4" />
                <Minimize2 v-else class="h-4 w-4" />
                <span class="sr-only">{{ isFullscreen ? 'Exit Fullscreen' : 'Enter Fullscreen' }}</span>
            </button>
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
