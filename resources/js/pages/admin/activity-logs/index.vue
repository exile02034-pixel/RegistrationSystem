<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import ActivityTypeIcon from '@/components/admin/ActivityTypeIcon.vue'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Pagination } from '@/components/ui/pagination'
import { useActivityLogs } from '@/composables/admin/useActivityLogs'
import AppLayout from '@/layouts/AppLayout.vue'

type ActivityLogItem = {
  id: string
  type: string
  description: string
  performed_by_name: string | null
  performed_by_email: string | null
  performed_by_role: string | null
  company_type: string | null
  files_count: number | null
  filenames: string[]
  file_types: string[]
  metadata?: {
    section?: string
    section_label?: string
    updated_fields?: string[]
  }
  created_at: string | null
}

type PaginatedLogs = {
  data: ActivityLogItem[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const props = defineProps<{
  logs: PaginatedLogs
}>()

const { groupedByDay, formatDateTime, roleLabel, companyTypeLabel } = useActivityLogs(props.logs.data)

const reload = (page: number) => {
  router.get('/admin/activity-logs', { page }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}
</script>

<template>
  <Head title="Activity Log" />

  <AppLayout>
    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
      </div>

      <div class="relative space-y-6">
        <Card class="rounded-3xl border border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardHeader class="pb-2">
            <CardTitle class=" text-center font-['Space_Grotesk'] text-2xl font-semibold">Activity Log</CardTitle>
          </CardHeader>
          <CardContent>
          </CardContent>
        </Card>

        <div v-if="!logs.data.length" class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 text-center text-sm text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]">
          No activity yet.
        </div>

        <div v-for="group in groupedByDay" :key="group.label" class="space-y-3">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-[#2563EB] dark:text-[#93C5FD]">
            {{ group.label }}
          </h2>

          <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]">
            <CardContent class="p-0">
              <div v-for="log in group.items" :key="log.id" class="flex items-start justify-between gap-4 border-b border-[#E2E8F0] p-4 last:border-b-0 dark:border-[#1E3A5F]">
                <div class="flex items-start gap-3">
                  <span class="mt-0.5 rounded-full bg-[#EFF6FF] p-2 text-[#2563EB] dark:bg-[#0F2747] dark:text-[#93C5FD]">
                    <ActivityTypeIcon :type="log.type" class="h-4 w-4" />
                  </span>
                  <div class="space-y-1">
                    <p class="text-sm font-medium">{{ log.description }}</p>
                    <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
                      {{ log.performed_by_name ?? 'Unknown' }} ({{ log.performed_by_email ?? 'n/a' }})
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                      <Badge variant="outline">{{ roleLabel(log.performed_by_role) }}</Badge>
                      <Badge v-if="log.company_type" variant="outline">{{ companyTypeLabel(log.company_type) }}</Badge>
                      <Badge v-if="log.files_count" variant="outline">{{ log.files_count }} file(s)</Badge>
                    </div>
                    <div v-if="log.file_types?.length" class="flex flex-wrap items-center gap-2 pt-1">
                      <Badge
                        v-for="fileType in log.file_types"
                        :key="`type-${log.id}-${fileType}`"
                        variant="outline"
                      >
                        {{ fileType.toUpperCase() }}
                      </Badge>
                    </div>
                    <div v-if="log.filenames?.length" class="pt-1">
                      <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
                        Files: {{ log.filenames.join(', ') }}
                      </p>
                    </div>
                    <div v-if="log.metadata?.section_label || log.metadata?.updated_fields?.length" class="pt-1 space-y-1">
                      <p v-if="log.metadata?.section_label" class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
                        Section: {{ log.metadata.section_label }}
                      </p>
                      <p v-if="log.metadata?.updated_fields?.length" class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
                        Updated fields: {{ log.metadata.updated_fields.join(', ') }}
                      </p>
                    </div>
                  </div>
                </div>
                <p class="shrink-0 text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ formatDateTime(log.created_at) }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 pb-4 pt-3 dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <Pagination :current-page="logs.current_page" :last-page="logs.last_page" :total="logs.total" @change="reload" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
