<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { login } from '@/routes';

const page = usePage();

const isAuthenticated = computed(() => Boolean(page.props.auth?.user));

const dashboardHref = computed(() => {
    const role = page.props.auth?.user?.role;
    return role === 'admin' ? '/admin/dashboard' : '/user/dashboard';
});
</script>

<template>
    <Head title="Registrar Client Portal">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Public+Sans:wght@400;500;600&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
            <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
            <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
            <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
        </div>

        <div class="relative mx-auto flex w-full max-w-6xl flex-col gap-12 px-6 py-8 lg:py-10">
            <header class="flex items-center justify-between rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] px-5 py-4 backdrop-blur dark:border-[#1E3A5F] dark:bg-[#0B1F3A]">
                <div>
                    <p class="font-['Space_Grotesk'] text-lg font-semibold tracking-wide text-[#0B1F3A] dark:text-[#E6F1FF]">RegistrarFlow</p>
                    <p class="font-['Public_Sans'] text-xs text-[#475569] dark:text-[#9FB3C8]">Role-based client document management</p>
                </div>

                <div class="flex items-center gap-3">
                    <AppearanceTabs />
                    <Button
                        v-if="isAuthenticated"
                        as-child
                        class="rounded-xl border border-[#2563EB] bg-[#2563EB] font-['Public_Sans'] text-sm font-medium text-white transition hover:bg-[#1D4ED8] dark:border-[#2563EB] dark:bg-[#2563EB] dark:text-[#E6F1FF] dark:hover:bg-[#3B82F6]"
                    >
                        <Link :href="dashboardHref">Dashboard</Link>
                    </Button>
                    <Button
                        v-else
                        as-child
                        class="rounded-xl border border-[#2563EB] bg-[#2563EB] font-['Public_Sans'] text-sm font-medium text-white transition hover:bg-[#1D4ED8] dark:border-[#2563EB] dark:bg-[#2563EB] dark:text-[#E6F1FF] dark:hover:bg-[#3B82F6]"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                </div>
            </header>

            <main class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                <Card class="space-y-7 rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-7 backdrop-blur lg:p-10 dark:border-[#1E3A5F] dark:bg-[#12325B]">
                    <Badge class="inline-flex w-fit items-center rounded-full border border-[#60A5FA] bg-[#EFF6FF] px-3 py-1 font-['Public_Sans'] text-xs font-medium tracking-wide text-[#2563EB] dark:border-[#2563EB] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                        Registrar + Client Access Portal
                    </Badge>

                    <div class="space-y-4">
                        <CardTitle class="font-['Space_Grotesk'] text-3xl font-semibold leading-tight text-[#0B1F3A] sm:text-5xl dark:text-[#E6F1FF]">
                            One portal for registrar control and secure client document access.
                        </CardTitle>
                        <p class="max-w-2xl font-['Public_Sans'] text-base leading-relaxed text-[#475569] sm:text-lg dark:text-[#9FB3C8]">
                            This system is built for two roles only: <strong class="text-[#0B1F3A] dark:text-[#E6F1FF]">Registrar</strong> and
                            <strong class="text-[#0B1F3A] dark:text-[#E6F1FF]">Client</strong>. Registrar can create client accounts, send email notices,
                            and deliver forms through email. Clients can log in and access only their own submitted documents.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-[#60A5FA] bg-[#EFF6FF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Registrar permissions</p>
                            <p class="mt-2 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">View clients, edit, delete, and manage client documents.</p>
                        </div>
                        <div class="rounded-2xl border border-[#60A5FA] bg-[#EFF6FF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Client permissions</p>
                            <p class="mt-2 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">View and download their own documents only.</p>
                        </div>
                    </div>
                </Card>

                <Card class="flex flex-col gap-4 rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 backdrop-blur lg:p-8 dark:border-[#1E3A5F] dark:bg-[#12325B]">
                    <CardHeader class="px-0">
                        <CardTitle class="font-['Space_Grotesk'] text-2xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Workflow</CardTitle>
                    </CardHeader>

                    <div class="space-y-3">
                        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Step 1</p>
                            <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Registrar creates a client account and profile.</p>
                        </div>
                        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Step 2</p>
                            <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Registrar sends forms and instructions through email.</p>
                        </div>
                        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                            <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">Step 3</p>
                            <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Client logs in to view and download only assigned documents.</p>
                        </div>
                    </div>
                </Card>
            </main>

            <Card class="grid gap-4 rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 backdrop-blur md:grid-cols-2 lg:p-8 dark:border-[#1E3A5F] dark:bg-[#12325B]">
                <CardContent class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                    <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Registrar Access</h3>
                    <ul class="mt-3 space-y-2 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
                        <li>Create client users</li>
                        <li>Send forms by email</li>
                        <li>View all clients and their documents</li>
                        <li>Edit and delete client records</li>
                    </ul>
                </CardContent>

                <CardContent class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                    <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Client Access</h3>
                    <ul class="mt-3 space-y-2 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
                        <li>Log in with client credentials</li>
                        <li>View own documents only</li>
                        <li>Download personal documents</li>
                        <li>No access to other clients or admin actions</li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
