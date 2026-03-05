export const useSubmissionTracking = (editUrl: string) => {
const parseTimestamp = (value: string) => {
  const trimmed = value.trim()
  const hasTimezone = /(?:Z|[+-]\d{2}:\d{2})$/i.test(trimmed)

  return new Date(hasTimezone ? trimmed : `${trimmed}Z`)
}

const formatDate = (date: string | null) => {
  if (!date) return 'N/A'
  const utcDate = parseTimestamp(date)
  if (Number.isNaN(utcDate.getTime())) return 'N/A'

  return utcDate.toLocaleString('en-PH', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })
}
  const statusClass = (status: string) => {
    if (status === 'pending') return 'bg-amber-100 text-amber-700'
    if (status === 'incomplete') return 'bg-rose-100 text-rose-700'
    if (status === 'completed') return 'bg-emerald-100 text-emerald-700'

    return 'bg-slate-100 text-slate-700'
  }

  const sectionEditUrl = (sectionName: string) => {
    const separator = editUrl.includes('?') ? '&' : '?'

    return `${editUrl}${separator}section=${encodeURIComponent(sectionName)}`
  }

  return {
    formatDate,
    statusClass,
    sectionEditUrl,
  }
}
