<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Notifications',
    href: '/notifications',
  },
]

type NotificationItem = {
  id: string
  title: string
  message: string
  time: string | null
  is_read: boolean
  open_url: string
  target_url: string
}

const props = defineProps<{
  items: NotificationItem[]
}>()

const selectedIds = ref<string[]>([])

watch(
  () => props.items.map((item) => item.id),
  (ids) => {
    selectedIds.value = selectedIds.value.filter((id) => ids.includes(id))
  },
)

const allSelected = computed({
  get: () => props.items.length > 0 && selectedIds.value.length === props.items.length,
  set: (checked: boolean) => {
    selectedIds.value = checked ? props.items.map((item) => item.id) : []
  },
})

const hasSelection = computed(() => selectedIds.value.length > 0)

const markAllRead = () => {
  router.post('/notifications/mark-all-read', {}, { preserveScroll: true })
}

const clearAll = () => {
  router.delete('/notifications', { preserveScroll: true })
}

const markAsRead = (id: string) => {
  router.post(`/notifications/${id}/mark-read`, {}, { preserveScroll: true })
}

const deleteNotification = (id: string) => {
  router.delete(`/notifications/${id}`, { preserveScroll: true })
}

const markSelectedRead = () => {
  if (!hasSelection.value) return
  router.post('/notifications/mark-selected-read', { ids: selectedIds.value }, { preserveScroll: true })
}

const deleteSelected = () => {
  if (!hasSelection.value) return
  router.post('/notifications/delete-selected', { ids: selectedIds.value }, { preserveScroll: true })
}

const toggleSelected = (id: string, checked: boolean) => {
  if (checked) {
    if (!selectedIds.value.includes(id)) {
      selectedIds.value.push(id)
    }
    return
  }

  selectedIds.value = selectedIds.value.filter((selectedId) => selectedId !== id)
}
</script>

<template>
  <Head title="Notifications" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
      </div>

      <div class="relative space-y-6">
        <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardHeader>
            <div class="flex flex-wrap items-center justify-between gap-2">
              <CardTitle class="font-['Space_Grotesk'] text-3xl font-semibold">Notifications</CardTitle>
              <div class="flex items-center gap-2">
                <Button type="button" variant="outline" size="sm" :disabled="!hasSelection" @click="markSelectedRead">Mark selected as read</Button>
                <Button type="button" variant="destructive" size="sm" :disabled="!hasSelection" @click="deleteSelected">Delete selected</Button>
                <Button type="button" variant="outline" size="sm" @click="markAllRead">Mark all as read</Button>
                <Button type="button" variant="destructive" size="sm" @click="clearAll">Delete all</Button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-[#475569] dark:text-[#9FB3C8]">
              Review your latest updates and actions in the system.
            </p>
          </CardContent>
        </Card>

        <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardContent class="space-y-3 p-4">
            <div class="flex items-center gap-2 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
              <input
                id="select-all-notifications"
                v-model="allSelected"
                type="checkbox"
                class="h-4 w-4 rounded border-[#CBD5E1] text-[#2563EB] focus:ring-[#2563EB]"
              />
              <label for="select-all-notifications" class="text-sm text-[#0B1F3A] dark:text-[#E6F1FF]">
                Select all notifications
              </label>
            </div>

            <div
              v-for="item in props.items"
              :key="item.id"
              class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3 dark:border-[#1E3A5F] dark:bg-[#0F2747]"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="pt-1">
                  <input
                    :id="`notification-${item.id}`"
                    :checked="selectedIds.includes(item.id)"
                    type="checkbox"
                    class="h-4 w-4 rounded border-[#CBD5E1] text-[#2563EB] focus:ring-[#2563EB]"
                    @change="toggleSelected(item.id, ($event.target as HTMLInputElement).checked)"
                  />
                </div>
                <Link :href="item.open_url" class="block min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold">{{ item.title }}</p>
                    <span class="text-xs text-[#64748B] dark:text-[#94A3B8]">{{ item.time }}</span>
                  </div>
                  <p class="mt-1 text-sm text-[#475569] dark:text-[#9FB3C8]">{{ item.message }}</p>
                  <p class="mt-1 text-xs text-[#2563EB]">Open related page</p>
                </Link>
                <div class="flex flex-col items-end gap-1">
                  <button
                    v-if="!item.is_read"
                    type="button"
                    class="text-xs text-[#2563EB] hover:underline"
                    @click="markAsRead(item.id)"
                  >
                    Mark read
                  </button>
                  <button
                    type="button"
                    class="text-xs text-red-600 hover:underline"
                    @click="deleteNotification(item.id)"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>

            <p v-if="!props.items.length" class="py-8 text-center text-sm text-[#475569] dark:text-[#9FB3C8]">
              No notifications yet.
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
