<script setup lang="ts">
import { Download, Eye } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import type { UserFileGroup, UserFileItem } from '@/composables/useUserFiles'

defineProps<{
  group: UserFileGroup
  selectedIds: number[]
  groupSelected: boolean
}>()

const emit = defineEmits<{
  toggleGroup: [checked: boolean, uploads: UserFileItem[]]
  toggleUpload: [id: number, checked: boolean]
}>()

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

const downloadUrl = (upload: UserFileItem) => {
  return upload.can_convert_pdf ? upload.download_pdf_url : upload.download_original_url
}
</script>

<template>
  <table class="min-w-full divide-y divide-[#E2E8F0] text-sm dark:divide-[#1E3A5F]">
    <thead class="bg-[#EFF6FF] text-left dark:bg-[#0F2747]">
      <tr>
        <th class="px-4 py-3">
          <input
            type="checkbox"
            :checked="groupSelected"
            @change="emit('toggleGroup', ($event.target as HTMLInputElement).checked, group.uploads)"
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
          <input
            :checked="selectedIds.includes(upload.id)"
            type="checkbox"
            :value="upload.id"
            @change="emit('toggleUpload', upload.id, ($event.target as HTMLInputElement).checked)"
          >
        </td>
        <td class="px-4 py-3"><p>{{ upload.original_name }}</p></td>
        <td class="px-4 py-3">{{ formatBytes(upload.size_bytes) }}</td>
        <td class="px-4 py-3">{{ formatDate(upload.submitted_at) }}</td>
        <td class="px-4 py-3">
          <div class="flex gap-2">
            <Tooltip>
              <TooltipTrigger as-child>
                <Button as-child variant="outline" size="icon-sm" class="cursor-pointer">
                  <a :href="upload.download_original_url" aria-label="View">
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
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</template>
