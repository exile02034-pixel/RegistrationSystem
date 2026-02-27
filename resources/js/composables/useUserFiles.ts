import { router } from '@inertiajs/vue3'
import { computed, ref, type Ref } from 'vue'

export type UserFileItem = {
  id: number
  original_name: string
  size_bytes: number | null
  submitted_at: string | null
  company_type: string
  view_raw_url: string
  preview_pdf_url: string
  download_original_url: string
  download_pdf_url: string
  print_url: string
  can_convert_pdf: boolean
  is_pdf: boolean
}

export type UserFileGroup = {
  value: string
  label: string
  uploads: UserFileItem[]
}

type Filters = {
  sort: 'created_at'
  direction: 'asc' | 'desc'
}

type UseUserFilesOptions = {
  uploadGroups: Ref<UserFileGroup[]>
  batchPrintBaseUrl: string
  filters: Filters
}

export const useUserFiles = (options: UseUserFilesOptions) => {
  const direction = ref<'asc' | 'desc'>(options.filters.direction ?? 'desc')
  const selectedIds = ref<number[]>([])

  const allUploads = computed(() => options.uploadGroups.value.flatMap((group) => group.uploads))

  const setDirection = (nextDirection: 'asc' | 'desc') => {
    if (direction.value === nextDirection) return
    direction.value = nextDirection

    router.get(
      '/user/files',
      {
        sort: options.filters.sort ?? 'created_at',
        direction: direction.value,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      },
    )
  }

  const selectedUploads = computed(() => {
    return allUploads.value.filter((upload) => selectedIds.value.includes(upload.id))
  })

  const isGroupSelected = (uploads: UserFileItem[]) => {
    return uploads.length > 0 && uploads.every((upload) => selectedIds.value.includes(upload.id))
  }

  const toggleGroup = (checked: boolean, uploads: UserFileItem[]) => {
    const ids = uploads.map((upload) => upload.id)

    if (checked) {
      const next = new Set(selectedIds.value)
      ids.forEach((id) => next.add(id))
      selectedIds.value = Array.from(next)
      return
    }

    selectedIds.value = selectedIds.value.filter((id) => !ids.includes(id))
  }

  const toggleUpload = (id: number, checked: boolean) => {
    if (checked) {
      if (!selectedIds.value.includes(id)) {
        selectedIds.value = [...selectedIds.value, id]
      }

      return
    }

    selectedIds.value = selectedIds.value.filter((selectedId) => selectedId !== id)
  }

  const printSelected = () => {
    const printable = selectedUploads.value.filter((upload) => upload.can_convert_pdf || upload.is_pdf)
    const skipped = selectedUploads.value.length - printable.length

    if (printable.length > 0) {
      const ids = printable.map((upload) => upload.id).join(',')
      window.open(`${options.batchPrintBaseUrl}?ids=${encodeURIComponent(ids)}`, '_blank')
    }

    if (skipped > 0) {
      window.alert(`${skipped} file(s) were skipped because PDF printing is unavailable.`)
    }
  }

  return {
    direction,
    selectedIds,
    setDirection,
    isGroupSelected,
    toggleGroup,
    toggleUpload,
    printSelected,
  }
}
