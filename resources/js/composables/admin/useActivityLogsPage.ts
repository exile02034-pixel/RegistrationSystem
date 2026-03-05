import { router } from '@inertiajs/vue3'

export const useActivityLogsPage = () => {
  const reload = (page: number) => {
    router.get('/admin/activity-logs', { page }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }

  return {
    reload,
  }
}
