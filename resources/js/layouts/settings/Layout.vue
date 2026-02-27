<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { type NavItem } from '@/types';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Password',
        href: editPassword(),
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
    {
        title: 'Notifications',
        href: '/settings/notifications',
    },
];

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div
        class="mx-4 my-6 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
    >
        <Heading
            title="Settings"
            description="Manage your profile and account settings"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-56">
                <nav
                    class="flex flex-col space-y-2 space-x-0 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-2 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
                    aria-label="Settings"
                >
                    <Button
                        variant="ghost"
                        class="w-full justify-start rounded-lg border border-transparent text-[#0B1F3A] hover:border-[#E2E8F0] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:text-[#E6F1FF] dark:hover:border-[#1E3A5F] dark:hover:bg-[#12325B]"
                        as-child
                    >
                        <Link :href="editProfile()">Profile</Link>
                    </Button>
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start rounded-lg border border-transparent text-[#0B1F3A] hover:border-[#E2E8F0] hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:text-[#E6F1FF] dark:hover:border-[#1E3A5F] dark:hover:bg-[#12325B]',
                            isCurrentUrl(item.href)
                                ? 'border-[#60A5FA] bg-[#EFF6FF] text-[#2563EB] dark:border-[#2563EB] dark:bg-[#12325B] dark:text-[#E6F1FF]'
                                : '',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-6">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
