<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { AlertTriangle, CheckCircle2, Download, Eye, FileDown, Loader2, Mail, MoreHorizontal, Trash2, UserPlus } from 'lucide-vue-next'
import { computed, ref } from 'vue'
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
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
    company_type: 'corp' | 'sole_prop' | 'opc'
    company_type_label: string
    status: string
    created_at: string | null
    required_documents: string[]
    missing_documents: string[]
    has_missing_documents: boolean
    follow_up_url: string
    uploads: Upload[]
  }
}>()

const isDeleteModalOpen = ref(false)
const isFollowUpModalOpen = ref(false)
const isCreateUserModalOpen = ref(false)
const isPreviewModalOpen = ref(false)
const isStatusConfirmModalOpen = ref(false)
const deleting = ref(false)
const selectedUploadForDelete = ref<Upload | null>(null)
const selectedUploadForPreview = ref<Upload | null>(null)
const statusForm = useForm({
  status: props.registration.status as 'pending' | 'incomplete' | 'completed',
})
const followUpForm = useForm({})
const createUserForm = useForm({
  name: '',
  email: props.registration.email,
  password: '',
  password_confirmation: '',
})
const requiredCount = computed(() => props.registration.required_documents.length)
const missingCount = computed(() => props.registration.missing_documents.length)
const submittedCount = computed(() => requiredCount.value - missingCount.value)
const canCreateUser = computed(() => props.registration.status === 'completed')
const completionPercent = computed(() => {
  if (requiredCount.value === 0) return 0
  return Math.round((submittedCount.value / requiredCount.value) * 100)
})

const openDeleteModal = (upload: Upload) => {
  selectedUploadForDelete.value = upload
  isDeleteModalOpen.value = true
}

const openPreviewModal = (upload: Upload) => {
  selectedUploadForPreview.value = upload
  isPreviewModalOpen.value = true
}

const onPreviewModalOpenChange = (open: boolean) => {
  isPreviewModalOpen.value = open

  if (!open) {
    selectedUploadForPreview.value = null
  }
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
      isStatusConfirmModalOpen.value = false
      toast.success(`Successfully set the status to ${statusForm.status}.`)
    },
    onError: () => {
      toast.error('Unable to update status.')
    },
  })
}

const onClickSaveStatus = () => {
  isStatusConfirmModalOpen.value = true
}

const openFollowUpModal = () => {
  if (!props.registration.has_missing_documents) {
    toast.info('No missing documents to follow up.')
    return
  }

  isFollowUpModalOpen.value = true
}

const openCreateUserModal = () => {
  if (!canCreateUser.value) {
    toast.info('Set status to Completed before creating a user.')
    return
  }

  createUserForm.reset('name', 'password', 'password_confirmation')
  createUserForm.clearErrors()
  createUserForm.email = props.registration.email
  isCreateUserModalOpen.value = true
}

const createUser = () => {
  createUserForm.post('/admin/user', {
    preserveScroll: true,
    onSuccess: () => {
      createUserForm.reset('name', 'password', 'password_confirmation')
      createUserForm.email = props.registration.email
      isCreateUserModalOpen.value = false
      toast.success('User created successfully.')
    },
    onError: () => {
      toast.error('Unable to create user.')
    },
  })
}

