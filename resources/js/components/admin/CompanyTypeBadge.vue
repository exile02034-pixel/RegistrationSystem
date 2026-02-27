<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'

type CompanyTypeValue = 'opc' | 'sole_prop' | 'corp'

const props = defineProps<{
  value: CompanyTypeValue
  compact?: boolean
}>()

const label = computed(() => {
  if (props.compact) {
    if (props.value === 'opc') return 'OPC'
    if (props.value === 'corp') return 'CORP'
    return 'SOLE PROP'
  }

  if (props.value === 'opc') return 'OPC'
  if (props.value === 'corp') return 'Regular Corporation'
  return 'Proprietorship'
})
</script>

<template>
  <Badge>{{ label }}</Badge>
</template>
