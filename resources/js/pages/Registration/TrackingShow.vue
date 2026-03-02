<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import TrackedSectionCard from '@/components/registration/TrackedSectionCard.vue'
import { useSubmissionTracking } from '@/composables/useSubmissionTracking'

const props = defineProps<{
  email: string
  companyTypeLabel: string
  status: string
  statusLabel: string
  submittedAt: string | null
  canEdit: boolean
  editableSections: string[]
  editUrl: string
  logoutUrl: string
  revisionCount: number
  lastRevisionAt: string | null
  summary: {
    sections?: Array<{ name: string; label: string; fields: Array<{ name: string; label: string; value: string | null }> }>
  } | null
}>()

const form = useForm({})
const { formatDate, sectionEditUrl } = useSubmissionTracking(props.editUrl)
const canEditSection = (sectionName: string) =>
  props.canEdit && props.editableSections.includes(sectionName)

const logout = () => {
  form.post(props.logoutUrl)
}

</script>

<template>
  <Head title="Submission Tracking" />

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
      <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
    </div>

    <div class="relative mx-auto max-w-4xl space-y-4 px-4 py-8">
      <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#2A4A72] dark:bg-[#12325B]">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#F8FAFC]">Submission Tracking</h1>
            <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Email:</strong> {{ props.email }}</p>
            <p class="text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Company Type:</strong> {{ props.companyTypeLabel }}</p>
          </div>
          <button
            type="button"
            class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-sm font-medium text-[#0B1F3A] transition hover:bg-[#EFF6FF] dark:border-[#2A4A72] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#16345C]"
            @click="logout"
          >
            End session
          </button>
        </div>
      </div>

      <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#2A4A72] dark:bg-[#12325B]">
        <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#F8FAFC]">Submission Details</h2>
        <div class="mt-3 flex items-center gap-2">
          
        </div>
        <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
          Submitted at: {{ formatDate(props.submittedAt) }}
        </p>
      

        <p v-if="!props.canEdit" class="mt-4 text-sm text-[#64748B] dark:text-[#9FB3C8]">
          Editing is currently locked for this submission status.
        </p>
      </div>

      <div
        v-if="props.summary?.sections?.length"
        class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#2A4A72] dark:bg-[#12325B]"
      >
        <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#F8FAFC]">Submission Summary</h2>

        <div class="mt-4 space-y-4">
          <TrackedSectionCard
            v-for="section in props.summary.sections"
            :key="section.name"
            :section="section"
            :can-edit="canEditSection(section.name)"
            :edit-url="sectionEditUrl(section.name)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
