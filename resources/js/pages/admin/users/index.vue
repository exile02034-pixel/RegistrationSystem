<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Badge } from '@/components/ui/badge'
import Button from '@/components/ui/button/Button.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
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
import { toast } from '@/components/ui/sonner'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import AppLayout from '@/layouts/AppLayout.vue'
import { Eye, Trash2, UserPlus } from 'lucide-vue-next'

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
    const matchesSearch =
      !query ||
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)

    const matchesCompanyType =
      companyTypeFilter.value === 'all' ||
      user.company_type_values.includes(companyTypeFilter.value)

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

</script>

<template>
  <AppLayout>
    <div class="relative min-h-[calc(100vh-7rem)] overflow-hidden rounded-2xl bg-[#F8FAFC] p-6 text-[#0B1F3A] dark:bg-[#0A192F] dark:text-[#E6F1FF]">
      <div class="relative space-y-6">

        <!-- Header -->
        <Card class="rounded-2xl border bg-white dark:bg-[#12325B]">
          <CardHeader class="flex flex-row items-center justify-between">
            <div>
              <CardTitle class="text-3xl font-semibold">Users / Clients</CardTitle>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                Manage all users in the system.
              </p>
            </div>

            <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
              <DialogTrigger as-child>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Button class="cursor-pointer bg-blue-600 text-white hover:bg-blue-700" size="icon-sm" aria-label="Create User">
                      <UserPlus />
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Create User</TooltipContent>
                </Tooltip>
              </DialogTrigger>

              <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                  <DialogTitle>Create User / Client</DialogTitle>
                </DialogHeader>

                <div class="space-y-4">
                  <div>
                    <Label>Name</Label>
                    <Input v-model="form.name" />
                    <p v-if="form.errors.name" class="text-red-500 text-sm">
                      {{ form.errors.name }}
                    </p>
                  </div>

                  <div>
                    <Label>Email</Label>
                    <Input v-model="form.email" type="email" />
                    <p v-if="form.errors.email" class="text-red-500 text-sm">
                      {{ form.errors.email }}
                    </p>
                  </div>

                  <div>
                    <Label>Password</Label>
                    <Input v-model="form.password" type="password" />
                    <p v-if="form.errors.password" class="text-red-500 text-sm">
                      {{ form.errors.password }}
                    </p>
                  </div>

                  <div>
                    <Label>Confirm Password</Label>
                    <Input v-model="form.password_confirmation" type="password" />
                  </div>

                  <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="isCreateModalOpen = false">
                      Cancel
                    </Button>
                    <Button class="bg-blue-600 text-white" @click="submit">
                      Create User
                    </Button>
                  </div>
                </div>
              </DialogContent>
            </Dialog>
          </CardHeader>
        </Card>

        <!-- Table -->
        <Card class="overflow-x-auto rounded-2xl border bg-white dark:bg-[#12325B]">
          <CardContent class="p-4">

            <table class="min-w-full divide-y">
              <thead>
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase">Client</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase">Company Types</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase">Created</th>
                  <th class="px-6 py-3 text-left text-xs font-medium uppercase">Files</th>
                  <th class="px-6 py-3 text-right text-xs font-medium uppercase">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y">
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="h-9 w-9 flex items-center justify-center rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                        {{ initials(user.name) }}
                      </div>
                      <div>
                        <p class="text-sm font-semibold">{{ user.name }}</p>
                        <p class="text-xs text-gray-500">{{ user.email }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-for="type in user.company_types" :key="`${user.id}-${type.value}`">
                        {{ shortCompanyType(type.value) }}
                      </Badge>
                      <span v-if="!user.company_types.length">N/A</span>
                    </div>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    {{ formatDate(user.created_at) }}
                  </td>

                  <td class="px-6 py-4">
                    <Badge>
                      {{ user.uploads_count > 0 ? `${user.uploads_count} file(s)` : 'No Files' }}
                    </Badge>
                  </td>

                  <td class="px-6 py-4 text-right space-x-2">
                    <div class="inline-flex items-center gap-2">
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button
                            as="a"
                            :href="user.show_url"
                            size="icon-sm"
                            variant="outline"
                            class="cursor-pointer"
                            aria-label="View User Details"
                          >
                            <Eye />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>View</TooltipContent>
                      </Tooltip>

                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button
                            size="icon-sm"
                            variant="destructive"
                            class="cursor-pointer"
                            aria-label="Delete User"
                            @click="openDeleteModal(user)"
                          >
                            <Trash2 />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Delete</TooltipContent>
                      </Tooltip>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

          </CardContent>
        </Card>
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
          <AlertDialogCancel :disabled="deleting" @click="isDeleteModalOpen = false; selectedUserForDelete = null">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction :disabled="deleting" @click="confirmDelete">
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
