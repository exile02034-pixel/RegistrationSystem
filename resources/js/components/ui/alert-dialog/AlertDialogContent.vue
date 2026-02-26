<script setup lang="ts">
import { DialogContent } from '@/components/ui/dialog'
</script>

<template>
  <DialogContent :show-close-button="false" v-bind="$attrs">
    <slot />
  </DialogContent>
</template>
