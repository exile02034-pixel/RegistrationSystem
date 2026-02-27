import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from '@/components/ui/sonner'

export const useDelete = () => {
  const isOpen = ref(false)
  const deleting = ref(false)
  const targetUrl = ref<string | null>(null)

  const promptDelete = (url: string) => {
    targetUrl.value = url
    isOpen.value = true
  }

  const reset = () => {
    isOpen.value = false
    deleting.value = false
    targetUrl.value = null
  }

  const confirmDelete = () => {
    if (!targetUrl.value || deleting.value) return

    deleting.value = true

    router.delete(targetUrl.value, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Form deleted successfully.')
        reset()
      },
      onError: () => {
        toast.error('Failed to delete form. Please try again.')
        deleting.value = false
      },
    })
  }

  return {
    isOpen,
    deleting,
    promptDelete,
    confirmDelete,
    reset,
  }
}
