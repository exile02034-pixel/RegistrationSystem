<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import { AlertTriangle, LogOut } from 'lucide-vue-next';
import { useTemplateRef } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { logout } from '@/routes';

const passwordInput = useTemplateRef('passwordInput');
</script>

<template>
    <div class="space-y-6">
        <Heading
            variant="small"
            title="Delete account"
            description="Delete your account and all of its resources"
        />
        <div
            class="space-y-4 rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 dark:border-[#1E3A5F] dark:bg-[#12325B]"
        >
            <div class="relative space-y-0.5 text-[#0B1F3A] dark:text-[#E6F1FF]">
                <p class="font-medium text-red-600 dark:text-red-300">Warning</p>
                <p class="text-sm text-[#475569] dark:text-[#9FB3C8]">
                    Please proceed with caution, this cannot be undone.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="outline" data-test="logout-button">
                            <LogOut />
                            Log out
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Log out of your account?</DialogTitle>
                            <DialogDescription>
                                You will need to sign in again to continue.
                            </DialogDescription>
                        </DialogHeader>

                        <Alert
                            class="border-[#E2E8F0] bg-[#EFF6FF] text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                        >
                            <AlertTriangle class="h-4 w-4" />
                            <AlertTitle>Warning</AlertTitle>
                            <AlertDescription>
                                Make sure your unsaved work is saved before you
                                log out.
                            </AlertDescription>
                        </Alert>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>
                            <Button as-child>
                                <Link :href="logout()" as="button"
                                    >Log out</Link
                                >
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
                <Dialog>
                    <DialogTrigger as-child>
                        <Button
                            variant="destructive"
                            data-test="delete-user-button"
                            >Delete account</Button
                        >
                    </DialogTrigger>
                    <DialogContent>
                        <Form
                            v-bind="ProfileController.destroy.form()"
                            reset-on-success
                            @error="() => passwordInput?.$el?.focus()"
                            :options="{
                                preserveScroll: true,
                            }"
                            class="space-y-6"
                            v-slot="{ errors, processing, reset, clearErrors }"
                        >
                            <DialogHeader class="space-y-3">
                                <DialogTitle
                                    >Are you sure you want to delete your
                                    account?</DialogTitle
                                >
                                <DialogDescription>
                                    Once your account is deleted, all of its
                                    resources and data will also be permanently
                                    deleted. Please enter your password to
                                    confirm you would like to permanently delete
                                    your account.
                                </DialogDescription>
                            </DialogHeader>

                            <Alert
                                class="border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200"
                            >
                                <AlertTriangle class="h-4 w-4" />
                                <AlertTitle>This action is permanent</AlertTitle>
                                <AlertDescription>
                                    Deleted account data cannot be restored.
                                </AlertDescription>
                            </Alert>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only"
                                    >Password</Label
                                >
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    ref="passwordInput"
                                    placeholder="Password"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button
                                        variant="secondary"
                                        @click="
                                            () => {
                                                clearErrors();
                                                reset();
                                            }
                                        "
                                    >
                                        Cancel
                                    </Button>
                                </DialogClose>

                                <Button
                                    type="submit"
                                    variant="destructive"
                                    :disabled="processing"
                                    data-test="confirm-delete-user-button"
                                >
                                    Delete account
                                </Button>
                            </DialogFooter>
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </div>
</template>
