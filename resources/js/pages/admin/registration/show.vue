<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ChevronDown, ChevronUp, Download, Eye, FileText, MoreHorizontal, Upload, UserPlus } from 'lucide-vue-next'
import { ref } from 'vue'
import DocumentFormsPanel from '@/components/admin/registration/DocumentFormsPanel.vue'
import FormPdfList from '@/components/forms/FormPdfList.vue'
import FormSection from '@/components/forms/FormSection.vue'
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
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { toast } from '@/components/ui/sonner'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { useAdminRegistrationShow } from '@/composables/admin/useAdminRegistrationShow'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AdminRegistrationShowPageProps } from '@/types'

const props = defineProps<AdminRegistrationShowPageProps>()
const {
  canCreateUser,
  createUserForm,
  isCreateUserModalOpen,
  statusForm,
  formatDateTime,
  openCreateUserModal,
  submitCreateUser,
  updateStatus,
} = useAdminRegistrationShow(props.registration)

const uploadRequiredDocumentForm = useForm<{ document_type: string; file: File | null }>({
  document_type: '',
  file: null,
})
const deleteRequiredDocumentForm = useForm({})
const requiredDocumentFileInputs = ref<Record<string, HTMLInputElement | null>>({})
const requiredDeleteDialogOpen = ref(false)
const pendingRequiredDeleteUrl = ref<string | null>(null)
const pendingRequiredDeleteName = ref('')

const setRequiredDocumentFileInput = (type: string, element: Element | null) => {
  requiredDocumentFileInputs.value[type] = (element as HTMLInputElement | null)
}

const openRequiredDocumentPicker = (type: string) => {
  requiredDocumentFileInputs.value[type]?.click()
}

const uploadRequiredDocument = (type: string, uploadUrl: string, event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0] ?? null
  if (file === null) return

  uploadRequiredDocumentForm.document_type = type
  uploadRequiredDocumentForm.file = file

  uploadRequiredDocumentForm.post(uploadUrl, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Required document uploaded successfully.')
    },
    onError: (errors) => {
      const firstMessage = Object.values(errors).find((value) => typeof value === 'string')
      toast.error((firstMessage as string | undefined) ?? 'Failed to upload required document.')
    },
    onFinish: () => {
      uploadRequiredDocumentForm.reset('file')
      input.value = ''
    },
  })
}

const deleteRequiredDocument = (deleteUrl: string | null, name: string) => {
  if (!deleteUrl) return
  pendingRequiredDeleteUrl.value = deleteUrl
  pendingRequiredDeleteName.value = name
  requiredDeleteDialogOpen.value = true
}

const cancelDeleteRequiredDocument = () => {
  requiredDeleteDialogOpen.value = false
  pendingRequiredDeleteUrl.value = null
  pendingRequiredDeleteName.value = ''
}

const confirmDeleteRequiredDocument = () => {
  if (!pendingRequiredDeleteUrl.value) return

  deleteRequiredDocumentForm.delete(pendingRequiredDeleteUrl.value, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Required document deleted successfully.')
      cancelDeleteRequiredDocument()
    },
    onError: (errors) => {
      const firstMessage = Object.values(errors).find((value) => typeof value === 'string')
      toast.error((firstMessage as string | undefined) ?? 'Failed to delete required document.')
    },
  })
}
</script>

