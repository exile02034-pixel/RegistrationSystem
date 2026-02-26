<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'

const props = withDefaults(defineProps<{
  currentPage: number
  lastPage: number
  total?: number
  maxVisible?: number
}>(), {
  total: 0,
  maxVisible: 5,
})

const emit = defineEmits<{
  (e: 'change', page: number): void
}>()

const pageItems = computed<(number | '...')[]>(() => {
  const totalPages = props.lastPage
  const current = props.currentPage
  const max = Math.max(3, props.maxVisible)

  if (totalPages <= max + 2) {
    return Array.from({ length: totalPages }, (_, i) => i + 1)
  }

  const pages: (number | '...')[] = [1]
  const windowSize = max - 2
  let start = Math.max(2, current - Math.floor(windowSize / 2))
  let end = start + windowSize - 1

  if (end >= totalPages) {
    end = totalPages - 1
    start = end - windowSize + 1
  }

  if (start > 2) {
    pages.push('...')
  }

  for (let page = start; page <= end; page++) {
    pages.push(page)
  }

  if (end < totalPages - 1) {
    pages.push('...')
  }

  pages.push(totalPages)

  return pages
})

const changePage = (page: number) => {
  if (page < 1 || page > props.lastPage || page === props.currentPage) return
  emit('change', page)
}
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-2 pt-4 text-center">
    <p class="text-sm text-muted-foreground">
      <span v-if="total">Page {{ currentPage }} of {{ Math.max(lastPage, 1) }} · Total: {{ total }}</span>
      <span v-else>Page {{ currentPage }} of {{ Math.max(lastPage, 1) }}</span>
    </p>
    <div class="flex items-center gap-1">
      <Button
        type="button"
        variant="outline"
        size="sm"
        :disabled="currentPage <= 1 || lastPage <= 1"
        @click="changePage(currentPage - 1)"
      >
        Prev
      </Button>

      <template v-for="(item, index) in pageItems" :key="`${item}-${index}`">
        <Button
          v-if="item !== '...'"
          type="button"
          :variant="item === currentPage ? 'default' : 'outline'"
          size="sm"
          :disabled="lastPage <= 1"
          @click="changePage(item)"
        >
          {{ item }}
        </Button>
        <span v-else class="px-2 text-sm text-muted-foreground">...</span>
      </template>

      <Button
        type="button"
        variant="outline"
        size="sm"
        :disabled="currentPage >= lastPage || lastPage <= 1"
        @click="changePage(currentPage + 1)"
      >
        Next
      </Button>
    </div>
  </div>
</template>
