<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Printer } from 'lucide-vue-next'
import { computed, ref, toRef } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { toast } from '@/components/ui/sonner'
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

type UploadTarget = {
  id: number
  company_type_label: string
  status: string
}

const props = defineProps<{
  uploadGroups: UserFileGroup[]
  batchPrintBaseUrl: string
  filters: Filters
  uploadTargets: UploadTarget[]
  clientInfo: {
    name: string
    email: string
    company_types: Array<{ value: 'opc' | 'sole_prop' | 'corp'; label: string }>
  }
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
const isUploadModalOpen = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const selectedFiles = ref<File[]>([])

const uploadForm = useForm({
  registration_link_id: props.uploadTargets[0]?.id ?? null as number | null,
  files: [] as File[],
})

const onFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  selectedFiles.value = Array.from(target.files ?? [])
}

const submitUploads = () => {
  if (!selectedFiles.value.length) {
    toast.error('Please select at least one file.')
    return
  }

  if (!uploadForm.registration_link_id) {
    toast.error('Please select a target registration.')
    return
  }

  uploadForm.files = selectedFiles.value

  uploadForm.post('/user/uploads/store', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      uploadForm.reset()
      uploadForm.registration_link_id = props.uploadTargets[0]?.id ?? null
      selectedFiles.value = []
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      isUploadModalOpen.value = false
      toast.success('Files uploaded successfully.')
    },
    onError: () => {
      toast.error('Unable to upload files.')
    },
  })
}

const initials = (name: string) =>
  name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()

const shortCompanyType = (value: 'opc' | 'corp' | 'sole_prop') => {
  if (value === 'opc') return 'OPC'
  if (value === 'corp') return 'CORP'
  return 'SOLE PROP'
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
        <div class="flex items-center justify-between gap-4">
          <div>
            <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              My Files
            </h1>
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">
              View and manage your submitted registration files.
            </p>
          </div>
          <Dialog :open="isUploadModalOpen" @update:open="isUploadModalOpen = $event">
            <DialogTrigger as-child>
              <Button
                type="button"
                :disabled="!uploadTargets.length"
                class="inline-flex h-10 items-center justify-center rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 text-sm font-medium text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-60 dark:hover:bg-[#3B82F6]"
              >
                Upload Files
              </Button>
            </DialogTrigger>
            <DialogContent class="sm:max-w-lg dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <DialogHeader>
                <DialogTitle>Upload Files</DialogTitle>
              </DialogHeader>

              <div class="space-y-4">
                <div class="space-y-2">
                  <label class="text-sm font-medium">Registration Target</label>
                  <select
                    v-model="uploadForm.registration_link_id"
                    class="h-10 w-full rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-3 text-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]"
                  >
                    <option v-for="target in uploadTargets" :key="target.id" :value="target.id">
                      {{ target.company_type_label }} - {{ target.status }}
                    </option>
                  </select>
                  <p v-if="uploadForm.errors.registration_link_id" class="text-xs text-red-600">
                    {{ uploadForm.errors.registration_link_id }}
                  </p>
                </div>

                <div class="space-y-2">
                  <label class="text-sm font-medium">Files</label>
                  <input
                    ref="fileInput"
                    type="file"
                    multiple
                    class="w-full rounded-md border border-[#E2E8F0] bg-[#FFFFFF] p-2 text-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]"
                    @change="onFileChange"
                  >
                  <p v-if="uploadForm.errors.files" class="text-xs text-red-600">
                    {{ uploadForm.errors.files }}
                  </p>
                  <p v-if="uploadForm.errors['files.0']" class="text-xs text-red-600">
                    {{ uploadForm.errors['files.0'] }}
                  </p>
                </div>

                <div class="flex justify-end gap-2">
                  <Button type="button" variant="outline" @click="isUploadModalOpen = false">
                    Cancel
                  </Button>
                  <Button type="button" :disabled="uploadForm.processing" @click="submitUploads">
                    Submit Files
                  </Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 flex items-center justify-center rounded-full bg-[#EFF6FF] text-[#2563EB] text-xs font-semibold dark:bg-[#0F2747] dark:text-[#E6F1FF]">
              {{ initials(clientInfo.name) }}
            </div>
            <div>
              <p class="text-sm font-semibold">{{ clientInfo.name }}</p>
              <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ clientInfo.email }}</p>
            </div>
          </div>
          <div class="mt-3 flex flex-wrap gap-1.5">
            <Badge
              v-for="type in clientInfo.company_types"
              :key="type.value"
              class="border-[#60A5FA] bg-[#EFF6FF] text-[#2563EB] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
            >
              {{ shortCompanyType(type.value) }}
            </Badge>
            <span v-if="!clientInfo.company_types.length" class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
              No company type assigned yet.
            </span>
          </div>
        </div>

        <div v-if="hasUploads" class="space-y-3">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
            Submitted Files
          </h2>
          <div class="flex items-center justify-between gap-2 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-3 dark:border-[#1E3A5F] dark:bg-[#12325B]">
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
        </div>

        <div
          v-if="!hasUploads"
          class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 text-center text-sm text-[#475569] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]"
        >
          No files found yet.
        </div>

        <div
          v-for="group in uploadGroups"
          :key="group.value"
          class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
        >
          <div class="border-b border-[#E2E8F0] px-4 py-3 dark:border-[#1E3A5F]">
            <p class="text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">
              {{ group.label }}
            </p>
          </div>
          <div>
            <FileGroupTable
              :group="group"
              :selected-ids="selectedIds"
              :group-selected="isGroupSelected(group.uploads)"
              @toggle-group="toggleGroup"
              @toggle-upload="toggleUpload"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
