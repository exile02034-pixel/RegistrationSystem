<script setup lang="ts">
const props = defineProps<{
  steps: Array<{ label: string }>
  currentStep: number
}>()
</script>

<template>
  <ol class="grid gap-2 sm:grid-cols-4">
    <li
      v-for="(step, index) in props.steps"
      :key="`${step.label}-${index}`"
      class="rounded-xl border px-3 py-2 text-xs transition"
      :class="[
        index === props.currentStep
          ? 'border-[#2563EB] bg-[#EFF6FF] text-[#1D4ED8] dark:border-[#60A5FA] dark:bg-[#0F2747] dark:text-[#BFDBFE]'
          : index < props.currentStep
            ? 'border-emerald-500/70 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/25 dark:text-emerald-300'
            : 'border-[#E2E8F0] bg-white text-[#475569] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#9FB3C8]',
      ]"
    >
      <span class="font-medium">Step {{ index + 1 }}</span>
      <p class="truncate">{{ step.label }}</p>
    </li>
  </ol>
</template>
