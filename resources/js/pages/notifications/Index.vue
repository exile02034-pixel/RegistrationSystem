<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Bell, MoreHorizontal } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Pagination } from '@/components/ui/pagination';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type NotificationRow = {
    id: number;
    category: string;
    title: string;
    message: string | null;
    action_url: string | null;
    read_at: string | null;
    created_at: string | null;
};

type PaginatedNotifications = {
    data: NotificationRow[];
    current_page: number;
    last_page: number;
    total: number;
};

const props = defineProps<{
    notifications: PaginatedNotifications;
}>();
const selectedIds = ref<number[]>([]);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Notifications',
        href: '/notifications',
    },
];

const formatDate = (value: string | null) => {
    if (!value) {
        return 'n/a';
    }

    return new Date(value).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const markAsRead = (id: number) => {
    router.patch(
        `/notifications/${id}/read`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const openNotification = (item: NotificationRow) => {
    const target = item.action_url;

    if (item.read_at) {
        if (target) {
            router.visit(target);
        }
        return;
    }

    router.patch(
        `/notifications/${item.id}/read`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                if (target) {
                    router.visit(target);
                }
            },
        },
    );
};

const markAllAsRead = () => {
    router.post(
        '/notifications/read-all',
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('All notifications marked as read.');
            },
        },
    );
};

const hasSelected = computed(() => selectedIds.value.length > 0);
const allVisibleSelected = computed(() =>
    props.notifications.data.length > 0 &&
    props.notifications.data.every((item) => selectedIds.value.includes(item.id)),
);

const toggleSelect = (id: number) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter((selectedId) => selectedId !== id);
        return;
    }

    selectedIds.value = [...selectedIds.value, id];
};

const toggleSelectAll = () => {
    if (allVisibleSelected.value) {
        selectedIds.value = [];
        return;
    }

    selectedIds.value = props.notifications.data.map((item) => item.id);
};

const deleteNotification = (id: number) => {
    router.delete(`/notifications/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = selectedIds.value.filter((selectedId) => selectedId !== id);
            toast.success('Notification deleted.');
        },
        onError: () => {
            toast.error('Unable to delete notification.');
        },
    });
};

const deleteSelected = () => {
    if (!selectedIds.value.length) {
        return;
    }

    router.post(
        '/notifications/delete-selected',
        { ids: selectedIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
                toast.success('Selected notifications deleted.');
            },
            onError: () => {
                toast.error('Unable to delete selected notifications.');
            },
        },
    );
};

const deleteAll = () => {
    router.delete('/notifications', {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = [];
            toast.success('All notifications deleted.');
        },
        onError: () => {
            toast.error('Unable to delete notifications.');
        },
    });
};

const reload = (page = 1) => {
    router.get(
        '/notifications',
        { page },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
        },
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notifications" />

        <div
            class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]"
        >
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
                <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
                <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
                <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
            </div>

            <div class="relative space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
                            Notifications
                        </h1>
                        <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
                            Track system activities and status updates.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 text-sm font-medium text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
                            @click="toggleSelectAll"
                        >
                            {{ allVisibleSelected ? 'Unselect all' : 'Select all' }}
                        </button>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] transition-colors hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
                                >
                                    <MoreHorizontal class="size-4" />
                                    <span class="sr-only">Notification actions</span>
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-52">
                                <DropdownMenuItem @click="markAllAsRead">
                                    Mark all as read
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    :disabled="!hasSelected"
                                    @click="deleteSelected"
                                >
                                    Delete selected
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    class="text-red-600 focus:text-red-600 dark:text-red-400 dark:focus:text-red-400"
                                    @click="deleteAll"
                                >
                                    Delete all
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="item in notifications.data"
                        :key="item.id"
                        class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 rounded border-[#CBD5E1] text-[#2563EB] focus:ring-[#60A5FA]"
                                    :checked="selectedIds.includes(item.id)"
                                    @change="toggleSelect(item.id)"
                                >
                                <div class="inline-flex size-9 items-center justify-center rounded-full bg-[#EFF6FF] text-[#2563EB] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                                    <Bell class="size-4" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
                                        {{ item.title }}
                                    </p>
                                    <p class="text-sm text-[#64748B] dark:text-[#9FB3C8]">
                                        {{ item.message || 'No additional details.' }}
                                    </p>
                                    <p class="text-xs text-[#94A3B8] dark:text-[#9FB3C8]">
                                        {{ formatDate(item.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    v-if="!item.read_at"
                                    class="rounded-full border border-[#60A5FA] bg-[#EFF6FF] px-2 py-0.5 text-[11px] font-semibold text-[#2563EB] dark:border-[#2563EB] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                                >
                                    Unread
                                </span>
                                <button
                                    v-if="!item.read_at"
                                    type="button"
                                    class="text-xs font-medium text-[#2563EB] hover:underline dark:text-[#60A5FA]"
                                    @click="markAsRead(item.id)"
                                >
                                    Mark read
                                </button>
                                <button
                                    v-if="item.action_url"
                                    type="button"
                                    class="text-xs font-medium text-[#2563EB] hover:underline dark:text-[#60A5FA]"
                                    @click="openNotification(item)"
                                >
                                    Open
                                </button>
                                <button
                                    type="button"
                                    class="text-xs font-medium text-red-600 hover:underline dark:text-red-400"
                                    @click="deleteNotification(item.id)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="!notifications.data.length"
                        class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-8 text-center text-sm text-[#64748B] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]"
                    >
                        No notifications yet.
                    </div>
                </div>

                <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 pb-4 pt-2 dark:border-[#1E3A5F] dark:bg-[#12325B]">
                    <Pagination
                        :current-page="notifications.current_page"
                        :last-page="notifications.last_page"
                        :total="notifications.total"
                        @change="reload"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
