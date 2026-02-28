import { computed, ref } from 'vue'

export type PdfContext = 'admin' | 'user'
export type CompanyType = 'opc' | 'sole_prop' | 'corp'

type SubmittedField = {
  value: string | null
}

type SubmittedSection = {
  name: string
  fields: SubmittedField[]
}

type FormSubmission = {
  id: string
  sections: SubmittedSection[]
}

export type PdfListRow = {
  section: string
  name: string
  status: 'generated' | 'pending'
  canOpen: boolean
}

const SECTION_LABELS: Record<string, string> = {
  client_information: 'Client Information Form',
  treasurer_details: 'Treasurer Details',
  opc_details: 'OPC Details',
  proprietorship: 'Proprietorship Details',
  regular_corporation: 'Regular Corporation Details',
}

const COMPANY_SECTION: Record<CompanyType, string> = {
  opc: 'opc_details',
  sole_prop: 'proprietorship',
  corp: 'regular_corporation',
}

export const useFormPdf = (
  submission: FormSubmission | null,
  companyType: CompanyType,
  context: PdfContext,
) => {
  const selectedSections = ref<string[]>([])

  const baseSections = computed(() => {
    const companySection = COMPANY_SECTION[companyType]

    return ['client_information', 'treasurer_details', companySection]
  })

  const sectionHasData = (section: string) => {
    const entry = submission?.sections.find((item) => item.name === section)
    if (!entry) return false

    return entry.fields.some((field) => typeof field.value === 'string' && field.value.trim() !== '')
  }

  const rows = computed<PdfListRow[]>(() => {
    return baseSections.value.map((section) => {
      const generated = Boolean(submission?.id) && sectionHasData(section)

      return {
        section,
        name: SECTION_LABELS[section] ?? section,
        status: generated ? 'generated' : 'pending',
        canOpen: generated,
      }
    })
  })

  const getViewUrl = (section: string) => {
    if (!submission?.id) return '#'

    return `/${context}/submissions/${submission.id}/pdf/${section}/view`
  }

  const getDownloadUrl = (section: string) => {
    if (!submission?.id) return '#'

    return `/${context}/submissions/${submission.id}/pdf/${section}/download`
  }

  const getDeleteUrl = (section: string) => {
    if (!submission?.id) return '#'

    return `/${context}/submissions/${submission.id}/pdf/${section}`
  }

  const getPrintBatchUrl = () => {
    if (!submission?.id) return '#'

    return `/${context}/submissions/${submission.id}/pdf/print-batch`
  }

  const selectedGeneratedRows = computed(() => {
    return rows.value.filter((row) => row.canOpen && selectedSections.value.includes(row.section))
  })

  const availableSections = computed(() => {
    return rows.value.filter((row) => row.canOpen).map((row) => row.section)
  })

  const allSelected = computed(() => {
    return availableSections.value.length > 0 && selectedSections.value.length === availableSections.value.length
  })

  const toggleSection = (section: string, value: boolean) => {
    if (value) {
      if (!selectedSections.value.includes(section)) {
        selectedSections.value = [...selectedSections.value, section]
      }

      return
    }

    selectedSections.value = selectedSections.value.filter((item) => item !== section)
  }

  const toggleAll = (value: boolean) => {
    selectedSections.value = value ? [...availableSections.value] : []
  }

  const printSelected = () => {
    if (selectedGeneratedRows.value.length === 0) return

    const params = new URLSearchParams()
    selectedGeneratedRows.value.forEach((row) => {
      params.append('sections[]', row.section)
    })

    const url = `${getPrintBatchUrl()}?${params.toString()}`
    window.open(url, '_blank')
  }

  return {
    rows,
    selectedSections,
    selectedGeneratedRows,
    allSelected,
    getViewUrl,
    getDownloadUrl,
    getDeleteUrl,
    getPrintBatchUrl,
    toggleSection,
    toggleAll,
    printSelected,
  }
}
