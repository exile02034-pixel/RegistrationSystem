import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from '@/components/ui/sonner'
import { useRegistrationEmailForm } from '@/composables/admin/useRegistrationEmailForm'
import type { CompanyTypeOption, RegistrationLink } from '@/types'

export const useAdminRegistrationActions = (companyTypes: CompanyTypeOption[]) => {
  const isModalOpen = ref(false)
  const isDeleteModalOpen = ref(false)
  const deleting = ref(false)
  const selectedForDelete = ref<RegistrationLink | null>(null)

  const { form, submit } = useRegistrationEmailForm(companyTypes, {
    onSuccess: () => {
      isModalOpen.value = false
    },
  })

  const formatDate = (value: string | null) => {
    if (!value) return 'n/a'

    return new Date(value).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
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

  const statusClass = (status: string) => {
    if (status === 'completed') return 'text-emerald-600 dark:text-emerald-400'
    if (status === 'incomplete') return 'text-rose-600 dark:text-rose-400'
    if (status === 'pending') return 'text-amber-600 dark:text-amber-400'

    return 'text-[#0B1F3A] dark:text-[#E6F1FF]'
  }

  return {
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
  }
}
