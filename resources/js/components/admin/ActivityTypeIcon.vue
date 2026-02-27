<script setup lang="ts">
import { Building2, FileUp, Trash2, UserPlus } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
  type: string
  class?: string
}>()

const icon = computed(() => {
  if (props.type === 'client.registration.submitted') return FileUp
  if (props.type === 'user.files.submitted') return FileUp
  if (props.type === 'admin.user.created') return UserPlus
  if (props.type === 'admin.user.deleted' || props.type === 'admin.registration.deleted') return Trash2
  if (props.type.includes('company_type')) return Building2
  return FileUp
})
</script>

<template>
  <component :is="icon" :class="props.class ?? 'h-4 w-4'" />
</template>
