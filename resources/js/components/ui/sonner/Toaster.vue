<script setup lang="ts">
import { toastStore, dismiss } from './toast'

const variantClass = (variant: 'default' | 'success' | 'error') => {
  if (variant === 'success') {
    return 'border-emerald-200 bg-emerald-50 text-emerald-900'
  }

  if (variant === 'error') {
    return 'border-red-200 bg-red-50 text-red-900'
  }

  return 'border-slate-200 bg-white text-slate-900'
}
</script>

<template>
  <Teleport to="body">
    <div class="pointer-events-none fixed right-4 top-4 z-[100] flex w-full max-w-sm flex-col gap-2">
      <TransitionGroup name="toast">
        <div
          v-for="item in toastStore"
          :key="item.id"
          class="pointer-events-auto rounded-lg border p-3 shadow-md"
          :class="variantClass(item.variant)"
        >
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="text-sm font-semibold">{{ item.title }}</p>
              <p v-if="item.description" class="mt-1 text-xs opacity-90">{{ item.description }}</p>
            </div>
            <button
              type="button"
              class="text-xs opacity-80 hover:opacity-100"
              @click="dismiss(item.id)"
            >
              Close
            </button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.2s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
