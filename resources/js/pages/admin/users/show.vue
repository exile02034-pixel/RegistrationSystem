<script setup lang="ts">
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Pagination } from '@/components/ui/pagination'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Download, Eye, FileDown } from 'lucide-vue-next'

type CompanyType = {
  value: 'corp' | 'sole_prop' | 'opc'
  label: string
}

type UploadItem = {
  id: number
  registration_link_id: number
  company_type: 'corp' | 'sole_prop' | 'opc'
  company_type_label: string
  original_name: string
  mime_type: string | null
  size_bytes: number
  created_at: string | null
  view_url: string
  download_url: string
  download_pdf_url: string
  can_convert_pdf: boolean
}

type ActivityItem = {
  id: number
  type: string
  description: string
  created_at: string | null
  files_count: number | null
  filenames: string[]
}

const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
    status: string
    created_at: string | null
    company_types: CompanyType[]
  }
  uploads: UploadItem[]
  activities: ActivityItem[]
}>()

const groupedUploads = computed(() => {
  return props.uploads.reduce<Record<string, UploadItem[]>>((acc, upload) => {
    if (!acc[upload.company_type]) {
      acc[upload.company_type] = []
    }

    acc[upload.company_type].push(upload)
    return acc
  }, {})
})

const displayCompanyTypes = computed(() => {
  const known = new Set(props.user.company_types.map((item) => item.value))
  const fromUploads = props.uploads
    .filter((upload) => !known.has(upload.company_type))
    .map((upload) => ({
      value: upload.company_type,
      label: upload.company_type_label,
    }))

  return [...props.user.company_types, ...fromUploads]
})

const activitiesPerPage = 5
const activityPage = ref(1)

const activityLastPage = computed(() => {
  return Math.max(1, Math.ceil(props.activities.length / activitiesPerPage))
})

const paginatedActivities = computed(() => {
  const currentPage = Math.min(activityPage.value, activityLastPage.value)
  const start = (currentPage - 1) * activitiesPerPage
  const end = start + activitiesPerPage

  return props.activities.slice(start, end)
})

const changeActivityPage = (page: number) => {
  activityPage.value = Math.max(1, Math.min(page, activityLastPage.value))
}

const formatBytes = (bytes: number) => {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const formatDate = (value: string | null) => {
  if (!value) return 'n/a'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return value

  return parsed.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

const formatDateTime = (value: string | null) => {
  if (!value) return 'n/a'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return value

  return parsed.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <AppLayout>
    <div
      class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]"
    >
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div
          class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30"
        />
      </div>

      <div class="relative space-y-6">
        <a href="/admin/user" class="inline-flex text-sm font-medium text-[#2563EB] transition hover:underline dark:text-[#60A5FA]">
          Back to users
        </a>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h1 class="font-['Space_Grotesk'] text-2xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">User Details</h1>
          <div class="mt-3 grid gap-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Status:</strong> {{ user.status }}</p>
            <p><strong>Registered:</strong> {{ formatDate(user.created_at) }}</p>
          </div>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Company Types</h2>
          <div class="mt-3 flex flex-wrap gap-2">
            <Badge v-for="type in user.company_types" :key="type.value">{{ type.label }}</Badge>
            <p v-if="!user.company_types.length" class="text-sm text-[#64748B] dark:text-[#9FB3C8]">
              No company types found from registration records.
            </p>
          </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Submitted Documents</h2>

          <div
            v-if="!uploads.length"
            class="rounded-lg border border-[#E2E8F0] p-6 text-center text-sm text-[#64748B] dark:border-[#1E3A5F] dark:text-[#9FB3C8]"
          >
            No files uploaded yet.
          </div>

          <div v-for="type in displayCompanyTypes" :key="`uploads-${type.value}`" class="space-y-2">
            <h3 class="font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ type.label }}</h3>
            <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <table class="min-w-full text-sm">
                <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
                  <tr>
                    <th class="px-4 py-3">File</th>
                    <th class="px-4 py-3">Size</th>
                    <th class="px-4 py-3">Uploaded</th>
                    <th class="px-4 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="upload in groupedUploads[type.value] ?? []"
                    :key="upload.id"
                    class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]"
                  >
                    <td class="px-4 py-3 text-[#0B1F3A] dark:text-[#E6F1FF]">{{ upload.original_name }}</td>
                    <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">{{ formatBytes(upload.size_bytes) }}</td>
                    <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">{{ formatDate(upload.created_at) }}</td>
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-2">
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button as="a" :href="upload.view_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View">
                              <Eye class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>View</TooltipContent>
                        </Tooltip>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <Button as="a" :href="upload.download_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="Download Original">
                              <Download class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Download Original</TooltipContent>
                        </Tooltip>
                        <Tooltip v-if="upload.can_convert_pdf">
                          <TooltipTrigger as-child>
                            <Button as="a" :href="upload.download_pdf_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="Download PDF">
                              <FileDown class="h-4 w-4" />
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Download PDF</TooltipContent>
                        </Tooltip>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!(groupedUploads[type.value] ?? []).length">
                    <td colspan="4" class="px-4 py-4 text-center text-[#64748B] dark:text-[#9FB3C8]">
                      No files for this company type yet.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">User Activity Log</h2>
          <div
            v-if="!activities.length"
            class="rounded-lg border border-[#E2E8F0] p-4 text-sm text-[#64748B] dark:border-[#1E3A5F] dark:text-[#9FB3C8]"
          >
            No activity logs for this user yet.
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="activity in paginatedActivities"
              :key="activity.id"
              class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC]/80 p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]/60"
            >
              <div class="flex flex-wrap items-start justify-between gap-2">
                <p class="font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ activity.description }}</p>
                <span class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ formatDateTime(activity.created_at) }}</span>
              </div>
              <p class="mt-1 text-xs uppercase tracking-wide text-[#64748B] dark:text-[#9FB3C8]">{{ activity.type }}</p>
              <p v-if="activity.files_count !== null" class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
                Files submitted: {{ activity.files_count }}
              </p>
              <p v-if="activity.filenames.length" class="mt-1 text-sm text-[#475569] dark:text-[#9FB3C8]">
                {{ activity.filenames.join(', ') }}
              </p>
            </div>
            <Pagination
              :current-page="Math.min(activityPage, activityLastPage)"
              :last-page="activityLastPage"
              :total="activities.length"
              @change="changeActivityPage"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
