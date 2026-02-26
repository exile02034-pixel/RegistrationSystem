<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { toast } from '@/components/ui/sonner'
import AppLayout from '@/layouts/AppLayout.vue'

type UserRow = {
  id: number
  name: string
  email: string
  created_at: string | null
  company_type: 'opc' | 'sole_prop' | 'corp' | null
  registration_show_url: string | null
}

const props = defineProps<{
  users: UserRow[]
}>()

const isCreateModalOpen = ref(false)
const isDeleteModalOpen = ref(false)
const selectedUserForDelete = ref<UserRow | null>(null)
const deleting = ref(false)
const search = ref('')
const companyTypeFilter = ref<'all' | 'opc' | 'sole_prop' | 'corp'>('all')

const totalUsers = computed(() => props.users.length)

const filteredUsers = computed(() => {
  const query = search.value.trim().toLowerCase()
  return props.users.filter((user) => {
    const matchesSearch = !query
      || user.name.toLowerCase().includes(query)
      || user.email.toLowerCase().includes(query)

    const matchesCompanyType = companyTypeFilter.value === 'all'
      || user.company_type === companyTypeFilter.value

    return matchesSearch && matchesCompanyType
  })
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
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
  if (!selectedUserForDelete.value) {
    return
  }

  router.delete(`/admin/user/${selectedUserForDelete.value.id}`, {
    preserveScroll: true,
    onStart: () => {
      deleting.value = true
    },
    onSuccess: () => {
      toast.success('User deleted successfully', {
        description: 'The client account has been removed.',
      })
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

const initials = (name: string) => {
  return name
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

const formatDate = (value: string | null) => {
  if (!value) {
    return 'n/a'
  }

  return new Date(value).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const companyTypeLabel = (type: UserRow['company_type']) => {
  if (type === 'opc') return 'OPC'
  if (type === 'sole_prop') return 'SOLE PROP'
  if (type === 'corp') return 'REGULAR CORP'
  return 'N/A'
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

      <div class="relative space-y-6">
        <Card class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardHeader class="flex flex-row items-center justify-between gap-4">
            <div>
              <CardTitle class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Users / Clients</CardTitle>
              <p class="mt-1 font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">Manage all users in the system.</p>
            </div>

            <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
              <DialogTrigger as-child>
                <Button class="rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                  + Create User
                </Button>
              </DialogTrigger>
              <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                  <DialogTitle>Create User / Client</DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                  <div>
                    <Label for="new-user-name">Name</Label>
                    <Input id="new-user-name" v-model="form.name" type="text" placeholder="John Doe" required />
                    <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                  </div>

                  <div>
                    <Label for="new-user-email">Email</Label>
                    <Input id="new-user-email" v-model="form.email" type="email" placeholder="john@example.com" required />
                    <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                  </div>

                  <div>
                    <Label for="new-user-password">Password</Label>
                    <Input id="new-user-password" v-model="form.password" type="password" placeholder="********" required />
                    <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
                  </div>

                  <div>
                    <Label for="new-user-password-confirm">Confirm Password</Label>
                    <Input id="new-user-password-confirm" v-model="form.password_confirmation" type="password" placeholder="********" required />
                    <p v-if="form.errors.password_confirmation" class="text-sm text-red-500">{{ form.errors.password_confirmation }}</p>
                  </div>

                  <div class="flex justify-end gap-2 pt-2">
                    <Button
                      type="button"
                      variant="outline"
                      :disabled="form.processing"
                      @click="isCreateModalOpen = false"
                    >
                      Cancel
                    </Button>
                    <Button
                      type="button"
                      class="bg-blue-600 text-white hover:bg-blue-700"
                      :disabled="form.processing"
                      @click="submit"
                    >
                      Create User
                    </Button>
                  </div>
                </div>
              </DialogContent>
            </Dialog>
          </CardHeader>
        </Card>

        <Card class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
          <CardContent class="space-y-4 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <p class="text-sm text-[#475569] dark:text-[#9FB3C8]">
                Showing <strong>{{ filteredUsers.length }}</strong> of {{ totalUsers }} clients
              </p>
              <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                <select
                  v-model="companyTypeFilter"
                  class="h-9 rounded-md border border-[#E2E8F0] bg-white px-3 text-sm dark:border-[#1E3A5F] dark:bg-[#12325B]"
                >
                  <option value="all">All Company Types</option>
                  <option value="opc">OPC</option>
                  <option value="sole_prop">SOLE PROP</option>
                  <option value="corp">REGULAR CORP</option>
                </select>
                <Input
                  v-model="search"
                  type="text"
                  placeholder="Search by name or email..."
                  class="w-full sm:w-80"
                />
              </div>
            </div>

            <table class="min-w-full divide-y divide-[#E2E8F0] rounded-xl border dark:divide-[#1E3A5F] dark:border-[#1E3A5F]">
              <thead class="bg-[#EFF6FF] dark:bg-[#0F2747]">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#475569] dark:text-[#9FB3C8]">Client</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#475569] dark:text-[#9FB3C8]">Company Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#475569] dark:text-[#9FB3C8]">Created</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#475569] dark:text-[#9FB3C8]">Files</th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[#475569] dark:text-[#9FB3C8]">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#E2E8F0] bg-[#FFFFFF] dark:divide-[#1E3A5F] dark:bg-[#12325B]">
                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-[#F8FAFC] dark:hover:bg-[#0F2747]">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#DBEAFE] text-xs font-semibold text-[#1D4ED8] dark:bg-[#1E3A5F] dark:text-[#E6F1FF]">
                        {{ initials(user.name) }}
                      </div>
                      <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">{{ user.name }}</p>
                        <p class="truncate text-xs text-[#475569] dark:text-[#9FB3C8]">{{ user.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-[#0B1F3A] dark:text-[#E6F1FF]">
                    {{ companyTypeLabel(user.company_type) }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-sm text-[#0B1F3A] dark:text-[#E6F1FF]">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="whitespace-nowrap px-6 py-4">
                    <Badge
                      :class="user.registration_show_url ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'"
                    >
                      {{ user.registration_show_url ? 'Available' : 'No Files' }}
                    </Badge>
                  </td>
                  <td class="whitespace-nowrap px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <Button
                        v-if="user.registration_show_url"
                        as="a"
                        :href="user.registration_show_url"
                        size="sm"
                      >
                        View Files
                      </Button>
                      <Button v-else size="sm" class="cursor-not-allowed opacity-50" :disabled="true">
                        No Files
                      </Button>
                      <Button
                        size="sm"
                        variant="destructive"
                        @click="openDeleteModal(user)"
                      >
                        Delete
                      </Button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!filteredUsers.length">
                  <td colspan="5" class="px-6 py-8 text-center text-sm text-[#475569] dark:text-[#9FB3C8]">
                    No matching users found.
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>

        <Dialog :open="isDeleteModalOpen" @update:open="isDeleteModalOpen = $event">
          <DialogContent class="sm:max-w-md">
            <DialogHeader>
              <DialogTitle>Delete User</DialogTitle>
            </DialogHeader>
            <p class="text-sm text-gray-600">
              Are you sure you want to delete
              <strong>{{ selectedUserForDelete?.name }}</strong>?
              This action cannot be undone.
            </p>

            <div class="mt-4 flex justify-end gap-2">
              <Button
                type="button"
                variant="outline"
                :disabled="deleting"
                @click="isDeleteModalOpen = false"
              >
                Cancel
              </Button>
              <Button
                type="button"
                class="bg-red-600 text-white hover:bg-red-700"
                :disabled="deleting"
                @click="confirmDelete"
              >
                Yes, Delete
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AppLayout>
</template>
