import { useForm } from '@inertiajs/vue3'
import { toast } from '@/components/ui/sonner'
import type { CompanyTypeOption } from '@/types'

type UseRegistrationEmailFormOptions = {
  onSuccess?: () => void
}

export const useRegistrationEmailForm = (
  companyTypes: CompanyTypeOption[],
  options: UseRegistrationEmailFormOptions = {},
) => {
  const form = useForm({
    email: '',
    company_type: companyTypes[0]?.value ?? 'corp',
  })

  const submit = () => {
    form.post('/admin/registration/send', {
      preserveScroll: true,
      onSuccess: () => {
        form.reset('email')
        options.onSuccess?.()
        toast.success('Registration email sent successfully.')
      },
      onError: () => {
        toast.error('Unable to send registration email.')
      },
    })
  }

  return {
    form,
    submit,
  }
}
