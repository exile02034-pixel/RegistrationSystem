import { router } from '@inertiajs/vue3';
import { toast } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';
import type { NotificationSettingsPageProps } from '@/types/notifications';

export const useNotificationSettings = (props: NotificationSettingsPageProps) => {
    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Notification settings',
            href: '/settings/notifications',
        },
    ];

    const updatePreference = (key: string, value: boolean) => {
        const updated = {
            ...props.preferences,
            [key]: value,
        };

        router.put(
            '/settings/notifications',
            { preferences: updated },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Notification preference updated.');
                },
                onError: () => {
                    toast.error('Unable to update notification preference.');
                },
            },
        );
    };

    return {
        breadcrumbItems,
        updatePreference,
    };
};
