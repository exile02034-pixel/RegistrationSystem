<script setup lang="ts">
import { buttonVariants } from '@/components/ui/button'
import { DialogClose } from '@/components/ui/dialog'
import { cn } from '@/lib/utils'
</script>

<template>
  <DialogClose as-child>
    <button
      type="button"
      v-bind="$attrs"
      :class="cn(buttonVariants({ variant: 'outline' }))"
    >
      <slot />
    </button>
  </DialogClose>
</template>
