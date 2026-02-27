<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        totalUploads: number;
        pdfUploads: number;
        latestSubmissionAt: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/user/dashboard',
    },
];

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
                <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
                <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
                <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
            </div>

            <div class="relative space-y-6">
                <Card class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 backdrop-blur dark:border-[#1E3A5F] dark:bg-[#12325B]">
                    <CardHeader class="px-0 pb-2">
                        <CardTitle class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
                            Client Dashboard
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="px-0">
                        <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
                            Access your submitted documents and registration updates.
                        </p>
                    </CardContent>
                </Card>

                <div class="grid gap-4 md:grid-cols-2">
                    <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                        <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">My Access</p>
                        <ul class="mt-3 space-y-2 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
                            <li>View personal documents only</li>
                            <li>Download submitted files</li>
                          
                        </ul>
                    </Card>

                    <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
                        <p class="font-['Public_Sans'] text-xs uppercase tracking-wider text-[#2563EB] dark:text-[#60A5FA]">My Submission Stats</p>
                        <div class="mt-3 space-y-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
                            <p><strong>Total Submitted Files:</strong> {{ props.stats.totalUploads }}</p>
                            <p><strong>Latest Submission:</strong>   {{ props.stats.latestSubmissionAt ? new Date(props.stats.latestSubmissionAt).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' }) : 'n/a' }}</p>

                        </div>
                    </Card>
                </div>

                <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]">
                    <CardHeader class="pb-2">
                        <CardTitle class="font-['Space_Grotesk'] text-xl text-[#0B1F3A] dark:text-[#E6F1FF]">Quick Action</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-3">
                            <Button as-child variant="outline" class="border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]">
                                <Link href="/user/files">Open My Files</Link>
                            </Button>
                            <Button as-child variant="outline" class="border-[#2563EB] bg-[#EFF6FF] text-[#2563EB] hover:bg-[#DBEAFE] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]">
                                <Link href="/notifications">Notifications</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
