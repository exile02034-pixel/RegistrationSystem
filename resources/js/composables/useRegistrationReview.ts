import { computed } from 'vue'
import type { FormSectionSchema } from '@/types'

type UseRegistrationReviewOptions = {
  form: {
    sections: Record<string, Record<string, string>>
  }
}

export const useRegistrationReview = (
  formSchema: FormSectionSchema[],
  options: UseRegistrationReviewOptions,
) => {
  const filledRegularCorporationIncorporatorIndexes = computed(() => {
    const values = options.form.sections.regular_corporation ?? {}
    const indexes = new Set<number>()

    Object.entries(values).forEach(([fieldName, rawValue]) => {
      const match = fieldName.match(/^incorporator_(\d+)_/)
      if (!match) {
        return
      }

      const value = String(rawValue ?? '').trim()
      if (value === '') {
        return
      }

      indexes.add(Number.parseInt(match[1], 10))
    })

    return indexes
  })

  const reviewFieldsForSection = (section: FormSectionSchema) => {
    if (section.name === 'opc_details') {
      return section.fields.filter((field) => {
        return field.name !== 'nominee_person_choice'
          && field.name !== 'alternate_nominee_person_choice'
      })
    }

    if (section.name !== 'regular_corporation') {
      return section.fields
    }

    return section.fields.filter((field) => {
      const match = field.name.match(/^incorporator_(\d+)_/)
      if (!match) {
        return true
      }

      const index = Number.parseInt(match[1], 10)

      return filledRegularCorporationIncorporatorIndexes.value.has(index)
    })
  }

  const isLastStep = (currentStep: number, stepCount: number) => currentStep === stepCount - 1

  return {
    isLastStep,
    reviewFieldsForSection,
  }
}
