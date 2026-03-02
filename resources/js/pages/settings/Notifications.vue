<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { toast } from '@/components/ui/sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    preferences: Record<string, boolean>;
    labels: Record<string, string>;
}>();

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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notification settings" />

        <h1 class="sr-only">Notification Settings</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
                <Heading
                    variant="small"
                    title="Notification settings"
                    description="Choose which app notifications you want to receive"
                />

                <div class="space-y-3">
                    <div
                        v-for="(label, key) in labels"
                        :key="key"
                        class="flex items-center justify-between rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-4 py-3 dark:border-[#1E3A5F] dark:bg-[#12325B]"
                    >
                        <p class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">
                            {{ label }}
                        </p>
                        <button
                            type="button"
                            class="inline-flex h-8 min-w-20 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition-colors"
                            :class="
                                preferences[key]
                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                    : 'border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]'
                            "
                            @click="updatePreference(key, !preferences[key])"
                        >
                            {{ preferences[key] ? 'On' : 'Off' }}
                        </button>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

