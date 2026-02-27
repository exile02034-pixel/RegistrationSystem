<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
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
</script>

<template>
  <AppLayout>
    <div class="space-y-6 p-6">
      <a href="/admin/user" class="text-sm text-blue-600 hover:underline">Back to users</a>

      <div class="rounded-xl border bg-white p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]">
        <h1 class="text-2xl font-bold">User Details</h1>
        <div class="mt-3 grid gap-2 text-sm text-gray-700 dark:text-[#9FB3C8]">
          <p><strong>Name:</strong> {{ user.name }}</p>
          <p><strong>Email:</strong> {{ user.email }}</p>
          <p><strong>Status:</strong> {{ user.status }}</p>
          <p><strong>Registered:</strong> {{ formatDate(user.created_at) }}</p>
        </div>
      </div>

      <div class="rounded-xl border bg-white p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]">
        <h2 class="mb-3 text-xl font-semibold">Company Types</h2>
        
        <div class="flex flex-wrap gap-3">
          <Badge v-for="type in user.company_types" :key="type.value">{{ type.label }}</Badge>
          <p v-if="!user.company_types.length" class="text-sm text-gray-500 dark:text-[#9FB3C8]">
            No company types found from registration records.
          </p>
        </div>
      </div>

      <div class="space-y-4 rounded-xl border bg-white p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]">
        <h2 class="text-xl font-semibold">Submitted Documents</h2>

        <div v-if="!uploads.length" class="rounded-lg border p-6 text-center text-sm text-gray-500 dark:text-[#9FB3C8]">
          No files uploaded yet.
        </div>

        <div v-for="type in user.company_types" :key="`uploads-${type.value}`" class="space-y-2">
          <h3 class="font-semibold">{{ type.label }}</h3>
          <div class="overflow-x-auto rounded-xl border bg-white shadow-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]">
            <table class="min-w-full text-sm">
              <thead class="bg-gray-50 text-left text-gray-600 dark:bg-[#0A1F3A] dark:text-[#9FB3C8]">
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
                  class="border-t dark:border-[#1E3A5F]"
                >
                  <td class="px-4 py-3">{{ upload.original_name }}</td>
                  <td class="px-4 py-3">{{ formatBytes(upload.size_bytes) }}</td>
                  <td class="px-4 py-3">{{ formatDate(upload.created_at) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button as="a" :href="upload.view_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View">
                            <Eye />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>View</TooltipContent>
                      </Tooltip>
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button as="a" :href="upload.download_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="Download Original">
                            <Download />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Download Original</TooltipContent>
                      </Tooltip>
                      <Tooltip v-if="upload.can_convert_pdf">
                        <TooltipTrigger as-child>
                          <Button as="a" :href="upload.download_pdf_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="Download PDF">
                            <FileDown />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Download PDF</TooltipContent>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
                <tr v-if="!(groupedUploads[type.value] ?? []).length">
                  <td colspan="4" class="px-4 py-4 text-center text-gray-500 dark:text-[#9FB3C8]">
                    No files for this company type yet.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

