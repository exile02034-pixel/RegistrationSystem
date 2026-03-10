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
import { useAdminRegistrationActions } from '@/composables/admin/useAdminRegistrationActions'
import { useAdminRegistrations } from '@/composables/admin/useAdminRegistrations'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AdminRegistrationIndexPageProps } from '@/types'

const props = defineProps<AdminRegistrationIndexPageProps>()

const { search, sortIcon, companyTypeFilter, toggleSort, reload } = useAdminRegistrations({
  filters: props.filters,
  getCurrentPage: () => props.links.current_page,
})
const {
  deleting,
  form,
  isDeleteModalOpen,
  isModalOpen,
  selectedForDelete,
  confirmDelete,
  formatDate,
  openDeleteModal,
  statusClass,
  submit,
} = useAdminRegistrationActions(props.companyTypes)
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
            <h1 class="app-title">Client Registration</h1>
            <p class="app-subtitle">View all sent registration links and client submissions.</p>
          </div>
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
          <DialogTrigger as-child>
            <Button>
              Send Registration
            </Button>
          </DialogTrigger>
          <DialogContent class="app-dialog sm:max-w-xl">
            <DialogHeader>
              <DialogTitle>Send Registration Email</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
              <p v-if="form.recentlySuccessful" class="rounded-xl border border-emerald-300 bg-emerald-50 p-2 text-sm text-emerald-700 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-300">
                Registration email sent successfully.
              </p>

              <div class="space-y-2">
                <Label class="app-label">Client Email</Label>
                <Input v-model="form.email" type="email" class="app-input" placeholder="client@example.com" />
                <p v-if="form.errors.email" class="app-error">{{ form.errors.email }}</p>
              </div>

              <div class="space-y-2">
                <Label class="app-label">Company Type</Label>
                <select v-model="form.company_type" class="app-input">
                  <option v-for="type in companyTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <p v-if="form.errors.company_type" class="app-error">{{ form.errors.company_type }}</p>
              </div>

              <div class="flex justify-end gap-2">
                <Button type="button" variant="outline" :disabled="form.processing" @click="isModalOpen = false">Cancel</Button>
                <Button type="button" :disabled="form.processing" @click="submit">
                  Send Registration Email
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div class="space-y-3">
        <h2 class="app-section-title">Sent Registrations</h2>
        <div class="mb-3 flex flex-wrap items-center gap-2">
          <Input v-model="search" placeholder="Search email, token, or company type" class="max-w-md" />
          <select
            v-model="companyTypeFilter"
            class="app-input h-9 px-2 text-xs"
          >
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
                <th>Email</th>
                <th>Company Type</th>
                <th>Status</th>
                <th>Form Submission</th>
                <th>
                  <button type="button" class="inline-flex items-center gap-1 cursor-pointer" @click="toggleSort">
                    Created
                    <component :is="sortIcon" class="h-4 w-4" />
                  </button>
                </th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="link in links.data" :key="link.id" class="app-table-row">
                <td>{{ link.email }}</td>
                <td>{{ link.company_type_label }}</td>
                <td class="font-medium capitalize" :class="statusClass(link.status)">{{ link.status }}</td>
                <td>{{ link.form_submitted ? 'Submitted' : 'Pending' }}</td>
                <td>{{ formatDate(link.created_at) }}</td>
                <td>
                  <div class="flex items-center gap-3">
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <Button as="a" :href="link.show_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View">
                          <Eye />
                        </Button>
                      </TooltipTrigger>
                      <TooltipContent>View</TooltipContent>
                    </Tooltip>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button type="button" size="icon-sm" variant="ghost" class="cursor-pointer" aria-label="More Actions">
                          <MoreHorizontal />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-40">
                        <DropdownMenuItem class="text-destructive" @click="openDeleteModal(link)">
                          <Trash2 class="mr-2 h-4 w-4" />
                          Delete
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                </td>
              </tr>
              <tr v-if="!links.data.length">
                <td colspan="6" class="text-muted-foreground px-4 py-6 text-center">No registrations sent yet.</td>
              </tr>
            </tbody>
          </table>
          <div class="px-4 pb-4">
            <Pagination :current-page="links.current_page" :last-page="links.last_page" :total="links.total" @change="reload" />
          </div>
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
