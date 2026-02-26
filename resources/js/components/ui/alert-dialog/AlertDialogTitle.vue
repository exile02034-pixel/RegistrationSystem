<script setup lang="ts">
import { DialogTitle } from '@/components/ui/dialog'
</script>

<template>
  <DialogTitle v-bind="$attrs" class="text-lg font-semibold">
    <slot />
  </DialogTitle>
</template>
