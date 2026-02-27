<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { Download, Eye, FileText, Printer } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

type UploadItem = {
  id: number
  original_name: string
  size_bytes: number | null
  submitted_at: string | null
  company_type: string
  view_raw_url: string
  preview_pdf_url: string
  download_original_url: string
  download_pdf_url: string
  print_url: string
  can_convert_pdf: boolean
  is_pdf: boolean
}

type Filters = {
  sort: 'created_at'
  direction: 'asc' | 'desc'
}

const props = defineProps<{
  uploads: UploadItem[]
  batchPrintBaseUrl: string
  filters: Filters
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Files',
    href: '/user/files',
  },
]

const direction = ref<'asc' | 'desc'>(props.filters.direction ?? 'desc')

const companyTypes = [
  { value: 'opc', label: 'OPC' },
  { value: 'sole_prop', label: 'Proprietorship' },
  { value: 'corp', label: 'Regular Corporation' },
] as const

const groupedUploads = computed(() => {
  return props.uploads.reduce<Record<string, UploadItem[]>>((acc, upload) => {
    if (!acc[upload.company_type]) {
      acc[upload.company_type] = []
    }

    acc[upload.company_type].push(upload)
    return acc
  }, {})
})

const uploadGroups = computed(() => {
  const groups = companyTypes
    .map((type) => ({
      value: type.value,
      label: type.label,
      uploads: groupedUploads.value[type.value] ?? [],
    }))
    .filter((group) => group.uploads.length > 0)

  const otherGroups = Object.entries(groupedUploads.value)
    .filter(([key, uploads]) => !companyTypes.some((type) => type.value === key) && uploads.length > 0)
    .map(([key, uploads]) => ({
      value: key,
      label: key.replaceAll('_', ' ').trim() || 'N/A',
      uploads,
    }))

  return [...groups, ...otherGroups]
})

const buildQuery = () => {
  const query: Record<string, string | number> = {
    sort: props.filters.sort ?? 'created_at',
    direction: direction.value,
  }

  return query
}

const reload = () => {
  router.get('/user/files', buildQuery(), {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const setDirection = (nextDirection: 'asc' | 'desc') => {
  if (direction.value === nextDirection) return
  direction.value = nextDirection
  reload()
}

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

const isGroupSelected = (uploads: UploadItem[]) => {
  return uploads.length > 0 && uploads.every((upload) => selectedIds.value.includes(upload.id))
}

const toggleGroup = (checked: boolean, uploads: UploadItem[]) => {
  const ids = uploads.map((upload) => upload.id)

  if (checked) {
    const next = new Set(selectedIds.value)
    ids.forEach((id) => next.add(id))
    selectedIds.value = Array.from(next)
    return
  }

  selectedIds.value = selectedIds.value.filter((id) => !ids.includes(id))
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
            <CardTitle class="font-['Space_Grotesk'] text-center text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              My Files
            </CardTitle>
          </CardHeader>
          <CardContent class="px-0" />
        </Card>

        <div
          v-if="!uploadGroups.length"
          class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 text-center text-sm text-[#475569] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]"
        >
          No files found yet.
        </div>

        <Card
          v-for="group in uploadGroups"
          :key="group.value"
          class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
        >
          <CardHeader class="pb-2">
            <CardTitle class="text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              {{ group.label }}
            </CardTitle>
          </CardHeader>
          <CardContent class="p-0">
            <div class="flex items-center justify-between gap-2 border-b border-[#E2E8F0] p-3 dark:border-[#1E3A5F]">
              <div class="flex items-center gap-2">
                <span class="text-xs text-[#475569] dark:text-[#9FB3C8]">Sort by date</span>
                <Button
                  type="button"
                  variant="outline"
                  class="h-7 px-2 text-xs"
                  :class="direction === 'desc' ? 'border-[#2563EB] text-[#2563EB] dark:border-[#60A5FA] dark:text-[#93C5FD]' : ''"
                  @click="setDirection('desc')"
                >
                  Newest
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  class="h-7 px-2 text-xs"
                  :class="direction === 'asc' ? 'border-[#2563EB] text-[#2563EB] dark:border-[#60A5FA] dark:text-[#93C5FD]' : ''"
                  @click="setDirection('asc')"
                >
                  Oldest
                </Button>
              </div>
              <div class="flex items-center gap-2">
              <Tooltip>
                <TooltipTrigger as-child>
                  <Button variant="outline" size="icon-sm" class="cursor-pointer" :disabled="selectedIds.length === 0" aria-label="Print Selected" @click="printSelected">
                    <Printer />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Print Selected</TooltipContent>
              </Tooltip>
              </div>
            </div>
            <table class="min-w-full divide-y divide-[#E2E8F0] text-sm dark:divide-[#1E3A5F]">
              <thead class="bg-[#EFF6FF] text-left dark:bg-[#0F2747]">
                <tr>
                  <th class="px-4 py-3">
                    <input
                      type="checkbox"
                      :checked="isGroupSelected(group.uploads)"
                      @change="toggleGroup(($event.target as HTMLInputElement).checked, group.uploads)"
                    >
                  </th>
                  <th class="px-4 py-3">File</th>
                  <th class="px-4 py-3">Size</th>
                  <th class="px-4 py-3">Submitted</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E2E8F0] dark:divide-[#1E3A5F]">
                <tr v-for="upload in group.uploads" :key="upload.id">
                  <td class="px-4 py-3">
                    <input v-model="selectedIds" type="checkbox" :value="upload.id">
                  </td>
                  <td class="px-4 py-3"><p>{{ upload.original_name }}</p></td>
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
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
