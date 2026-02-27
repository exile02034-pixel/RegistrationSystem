<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { home } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />
    <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
            <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
            <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
            <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center px-6 py-8 lg:py-10">
            <div class="grid w-full gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <Card class="space-y-6 rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-7 backdrop-blur lg:p-10 dark:border-[#1E3A5F] dark:bg-[#12325B]">
                    <Link
                        :href="home()"
                        class="inline-flex items-center rounded-full border border-[#60A5FA] bg-[#EFF6FF] px-3 py-1 text-xs font-medium tracking-wide text-[#2563EB] dark:border-[#2563EB] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
                    >
                        RegistrationSystem Portal
                    </Link>
                    <h1 class="text-3xl font-semibold leading-tight text-[#0B1F3A] sm:text-5xl dark:text-[#E6F1FF]">
                        Secure login for Registrar and Client access.
                    </h1>
                    <p class="max-w-xl text-base leading-relaxed text-[#475569] sm:text-lg dark:text-[#9FB3C8]">
                        Registrar can manage clients, send forms through email, and review documents. Clients can only view and download their own documents after login.
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-[#60A5FA] bg-[#EFF6FF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Registrar</p>
                            <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">View clients, edit records, delete entries, and manage submitted files.</p>
                        </div>
                        <div class="rounded-2xl border border-[#60A5FA] bg-[#EFF6FF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Client</p>
                            <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">Access personal documents only, with view and download permissions.</p>
                        </div>
                    </div>
                </Card>

                <Card class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] backdrop-blur dark:border-[#1E3A5F] dark:bg-[#12325B] lg:p-2">
                    <CardHeader>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <CardTitle class="text-2xl text-[#0B1F3A] dark:text-[#E6F1FF]">Log in</CardTitle>
                                <CardDescription class="text-sm text-[#475569] dark:text-[#9FB3C8]">Use your assigned account to continue.</CardDescription>
                            </div>
                            <AppearanceTabs />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="status"
                            class="mb-4"
                        >
                            <Alert class="border-[#60A5FA] bg-[#EFF6FF] text-[#2563EB] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
                                <AlertTitle>Status</AlertTitle>
                                <AlertDescription>{{ status }}</AlertDescription>
                            </Alert>
                        </div>

                        <Form
                            v-bind="store.form()"
                            :reset-on-success="['password']"
                            v-slot="{ errors, processing }"
                            class="mt-2 flex flex-col gap-5"
                        >
                            <div class="grid gap-2">
                                <Label for="email" class="text-[#0B1F3A] dark:text-[#E6F1FF]">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="email@example.com"
                                    class="border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <div class="flex items-center justify-between">
                                    <Label for="password" class="text-[#0B1F3A] dark:text-[#E6F1FF]">Password</Label>
                                    <TextLink
                                        v-if="canResetPassword"
                                        :href="request()"
                                        class="text-sm text-[#2563EB] hover:text-[#1D4ED8] dark:text-[#60A5FA] dark:hover:text-[#E6F1FF]"
                                        :tabindex="5"
                                    >
                                        Forgot password?
                                    </TextLink>
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Password"
                                    class="border-[#E2E8F0] bg-[#FFFFFF] text-[#0B1F3A] placeholder:text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:placeholder:text-[#9FB3C8]"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <Label for="remember" class="flex items-center space-x-3 text-[#0B1F3A] dark:text-[#E6F1FF]">
                                <Checkbox id="remember" name="remember" :tabindex="3" />
                                <span>Remember me</span>
                            </Label>

                            <Button
                                type="submit"
                                class="mt-2 w-full border border-[#2563EB] bg-[#2563EB] text-white hover:bg-[#1D4ED8] dark:border-[#2563EB] dark:bg-[#2563EB] dark:text-[#E6F1FF] dark:hover:bg-[#3B82F6]"
                                :tabindex="4"
                                :disabled="processing"
                                data-test="login-button"
                            >
                                <Spinner v-if="processing" />
                                Log in
                            </Button>
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
