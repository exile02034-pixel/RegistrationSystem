import { usePage } from '@inertiajs/vue3'
import { edit } from '@/routes/profile'
import type { BreadcrumbItem } from '@/types'

export const useProfileSettingsPage = () => {
  const breadcrumbItems: BreadcrumbItem[] = [
    {
      title: 'Profile settings',
      href: edit().url,
    },
  ]

  const page = usePage()
  const user = page.props.auth.user

  return {
    breadcrumbItems,
    user,
  }
}
