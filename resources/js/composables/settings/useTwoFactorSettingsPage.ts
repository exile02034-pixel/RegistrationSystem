import { onUnmounted, ref } from 'vue'
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth'
import { show } from '@/routes/two-factor'
import type { BreadcrumbItem } from '@/types'

export const useTwoFactorSettingsPage = () => {
  const breadcrumbs: BreadcrumbItem[] = [
    {
      title: 'Two-Factor Authentication',
      href: show.url(),
    },
  ]

  const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth()
  const showSetupModal = ref<boolean>(false)

  onUnmounted(() => {
    clearTwoFactorAuthData()
  })

  return {
    breadcrumbs,
    hasSetupData,
    showSetupModal,
  }
}
