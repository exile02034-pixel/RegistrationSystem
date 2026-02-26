<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button'
import { cn } from '@/lib/utils'
</script>

<template>
  <button
    type="button"
    v-bind="$attrs"
    :class="cn(buttonVariants({ variant: 'destructive' }))"
  >
    <slot />
  </button>
</template>
