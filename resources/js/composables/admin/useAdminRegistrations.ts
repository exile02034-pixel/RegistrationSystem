import { router } from '@inertiajs/vue3'
import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'

type Filters = {
  search: string
  sort: 'created_at'
  direction: 'asc' | 'desc'
  company_type: '' | 'opc' | 'sole_prop' | 'corp'
}

type UseAdminRegistrationsOptions = {
  filters: Filters
  getCurrentPage: () => number
}

export const useAdminRegistrations = (options: UseAdminRegistrationsOptions) => {
  const search = ref(options.filters.search ?? '')
  const sort = ref<'created_at'>(options.filters.sort ?? 'created_at')
  const direction = ref<'asc' | 'desc'>(options.filters.direction ?? 'desc')
  const companyTypeFilter = ref<Filters['company_type']>(options.filters.company_type ?? '')
  let debounceTimer: ReturnType<typeof setTimeout> | null = null

  const buildQuery = (overrides: Partial<{ page: number }> = {}) => {
    const query: Record<string, string | number> = {
      page: overrides.page ?? options.getCurrentPage(),
      search: search.value.trim(),
      sort: sort.value,
      direction: direction.value,
    }

    if (companyTypeFilter.value) {
      query.company_type = companyTypeFilter.value
    }

    if (!query.search) {
      delete query.search
    }

    return query
  }

  const reload = (page = 1) => {
    router.get('/admin/registration', buildQuery({ page }), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }

  watch(search, () => {
    if (debounceTimer) clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => reload(1), 300)
  })

  watch(companyTypeFilter, () => reload(1))

  const toggleSort = () => {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc'
    reload(1)
  }

  const sortIcon = computed(() => {
    if (sort.value !== 'created_at') return ChevronsUpDown
    return direction.value === 'asc' ? ChevronUp : ChevronDown
  })

  return {
    search,
    sort,
    direction,
    companyTypeFilter,
    reload,
    toggleSort,
    sortIcon,
  }
}
