import { computed } from 'vue'

type ActivityLogItem = {
  id: number
  type: string
  description: string
  performed_by_name: string | null
  performed_by_email: string | null
  performed_by_role: string | null
  company_type: string | null
  files_count?: number | null
  filenames?: string[]
  file_types?: string[]
  metadata?: {
    section?: string
    section_label?: string
    updated_fields?: string[]
  }
  created_at: string | null
}

export const useActivityLogs = (logs: ActivityLogItem[]) => {
  const formatDateTime = (value: string | null) => {
    if (!value) return 'n/a'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return value

    return date.toLocaleString('en-US', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: 'numeric',
      minute: '2-digit',
    })
  }

  const relativeTime = (value: string | null) => {
    if (!value) return 'n/a'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return 'n/a'

    const seconds = Math.floor((Date.now() - date.getTime()) / 1000)
    if (seconds < 60) return 'just now'

    const minutes = Math.floor(seconds / 60)
    if (minutes < 60) return `${minutes} minute${minutes === 1 ? '' : 's'} ago`

    const hours = Math.floor(minutes / 60)
    if (hours < 24) return `${hours} hour${hours === 1 ? '' : 's'} ago`

    const days = Math.floor(hours / 24)
    return `${days} day${days === 1 ? '' : 's'} ago`
  }

  const roleLabel = (role: string | null) => {
    if (role === 'admin') return 'Admin'
    if (role === 'user') return 'User'
    if (role === 'client') return 'Client'
    return 'Unknown'
  }

  const companyTypeLabel = (type: string | null) => {
    if (!type) return null
    if (type === 'opc') return 'OPC'
    if (type === 'corp') return 'Regular Corporation'
    if (type === 'sole_prop') return 'Proprietorship'
    return type.replaceAll('_', ' ')
  }

  const dayLabel = (value: string | null) => {
    if (!value) return 'Unknown date'
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return 'Unknown date'

    const today = new Date()
    const yesterday = new Date()
    yesterday.setDate(today.getDate() - 1)

    const isSameDay = (a: Date, b: Date) =>
      a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate()

    if (isSameDay(date, today)) return 'Today'
    if (isSameDay(date, yesterday)) return 'Yesterday'

    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    })
  }

  const groupedByDay = computed(() => {
    const map = new Map<string, ActivityLogItem[]>()

    for (const log of logs) {
      const label = dayLabel(log.created_at)
      const current = map.get(label) ?? []
      current.push(log)
      map.set(label, current)
    }

    return Array.from(map.entries()).map(([label, items]) => ({
      label,
      items,
    }))
  })

  return {
    groupedByDay,
    formatDateTime,
    relativeTime,
    roleLabel,
    companyTypeLabel,
  }
}
