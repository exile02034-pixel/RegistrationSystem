<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { useNotificationSettings } from '@/composables/useNotificationSettings';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import type { NotificationSettingsPageProps } from '@/types/notifications';

const props = defineProps<NotificationSettingsPageProps>();
const { breadcrumbItems, updatePreference } = useNotificationSettings(props);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Notification settings" />

        <h1 class="sr-only">Notification Settings</h1>

        <SettingsLayout>
            <div class="app-card space-y-6 p-6">
                <Heading
                    variant="small"
                    title="Notification settings"
                    description="Choose which app notifications you want to receive"
                />

                <div class="space-y-3">
                    <div
                        v-for="(label, key) in labels"
                        :key="key"
                        class="app-card-soft flex items-center justify-between px-4 py-3"
                    >
                        <p class="text-sm font-medium text-foreground">
                            {{ label }}
                        </p>
                        <Button
                            type="button"
                            size="sm"
                            :class="
                                preferences[key]
                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                    : ''
                            "
                            @click="updatePreference(key, !preferences[key])"
                        >
                            {{ preferences[key] ? 'On' : 'Off' }}
                        </Button>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
