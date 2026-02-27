<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Download, FileDown, Trash2, UserPlus } from 'lucide-vue-next'
import { ref } from 'vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { toast } from '@/components/ui/sonner'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import AppLayout from '@/layouts/AppLayout.vue'

type Upload = {
  id: number
  original_name: string
  mime_type: string | null
  size_bytes: number
  created_at: string | null
  view_url: string
  view_pdf_url: string
  download_url: string
  download_pdf_url: string
  delete_url: string
  can_convert_pdf: boolean
}

const props = defineProps<{
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

const isDeleteModalOpen = ref(false)
const deleting = ref(false)
const selectedUploadForDelete = ref<Upload | null>(null)
const statusForm = useForm({
  status: props.registration.status as 'pending' | 'incomplete' | 'completed',
})

const openDeleteModal = (upload: Upload) => {
  selectedUploadForDelete.value = upload
  isDeleteModalOpen.value = true
}

const confirmDelete = () => {
  if (!selectedUploadForDelete.value) return

  router.delete(selectedUploadForDelete.value.delete_url, {
    preserveScroll: true,
    onStart: () => {
      deleting.value = true
    },
    onSuccess: () => {
      toast.success('Deleted successfully.')
      selectedUploadForDelete.value = null
    },
    onError: () => {
      toast.error('Unable to delete file.')
    },
    onFinish: () => {
      deleting.value = false
      isDeleteModalOpen.value = false
    },
  })
}

const updateStatus = () => {
  statusForm.patch(`/admin/registration/${props.registration.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`Successfully set the status to ${statusForm.status}.`)
    },
    onError: () => {
      toast.error('Unable to update status.')
    },
  })
}

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
    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
      </div>

      <div class="relative space-y-6">
        <a href="/admin/registration" class="inline-flex text-sm font-medium text-[#2563EB] transition hover:underline dark:text-[#60A5FA]">Back to registrations</a>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <div class="flex items-center justify-between gap-3">
            <h1 class="font-['Space_Grotesk'] text-2xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Registration Details</h1>
          <div class="flex items-center gap-2">
            <Tooltip>
              <TooltipTrigger as-child>
                <Button
                  as="a"
                  :href="`/admin/user/create?email=${encodeURIComponent(registration.email)}`"
                  size="icon-sm"
                  class="cursor-pointer bg-blue-600 text-white hover:bg-blue-700"
                  aria-label="Create User"
                >
                  <UserPlus />
                </Button>
              </TooltipTrigger>
              <TooltipContent>Create User</TooltipContent>
            </Tooltip>
          </div>
          </div>
          <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Email:</strong> {{ registration.email }}</p>
          <p class="text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Company Type:</strong> {{ registration.company_type_label }}</p>
          <div class="mt-3 flex flex-wrap items-end gap-2">
            <div class="space-y-1">
              <label class="text-sm me-3 font-medium text-[#475569] dark:text-[#9FB3C8]">Status</label>
              <select
                v-model="statusForm.status"
                class="h-9 rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-2 text-sm text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]"
              >
                <option value="pending">Pending</option>
                <option value="incomplete">Incomplete</option>
                <option value="completed">Completed</option>
              </select>
              <p v-if="statusForm.errors.status" class="text-xs text-red-600">{{ statusForm.errors.status }}</p>
            </div>
            <Button type="button" :disabled="statusForm.processing" variant="outline" class="cursor-pointer" @click="updateStatus">
              Status
            </Button>
          </div>
        </div>

        <div class="space-y-3">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Submitted Files</h2>
          <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <table class="min-w-full text-sm">
            <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
              <tr>
                <th class="px-4 py-3">File</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Size</th>
                <th class="px-4 py-3">Uploaded</th>
                <th class="px-4 py-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="upload in registration.uploads" :key="upload.id" class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]">
                <td class="px-4 py-3">
                  <a
                    :href="upload.can_convert_pdf ? upload.view_pdf_url : upload.view_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-medium text-[#2563EB] underline-offset-2 hover:underline dark:text-[#60A5FA]"
                  >
                    {{ upload.original_name }}
                  </a>
                </td>
                <td class="px-4 py-3">{{ formatFileType(upload) }}</td>
                <td class="px-4 py-3">{{ formatBytes(upload.size_bytes) }}</td>
                <td class="px-4 py-3">{{ formatUploadedDate(upload.created_at) }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button
                          as="a"
                          :href="upload.download_url"
                          size="icon-sm"
                          variant="outline"
                          class="cursor-pointer"
                          aria-label="Download Original"
                        >
                          <Download />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Download Original</TooltipContent>
                    </Tooltip>
                    <Tooltip v-if="upload.can_convert_pdf">
                      <TooltipTrigger as-child>
                        <Button
                          as="a"
                          :href="upload.download_pdf_url"
                          size="icon-sm"
                          variant="outline"
                          class="cursor-pointer"
                          aria-label="Download PDF"
                        >
                          <FileDown />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Download PDF</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button
                          type="button"
                          size="icon-sm"
                          variant="destructive"
                          class="cursor-pointer"
                          aria-label="Delete File"
                          @click="openDeleteModal(upload)"
                        >
                          <Trash2 />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Delete File</TooltipContent>
                    </Tooltip>
                  </div>
                </td>
              </tr>
              <tr v-if="!registration.uploads.length">
                <td colspan="5" class="px-4 py-6 text-center text-[#64748B] dark:text-[#9FB3C8]">No files uploaded yet.</td>
              </tr>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>

    <AlertDialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete File</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this file? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel
            :disabled="deleting"
            @click="isDeleteModalOpen = false; selectedUploadForDelete = null"
          >
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction :disabled="deleting" @click="confirmDelete">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
