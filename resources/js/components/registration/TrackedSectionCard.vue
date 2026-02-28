<script setup lang="ts">
import { Pencil } from 'lucide-vue-next'

type SummaryField = {
  name: string
  label: string
  value: string | null
}

type SummarySection = {
  name: string
  label: string
  fields: SummaryField[]
}

const props = defineProps<{
  section: SummarySection
  canEdit: boolean
  editUrl: string
}>()
</script>

<template>
  <div class="rounded-xl border border-[#E2E8F0] p-4">
    <div class="flex items-center justify-between gap-2">
      <h3 class="font-semibold">{{ props.section.label }}</h3>
      <a
        v-if="props.canEdit"
        :href="props.editUrl"
        class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-[#E2E8F0] text-[#2563EB] transition hover:bg-[#EFF6FF]"
        :aria-label="`Edit ${props.section.label}`"
        :title="`Edit ${props.section.label}`"
      >
        <Pencil class="h-4 w-4" />
      </a>
    </div>

    <div class="mt-2 grid gap-2 md:grid-cols-2">
      <div
        v-for="field in props.section.fields"
        :key="`${props.section.name}-${field.name}`"
        class="rounded-md bg-[#F8FAFC] px-3 py-2"
      >
        <p class="text-xs text-[#64748B]">{{ field.label }}</p>
        <p class="font-medium">{{ field.value || '—' }}</p>
      </div>
    </div>
  </div>
</template>
