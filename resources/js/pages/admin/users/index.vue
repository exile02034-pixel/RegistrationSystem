<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Badge } from '@/components/ui/badge'
import Button from '@/components/ui/button/Button.vue'
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Pagination } from '@/components/ui/pagination'
import { toast } from '@/components/ui/sonner'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import AppLayout from '@/layouts/AppLayout.vue'
import { ChevronDown, ChevronUp, ChevronsUpDown, Eye, Trash2 } from 'lucide-vue-next'

type UserRow = {
  id: number
  name: string
  email: string
  created_at: string | null
  company_types: Array<{ value: 'opc' | 'sole_prop' | 'corp'; label: string }>
  company_type_values: Array<'opc' | 'sole_prop' | 'corp'>
  uploads_count: number
  show_url: string
}

type PaginatedUsers = {
  data: UserRow[]
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
  users: PaginatedUsers
  filters: Filters
}>()

const isCreateModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedUserForDelete = ref<UserRow | null>(null)
const deleting = ref(false)

const search = ref(props.filters.search ?? '')
const companyTypeFilter = ref<Filters['company_type']>(props.filters.company_type ?? '')
const sort = ref<'created_at'>(props.filters.sort ?? 'created_at')
const direction = ref<'asc' | 'desc'>(props.filters.direction ?? 'desc')

let debounceTimer: ReturnType<typeof setTimeout> | null = null

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const initials = (name: string) =>
  name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()

