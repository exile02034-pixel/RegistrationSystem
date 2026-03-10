<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { ChevronDown, ChevronUp } from 'lucide-vue-next'
import FormPdfList from '@/components/forms/FormPdfList.vue'
import FormSection from '@/components/forms/FormSection.vue'
import { Badge } from '@/components/ui/badge'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { useUserFiles } from '@/composables/useUserFiles'
import AppLayout from '@/layouts/AppLayout.vue'
import type { UserFilesPageProps } from '@/types/user-pages'

const props = defineProps<UserFilesPageProps>()
const { submissions, clientInfo } = props
const { breadcrumbs, avatarInitials, shortCompanyType } = useUserFiles(props.clientInfo)

</script>

<template>
  <Head title="About Me" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="app-page">
      <div class="app-page-bg">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="app-page-pattern" />
      </div>

      <div class="relative space-y-6">
        <div>
          <h1 class="app-title">
            My Files
          </h1>
          <p class="app-subtitle">
            View your submitted registration form data.
          </p>
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 flex items-center justify-center rounded-full bg-[#EFF6FF] text-[#2563EB] text-xs font-semibold dark:bg-[#0F2747] dark:text-[#E6F1FF]">
              {{ avatarInitials }}
            </div>
            <div>
              <p class="text-sm font-semibold">{{ clientInfo.name }}</p>
              <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ clientInfo.email }}</p>
            </div>
          </div>
          <div class="mt-3 flex flex-wrap gap-1.5">
            <Badge
              v-for="type in clientInfo.company_types"
              :key="type.value"
              class="border-[#60A5FA] bg-[#EFF6FF] text-[#2563EB] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]"
            >
              {{ shortCompanyType(type.value) }}
            </Badge>
            <span v-if="!clientInfo.company_types.length" class="text-xs text-[#64748B] dark:text-[#9FB3C8]">
              No company type assigned yet.
            </span>
          </div>
        </div>

        <div
          v-if="!submissions.length"
          class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 text-center text-sm text-[#475569] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]"
        >
          No registration records found yet.
        </div>

        <div v-if="submissions.length" class="space-y-3">
          <h2 class="app-section-title">Generated PDF</h2>
          <div class="grid gap-3">
            <FormPdfList
              v-for="entry in submissions"
              :key="`pdf-${entry.registration_id}`"
              :submission="entry.form_submission"
              :company-type="entry.company_type"
              :title="entry.company_type_label"
              :subtitle="`Registration #${entry.registration_id}`"
              context="user"
            />
          </div>
        </div>

        <div v-for="entry in submissions" :key="entry.registration_id" class="space-y-3">
          <Collapsible
            class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
          >
            <CollapsibleTrigger class="group w-full">
              <div class="flex flex-wrap items-center justify-between gap-2 text-left">
                <div>
                  <h2 class="font-['Space_Grotesk'] text-xl font-semibold">{{ entry.company_type_label }}</h2>
                  <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">Registration #{{ entry.registration_id }}</p>
                </div>
                <div class="flex items-center gap-2">

                  <ChevronDown class="h-4 w-4 text-[#2563EB] group-data-[state=open]:hidden dark:text-[#60A5FA]" />
                  <ChevronUp class="hidden h-4 w-4 text-[#2563EB] group-data-[state=open]:block dark:text-[#60A5FA]" />
                </div>
              </div>
            </CollapsibleTrigger>

            <CollapsibleContent class="pt-3">
              <div
                v-if="entry.form_submission"
                class="space-y-3"
              >
                <FormSection
                  v-for="section in entry.form_submission.sections"
                  :key="section.name"
                  :section="section"
                  :update-url="`/user/submissions/${entry.form_submission.id}/section/${section.name}`"
                />
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
    </div>
  </AppLayout>
</template>
