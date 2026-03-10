<script setup lang="ts">
import { Eye, MoreHorizontal, Trash2 } from 'lucide-vue-next'
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
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Pagination } from '@/components/ui/pagination'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { useAdminUserActions } from '@/composables/admin/useAdminUserActions'
import { useAdminUsers } from '@/composables/admin/useAdminUsers'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AdminUsersIndexPageProps } from '@/types'

const props = defineProps<AdminUsersIndexPageProps>()

const { search, companyTypeFilter, sortIcon, toggleSort, reload } = useAdminUsers({
  filters: props.filters,
  getCurrentPage: () => props.users.current_page,
})
const {
  deleting,
  form,
  isCreateModalOpen,
  isDeleteModalOpen,
  selectedUserForDelete,
  confirmDelete,
  formatDate,
  initials,
  openDeleteModal,
  shortCompanyType,
  submit,
} = useAdminUserActions()
</script>

<template>
  <AppLayout>
    <div class="app-page">
      <div class="app-page-bg">
        <div class="absolute -left-20 top-14 h-72 w-72 rounded-full bg-[#60A5FA]/35 blur-3xl dark:bg-[#2563EB]/20" />
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl dark:bg-[#3B82F6]/20" />
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-[#60A5FA]/20 blur-3xl dark:bg-[#2563EB]/15" />
        <div class="app-page-pattern" />
      </div>

      <div class="app-page-content">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h1 class="app-title">Users / Clients</h1>
            <p class="app-subtitle">Manage all users in the system.</p>
          </div>

          <Dialog :open="isCreateModalOpen" @update:open="isCreateModalOpen = $event">
            <DialogTrigger as-child>
              <Button>Create User</Button>
            </DialogTrigger>

            <DialogContent class="app-dialog sm:max-w-lg">
              <DialogHeader>
                <DialogTitle>Create User / Client</DialogTitle>
              </DialogHeader>

              <div class="space-y-4">
                <div class="space-y-2">
                  <Label class="app-label">Name</Label>
                  <Input v-model="form.name" class="app-input" />
                  <p v-if="form.errors.name" class="app-error">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="app-label">Email</Label>
                  <Input v-model="form.email" type="email" class="app-input" />
                  <p v-if="form.errors.email" class="app-error">{{ form.errors.email }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="app-label">Password</Label>
                  <Input v-model="form.password" type="password" class="app-input" />
                  <p v-if="form.errors.password" class="app-error">{{ form.errors.password }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="app-label">Confirm Password</Label>
                  <Input v-model="form.password_confirmation" type="password" class="app-input" />
                </div>
                <div class="flex justify-end gap-2">
                  <Button type="button" variant="outline" @click="isCreateModalOpen = false">Cancel</Button>
                  <Button type="button" @click="submit">Create User</Button>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>

        <div class="space-y-3">
          <h2 class="app-section-title">Users</h2>
          <div class="mb-3 flex flex-wrap items-center gap-2">
            <Input v-model="search" placeholder="Search by name or email..." class="max-w-md" />
            <select v-model="companyTypeFilter" class="app-input h-9 px-2 text-xs">
              <option value="">All Types</option>
              <option value="opc">OPC</option>
              <option value="sole_prop">SOLE PROP</option>
              <option value="corp">CORP</option>
            </select>
          </div>

          <div class="app-table-wrap overflow-x-auto bg-card shadow-sm">
            <table class="app-table">
              <thead class="app-table-head">
                <tr>
                  <th>Client</th>
                  <th>Company Types</th>
                  <th>
                    <button type="button" class="inline-flex items-center gap-1 cursor-pointer" @click="toggleSort">
                      Created
                      <component :is="sortIcon" class="h-4 w-4" />
                    </button>
                  </th>
                  <th>Form Submission</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="user in users.data" :key="user.id" class="app-table-row">
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="bg-primary/10 text-primary h-9 w-9 flex items-center justify-center rounded-full text-xs font-semibold">
                        {{ initials(user.name) }}
                      </div>
                      <div>
                        <p class="text-sm font-semibold">{{ user.name }}</p>
                        <p class="text-muted-foreground text-xs">{{ user.email }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="text-sm">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-for="type in user.company_types" :key="`${user.id}-${type.value}`">
                        {{ shortCompanyType(type.value) }}
                      </Badge>
                      <span v-if="!user.company_types.length">N/A</span>
                    </div>
                  </td>

                  <td>{{ formatDate(user.created_at) }}</td>

                  <td>
                    <Badge>{{ user.submissions_count > 0 ? `${user.submissions_count} submitted` : 'Not Submitted' }}</Badge>
                  </td>

                  <td>
                    <div class="flex items-center gap-3">
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button as="a" :href="user.show_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View User Details">
                            <Eye />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>View</TooltipContent>
                      </Tooltip>

                      <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                          <Button size="icon-sm" variant="ghost" class="cursor-pointer" aria-label="More Actions">
                            <MoreHorizontal />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-40">
                          <DropdownMenuItem class="text-destructive" @click="openDeleteModal(user)">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </div>
                  </td>
                </tr>
                <tr v-if="!users.data.length">
                  <td colspan="5" class="text-muted-foreground px-4 py-6 text-center">No users found.</td>
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