const sendMissingDocumentsFollowUp = () => {
  if (!props.registration.has_missing_documents) return

  followUpForm.post(props.registration.follow_up_url, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Follow-up email sent successfully.')
      isFollowUpModalOpen.value = false
    },
    onError: () => {
      toast.error('Unable to send follow-up email.')
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

const statusBadgeClass = computed(() => {
  if (statusForm.status === 'completed') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200'
  if (statusForm.status === 'incomplete') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200'
  return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200'
})

const statusLabel = computed(() => statusForm.status.charAt(0).toUpperCase() + statusForm.status.slice(1))
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
                  type="button"
                  size="icon-sm"
                  :class="canCreateUser ? 'cursor-pointer bg-blue-600 text-white hover:bg-blue-700' : 'cursor-not-allowed bg-slate-300 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                  :disabled="!canCreateUser"
                  aria-label="Create User"
                  @click="openCreateUserModal"
                >
                  <UserPlus />
                </Button>
              </TooltipTrigger>
              <TooltipContent>
                {{ canCreateUser ? 'Create User' : 'Set status to Completed before creating a user' }}
              </TooltipContent>
            </Tooltip>
          </div>
          </div>
          <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Email:</strong> {{ registration.email }}</p>
          <p class="text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Company Type:</strong> {{ registration.company_type_label }}</p>
          <div class="mt-1">
            <span
              class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
              :class="statusBadgeClass"
            >
              {{ statusLabel }}
            </span>
          </div>
          <p
            v-if="!canCreateUser"
            class="mt-1 text-sm text-amber-700 dark:text-amber-300"
          >
            Complete this registration first. User creation is enabled only when status is set to Completed.
          </p>
          <p class="text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Required Files:</strong> {{ registration.required_documents.join(', ') }}</p>
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
            <Button type="button" :disabled="statusForm.processing" variant="outline" class="cursor-pointer" @click="onClickSaveStatus">
              Save
            </Button>
            <Button
              type="button"
              :disabled="followUpForm.processing || !registration.has_missing_documents"
              class="cursor-pointer bg-amber-600 text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50"
              @click="openFollowUpModal"
            >
              <Mail class="mr-2 h-4 w-4" />
              Send Missing Docs Follow-up
            </Button>
          </div>

          <div class="mt-4 rounded-lg border border-[#DBEAFE] bg-[#EFF6FF] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
            <div class="flex items-center justify-between text-sm">
              <p class="font-medium text-[#1E3A8A] dark:text-[#BFDBFE]">
                Document Completion: {{ submittedCount }}/{{ requiredCount }}
              </p>
              <p class="font-semibold text-[#1E3A8A] dark:text-[#BFDBFE]">{{ completionPercent }}%</p>
            </div>
            <div class="mt-2 h-2 rounded-full bg-[#BFDBFE] dark:bg-[#1E3A5F]">
              <div
                class="h-2 rounded-full bg-[#2563EB] transition-all"
                :style="{ width: `${completionPercent}%` }"
              />
            </div>
          </div>

          <div class="mt-3 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-100">
            <div class="flex items-center justify-between">
              <p class="font-medium">Missing Files</p>
              <span class="inline-flex items-center rounded-full border border-amber-400 px-2 py-0.5 text-xs font-semibold">
                {{ missingCount }} missing
              </span>
            </div>

            <ul class="mt-2 space-y-2">
              <li
                v-for="requiredFile in registration.required_documents"
                :key="requiredFile"
                class="flex items-center justify-between rounded-md border border-amber-200 bg-white px-2 py-1.5 dark:border-amber-800 dark:bg-amber-950/20"
              >
                <span class="truncate pr-2">{{ requiredFile }}</span>
                <span
                  v-if="registration.missing_documents.includes(requiredFile)"
                  class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                >
                  <AlertTriangle class="h-3.5 w-3.5" />
                  Missing
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200"
                >
                  <CheckCircle2 class="h-3.5 w-3.5" />
                  Submitted
                </span>
              </li>
            </ul>
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
                  <button
                    type="button"
                    class="text-[#2563EB] hover:underline dark:text-[#60A5FA]"
                    @click="openPreviewModal(upload)"
                  >
                    {{ upload.original_name }}
                  </button>
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
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button
                          type="button"
                          size="icon-sm"
                          variant="outline"
                          class="cursor-pointer"
                          aria-label="More file actions"
                        >
                          <MoreHorizontal />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-44">
                        <DropdownMenuItem
                          @click="openPreviewModal(upload)"
                        >
                          <span class="flex w-full items-center gap-2">
                            <Eye class="h-4 w-4" />
                            View File
                          </span>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          v-if="upload.can_convert_pdf"
                          as-child
                        >
                          <a
                            :href="upload.download_pdf_url"
                            class="flex w-full items-center gap-2"
                          >
                            <FileDown class="h-4 w-4" />
                            Download PDF
                          </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          class="text-red-600 focus:text-red-600 dark:text-red-400 dark:focus:text-red-400"
                          @click="openDeleteModal(upload)"
                        >
                          <Trash2 class="h-4 w-4" />
                          Delete File
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
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

    <Dialog :open="isPreviewModalOpen" @update:open="onPreviewModalOpenChange">
      <DialogContent class="max-h-[90vh] w-[min(95vw,1100px)] max-w-none overflow-hidden p-0 dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <DialogHeader class="border-b border-[#E2E8F0] px-4 py-3 dark:border-[#1E3A5F]">
          <DialogTitle class="truncate pr-8">
            Preview: {{ selectedUploadForPreview?.original_name }}
          </DialogTitle>
        </DialogHeader>

        <div class="h-[75vh] bg-[#F8FAFC] dark:bg-[#0F2747]">
          <iframe
            v-if="selectedUploadForPreview"
            :src="selectedUploadForPreview.view_url"
            class="h-full w-full border-0"
            title="PDF preview"
          />
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="isStatusConfirmModalOpen" @update:open="isStatusConfirmModalOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm Status Update</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to set this registration status to {{ statusLabel }}?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="statusForm.processing">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="statusForm.processing" @click="updateStatus">
            Confirm
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

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

    <Dialog :open="isCreateUserModalOpen" @update:open="isCreateUserModalOpen = $event">
      <DialogContent class="sm:max-w-lg dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <DialogHeader>
          <DialogTitle>Create User / Client</DialogTitle>
        </DialogHeader>

        <div class="space-y-4">
          <div class="space-y-2">
            <Label>Name</Label>
            <Input v-model="createUserForm.name" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
            <p v-if="createUserForm.errors.name" class="text-sm text-red-500">{{ createUserForm.errors.name }}</p>
          </div>
          <div class="space-y-2">
            <Label>Email</Label>
            <Input
              v-model="createUserForm.email"
              type="email"
              readonly
              class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            />
            <p v-if="createUserForm.errors.email" class="text-sm text-red-500">{{ createUserForm.errors.email }}</p>
          </div>
          <div class="space-y-2">
            <Label>Password</Label>
            <Input v-model="createUserForm.password" type="password" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
            <p v-if="createUserForm.errors.password" class="text-sm text-red-500">{{ createUserForm.errors.password }}</p>
          </div>
          <div class="space-y-2">
            <Label>Confirm Password</Label>
            <Input v-model="createUserForm.password_confirmation" type="password" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
          </div>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 py-2 text-sm text-[#0B1F3A] transition hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
              :disabled="createUserForm.processing"
              @click="isCreateUserModalOpen = false"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 py-2 text-sm text-white transition hover:bg-[#1D4ED8] disabled:opacity-50 dark:hover:bg-[#3B82F6]"
              :disabled="createUserForm.processing"
              @click="createUser"
            >
              Create User
            </button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="isFollowUpModalOpen" @update:open="isFollowUpModalOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Send Follow-up Email</AlertDialogTitle>
          <AlertDialogDescription>
            This will send a polite follow-up email to {{ registration.email }} with the missing document list and attached template files below.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div class="space-y-2 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-100">
          <p class="font-medium">Files To Include In Follow-up</p>
          <ul class="space-y-1">
            <li
              v-for="fileName in registration.missing_documents"
              :key="fileName"
              class="rounded-md border border-amber-200 bg-white px-2 py-1 dark:border-amber-800 dark:bg-amber-950/20"
            >
              {{ fileName }}
            </li>
          </ul>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel :disabled="followUpForm.processing">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="followUpForm.processing" @click="sendMissingDocumentsFollowUp">
            <Loader2 v-if="followUpForm.processing" class="mr-2 h-4 w-4 animate-spin" />
            <Mail v-else class="mr-2 h-4 w-4" />
            Send Follow-up
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
