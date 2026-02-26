<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Download, Eye, FileText, Printer } from 'lucide-vue-next'
import { computed, ref } from 'vue'

type UploadItem = {
  id: number
  original_name: string
  size_bytes: number | null
  submitted_at: string | null
  view_raw_url: string
  preview_pdf_url: string
  download_original_url: string
  download_pdf_url: string
  print_url: string
  can_convert_pdf: boolean
  is_pdf: boolean
}

const props = defineProps<{
  uploads: UploadItem[]
  batchPrintBaseUrl: string
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Files',
    href: '/user/files',
  },
]

const microsoftViewerUrl = (rawUrl: string) => {
  const encoded = encodeURIComponent(rawUrl)
  return `https://view.officeapps.live.com/op/view.aspx?src=${encoded}`
}

const previewUrl = (upload: UploadItem) => {
  if (upload.can_convert_pdf || upload.is_pdf) {
    return upload.preview_pdf_url
  }

  return microsoftViewerUrl(upload.view_raw_url)
}

const isDocxLike = (upload: UploadItem) => {
  const extension = upload.original_name.split('.').pop()?.toLowerCase()
  return extension === 'doc' || extension === 'docx'
}

const formatBytes = (bytes: number | null) => {
  if (!bytes || bytes <= 0) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  const value = bytes / 1024 ** index
  return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`
}

const formatDate = (value: string | null) => {
  if (!value) return 'n/a'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

const downloadUrl = (upload: UploadItem) => {
  return upload.can_convert_pdf ? upload.download_pdf_url : upload.download_original_url
}

const selectedIds = ref<number[]>([])

const allSelected = computed(() => {
  return props.uploads.length > 0 && selectedIds.value.length === props.uploads.length
})

const toggleAll = (checked: boolean) => {
  selectedIds.value = checked ? props.uploads.map((upload) => upload.id) : []
}

const selectedUploads = computed(() => {
  return props.uploads.filter((upload) => selectedIds.value.includes(upload.id))
})

const printSelected = () => {
  const printable = selectedUploads.value.filter((upload) => upload.can_convert_pdf || upload.is_pdf)
  const skipped = selectedUploads.value.length - printable.length

  if (printable.length > 0) {
    const ids = printable.map((upload) => upload.id).join(',')
    window.open(`${props.batchPrintBaseUrl}?ids=${encodeURIComponent(ids)}`, '_blank')
  }

  if (skipped > 0) {
    window.alert(`${skipped} file(s) were skipped because PDF printing is unavailable.`)
  }
}

const printAll = () => {
  const printable = props.uploads.filter((upload) => upload.can_convert_pdf || upload.is_pdf)
  const skipped = props.uploads.length - printable.length

  if (printable.length > 0) {
    window.open(`${props.batchPrintBaseUrl}?all=1`, '_blank')
  }

  if (skipped > 0) {
    window.alert(`${skipped} file(s) were skipped because PDF printing is unavailable.`)
  }
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
            <CardTitle class="font-['Space_Grotesk'] text-3xl text-center font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              My Files
            </CardTitle>
          </CardHeader>
          <CardContent class="px-0">
            
          </CardContent>
        </Card>

        <Card class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardContent class="p-0">
            <div class="flex items-center justify-end gap-2 border-b border-[#E2E8F0] p-3 dark:border-[#1E3A5F]">
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="outline"
                    size="icon-sm"
                    class="cursor-pointer"
                    :disabled="selectedIds.length === 0"
                    aria-label="Print Selected"
                    @click="printSelected"
                  >
                    <Printer />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Print Selected</TooltipContent>
              </Tooltip>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button
                    variant="outline"
                    size="icon-sm"
                    class="cursor-pointer"
                    :disabled="props.uploads.length === 0"
                    aria-label="Print All"
                    @click="printAll"
                  >
                    <Printer />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Print All</TooltipContent>
              </Tooltip>
            </div>
            <table class="min-w-full divide-y divide-[#E2E8F0] text-sm dark:divide-[#1E3A5F]">
              <thead class="bg-[#EFF6FF] text-left dark:bg-[#0F2747]">
                <tr>
                  <th class="px-4 py-3">
                    <input
                      type="checkbox"
                      :checked="allSelected"
                      @change="toggleAll(($event.target as HTMLInputElement).checked)"
                    />
                  </th>
                  <th class="px-4 py-3">File</th>
                  <th class="px-4 py-3">Size</th>
                  <th class="px-4 py-3">Submitted</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E2E8F0] dark:divide-[#1E3A5F]">
                <tr v-for="upload in props.uploads" :key="upload.id">
                  <td class="px-4 py-3">
                    <input type="checkbox" :value="upload.id" v-model="selectedIds" />
                  </td>
                  <td class="px-4 py-3">
                    <p>{{ upload.original_name }}</p>
                  </td>
                  <td class="px-4 py-3">{{ formatBytes(upload.size_bytes) }}</td>
                  <td class="px-4 py-3">{{ formatDate(upload.submitted_at) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex gap-2">
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button as-child variant="outline" size="icon-sm" class="cursor-pointer">
                            <a :href="previewUrl(upload)" target="_blank" rel="noopener noreferrer" aria-label="View">
                              <Eye class="h-4 w-4" />
                            </a>
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>View</TooltipContent>
                      </Tooltip>
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button as-child variant="outline" size="icon-sm" class="cursor-pointer">
                            <a :href="downloadUrl(upload)" aria-label="Download">
                              <Download class="h-4 w-4" />
                            </a>
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Download</TooltipContent>
                      </Tooltip>
                      <Tooltip v-if="isDocxLike(upload)">
                        <TooltipTrigger as-child>
                          <Button as-child variant="outline" size="icon-sm" class="cursor-pointer">
                            <a :href="upload.download_original_url" aria-label="Download DOCX">
                              <FileText class="h-4 w-4" />
                            </a>
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Download DOCX</TooltipContent>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
                <tr v-if="!props.uploads.length">
                  <td colspan="5" class="px-4 py-6 text-center text-[#475569] dark:text-[#9FB3C8]">
                    No files found yet.
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
