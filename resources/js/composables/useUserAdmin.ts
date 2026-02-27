// composables/useUserAdmin.ts
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import user from '@/routes/admin/user'
import { toast } from '@/components/ui/sonner'

export type FormData = {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export type FormErrors = {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export const useUserAdmin = () => {
  const form = reactive<FormData>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })

  const errors = reactive<FormErrors>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })

  const success = ref(false) // <-- success flag

  const submit = () => {
    // Reset errors and success
    (Object.keys(errors) as Array<keyof FormErrors>).forEach((key) => (errors[key] = ''))
    success.value = false

    router.post(user.store.url(), form, {
      onSuccess: () => {
        // Reset form
        (Object.keys(form) as Array<keyof FormData>).forEach((key) => (form[key] = ''))

        // Show success message
        success.value = true
        toast.success('User created successfully.')
      },
      onError: (e: Partial<FormErrors>) => {
        Object.assign(errors, e)
        toast.error('Unable to create user. Please check the form.')
      },
    })
  }

  return {
    form,
    errors,
    success,
    submit,
  }
}
