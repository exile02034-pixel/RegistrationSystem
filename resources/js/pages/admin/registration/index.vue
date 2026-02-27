<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
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
import { ChevronDown, ChevronUp, ChevronsUpDown, Eye, Loader2, Mail, MoreHorizontal, Trash2 } from 'lucide-vue-next'

type RegistrationLink = {
  id: number
  email: string
  company_type_label: string
  status: string
  uploads_count: number
  missing_documents_count: number
  has_missing_documents: boolean
  follow_up_url: string
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
const isFollowUpModalOpen = ref(false)
const deleting = ref(false)
const followUpSendingId = ref<number | null>(null)
const selectedForDelete = ref<RegistrationLink | null>(null)
const selectedForFollowUp = ref<RegistrationLink | null>(null)

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

const formatDate = (value: string | null) => {
  if (!value) return 'n/a'

  return new Date(value).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const submit = () => {
  form.post('/admin/registration/send', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('email')
      isModalOpen.value = false
      toast.success('Registration email sent successfully.')
    },
    onError: () => {
      toast.error('Unable to send registration email.')
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

const openFollowUpModal = (link: RegistrationLink) => {
  if (!link.has_missing_documents) {
    toast.info('No missing documents for this registration.')
    return
  }

  selectedForFollowUp.value = link
  isFollowUpModalOpen.value = true
}

const sendMissingDocsFollowUp = () => {
  if (!selectedForFollowUp.value) return

  const link = selectedForFollowUp.value
  followUpSendingId.value = link.id

  router.post(link.follow_up_url, {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Follow-up email sent successfully.')
      isFollowUpModalOpen.value = false
      selectedForFollowUp.value = null
    },
    onError: () => {
      toast.error('Unable to send follow-up email.')
    },
    onFinish: () => {
      followUpSendingId.value = null
    },
  })
}

const statusBadgeClass = (status: string) => {
  if (status === 'completed') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200'
  if (status === 'incomplete') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200'
  return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200'
}

const statusLabel = (status: string) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
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
            <h1 class="font-['Space_Grotesk'] text-3xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Client Registration</h1>
            <p class="font-['Public_Sans'] text-sm text-[#475569] dark:text-[#9FB3C8]">View all sent registration links and client submissions.</p>
          </div>
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
          <DialogTrigger as-child>
            <button class="inline-flex h-10 items-center justify-center rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 text-sm font-medium text-white transition hover:bg-[#1D4ED8] dark:hover:bg-[#3B82F6]">
              Send Registration
            </button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-xl dark:border-[#1E3A5F] dark:bg-[#12325B]">
            <DialogHeader>
              <DialogTitle>Send Registration Email</DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
              <p v-if="form.recentlySuccessful" class="rounded-xl border border-emerald-300 bg-emerald-50 p-2 text-sm text-emerald-700 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-300">
                Registration email sent successfully.
              </p>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Client Email</label>
                <input v-model="form.email" type="email" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]" placeholder="client@example.com" />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
              </div>

              <div class="space-y-2">
                <label class="block text-sm font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">Company Type</label>
                <select v-model="form.company_type" class="h-11 w-full rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#0B1F3A] outline-none ring-[#60A5FA] transition focus:ring-2 dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF]">
                  <option v-for="type in companyTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                </select>
                <p v-if="form.errors.company_type" class="mt-1 text-sm text-red-600">{{ form.errors.company_type }}</p>
              </div>

              <div class="flex justify-end gap-2">
                <button type="button" class="rounded-xl border border-[#E2E8F0] bg-[#FFFFFF] px-4 py-2 text-sm text-[#0B1F3A] transition hover:bg-[#EFF6FF] hover:text-[#1D4ED8] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#E6F1FF] dark:hover:bg-[#12325B]" :disabled="form.processing" @click="isModalOpen = false">Cancel</button>
                <button type="button" :disabled="form.processing" @click="submit" class="rounded-xl border border-[#2563EB] bg-[#2563EB] px-4 py-2 text-sm text-white transition hover:bg-[#1D4ED8] disabled:opacity-50 dark:hover:bg-[#3B82F6]">
                  Send Registration Email
                </button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      <div class="space-y-3">
        <h2 class="font-['Space_Grotesk'] text-xl font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Sent Registrations</h2>
        <div class="mb-3 flex flex-wrap items-center gap-2">
          <Input v-model="search" placeholder="Search email, token, or company type" class="max-w-md border-[#E2E8F0] bg-[#FFFFFF] dark:border-[#1E3A5F] dark:bg-[#12325B]" />
          <select
            v-model="companyTypeFilter"
            class="h-9 rounded-md border border-[#E2E8F0] bg-[#FFFFFF] px-2 text-xs text-[#0B1F3A] dark:border-[#1E3A5F] dark:bg-[#12325B] dark:text-[#E6F1FF]"
          >
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
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Company Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Files Uploaded</th>
                <th class="px-4 py-3">Missing</th>
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
              <tr v-for="link in links.data" :key="link.id" class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]">
                <td class="px-4 py-3">{{ link.email }}</td>
                <td class="px-4 py-3">{{ link.company_type_label }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                    :class="statusBadgeClass(link.status)"
                  >
                    {{ statusLabel(link.status) }}
                  </span>
                </td>
                <td class="px-4 py-3">{{ link.uploads_count }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                    :class="link.has_missing_documents ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200'"
                  >
                    {{ link.missing_documents_count }}
                  </span>
                </td>
                <td class="px-4 py-3">{{ formatDate(link.created_at) }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <Button as="a" :href="link.show_url" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="View Registration">
                      <Eye class="h-4 w-4" />
                    </Button>
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button type="button" size="icon-sm" variant="outline" class="cursor-pointer" aria-label="More actions">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-56">
                        <DropdownMenuItem
                          :disabled="followUpSendingId === link.id || !link.has_missing_documents"
                          @click="openFollowUpModal(link)"
                        >
                          <Loader2 v-if="followUpSendingId === link.id" class="mr-2 h-4 w-4 animate-spin" />
                          <Mail v-else class="mr-2 h-4 w-4" />
                          {{ link.has_missing_documents ? 'Send Missing Docs Follow-up' : 'No missing documents' }}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                          class="text-red-600 focus:text-red-600 dark:text-red-400 dark:focus:text-red-400"
                          @click="openDeleteModal(link)"
                        >
                          <Trash2 class="mr-2 h-4 w-4" />
                          Delete Registration
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                </td>
              </tr>
              <tr v-if="!links.data.length">
                <td colspan="8" class="px-4 py-6 text-center text-[#64748B] dark:text-[#9FB3C8]">No registrations sent yet.</td>
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

    <AlertDialog :open="isFollowUpModalOpen" @update:open="isFollowUpModalOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Send Missing Docs Follow-up</AlertDialogTitle>
          <AlertDialogDescription>
            This will send a follow-up email to
            <strong>{{ selectedForFollowUp?.email }}</strong>
            with the missing document request.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel
            :disabled="selectedForFollowUp !== null && followUpSendingId === selectedForFollowUp.id"
            @click="isFollowUpModalOpen = false; selectedForFollowUp = null"
          >
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction
            :disabled="selectedForFollowUp !== null && followUpSendingId === selectedForFollowUp.id"
            @click="sendMissingDocsFollowUp"
          >
            <Loader2
              v-if="selectedForFollowUp !== null && followUpSendingId === selectedForFollowUp.id"
              class="mr-2 h-4 w-4 animate-spin"
            />
            Send Follow-up
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
