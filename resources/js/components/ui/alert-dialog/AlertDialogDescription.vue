<script setup lang="ts">
import { DialogDescription } from '@/components/ui/dialog'
</script>

<template>
  <DialogDescription v-bind="$attrs" class="text-sm text-muted-foreground">
    <slot />
  </DialogDescription>
</template>
