<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile Settings</h1>

        <SettingsLayout>
            <div
                class="flex flex-col space-y-6 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="name" class="text-[#0B1F3A] dark:text-[#E6F1FF]">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="text-[#0B1F3A] dark:text-[#E6F1FF]">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-[#475569] dark:text-[#9FB3C8]">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-[#2563EB] underline underline-offset-4 transition-colors duration-300 ease-out hover:text-[#1D4ED8] dark:text-[#60A5FA] dark:hover:text-[#E6F1FF]"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-[#2563EB] dark:text-[#60A5FA]"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            class="border border-[#2563EB] bg-[#2563EB] text-white hover:bg-[#1D4ED8] dark:border-[#2563EB] dark:bg-[#2563EB] dark:text-[#E6F1FF] dark:hover:bg-[#3B82F6]"
                            :disabled="processing"
                            data-test="update-profile-button"
                            >Save</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-[#475569] dark:text-[#9FB3C8]"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
