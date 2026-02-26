<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'

type Upload = {
  id: number
  original_name: string
  mime_type: string | null
  size_bytes: number
  created_at: string | null
  download_url: string
  download_pdf_url: string
  can_convert_pdf: boolean
}

defineProps<{
  registration: {
    id: number
    email: string
    token: string
    company_type_label: string
    status: string
    created_at: string | null
    uploads: Upload[]
  }
}>()

const formatBytes = (bytes: number) => {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const formatFileType = (upload: Upload) => {
  const extension = upload.original_name.split('.').pop()?.toLowerCase()

  if (extension === 'pdf') return 'PDF'
  if (extension === 'doc' || extension === 'docx') return 'DOCX'

  if (upload.mime_type?.includes('pdf')) return 'PDF'
  if (upload.mime_type?.includes('word') || upload.mime_type?.includes('officedocument')) return 'DOCX'

  return 'FILE'
}

const formatUploadedDate = (dateString: string | null) => {
  if (!dateString) return 'n/a'

  const parsed = new Date(dateString)
  if (Number.isNaN(parsed.getTime())) return dateString

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
      <a href="/admin/registration" class="text-sm text-blue-600 hover:underline">Back to registrations</a>

      <div class="rounded-xl border bg-white p-5 shadow-sm">
        <h1 class="text-2xl font-bold">Registration Details</h1>
        <p class="mt-2 text-sm text-gray-600"><strong>Email:</strong> {{ registration.email }}</p>
        <p class="text-sm text-gray-600"><strong>Company Type:</strong> {{ registration.company_type_label }}</p>
        <p class="text-sm text-gray-600"><strong>Status:</strong> {{ registration.status }}</p>
      </div>

      <div class="space-y-3">
        <h2 class="text-xl font-semibold">Submitted Files</h2>
        <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
              <tr>
                <th class="px-4 py-3">File</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Size</th>
                <th class="px-4 py-3">Uploaded</th>
                <th class="px-4 py-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="upload in registration.uploads" :key="upload.id" class="border-t">
                <td class="px-4 py-3">{{ upload.original_name }}</td>
                <td class="px-4 py-3">{{ formatFileType(upload) }}</td>
                <td class="px-4 py-3">{{ formatBytes(upload.size_bytes) }}</td>
                <td class="px-4 py-3">{{ formatUploadedDate(upload.created_at) }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <a :href="upload.download_url" class="text-blue-600 hover:underline">Original</a>
                    <a
                      v-if="upload.can_convert_pdf"
                      :href="upload.download_pdf_url"
                      class="text-green-700 hover:underline"
                    >
                      PDF
                    </a>
                  </div>
                </td>
              </tr>
              <tr v-if="!registration.uploads.length">
                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No files uploaded yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
