<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ChevronDown, ChevronUp, UserPlus } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import FormPdfList from '@/components/forms/FormPdfList.vue'
import { toast } from '@/components/ui/sonner'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import AppLayout from '@/layouts/AppLayout.vue'

type SubmittedField = {
  name: string
  label: string
  value: string | null
}

type SubmittedSection = {
  name: string
  label: string
  fields: SubmittedField[]
}

type FormSubmission = {
  id: number
  email?: string
  status: 'pending' | 'incomplete' | 'completed'
  submitted_at: string | null
  sections: SubmittedSection[]
}

const props = defineProps<{
  registration: {
    id: number
    email: string
    token: string
    company_type: 'opc' | 'sole_prop' | 'corp'
    company_type_label: string
    status: string
    created_at: string | null
    form_submission: FormSubmission | null
  }
}>()

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

const canCreateUser = props.registration.status === 'completed'

const submissionStatusClass = (status: FormSubmission['status']) => {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
  if (status === 'incomplete') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'

  return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
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
                    v-if="canCreateUser"
                    as="a"
                    :href="`/admin/user/create?email=${encodeURIComponent(registration.email)}`"
                    size="icon-sm"
                    class="cursor-pointer bg-blue-600 text-white hover:bg-blue-700"
                    aria-label="Create User"
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
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Submitted Form Data</h2>

          <FormPdfList
            :submission="registration.form_submission"
            :company-type="registration.company_type"
            context="admin"
          />

          <Collapsible
            v-if="registration.form_submission"
            class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
          >
            <CollapsibleTrigger class="group w-full">
              <div class="flex flex-wrap items-center justify-between gap-2 text-left">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-[#475569] dark:text-[#9FB3C8]">Submitted Form:</span>
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium uppercase"
                    :class="submissionStatusClass(registration.form_submission.status)"
                  >
                    {{ registration.form_submission.status }}
                  </span>
                </div>
                <ChevronDown class="h-4 w-4 text-[#2563EB] group-data-[state=open]:hidden dark:text-[#60A5FA]" />
                <ChevronUp class="hidden h-4 w-4 text-[#2563EB] group-data-[state=open]:block dark:text-[#60A5FA]" />
              </div>
            </CollapsibleTrigger>

            <CollapsibleContent class="space-y-4 pt-3">
              <Collapsible
                v-for="section in registration.form_submission.sections"
                :key="section.name"
                class="rounded-xl border border-[#E2E8F0] p-4 dark:border-[#1E3A5F]"
              >
                <CollapsibleTrigger class="group w-full">
                  <div class="flex items-center justify-between gap-2 text-left">
                    <h3 class="font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ section.label }}</h3>
                    <ChevronDown class="h-4 w-4 text-[#2563EB] group-data-[state=open]:hidden dark:text-[#60A5FA]" />
                    <ChevronUp class="hidden h-4 w-4 text-[#2563EB] group-data-[state=open]:block dark:text-[#60A5FA]" />
                  </div>
                </CollapsibleTrigger>

                <CollapsibleContent>
                  <div class="mt-3 grid gap-2 md:grid-cols-2">
                    <div
                      v-for="field in section.fields"
                      :key="`${section.name}-${field.name}`"
                      class="rounded-md bg-[#F8FAFC] px-3 py-2 text-sm dark:bg-[#0F2747]"
                    >
                      <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ field.label }}</p>
                      <p class="font-medium">{{ field.value || '—' }}</p>
                    </div>
                  </div>
                </CollapsibleContent>
              </Collapsible>
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
