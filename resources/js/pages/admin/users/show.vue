<script setup lang="ts">
import { computed, ref } from 'vue'
import { ChevronDown, ChevronUp } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import FormPdfList from '@/components/forms/FormPdfList.vue'
import FormSection from '@/components/forms/FormSection.vue'
import { Badge } from '@/components/ui/badge'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Pagination } from '@/components/ui/pagination'

type CompanyType = {
  value: 'corp' | 'sole_prop' | 'opc'
  label: string
}

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
  id: string
  email?: string
  status: 'pending' | 'incomplete' | 'completed'
  submitted_at: string | null
  sections: SubmittedSection[]
}

type UserSubmission = {
  registration_id: string
  company_type: 'opc' | 'sole_prop' | 'corp'
  company_type_label: string
  registration_status: 'pending' | 'incomplete' | 'completed'
  created_at: string | null
  form_submission: FormSubmission | null
}

type ActivityItem = {
  id: string
  type: string
  description: string
  created_at: string | null
  files_count: number | null
  filenames: string[]
  section_label?: string | null
  updated_fields?: string[]
}

const props = defineProps<{
  user: {
    id: string
    name: string
    email: string
    status: string
    created_at: string | null
    company_types: CompanyType[]
  }
  submissions: UserSubmission[]
  activities: ActivityItem[]
}>()

const activitiesPerPage = 5
const activityPage = ref(1)

const activityLastPage = computed(() => {
  return Math.max(1, Math.ceil(props.activities.length / activitiesPerPage))
})

const paginatedActivities = computed(() => {
  const currentPage = Math.min(activityPage.value, activityLastPage.value)
  const start = (currentPage - 1) * activitiesPerPage
  const end = start + activitiesPerPage

  return props.activities.slice(start, end)
})

