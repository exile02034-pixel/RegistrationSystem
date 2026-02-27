<script setup lang="ts">
import { computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

type Option = {
  label: string
  value: string
}

type FieldSchema = {
  name: string
  label: string
  type?: string
  required?: boolean
  options?: Option[]
}

const props = defineProps<{
  sectionName: string
  field: FieldSchema
  modelValue: string
  error?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const inputType = computed(() => {
  if (props.field.type === 'email') return 'email'
  if (props.field.type === 'date') return 'date'
  if (props.field.type === 'number') return 'number'

  return 'text'
})

const fieldId = computed(() => `${props.sectionName}-${props.field.name}`)
const isNumericField = computed(() => /(tin|mobile|phone|contact_number|_number$)/i.test(props.field.name) || props.field.type === 'number')

const onInputValueChange = (value: string) => {
  if (isNumericField.value) {
    emit('update:modelValue', value.replace(/\D+/g, ''))

    return
  }

  emit('update:modelValue', value)
}
</script>

<template>
  <div class="space-y-2">
    <Label :for="fieldId" class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">
      {{ field.label }}
      <span v-if="field.required" class="text-red-500">*</span>
    </Label>

    <select
      v-if="field.type === 'select'"
      :id="fieldId"
      :value="modelValue"
      class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
      @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option value="">Select an option</option>
      <option v-for="option in field.options ?? []" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>

    <textarea
      v-else-if="field.type === 'textarea'"
      :id="fieldId"
      :value="modelValue"
      class="min-h-28 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
      @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />

    <Input
      v-else
      :id="fieldId"
      :type="isNumericField ? 'text' : inputType"
      :inputmode="isNumericField ? 'numeric' : undefined"
      :pattern="isNumericField ? '[0-9]*' : undefined"
      :model-value="modelValue"
      @update:model-value="onInputValueChange(String($event ?? ''))"
    />

    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
  </div>
</template>
