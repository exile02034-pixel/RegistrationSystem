<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

type UploadItem = {
  id: number
  original_name: string
  pdf_name: string
  size_bytes: number | null
  submitted_at: string | null
  view_url: string
  download_url: string
  is_pdf: boolean
}

const props = defineProps<{
  uploads: UploadItem[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Files',
    href: '/user/files',
  },
]

const formatBytes = (bytes: number | null) => {
  if (!bytes || bytes <= 0) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  const value = bytes / 1024 ** index
  return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`
}
</script>

<template>
  <Head title="My Files" />

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
              My PDF Files
            </CardTitle>
          </CardHeader>
          <CardContent class="px-0">
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
              This page shows only your submitted PDF files. You can view or download them.
            </p>
          </CardContent>
        </Card>

        <Card class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardContent class="p-0">
            <table class="min-w-full divide-y divide-[#E2E8F0] text-sm dark:divide-[#1E3A5F]">
              <thead class="bg-[#EFF6FF] text-left dark:bg-[#0F2747]">
                <tr>
                  <th class="px-4 py-3">File</th>
                  <th class="px-4 py-3">Size</th>
                  <th class="px-4 py-3">Submitted</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E2E8F0] dark:divide-[#1E3A5F]">
                <tr v-for="upload in props.uploads" :key="upload.id">
                  <td class="px-4 py-3">
                    <p>{{ upload.pdf_name }}</p>
                    <p class="text-xs text-[#475569] dark:text-[#9FB3C8]">Original: {{ upload.original_name }}</p>
                  </td>
                  <td class="px-4 py-3">{{ formatBytes(upload.size_bytes) }}</td>
                  <td class="px-4 py-3">{{ upload.submitted_at ?? 'n/a' }}</td>
                  <td class="px-4 py-3">
                    <div class="flex gap-2">
                      <Button as-child variant="outline" class="h-8">
                        <a :href="upload.view_url" target="_blank" rel="noopener noreferrer">View</a>
                      </Button>
                      <Button as-child variant="outline" class="h-8">
                        <Link :href="upload.download_url">Download</Link>
                      </Button>
                    </div>
                    <p v-if="!upload.is_pdf" class="mt-1 text-xs text-[#475569] dark:text-[#9FB3C8]">
                      Converted to PDF for client access.
                    </p>
                  </td>
                </tr>
                <tr v-if="!props.uploads.length">
                  <td colspan="4" class="px-4 py-6 text-center text-[#475569] dark:text-[#9FB3C8]">
                    No PDF files found yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
