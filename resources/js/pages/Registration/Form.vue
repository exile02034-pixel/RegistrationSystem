<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import FormSection from '@/components/registration/FormSection.vue'
import FormStepper from '@/components/registration/FormStepper.vue'
import { Button } from '@/components/ui/button'
import { useRegistrationForm } from '@/composables/useRegistrationForm'

type FieldSchema = {
  name: string
  label: string
  type?: string
  required?: boolean
  options?: Array<{ label: string; value: string }>
}

type SectionSchema = {
  name: string
  label: string
  fields: FieldSchema[]
}

const props = defineProps<{
  token: string
  email: string
  companyType: string
  companyTypeLabel: string
  formSchema: SectionSchema[]
  submitUrl: string
  qrCodeDataUri: string
  initialSections?: Record<string, Record<string, string>>
  isEditing?: boolean
  focusSection?: string | null
}>()

const {
  canMoveNext,
  currentSection,
  currentStep,
  form,
  isReviewStep,
  next,
  previous,
  sectionErrors,
  stepItems,
  submit,
} = useRegistrationForm(
  props.formSchema,
  props.submitUrl,
  props.initialSections ?? {},
  props.focusSection ?? null,
)

const isLastStep = () => currentStep.value === stepItems.value.length - 1
</script>

<template>
  <Head title="Online Registration Form">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link
      href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Public+Sans:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
  </Head>

  <div class="relative min-h-screen overflow-hidden bg-[#F8FAFC] text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
    <div class="pointer-events-none absolute inset-0">
      <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
      <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
      <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
      <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
    </div>

    <form class="relative mx-auto max-w-6xl space-y-6 px-4 py-8 md:px-6" @submit.prevent="submit">
      <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="font-['Space_Grotesk'] text-3xl font-semibold">
              {{ isEditing ? 'Edit Registration Form' : 'Online Registration Form' }}
            </h1>
            <p class="mt-2 text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Email:</strong> {{ email }}</p>
            <p class="text-sm text-[#475569] dark:text-[#9FB3C8]"><strong>Company Type:</strong> {{ companyTypeLabel }}</p>
            <p
              v-if="isEditing"
              class="mt-1 text-xs text-[#2563EB] dark:text-[#93C5FD]"
            >
              Your previously submitted values are loaded below.
            </p>
          </div>

          <img :src="qrCodeDataUri" alt="Registration QR Code" class="h-24 w-24 rounded-lg border border-[#E2E8F0] dark:border-[#1E3A5F]" />
        </div>
      </div>

      <FormStepper :steps="stepItems" :current-step="currentStep" />

      <FormSection
        v-if="currentSection"
        :section="currentSection"
        :model-value="form.sections[currentSection.name]"
        :errors="sectionErrors(currentSection.name)"
        @update:model-value="form.sections[currentSection.name] = $event"
      />

      <section
        v-if="isReviewStep"
        class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
      >
        <h2 class="font-['Space_Grotesk'] text-xl font-semibold">Review & Submit</h2>
        <p v-if="Object.keys(form.errors).length" class="mt-2 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-300">
          Please fix the highlighted required fields before submitting.
        </p>

        <div class="mt-4 space-y-5">
          <div v-for="section in formSchema" :key="section.name" class="space-y-2 rounded-xl border border-[#E2E8F0] p-4 dark:border-[#1E3A5F]">
            <h3 class="font-semibold">{{ section.label }}</h3>

            <div class="grid gap-2 text-sm md:grid-cols-2">
              <div v-for="field in section.fields" :key="field.name" class="rounded-md bg-[#F8FAFC] px-3 py-2 dark:bg-[#0F2747]">
                <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ field.label }}</p>
                <p class="font-medium">{{ form.sections[section.name][field.name] || '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div class="flex items-center justify-between">
        <Button type="button" variant="outline" :disabled="currentStep === 0 || form.processing" @click="previous">
          Previous
        </Button>

        <div class="flex items-center gap-2">
          <p v-if="!canMoveNext && !isReviewStep" class="text-xs text-red-600">Please complete required fields first.</p>
          <Button
            v-if="!isLastStep()"
            type="button"
            :disabled="form.processing"
            @click="next"
          >
            Next
          </Button>
          <Button
            v-else
            type="submit"
            :disabled="form.processing"
          >
            {{ form.processing ? 'Submitting...' : 'Submit Registration' }}
          </Button>
        </div>
      </div>
    </form>
  </div>
</template>