<template>
  <AppLayout>
    <div class="app-page">
      <div class="app-page-bg">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="app-page-pattern" />
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
                    v-if="canCreateUser"
                    type="button"
                    size="icon-sm"
                    class="cursor-pointer bg-blue-600 text-white hover:bg-blue-700"
                    aria-label="Create User"
                    @click="openCreateUserModal"
                  >
                    <UserPlus />
                  </Button>
                  <Button
                    v-else
                    type="button"
                    size="icon-sm"
                    class="cursor-not-allowed bg-slate-300 text-slate-600 hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-300"
                    aria-label="Create User"
                    :disabled="true"
                  >
                    <UserPlus />
                  </Button>
                </TooltipTrigger>
                <TooltipContent>
                  {{ canCreateUser ? 'Create User' : 'Set registration status to Completed before creating a user.' }}
                </TooltipContent>
              </Tooltip>
            </div>
          </div>
          <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Email:</strong> {{ registration.email }}</p>
          <p class="text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Company Type:</strong> {{ registration.company_type_label }}</p>
          <p class="text-sm text-[#475569] dark:text-[#9FB3C8]">
            <strong>Revision History:</strong> {{ registration.revision_count }} change(s)
            <span v-if="registration.last_revision_at">(last update: {{ formatDateTime(registration.last_revision_at) }})</span>
          </p>
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
              Save
            </Button>
          </div>
        </div>

        <Dialog :open="isCreateUserModalOpen" @update:open="isCreateUserModalOpen = $event">
          <DialogContent class="sm:max-w-lg dark:border-[#1E3A5F] dark:bg-[#12325B]">
            <DialogHeader>
              <DialogTitle>Create User / Client</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
              <div class="space-y-2">
                <Label>Name</Label>
                <Input v-model="createUserForm.name" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                <p v-if="createUserForm.errors.name" class="text-red-500 text-sm">{{ createUserForm.errors.name }}</p>
              </div>
              <div class="space-y-2">
                <Label>Email</Label>
                <Input v-model="createUserForm.email" type="email" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                <p v-if="createUserForm.errors.email" class="text-red-500 text-sm">{{ createUserForm.errors.email }}</p>
              </div>
              <div class="space-y-2">
                <Label>Password</Label>
                <Input v-model="createUserForm.password" type="password" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                <p v-if="createUserForm.errors.password" class="text-red-500 text-sm">{{ createUserForm.errors.password }}</p>
              </div>
              <div class="space-y-2">
                <Label>Confirm Password</Label>
                <Input v-model="createUserForm.password_confirmation" type="password" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
              </div>
              <div class="flex justify-end gap-2">
                <button type="button" class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 py-2 text-sm text-[#0B1F3A] transition hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]" @click="isCreateUserModalOpen = false">Cancel</button>
                <button type="button" class="rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 py-2 text-sm text-white transition hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]" @click="submitCreateUser">Create User</button>
              </div>
            </div>
          </DialogContent>
        </Dialog>

        <div class="space-y-3">
          <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
            <div class="mb-3 flex items-center gap-2">
              <FileText class="h-4 w-4 text-[#2563EB] dark:text-[#60A5FA]" />
              <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Required Documents</h3>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <table class="min-w-full text-sm">
                <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
                  <tr>
                    <th class="px-4 py-3">Document</th>
                    <th class="px-4 py-3">Uploaded By</th>
                    <th class="px-4 py-3">Uploaded At</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="requiredDocument in registration.required_documents"
                    :key="requiredDocument.type"
                    class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]"
                  >
                    <td class="px-4 py-3">
                      <p class="font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ requiredDocument.name }}</p>
                      <p v-if="requiredDocument.is_uploaded" class="text-xs text-[#475569] dark:text-[#9FB3C8]">
                        {{ requiredDocument.original_filename }}
                      </p>
                      <p v-else class="text-xs text-[#64748B] dark:text-[#9FB3C8]">No file uploaded yet.</p>
                    </td>
                    <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">
                      {{ requiredDocument.uploaded_by || 'n/a' }}
                    </td>
                    <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">
                      {{ formatDateTime(requiredDocument.uploaded_at) }}
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex items-center justify-end gap-1">
                        <input
                          :ref="(el) => setRequiredDocumentFileInput(requiredDocument.type, el as Element | null)"
                          type="file"
                          class="hidden"
                          @change="(event) => uploadRequiredDocument(requiredDocument.type, requiredDocument.upload_url, event)"
                        >
                        <Button
                          type="button"
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8"
                          :disabled="uploadRequiredDocumentForm.processing"
                          @click="openRequiredDocumentPicker(requiredDocument.type)"
                        >
                          <Upload class="h-4 w-4" />
                        </Button>
                        <Button
                          v-if="requiredDocument.view_url"
                          as="a"
                          :href="requiredDocument.view_url"
                          target="_blank"
                          rel="noopener noreferrer"
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8"
                        >
                          <Eye class="h-4 w-4" />
                        </Button>
                        <Button
                          v-if="requiredDocument.download_url"
                          as="a"
                          :href="requiredDocument.download_url"
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8"
                        >
                          <Download class="h-4 w-4" />
                        </Button>
                        <DropdownMenu v-if="requiredDocument.delete_url">
                          <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="icon" class="h-8 w-8">
                              <MoreHorizontal class="h-4 w-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end" class="w-40">
                            <DropdownMenuItem
                              class="text-destructive"
                              :disabled="deleteRequiredDocumentForm.processing"
                              @click="deleteRequiredDocument(requiredDocument.delete_url, requiredDocument.name)"
                            >
                              Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <h2 class="app-section-title">Submitted Form Data</h2>

          <FormPdfList
            :submission="registration.form_submission"
            :company-type="registration.company_type"
            context="admin"
            :registration-id="registration.id"
            :registration-email="registration.email"
          />

          <DocumentFormsPanel
            :registration-id="registration.id"
            :forms="registration.document_forms"
            :generated-documents="registration.generated_documents"
            :gis-autofill="registration.gis_autofill"
            :appointment-autofill="registration.appointment_autofill"
          />

          <Collapsible
            v-if="registration.form_submission"
            class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
          >
            <CollapsibleTrigger class="group w-full">
              <div class="flex flex-wrap items-center justify-between gap-2 text-left">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-[#475569] dark:text-[#9FB3C8]">Submitted Form:</span>
                 
                </div>
                <ChevronDown class="h-4 w-4 text-[#2563EB] group-data-[state=open]:hidden dark:text-[#60A5FA]" />
                <ChevronUp class="hidden h-4 w-4 text-[#2563EB] group-data-[state=open]:block dark:text-[#60A5FA]" />
              </div>
            </CollapsibleTrigger>

            <CollapsibleContent class="space-y-4 pt-3">
              <div
                v-for="section in registration.form_submission.sections"
                :key="section.name"
                class="mt-3"
              >
                <FormSection
                  :section="section"
                  :update-url="`/admin/submissions/${registration.form_submission.id}/section/${section.name}`"
                />
              </div>
            </CollapsibleContent>
          </Collapsible>
          <div
            v-else
            class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 text-sm text-[#64748B] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]"
          >
            No online form submission yet.
          </div>
        </div>
      </div>
    </div>

    <AlertDialog :open="requiredDeleteDialogOpen" @update:open="(value) => !value && cancelDeleteRequiredDocument()">
      <AlertDialogContent class="dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Required Document</AlertDialogTitle>
          <AlertDialogDescription>
            Delete uploaded file for {{ pendingRequiredDeleteName }}? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deleteRequiredDocumentForm.processing" @click="cancelDeleteRequiredDocument">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction :disabled="deleteRequiredDocumentForm.processing" @click="confirmDeleteRequiredDocument">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
