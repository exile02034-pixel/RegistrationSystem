import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';
import type { UserDashboardStats } from '@/types/user-pages';

export const useUserDashboard = (stats: UserDashboardStats) => {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/user/dashboard',
        },
    ];

    const latestSubmissionLabel = computed(() => {
        if (!stats.latestSubmissionAt) {
            return 'n/a';
        }

        return new Date(stats.latestSubmissionAt).toLocaleDateString('en-PH', {
            timeZone: 'Asia/Manila',
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        });
    });

    return {
        breadcrumbs,
        latestSubmissionLabel,
    };
};
