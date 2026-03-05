import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from '@/components/ui/sonner'
import type { AdminUserRow, CompanyTypeValue } from '@/types'

export const useAdminUserActions = () => {
  const isCreateModalOpen = ref(false)
  const isDeleteModalOpen = ref(false)
  const selectedUserForDelete = ref<AdminUserRow | null>(null)
  const deleting = ref(false)

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

    return new Date(value).toLocaleDateString('en-PH', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  }

  const shortCompanyType = (value: CompanyTypeValue) => {
    if (value === 'opc') return 'OPC'
    if (value === 'corp') return 'CORP'
    return 'SOLE PROP'
  }

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

  const openDeleteModal = (user: AdminUserRow) => {
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

  return {
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
  }
}
