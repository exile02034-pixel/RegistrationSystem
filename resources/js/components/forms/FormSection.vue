<script setup lang="ts">
import { Pencil } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { useEditFormSection } from '@/composables/useEditFormSection'

type FieldOption = {
  label: string
  value: string
}

type SectionField = {
  name: string
  label: string
  type?: string
  options?: FieldOption[]
  value: string | null
}

type SectionData = {
  name: string
  label: string
  fields: SectionField[]
}

const props = defineProps<{
  section: SectionData
  updateUrl: string
}>()

const { isEditing, isSaving, editData, startEdit, cancelEdit, saveEdit } = useEditFormSection(props.section, props.updateUrl)

const inputType = (type?: string) => {
  if (type === 'date') return 'date'
  if (type === 'number') return 'number'
  if (type === 'email') return 'email'

  return 'text'
}
</script>

<template>
  <div class="rounded-xl border border-[#E2E8F0] p-4 dark:border-[#1E3A5F]">
    <div class="flex items-center justify-between gap-2">
      <h3 class="font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ section.label }}</h3>
      <div class="flex items-center gap-2">
        <template v-if="!isEditing">
          <Tooltip>
            <TooltipTrigger as-child>
              <Button type="button" variant="outline" size="icon-sm" class="cursor-pointer" @click="startEdit">
                <Pencil class="h-4 w-4" />
              </Button>
            </TooltipTrigger>
            <TooltipContent>Edit Form</TooltipContent>
          </Tooltip>
        </template>
        <template v-else>
          <Button type="button" variant="outline" class="cursor-pointer" :disabled="isSaving" @click="cancelEdit">Cancel</Button>
          <Button type="button" class="cursor-pointer" :disabled="isSaving" @click="saveEdit">
            {{ isSaving ? 'Saving...' : 'Save' }}
          </Button>
        </template>
      </div>
    </div>

    <div class="mt-3 grid gap-2 md:grid-cols-2">
      <div
        v-for="field in section.fields"
        :key="`${section.name}-${field.name}`"
        class="rounded-md bg-[#F8FAFC] px-3 py-2 text-sm dark:bg-[#0F2747]"
      >
        <p class="mb-1 text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ field.label }}</p>

        <template v-if="isEditing">
          <textarea
            v-if="field.type === 'textarea'"
            v-model="editData[field.name]"
            rows="3"
            class="w-full rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-2 py-1 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]"
          />
          <select
            v-else-if="field.type === 'select'"
            v-model="editData[field.name]"
            class="h-9 w-full rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-2 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]"
          >
            <option value="">Select...</option>
            <option v-for="option in field.options ?? []" :key="`${field.name}-${option.value}`" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <Input
            v-else
            v-model="editData[field.name]"
            :type="inputType(field.type)"
            class="h-9 border-[#E2E8F0] bg-[#FFFFFF] text-sm text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]"
          />
        </template>
        <p v-else class="font-medium">{{ field.value || '—' }}</p>
      </div>
    </div>
  </div>
</template>
