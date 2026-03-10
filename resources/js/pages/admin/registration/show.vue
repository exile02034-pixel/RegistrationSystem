<script setup lang="ts">
import { ChevronDown, ChevronUp, Download, Eye, Upload, UserPlus } from 'lucide-vue-next'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import DocumentFormsPanel from '@/components/admin/registration/DocumentFormsPanel.vue'
import FormPdfList from '@/components/forms/FormPdfList.vue'
import FormSection from '@/components/forms/FormSection.vue'
import { Button } from '@/components/ui/button'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
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
const requiredDocumentFileInputs = ref<Record<string, HTMLInputElement | null>>({})

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
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Required Documents</h2>
          <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
            <div class="space-y-3">
              <div
                v-for="requiredDocument in registration.required_documents"
                :key="requiredDocument.type"
                class="flex flex-col gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747] md:flex-row md:items-center md:justify-between"
              >
                <div class="space-y-1">
                  <p class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ requiredDocument.name }}</p>
                  <p v-if="requiredDocument.is_uploaded" class="text-xs text-[#475569] dark:text-[#9FB3C8]">
                    Uploaded: {{ requiredDocument.original_filename }}<span v-if="requiredDocument.uploaded_by"> by {{ requiredDocument.uploaded_by }}</span>
                  </p>
                  <p v-else class="text-xs text-[#64748B] dark:text-[#9FB3C8]">No file uploaded yet.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <input
                    :ref="(el) => setRequiredDocumentFileInput(requiredDocument.type, el as Element | null)"
                    type="file"
                    class="hidden"
                    @change="(event) => uploadRequiredDocument(requiredDocument.type, requiredDocument.upload_url, event)"
                  >
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="uploadRequiredDocumentForm.processing"
                    @click="openRequiredDocumentPicker(requiredDocument.type)"
                  >
                    <Upload class="mr-1 h-4 w-4" />
                    Upload
                  </Button>

                  <a
                    v-if="requiredDocument.view_url"
                    :href="requiredDocument.view_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-8 items-center rounded-md border border-[#E2E8F0] px-3 text-xs font-medium text-[#0B1F3A] hover:bg-[#EFF6FF] dark:border-[#1E3A5F] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
                  >
                    <Eye class="mr-1 h-4 w-4" />
                    View
                  </a>
                  <a
                    v-if="requiredDocument.download_url"
                    :href="requiredDocument.download_url"
                    class="inline-flex h-8 items-center rounded-md border border-[#E2E8F0] px-3 text-xs font-medium text-[#0B1F3A] hover:bg-[#EFF6FF] dark:border-[#1E3A5F] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
                  >
                    <Download class="mr-1 h-4 w-4" />
                    Download
                  </a>
                </div>
              </div>
            </div>
          </div>

          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Submitted Form Data</h2>

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
  </AppLayout>
</template>
