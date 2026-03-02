<script setup lang="ts">
import FormField from '@/components/registration/FormField.vue'

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
  section: SectionSchema
  modelValue: Record<string, string>
  errors?: Record<string, string>
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: Record<string, string>): void
}>()

const updateField = (fieldName: string, value: string) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [fieldName]: value,
  })
}

const clearSectionValues = () => {
  const cleared = props.section.fields.reduce<Record<string, string>>((carry, field) => {
    carry[field.name] = ''
    return carry
  }, {})

  emit('update:modelValue', {
    ...props.modelValue,
    ...cleared,
  })
}
</script>

<template>
  <section class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
    <div class="flex items-center justify-between gap-3">
      <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ section.label }}</h2>
      <button
        v-if="section.name === 'treasurer_details'"
        type="button"
        class="rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-3 py-1 text-xs font-medium text-[#0B1F3A] transition hover:bg-[#EFF6FF] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
        @click="clearSectionValues"
      >
        Make Treasurer Details N/A
      </button>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-2">
      <FormField
        v-for="field in section.fields"
        :key="field.name"
        :section-name="section.name"
        :field="field"
        :model-value="modelValue[field.name] ?? ''"
        :error="errors?.[field.name]"
        @update:model-value="(value) => updateField(field.name, value)"
      />
    </div>
  </section>
</template>
