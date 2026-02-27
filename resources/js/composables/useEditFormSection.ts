import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { toast } from '@/components/ui/sonner'

type SectionField = {
  name: string
  value: string | null
}

type SectionData = {
  name: string
  fields: SectionField[]
}

export const useEditFormSection = (section: SectionData, updateUrl: string) => {
  const isEditing = ref(false)
  const isSaving = ref(false)
  const editData = reactive<Record<string, string>>({})

  const hydrateFromSection = () => {
    section.fields.forEach((field) => {
      editData[field.name] = field.value ?? ''
    })
  }

  hydrateFromSection()

  const startEdit = () => {
    hydrateFromSection()
    isEditing.value = true
  }

  const cancelEdit = () => {
    hydrateFromSection()
    isEditing.value = false
  }

  const saveEdit = () => {
    if (isSaving.value) return

    isSaving.value = true

    router.patch(updateUrl, {
      section: section.name,
      fields: { ...editData },
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        isEditing.value = false
        toast.success('Form updated successfully.')
      },
      onError: () => {
        toast.error('Failed to update form. Please try again.')
      },
      onFinish: () => {
        isSaving.value = false
      },
    })
  }

  return {
    isEditing,
    isSaving,
    editData,
    startEdit,
    cancelEdit,
    saveEdit,
  }
}
