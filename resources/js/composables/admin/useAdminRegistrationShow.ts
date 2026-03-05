import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { toast } from '@/components/ui/sonner'
import type { AdminRegistrationShowRecord, RegistrationStatus } from '@/types'

export const useAdminRegistrationShow = (registration: AdminRegistrationShowRecord) => {
  const statusForm = useForm({
    status: registration.status as RegistrationStatus,
  })
  const currentStatus = ref(registration.status as RegistrationStatus)
  const createUserForm = useForm({
    name: '',
    email: registration.email,
    password: '',
    password_confirmation: '',
  })
  const isCreateUserModalOpen = ref(false)

  const updateStatus = () => {
    statusForm.patch(`/admin/registration/${registration.id}/status`, {
      preserveScroll: true,
      onSuccess: () => {
        currentStatus.value = statusForm.status
        toast.success(`Successfully set the status to ${statusForm.status}.`)
      },
      onError: () => {
        statusForm.status = currentStatus.value
        toast.error('Unable to update status.')
      },
    })
  }

  const canCreateUser = computed(() => currentStatus.value === 'completed')

  const openCreateUserModal = () => {
    createUserForm.clearErrors()
    createUserForm.email = registration.email
    isCreateUserModalOpen.value = true
  }

  const submitCreateUser = () => {
    createUserForm.post('/admin/user', {
      preserveScroll: true,
      onSuccess: () => {
        createUserForm.reset()
        createUserForm.email = registration.email
        isCreateUserModalOpen.value = false
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

  const formatDateTime = (value: string | null) => {
    if (!value) return 'N/A'

    const utcDate = new Date(`${value}Z`)
    if (Number.isNaN(utcDate.getTime())) return 'N/A'

    return utcDate.toLocaleString('en-PH', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
    })
  }

  return {
    canCreateUser,
    createUserForm,
    currentStatus,
    isCreateUserModalOpen,
    statusForm,
    formatDateTime,
    openCreateUserModal,
    submitCreateUser,
    updateStatus,
  }
}
