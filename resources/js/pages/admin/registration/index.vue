<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Pagination } from '@/components/ui/pagination'
import { toast } from '@/components/ui/sonner'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { ChevronDown, ChevronUp, ChevronsUpDown, Eye, Trash2 } from 'lucide-vue-next'

type RegistrationLink = {
  id: number
  email: string
  company_type_label: string
  status: string
  uploads_count: number
  created_at: string | null
  client_url: string
  show_url: string
}

type CompanyType = {
  value: string
  label: string
}

type PaginatedLinks = {
  data: RegistrationLink[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

type Filters = {
  search: string
  sort: 'created_at'
  direction: 'asc' | 'desc'
  company_type: '' | 'opc' | 'sole_prop' | 'corp'
}

const props = defineProps<{
  links: PaginatedLinks
  companyTypes: CompanyType[]
  filters: Filters
}>()

const isModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const deleting = ref(false)
const selectedForDelete = ref<RegistrationLink | null>(null)

const search = ref(props.filters.search ?? '')
const sort = ref<'created_at'>(props.filters.sort ?? 'created_at')
const direction = ref<'asc' | 'desc'>(props.filters.direction ?? 'desc')
const companyTypeFilter = ref<Filters['company_type']>(props.filters.company_type ?? '')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

const form = useForm({
  email: '',
  company_type: props.companyTypes[0]?.value ?? 'corp',
})

const buildQuery = (overrides: Partial<{ page: number }> = {}) => {
  const query: Record<string, string | number> = {
    page: overrides.page ?? props.links.current_page,
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

const submit = () => {
  form.post('/admin/registration/send', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('email')
      isModalOpen.value = false
    },
  })
}

const openDeleteModal = (link: RegistrationLink) => {
  selectedForDelete.value = link
  isDeleteModalOpen.value = true
}

const confirmDelete = () => {
  if (!selectedForDelete.value) return

  router.delete(`/admin/registrations/${selectedForDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => {
      deleting.value = true
    },
    onSuccess: () => {
      toast.success('Deleted successfully.')
      isDeleteModalOpen.value = false
      selectedForDelete.value = null
    },
    onError: () => {
      toast.error('Unable to delete registration.')
    },
    onFinish: () => {
      deleting.value = false
    },
  })
}
</script>

<template>
  <AppLayout>
    <div class="space-y-8 p-6">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold">Client Registration</h1>
          <p class="text-gray-500">View all sent registration links and client submissions.</p>
        </div>
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
          <DialogTrigger as-child>
            <button class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
              Send Registration
            </button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-xl">
            <DialogHeader>
              <DialogTitle>Send Registration Email</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
              <p v-if="form.recentlySuccessful" class="rounded bg-green-100 p-2 text-sm text-green-700">
                Registration email sent successfully.
              </p>

              <div>
                <label class="mb-1 block text-sm font-medium">Client Email</label>
                <input v-model="form.email" type="email" class="w-full rounded border p-2" placeholder="client@example.com" />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
              </div>

              <div>
                <label class="mb-1 block text-sm font-medium">Company Type</label>
                <select v-model="form.company_type" class="w-full rounded border p-2">
                  <option v-for="type in companyTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <p v-if="form.errors.company_type" class="mt-1 text-sm text-red-600">{{ form.errors.company_type }}</p>
              </div>

              <div class="flex justify-end gap-2">
                <button type="button" class="rounded border px-4 py-2 text-sm hover:bg-gray-50" :disabled="form.processing" @click="isModalOpen = false">Cancel</button>
                <button type="button" :disabled="form.processing" @click="submit" class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700 disabled:opacity-50">
                  Send Registration Email
                </button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div class="space-y-3">
        <h2 class="text-xl font-semibold">Sent Registrations</h2>
        <div class="mb-3 flex flex-wrap items-center gap-2">
          <Input v-model="search" placeholder="Search email, token, or company type" class="max-w-md" />
          <select
            v-model="companyTypeFilter"
            class="h-8 rounded-md border bg-background px-2 text-xs"
          >
            <option value="">All Types</option>
            <option value="opc">OPC</option>
            <option value="sole_prop">SOLE PROP</option>
            <option value="corp">CORP</option>
          </select>
        </div>
        <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
              <tr>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Company Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Files Uploaded</th>
                <th class="px-4 py-3">
                  <button type="button" class="inline-flex items-center gap-1 cursor-pointer" @click="toggleSort">
                    Created
                    <component :is="sortIcon" class="h-4 w-4" />
                  </button>
                </th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="link in links.data" :key="link.id" class="border-t">
                <td class="px-4 py-3">{{ link.email }}</td>
                <td class="px-4 py-3">{{ link.company_type_label }}</td>
                <td class="px-4 py-3">{{ link.status }}</td>
                <td class="px-4 py-3">{{ link.uploads_count }}</td>
                <td class="px-4 py-3">{{ link.created_at }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button as="a" :href="link.show_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View">
                          <Eye />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>View</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button type="button" size="icon-sm" variant="destructive" class="cursor-pointer" aria-label="Delete Registration" @click="openDeleteModal(link)">
                          <Trash2 />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>Delete</TooltipContent>
                    </Tooltip>
                  </div>
                </td>
              </tr>
              <tr v-if="!links.data.length">
                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No registrations sent yet.</td>
              </tr>
            </tbody>
          </table>
          <div class="px-4 pb-4">
            <Pagination :current-page="links.current_page" :last-page="links.last_page" :total="links.total" @change="reload" />
          </div>
        </div>
      </div>
    </div>

    <AlertDialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Registration</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this registration? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deleting" @click="isDeleteModalOpen = false; selectedForDelete = null">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="deleting" @click="confirmDelete">Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
