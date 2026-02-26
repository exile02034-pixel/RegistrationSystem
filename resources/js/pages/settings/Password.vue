<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import { edit } from '@/routes/user-password';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <h1 class="sr-only">Password Settings</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
                <Heading
                    variant="small"
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <Form
                    v-bind="PasswordController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password" class="text-[#0B1F3A] dark:text-[#E6F1FF]">Current password</Label>
                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="mt-1 block w-full border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="text-[#0B1F3A] dark:text-[#E6F1FF]">New password</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="text-[#0B1F3A] dark:text-[#E6F1FF]"
                            >Confirm password</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            class="border border-[#2563EB] bg-[#2563EB] text-white hover:bg-[#1D4ED8] dark:border-[#2563EB] dark:bg-[#2563EB] dark:text-[#E6F1FF] dark:hover:bg-[#3B82F6]"
                            :disabled="processing"
                            data-test="update-password-button"
                            >Save password</Button
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
        </SettingsLayout>
    </AppLayout>
</template>
