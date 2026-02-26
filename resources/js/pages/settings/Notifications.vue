<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import Heading from '@/components/Heading.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { type BreadcrumbItem } from '@/types'
import { reactive, ref } from 'vue'

const props = defineProps<{
  preferences: {
    registration_submitted: boolean
    registration_link_sent: boolean
    client_created: boolean
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Notification settings',
    href: '/settings/notifications',
  },
]

const preferences = reactive({
  registration_submitted: props.preferences.registration_submitted,
  registration_link_sent: props.preferences.registration_link_sent,
  client_created: props.preferences.client_created,
})
const updatingKey = ref<null | 'registration_submitted' | 'registration_link_sent' | 'client_created'>(null)

const togglePreference = (
  key: 'registration_submitted' | 'registration_link_sent' | 'client_created',
  value: boolean,
) => {
  if (updatingKey.value !== null) {
    return
  }

  const previousValue = preferences[key]
  preferences[key] = value
  updatingKey.value = key

  router.patch(
    '/settings/notifications',
    { key, enabled: preferences[key] },
    {
      preserveScroll: true,
      preserveState: true,
      onError: () => {
        preferences[key] = previousValue
      },
      onFinish: () => {
        updatingKey.value = null
      },
    },
  )
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Notification settings" />

    <SettingsLayout>
      <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-6 dark:border-[#1E3A5F] dark:bg-[#0F2747]">
        <CardContent class="space-y-6 p-0">
          <Heading
            variant="small"
            title="Notification settings"
            description="Choose which notifications you want to receive."
          />

          <div class="space-y-3">
            <div class="flex items-start justify-between rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <div class="pr-4">
                <Label for="registration-submitted" class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">New registration submitted</Label>
                <p class="mt-1 text-xs text-[#475569] dark:text-[#9FB3C8]">Get notified when a client submits registration files.</p>
              </div>
              <button
                id="registration-submitted"
                type="button"
                :disabled="updatingKey !== null"
                :aria-pressed="preferences.registration_submitted"
                class="min-w-16 rounded-full border px-3 py-1 text-xs font-semibold transition"
                :class="preferences.registration_submitted
                  ? 'border-[#2563EB] bg-[#2563EB] text-white dark:border-[#60A5FA] dark:bg-[#60A5FA] dark:text-[#0B1F3A]'
                  : 'border-[#CBD5E1] bg-white text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]'"
                @click="togglePreference('registration_submitted', !preferences.registration_submitted)"
              >
                {{ preferences.registration_submitted ? 'ON' : 'OFF' }}
              </button>
            </div>

            <div class="flex items-start justify-between rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <div class="pr-4">
                <Label for="registration-link-sent" class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Registration link sent</Label>
                <p class="mt-1 text-xs text-[#475569] dark:text-[#9FB3C8]">Get notified when a registration link is sent to a client.</p>
              </div>
              <button
                id="registration-link-sent"
                type="button"
                :disabled="updatingKey !== null"
                :aria-pressed="preferences.registration_link_sent"
                class="min-w-16 rounded-full border px-3 py-1 text-xs font-semibold transition"
                :class="preferences.registration_link_sent
                  ? 'border-[#2563EB] bg-[#2563EB] text-white dark:border-[#60A5FA] dark:bg-[#60A5FA] dark:text-[#0B1F3A]'
                  : 'border-[#CBD5E1] bg-white text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]'"
                @click="togglePreference('registration_link_sent', !preferences.registration_link_sent)"
              >
                {{ preferences.registration_link_sent ? 'ON' : 'OFF' }}
              </button>
            </div>

            <div class="flex items-start justify-between rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-4 dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <div class="pr-4">
                <Label for="client-created" class="text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Client account created</Label>
                <p class="mt-1 text-xs text-[#475569] dark:text-[#9FB3C8]">Get notified when a new client user is created.</p>
              </div>
              <button
                id="client-created"
                type="button"
                :disabled="updatingKey !== null"
                :aria-pressed="preferences.client_created"
                class="min-w-16 rounded-full border px-3 py-1 text-xs font-semibold transition"
                :class="preferences.client_created
                  ? 'border-[#2563EB] bg-[#2563EB] text-white dark:border-[#60A5FA] dark:bg-[#60A5FA] dark:text-[#0B1F3A]'
                  : 'border-[#CBD5E1] bg-white text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]'"
                @click="togglePreference('client_created', !preferences.client_created)"
              >
                {{ preferences.client_created ? 'ON' : 'OFF' }}
              </button>
            </div>
          </div>
        </CardContent>
      </Card>
    </SettingsLayout>
  </AppLayout>
</template>