const formatDate = (value: string | null) => {
  if (!value) return 'n/a'

  return new Date(value).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const shortCompanyType = (value: 'opc' | 'corp' | 'sole_prop') => {
  if (value === 'opc') return 'OPC'
  if (value === 'corp') return 'CORP'
  return 'SOLE PROP'
}

const buildQuery = (overrides: Partial<{ page: number }> = {}) => {
  const query: Record<string, string | number> = {
    page: overrides.page ?? props.users.current_page,
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
  router.get('/admin/user', buildQuery({ page }), {
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
  if (sort.value === 'created_at') {
    direction.value = direction.value === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value = 'created_at'
    direction.value = 'desc'
  }

  reload(1)
}

const sortIcon = computed(() => {
  if (sort.value !== 'created_at') return ChevronsUpDown
  return direction.value === 'asc' ? ChevronUp : ChevronDown
})

const submit = () => {
  form.post('/admin/user', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      isCreateModalOpen.value = false
      toast.success('User created successfully', {
        description: 'The new client account is ready to use.',
      })
    },
    onError: () => {
      toast.error('Unable to create user', {
        description: 'Please check the form fields and try again.',
      })
    },
  })
}

const openDeleteModal = (user: UserRow) => {
  selectedUserForDelete.value = user
  isDeleteModalOpen.value = true
}

const confirmDelete = () => {
  if (!selectedUserForDelete.value) return

  router.delete(`/admin/user/${selectedUserForDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => {
      deleting.value = true
    },
    onSuccess: () => {
      toast.success('Deleted successfully.')
      isDeleteModalOpen.value = false
      selectedUserForDelete.value = null
    },
    onError: () => {
      toast.error('Unable to delete user', {
        description: 'Please try again.',
      })
    },
    onFinish: () => {
      deleting.value = false
    },
  })
}
</script>

<template>
  <AppLayout>
    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="absolute inset-0 bg-[linear-gradient(rgba(120,140,170,0.14)_1px,transparent_1px),linear-gradient(90deg,rgba(120,140,170,0.14)_1px,transparent_1px)] bg-[size:34px_34px] opacity-40 dark:bg-[linear-gradient(rgba(160,180,200,0.08)_1px,transparent_1px),linear-gradient(90deg,rgba(160,180,200,0.08)_1px,transparent_1px)] dark:opacity-30" />
      </div>

      <div class="relative space-y-8">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Users / Clients</h1>
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Manage all users in the system.</p>
          </div>

          <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
            <DialogTrigger as-child>
              <button class="inline-flex h-10 items-center justify-center rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 text-sm font-medium text-white transition hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]">
                Create User
              </button>
            </DialogTrigger>

            <DialogContent class="sm:max-w-lg dark:border-[#1E3A5F] dark:bg-[#12325B]">
              <DialogHeader>
                <DialogTitle>Create User / Client</DialogTitle>
              </DialogHeader>

              <div class="space-y-4">
                <div class="space-y-2">
                  <Label>Name</Label>
                  <Input v-model="form.name" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                  <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Email</Label>
                  <Input v-model="form.email" type="email" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                  <p v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Password</Label>
                  <Input v-model="form.password" type="password" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                  <p v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Confirm Password</Label>
                  <Input v-model="form.password_confirmation" type="password" class="border-[#E2E8F0] bg-[#F8FAFC] dark:border-[#1E3A5F] dark:bg-[#0F2747]" />
                </div>
                <div class="flex justify-end gap-2">
                  <button type="button" class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 py-2 text-sm text-[#0B1F3A] transition hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]" @click="isCreateModalOpen = false">Cancel</button>
                  <button type="button" class="rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 py-2 text-sm text-white transition hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]" @click="submit">Create User</button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        <div class="space-y-3">
          <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Users</h2>
          <div class="mb-3 flex flex-wrap items-center gap-2">
            <Input v-model="search" placeholder="Search by name or email..." class="max-w-md border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]" />
            <select v-model="companyTypeFilter" class="h-9 rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-2 text-xs text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]">
              <option value="">All Types</option>
              <option value="opc">OPC</option>
              <option value="sole_prop">SOLE PROP</option>
              <option value="corp">CORP</option>
            </select>
          </div>

          <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
            <table class="min-w-full text-sm">
              <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
                <tr>
                  <th class="px-4 py-3">Client</th>
                  <th class="px-4 py-3">Company Types</th>
                  <th class="px-4 py-3">
                    <button type="button" class="inline-flex items-center gap-1 cursor-pointer" @click="toggleSort">
                      Created
                      <component :is="sortIcon" class="h-4 w-4" />
                    </button>
                  </th>
                  <th class="px-4 py-3">Files</th>
                  <th class="px-4 py-3">Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="user in users.data" :key="user.id" class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]">
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="h-9 w-9 flex items-center justify-center rounded-full bg-[#EFF6FF] text-[#2563EB] text-xs font-semibold dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                        {{ initials(user.name) }}
                      </div>
                      <div>
                        <p class="text-sm font-semibold">{{ user.name }}</p>
                        <p class="text-xs text-[#64748B] dark:text-[#9FB3C8]">{{ user.email }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-4 py-3 text-sm">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-for="type in user.company_types" :key="`${user.id}-${type.value}`">
                        {{ shortCompanyType(type.value) }}
                      </Badge>
                      <span v-if="!user.company_types.length">N/A</span>
                    </div>
                  </td>

                  <td class="px-4 py-3">{{ formatDate(user.created_at) }}</td>

                  <td class="px-4 py-3">
                    <Badge>{{ user.uploads_count > 0 ? `${user.uploads_count} file(s)` : 'No Files' }}</Badge>
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button as="a" :href="user.show_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View User Details">
                            <Eye />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>View</TooltipContent>
                      </Tooltip>

                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button size="icon-sm" variant="destructive" class="cursor-pointer" aria-label="Delete User" @click="openDeleteModal(user)">
                            <Trash2 />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Delete</TooltipContent>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
                <tr v-if="!users.data.length">
                  <td colspan="5" class="px-4 py-6 text-center text-[#64748B] dark:text-[#9FB3C8]">No users found.</td>
                </tr>
              </tbody>
            </table>

            <div class="px-4 pb-4">
              <Pagination
                :current-page="users.current_page"
                :last-page="users.last_page"
                :total="users.total"
                @change="reload"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <AlertDialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete User</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deleting" @click="isDeleteModalOpen = false; selectedUserForDelete = null">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="deleting" @click="confirmDelete">Delete</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
