<script setup lang="ts">
import { computed, ref, watch } from 'vue'
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

const MIN_REGULAR_CORP_ENTRIES = 2
const MAX_REGULAR_CORP_ENTRIES = 15
const OPC_NOMINEE_SELECTOR = 'nominee_person_choice'
const OPC_ALTERNATE_NOMINEE_SELECTOR = 'alternate_nominee_person_choice'

type OpcPerson = {
  first_name: string
  middle_name: string
  surname: string
  birthdate: string
  complete_address: string
  tin: string
  email_address: string
  contact_number: string
}

const OPC_PEOPLE: Record<string, OpcPerson> = {
  person_1: {
    first_name: 'Vince',
    middle_name: 'Anthony Paule',
    surname: 'Feir',
    birthdate: '1995-09-27',
    complete_address: '299 Purok 1 San Agustin Lubao Pampanga',
    tin: '7652411270000',
    email_address: 'vafeir@gmail.com',
    contact_number: '09271713690',
  },
  person_2: {
    first_name: 'Tristan',
    middle_name: 'Harvey Mallari',
    surname: 'Braceros',
    birthdate: '1992-12-18',
    complete_address: '278 Jose Abad Santos Guagua Pampanga',
    tin: '720772882000',
    email_address: 'engagement@thefimconsultant.ph',
    contact_number: '09682312875',
  },
}

const isRegularCorporationSection = computed(() => props.section.name === 'regular_corporation')

const parseIndexedField = (fieldName: string, prefix: 'incorporator'): number | null => {
  const match = fieldName.match(new RegExp(`^${prefix}_(\\d+)_`))

  if (!match) {
    return null
  }

  return Number.parseInt(match[1], 10)
}

const maxConfiguredIndex = (prefix: 'incorporator'): number => {
  return props.section.fields.reduce((max, field) => {
    const index = parseIndexedField(field.name, prefix)

    if (index === null) {
      return max
    }

    return Math.max(max, index)
  }, 0)
}

const maxIncorporatorCount = computed(() => Math.min(MAX_REGULAR_CORP_ENTRIES, Math.max(MIN_REGULAR_CORP_ENTRIES, maxConfiguredIndex('incorporator'))))

const highestFilledIndex = (prefix: 'incorporator'): number => {
  return props.section.fields.reduce((max, field) => {
    const index = parseIndexedField(field.name, prefix)

    if (index === null) {
      return max
    }

    const value = String(props.modelValue[field.name] ?? '').trim()

    if (value === '') {
      return max
    }

    return Math.max(max, index)
  }, 0)
}

const visibleIncorporatorCount = ref(MIN_REGULAR_CORP_ENTRIES)

const syncRegularCorporationVisibility = () => {
  if (!isRegularCorporationSection.value) {
    return
  }

  const incorporatorHighest = highestFilledIndex('incorporator')

  visibleIncorporatorCount.value = Math.min(
    maxIncorporatorCount.value,
    Math.max(MIN_REGULAR_CORP_ENTRIES, visibleIncorporatorCount.value, incorporatorHighest),
  )
}

watch(() => props.section.name, syncRegularCorporationVisibility, { immediate: true })

const regularCorporationStaticFields = computed(() => {
  if (!isRegularCorporationSection.value) {
    return props.section.fields
  }

  return props.section.fields.filter((field) => parseIndexedField(field.name, 'incorporator') === null)
})

const incorporatorGroups = computed(() => {
  if (!isRegularCorporationSection.value) {
    return []
  }

  const groups: Array<{ index: number; fields: FieldSchema[] }> = []

  for (let index = 1; index <= visibleIncorporatorCount.value; index += 1) {
    const fields = props.section.fields.filter((field) => parseIndexedField(field.name, 'incorporator') === index)

    if (fields.length > 0) {
      groups.push({ index, fields })
    }
  }

  return groups
})

const canAddIncorporator = computed(() => isRegularCorporationSection.value && visibleIncorporatorCount.value < maxIncorporatorCount.value)

const addIncorporator = () => {
  if (!canAddIncorporator.value) {
    return
  }

  visibleIncorporatorCount.value += 1
}

const applyOpcPersonSelection = (
  values: Record<string, string>,
  prefix: 'nominee' | 'alternate_nominee',
  selected: string,
) => {
  const person = OPC_PEOPLE[selected]
  const fields = [
    'first_name',
    'middle_name',
    'surname',
    'birthdate',
    'complete_address',
    'tin',
    'email_address',
    'contact_number',
  ] as const

  fields.forEach((field) => {
    values[`${prefix}_${field}`] = person?.[field] ?? ''
  })
}

const updateField = (fieldName: string, value: string) => {
  if (props.section.name === 'opc_details' && (fieldName === OPC_NOMINEE_SELECTOR || fieldName === OPC_ALTERNATE_NOMINEE_SELECTOR)) {
    const nextValues = {
      ...props.modelValue,
      [fieldName]: value,
    }

    if (fieldName === OPC_NOMINEE_SELECTOR) {
      applyOpcPersonSelection(nextValues, 'nominee', value)
    } else {
      applyOpcPersonSelection(nextValues, 'alternate_nominee', value)
    }

    emit('update:modelValue', nextValues)

    return
  }

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

    <div class="mt-4 grid gap-4 md:grid-cols-2" :class="{ 'md:grid-cols-3': isRegularCorporationSection }">
      <FormField
        v-for="field in regularCorporationStaticFields"
        :key="field.name"
        :section-name="section.name"
        :field="field"
        :model-value="modelValue[field.name] ?? ''"
        :error="errors?.[field.name]"
        @update:model-value="(value) => updateField(field.name, value)"
      />
    </div>

    <template v-if="isRegularCorporationSection">
      <div class="mt-6 space-y-4">
        <div class="flex items-center justify-between gap-3">
          <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Incorporators</h3>
          <button
            type="button"
            class="rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-3 py-1 text-xs font-medium text-[#0B1F3A] transition hover:bg-[#EFF6FF] disabled:cursor-not-allowed disabled:opacity-50 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]"
            :disabled="!canAddIncorporator"
            @click="addIncorporator"
          >
            Add Incorporator
          </button>
        </div>

        <div
          v-for="group in incorporatorGroups"
          :key="`incorporator-${group.index}`"
          class="space-y-3 rounded-xl border border-[#E2E8F0] p-4 dark:border-[#1E3A5F]"
        >
          <h4 class="text-sm font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Incorporator {{ group.index }}</h4>
          <div class="grid gap-4 md:grid-cols-2">
            <FormField
              v-for="field in group.fields"
              :key="field.name"
              :section-name="section.name"
              :field="field"
              :model-value="modelValue[field.name] ?? ''"
              :error="errors?.[field.name]"
              @update:model-value="(value) => updateField(field.name, value)"
            />
          </div>
        </div>
      </div>

    </template>
  </section>
</template>
