<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Printer } from 'lucide-vue-next'
import { computed, toRef } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import FileGroupTable from '@/components/user/files/FileGroupTable.vue'
import SortControl from '@/components/user/files/SortControl.vue'
import { useUserFiles, type UserFileGroup } from '@/composables/useUserFiles'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

type Filters = {
  sort: 'created_at'
  direction: 'asc' | 'desc'
}

const props = defineProps<{
  uploadGroups: UserFileGroup[]
  batchPrintBaseUrl: string
  filters: Filters
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'My Files',
    href: '/user/files',
  },
]

const uploadGroups = toRef(props, 'uploadGroups')
const {
  direction,
  selectedIds,
  setDirection,
  isGroupSelected,
  toggleGroup,
  toggleUpload,
  printSelected,
} = useUserFiles({
  uploadGroups,
  batchPrintBaseUrl: props.batchPrintBaseUrl,
  filters: props.filters,
})

const hasUploads = computed(() => uploadGroups.value.length > 0)
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

        <Card v-if="hasUploads" class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardContent class="p-0">
            <div class="flex items-center justify-between gap-2 border-b border-[#E2E8F0] p-3 dark:border-[#1E3A5F]">
              <SortControl :direction="direction" @change="setDirection" />
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
          </CardContent>
        </Card>

        <div
          v-if="!hasUploads"
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
            <FileGroupTable
              :group="group"
              :selected-ids="selectedIds"
              :group-selected="isGroupSelected(group.uploads)"
              @toggle-group="toggleGroup"
              @toggle-upload="toggleUpload"
            />
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
