import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { toast } from '@/components/ui/sonner'

type FormField = {
  name: string
  label: string
  type?: string
  required?: boolean
  options?: Array<{ label: string; value: string }>
}

type FormSection = {
  name: string
  label: string
  fields: FormField[]
}

type SectionErrorMap = Record<string, Record<string, string>>

export const useRegistrationForm = (
  formSchema: FormSection[],
  submitUrl: string,
  initialValues: Record<string, Record<string, string>> = {},
  initialSectionName: string | null = null,
) => {
  const schemaSections = formSchema.reduce<Record<string, Record<string, string>>>((carry, section) => {
    carry[section.name] = section.fields.reduce<Record<string, string>>((fieldCarry, field) => {
      fieldCarry[field.name] = ''

      return fieldCarry
    }, {})

    return carry
  }, {})

  const initialSections = Object.keys(schemaSections).reduce<Record<string, Record<string, string>>>((carry, sectionName) => {
    const base = schemaSections[sectionName]
    const provided = initialValues[sectionName] ?? {}

    carry[sectionName] = {
      ...base,
      ...provided,
    }

    return carry
  }, {})

  const form = useForm({
    sections: initialSections,
  })

  const initialStep = (() => {
    if (!initialSectionName) {
      return 0
    }

    const index = formSchema.findIndex((section) => section.name === initialSectionName)

    return index >= 0 ? index : 0
  })()

  const currentStep = ref(initialStep)
  const totalSteps = computed(() => formSchema.length + 1)
  const isReviewStep = computed(() => currentStep.value === formSchema.length)
  const clientErrors = ref<SectionErrorMap>({})

  const stepItems = computed(() => [
    ...formSchema.map((section) => ({ label: section.label })),
    { label: 'Review & Submit' },
  ])

  const currentSection = computed(() => formSchema[currentStep.value] ?? null)

  const valueFor = (sectionName: string, fieldName: string) => {
    return String(form.sections?.[sectionName]?.[fieldName] ?? '').trim()
  }

  const isNumericField = (field: FormField): boolean => {
    if (field.type === 'number') {
      return true
    }

    return /(mobile|phone|contact_number|_number$)/i.test(field.name)
  }

  const isTinField = (field: FormField): boolean => {
    return /tin/i.test(field.name)
  }

  const validateField = (sectionName: string, field: FormField): string | null => {
    const value = valueFor(sectionName, field.name)

    if (field.required && value === '') {
      return 'This field is required.'
    }

    if (value === '') {
      return null
    }

    if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      return 'Please enter a valid email address.'
    }

    if (field.type === 'date' && !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
      return 'Please enter a valid date.'
    }

    if (field.type === 'select' && (field.options ?? []).length > 0) {
      const allowed = new Set((field.options ?? []).map((option) => option.value))

      if (!allowed.has(value)) {
        return 'Please select a valid option.'
      }
    }

    if (isTinField(field)) {
      if (/^na$/i.test(value)) {
        return null
      }

      if (!/^\d+$/.test(value)) {
        return 'Numbers only or NA.'
      }

      if (value.length < 9 || value.length > 15) {
        return 'TIN must be 9 to 15 digits, or NA.'
      }

      return null
    }

    if (isNumericField(field) && !/^\d+$/.test(value)) {
      return 'Numbers only.'
    }

    return null
  }

  const validateSection = (section: FormSection): Record<string, string> => {
    return section.fields.reduce<Record<string, string>>((errors, field) => {
      const error = validateField(section.name, field)

      if (error !== null) {
        errors[field.name] = error
      }

      return errors
    }, {})
  }

  const canMoveNext = computed(() => {
    if (isReviewStep.value || currentSection.value === null) {
      return true
    }

    return Object.keys(validateSection(currentSection.value)).length === 0
  })

  const next = () => {
    if (currentStep.value >= totalSteps.value - 1 || currentSection.value === null) {
      return
    }

    const errors = validateSection(currentSection.value)
    clientErrors.value[currentSection.value.name] = errors

    if (Object.keys(errors).length > 0) {
      toast.error('Please complete all required fields correctly before proceeding.')

      return
    }

    currentStep.value += 1
  }

  const previous = () => {
    if (currentStep.value > 0) {
      currentStep.value -= 1
    }
  }

  const sectionErrors = (sectionName: string): Record<string, string> => {
    const errors: Record<string, string> = {
      ...(clientErrors.value[sectionName] ?? {}),
    }
    const serverErrors = form.errors as Record<string, string>

    Object.keys(serverErrors).forEach((key) => {
      const prefix = `sections.${sectionName}.`

      if (key.startsWith(prefix)) {
        errors[key.replace(prefix, '')] = serverErrors[key]
      }
    })

    return errors
  }

  const submit = () => {
    const nextClientErrors: SectionErrorMap = {}

    formSchema.forEach((section) => {
      const errors = validateSection(section)

      if (Object.keys(errors).length > 0) {
        nextClientErrors[section.name] = errors
      }
    })

    clientErrors.value = nextClientErrors

    const firstInvalidSectionName = Object.keys(nextClientErrors)[0]

    if (firstInvalidSectionName) {
      const stepIndex = formSchema.findIndex((section) => section.name === firstInvalidSectionName)

      if (stepIndex >= 0) {
        currentStep.value = stepIndex
      }

      toast.error('Please fix form validation errors before submitting.')

      return
    }

    form.post(submitUrl, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Registration submitted successfully.')
      },
      onError: (errors) => {
        toast.error('Unable to submit registration. Please check required fields.')

        const firstErrorKey = Object.keys(errors)[0]

        if (!firstErrorKey?.startsWith('sections.')) {
          return
        }

        const sectionName = firstErrorKey.split('.')[1]
        const stepIndex = formSchema.findIndex((section) => section.name === sectionName)

        if (stepIndex >= 0) {
          currentStep.value = stepIndex
        }
      },
    })
  }

  return {
    canMoveNext,
    currentSection,
    currentStep,
    form,
    isReviewStep,
    next,
    previous,
    sectionErrors,
    stepItems,
    submit,
    totalSteps,
  }
}
