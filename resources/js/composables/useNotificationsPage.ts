import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import type { NotificationsPageProps, NotificationRow } from '@/types/notifications';

export const useNotificationsPage = (props: NotificationsPageProps) => {
    const selectedIds = ref<string[]>([]);

    const formatDate = (value: string | null) => {
        if (!value) {
            return 'n/a';
        }

        return new Date(value).toLocaleDateString('en-PH', {
            timeZone: 'Asia/Manila',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    const markAsRead = (id: string) => {
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
    const allVisibleSelected = computed(
        () =>
            props.notifications.data.length > 0 &&
            props.notifications.data.every((item) => selectedIds.value.includes(item.id)),
    );

    const toggleSelect = (id: string) => {
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

    const deleteNotification = (id: string) => {
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

    return {
        selectedIds,
        formatDate,
        markAsRead,
        openNotification,
        markAllAsRead,
        hasSelected,
        allVisibleSelected,
        toggleSelect,
        toggleSelectAll,
        deleteNotification,
        deleteSelected,
        deleteAll,
        reload,
    };
};
