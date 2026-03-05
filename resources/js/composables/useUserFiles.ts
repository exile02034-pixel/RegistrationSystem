import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';
import type { CompanyTypeValue, UserFilesClientInfo } from '@/types/user-pages';

export const useUserFiles = (clientInfo: UserFilesClientInfo) => {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'My Submission',
            href: '/user/about-me',
        },
    ];

    const avatarInitials = computed(() =>
        clientInfo.name
            .split(' ')
            .map((part) => part[0])
            .join('')
            .slice(0, 2)
            .toUpperCase(),
    );

    const shortCompanyType = (value: CompanyTypeValue): string => {
        if (value === 'opc') return 'OPC';
        if (value === 'corp') return 'CORP';

        return 'SOLE PROP';
    };

    return {
        breadcrumbs,
        avatarInitials,
        shortCompanyType,
    };
};
