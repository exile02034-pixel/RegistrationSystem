<script setup lang="ts">
import { Button } from '@/components/ui/button'

type Direction = 'asc' | 'desc'

defineProps<{
  direction: Direction
}>()

const emit = defineEmits<{
  change: [direction: Direction]
}>()
</script>

<template>
  <div class="flex items-center gap-2">
    <span class="text-xs text-[#475569] dark:text-[#9FB3C8]">Sort by date</span>
    <Button
      type="button"
      variant="outline"
      class="h-7 px-2 text-xs"
      :class="direction === 'desc' ? 'border-[#2563EB] text-[#2563EB] dark:border-[#60A5FA] dark:text-[#93C5FD]' : ''"
      @click="emit('change', 'desc')"
    >
      Newest
    </Button>
    <Button
      type="button"
      variant="outline"
      class="h-7 px-2 text-xs"
      :class="direction === 'asc' ? 'border-[#2563EB] text-[#2563EB] dark:border-[#60A5FA] dark:text-[#93C5FD]' : ''"
      @click="emit('change', 'asc')"
    >
      Oldest
    </Button>
  </div>
</template>
