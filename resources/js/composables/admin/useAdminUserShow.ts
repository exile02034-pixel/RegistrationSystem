import { computed, ref } from 'vue'
import type { UserActivityItem } from '@/types'

export const useAdminUserShow = (activities: UserActivityItem[]) => {
  const activitiesPerPage = 5
  const activityPage = ref(1)

  const activityLastPage = computed(() => {
    return Math.max(1, Math.ceil(activities.length / activitiesPerPage))
  })

  const paginatedActivities = computed(() => {
    const currentPage = Math.min(activityPage.value, activityLastPage.value)
    const start = (currentPage - 1) * activitiesPerPage
    const end = start + activitiesPerPage

    return activities.slice(start, end)
  })

  const changeActivityPage = (page: number) => {
    activityPage.value = Math.max(1, Math.min(page, activityLastPage.value))
  }

  const formatDate = (value: string | null) => {
    if (!value) return 'n/a'
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return value

    return parsed.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    })
  }

  const formatDateTime = (value: string | null) => {
    if (!value) return 'n/a'
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return value

    return parsed.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  return {
    activityLastPage,
    activityPage,
    paginatedActivities,
    changeActivityPage,
    formatDate,
    formatDateTime,
  }
}
