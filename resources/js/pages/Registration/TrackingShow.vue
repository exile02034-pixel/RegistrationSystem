<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import TrackedSectionCard from '@/components/registration/TrackedSectionCard.vue'
import { useTrackingShow } from '@/composables/useTrackingShow'
import type { TrackingShowPageProps } from '@/types/registration'

const props = defineProps<TrackingShowPageProps>()
const { requestEditForm, formatDate, sectionEditUrl, canEditSection, summarySections, logout, requestEditPermission } = useTrackingShow(props)

</script>

<template>
  <Head title="Submission Tracking" />

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="app-page-bg">
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
        <p v-if="props.statusMessage" class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-3 text-sm text-emerald-700">
          {{ props.statusMessage }}
        </p>
        <p v-if="props.errorMessage" class="mt-4 rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm text-rose-700">
          {{ props.errorMessage }}
        </p>
        <div class="mt-3 flex items-center gap-2">
          
        </div>
        <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]">
          Submitted at: {{ formatDate(props.submittedAt) }}
        </p>
      

        <p v-if="!props.canEdit" class="mt-4 text-sm text-[#64748B] dark:text-[#9FB3C8]">
          Editing is currently locked for this submission status.
        </p>
        <button
          v-if="!props.canEdit"
          type="button"
          class="mt-4 inline-flex rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1D4ED8] disabled:cursor-not-allowed disabled:opacity-60"
          :disabled="requestEditForm.processing"
          @click="requestEditPermission"
        >
          {{ requestEditForm.processing ? 'Sending request...' : 'Request Edit Permission' }}
        </button>
      </div>

      <div
        v-if="summarySections.length"
        class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#2A4A72] dark:bg-[#12325B]"
      >
        <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#F8FAFC]">Submission Summary</h2>

        <div class="mt-4 space-y-4">
          <TrackedSectionCard
            v-for="section in summarySections"
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