const changeActivityPage = (page: number) => {
  activityPage.value = Math.max(1, Math.min(page, activityLastPage.value))
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

const formatDateTime = (value: string | null) => {
  if (!value) return 'n/a'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return value

  return parsed.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const submissionStatusClass = (status: 'pending' | 'incomplete' | 'completed') => {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
  if (status === 'incomplete') return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'

  return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}
</script>

<template>
  <AppLayout>
    <div
      class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]"
    >
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div
          class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30"
        />
      </div>

      <div class="relative space-y-6">
        <a href="/admin/user" class="inline-flex text-sm font-medium text-[#2563EB] transition hover:underline dark:text-[#60A5FA]">
          Back to users
        </a>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h1 class="font-['Space_Grotesk'] text-2xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">User Details</h1>
          <div class="mt-3 grid gap-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Registered:</strong> {{ formatDate(user.created_at) }}</p>
          </div>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Company Types</h2>
          <div class="mt-3 flex flex-wrap gap-2">
            <Badge v-for="type in user.company_types" :key="type.value">{{ type.label }}</Badge>
            <p v-if="!user.company_types.length" class="text-sm text-[#64748B] dark:text-[#9FB3C8]">
              No company types found from registration records.
            </p>
          </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Submitted Form Data</h2>

          <div
            v-if="!submissions.length"
            class="rounded-lg border border-[#E2E8F0] p-6 text-center text-sm text-[#64748B] dark:border-[#1E3A5F] dark:text-[#9FB3C8]"
          >
            No submitted form records yet.
          </div>

          <div v-if="submissions.length" class="space-y-3">
            <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Generated PDF</h3>
            <div class="grid gap-3">
              <FormPdfList
                v-for="entry in submissions"
                :key="`pdf-${entry.registration_id}`"
                :submission="entry.form_submission"
                :company-type="entry.company_type"
                :title="entry.company_type_label"
                :subtitle="`Registration #${entry.registration_id}`"
                context="admin"
              />
            </div>
          </div>

          <div
            v-for="entry in submissions"
            :key="entry.registration_id"
            class="space-y-3"
          >
            <Collapsible class="rounded-xl border border-[#E2E8F0] p-4 dark:border-[#1E3A5F]">
              <CollapsibleTrigger class="group w-full">
                <div class="flex flex-wrap items-center justify-between gap-2 text-left">
                  <div>
                    <h3 class="font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ entry.company_type_label }}</h3>
                    <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">Registration #{{ entry.registration_id }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex rounded-full px-2 py-1 text-xs font-medium uppercase"
                      :class="submissionStatusClass(entry.form_submission?.status ?? entry.registration_status)"
                    >
                      {{ entry.form_submission?.status ?? entry.registration_status }}
                    </span>
                    <ChevronDown class="h-4 w-4 text-[#2563EB] group-data-[state=open]:hidden dark:text-[#60A5FA]" />
                    <ChevronUp class="hidden h-4 w-4 text-[#2563EB] group-data-[state=open]:block dark:text-[#60A5FA]" />
                  </div>
                </div>
              </CollapsibleTrigger>

              <CollapsibleContent class="pt-3">
                <div v-if="entry.form_submission" class="space-y-3">
                  <Collapsible
                    v-for="section in entry.form_submission.sections"
                    :key="section.name"
                    class="rounded-xl border border-[#E2E8F0] p-4 dark:border-[#1E3A5F]"
                  >
                    <CollapsibleTrigger class="group w-full">
                      <div class="flex items-center justify-between gap-2 text-left">
                        <h4 class="font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ section.label }}</h4>
                        <ChevronDown class="h-4 w-4 text-[#2563EB] group-data-[state=open]:hidden dark:text-[#60A5FA]" />
                        <ChevronUp class="hidden h-4 w-4 text-[#2563EB] group-data-[state=open]:block dark:text-[#60A5FA]" />
                      </div>
                    </CollapsibleTrigger>

                    <CollapsibleContent>
                      <div class="mt-3">
                        <FormSection
                          :section="section"
                          :update-url="`/admin/submissions/${entry.form_submission.id}/section/${section.name}`"
                        />
                      </div>
                    </CollapsibleContent>
                  </Collapsible>
                </div>

                <div
                  v-else
                  class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 text-sm text-[#64748B] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]"
                >
                  No submitted form data yet for this registration.
                </div>
              </CollapsibleContent>
            </Collapsible>
          </div>
        </div>

        <div class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">User Activity Log</h2>
          <div
            v-if="!activities.length"
            class="rounded-lg border border-[#E2E8F0] p-4 text-sm text-[#64748B] dark:border-[#1E3A5F] dark:text-[#9FB3C8]"
          >
            No activity logs for this user yet.
          </div>
          <div v-else class="space-y-2">
            <div
              v-for="activity in paginatedActivities"
              :key="activity.id"
              class="rounded-lg border border-[#E2E8F0] bg-[#F8FAFC]/80 p-4 dark:border-[#1E3A5F] dark:bg-[#0F2747]/60"
            >
              <div class="flex flex-wrap items-start justify-between gap-2">
                <p class="font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ activity.description }}</p>
                <span class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ formatDateTime(activity.created_at) }}</span>
              </div>
              <p class="mt-1 text-xs uppercase tracking-wide text-[#64748B] dark:text-[#9FB3C8]">{{ activity.type }}</p>
              <p v-if="activity.files_count !== null" class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
                Files submitted: {{ activity.files_count }}
              </p>
              <p v-if="activity.filenames.length" class="mt-1 text-sm text-[#475569] dark:text-[#9FB3C8]">
                {{ activity.filenames.join(', ') }}
              </p>
              <p v-if="activity.section_label" class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
                Section: {{ activity.section_label }}
              </p>
              <p v-if="activity.updated_fields?.length" class="mt-1 text-sm text-[#475569] dark:text-[#9FB3C8]">
                Updated fields: {{ activity.updated_fields.join(', ') }}
              </p>
            </div>
            <Pagination
              :current-page="Math.min(activityPage, activityLastPage)"
              :last-page="activityLastPage"
              :total="activities.length"
              @change="changeActivityPage"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
