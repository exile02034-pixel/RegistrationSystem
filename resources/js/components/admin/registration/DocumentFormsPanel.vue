<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Download, Eye, FileText, Trash2 } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { toast } from '@/components/ui/sonner'
import { useDelete } from '@/composables/useDelete'

type DocumentForm = {
  type: 'secretary_certificate' | 'appointment_form_opc' | 'gis_stock_corporation'
  name: string
  description: string
}

type GeneratedDocument = {
  id: string
  document_type: string
  document_name: string
  created_at: string | null
  generated_by: string | null
  view_url: string
  download_url: string
  delete_url: string
}

const props = defineProps<{
  registrationId: string
  forms: DocumentForm[]
  generatedDocuments: GeneratedDocument[]
}>()

const isModalOpen = ref(false)
const activeType = ref<DocumentForm['type'] | null>(null)
const gisStep = ref(1)

const { isOpen, deleting, promptDelete, confirmDelete, reset } = useDelete()

const form = useForm<{ fields: Record<string, any> }>({
  fields: {},
})

const defaultAppointmentOfficers = () => ([
  { role: 'President', name_and_residential_address: '', nationality: '', gender: '', tin: '' },
  { role: 'Treasurer', name_and_residential_address: '', nationality: '', gender: '', tin: '' },
  { role: 'Corporate Secretary', name_and_residential_address: '', nationality: '', gender: '', tin: '' },
])

const defaultGisRows = () => ([
  { name: '', nationality: '', shareholdings: '' },
  { name: '', nationality: '', shareholdings: '' },
  { name: '', nationality: '', shareholdings: '' },
])

const defaultGisOfficers = () => ([
  { position: '', name: '', tin: '' },
  { position: '', name: '', tin: '' },
  { position: '', name: '', tin: '' },
])

const defaultGisStockholders = () => ([
  { stockholder_name: '', shares: '' },
  { stockholder_name: '', shares: '' },
  { stockholder_name: '', shares: '' },
])

const defaultFields = (type: DocumentForm['type']) => {
  if (type === 'secretary_certificate') {
    return {
      secretary_name: '',
      secretary_address: '',
      corporation_name: '',
      corporation_address: '',
      authorized_person_name: '',
      signing_date: '',
      tin: '',
      doc_no: '',
      page_no: '',
      book_no: '',
      series: '',
    }
  }

  if (type === 'appointment_form_opc') {
    return {
      corporate_name: '',
      trade_name: '',
      sec_registration_number: '',
      date_of_registration: '',
      fiscal_year_end: '',
      complete_business_address: '',
      email_address: '',
      telephone_number: '',
      corporate_tin: '',
      primary_purpose_activity: '',
      officers: defaultAppointmentOfficers(),
      certifier_name: '',
      certifier_tin: '',
    }
  }

  return {
    step_1: {
      corporate_name: '',
      sec_registration_number: '',
      principal_office_address: '',
      business_address: '',
      email: '',
      telephone: '',
      meeting_date_annual: '',
      meeting_date_special: '',
    },
    step_2: {
      amla_covered: false,
      amla_reporting_entity: false,
      amla_other_details: '',
    },
    step_3: {
      authorized_capital_stock: '',
      subscribed_capital_stock: '',
      paid_up_capital_stock: '',
    },
    step_4: defaultGisRows(),
    step_5: defaultGisOfficers(),
    step_6: defaultGisStockholders(),
    step_7: {
      external_auditor_name: '',
      external_auditor_tin: '',
    },
    step_8: {
      corporate_secretary_name: '',
      corporate_secretary_tin: '',
    },
    step_9: {
      certifier_name: '',
      certifier_tin: '',
      certifier_date: '',
    },
  }
}

const activeForm = computed(() => props.forms.find((item) => item.type === activeType.value) ?? null)
const isGis = computed(() => activeType.value === 'gis_stock_corporation')

const openForm = (type: DocumentForm['type']) => {
  activeType.value = type
  gisStep.value = 1
  form.clearErrors()
  form.reset()
  form.fields = defaultFields(type)
  isModalOpen.value = true
}

const closeForm = () => {
  isModalOpen.value = false
  activeType.value = null
  gisStep.value = 1
  form.clearErrors()
}

const formatDateTime = (value: string | null) => {
  if (!value) return 'n/a'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  })
}

const generateUrl = computed(() => {
  if (!activeType.value) return '#'

  return `/admin/registration/${props.registrationId}/documents/${activeType.value}/generate`
})

const isBlank = (value: unknown) => String(value ?? '').trim() === ''
const isValidEmail = (value: string) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
const isIsoDate = (value: unknown) => /^\d{4}-\d{2}-\d{2}$/.test(String(value ?? '').trim())
const isTinFormat = (value: string) => /^\d{3}-\d{3}-\d{3}(?:-\d{3,4})?$/.test(value.trim())
const isDigitsOnly = (value: unknown) => /^\d+$/.test(String(value ?? '').trim())
const isPhoneLike = (value: unknown) => /^\+?[0-9()\-.\s]{6,20}$/.test(String(value ?? '').trim())
const isNonNegativeNumber = (value: unknown) => {
  const numeric = Number(value)

  return !Number.isNaN(numeric) && Number.isFinite(numeric) && numeric >= 0
}

const validateSecretaryForm = (): string[] => {
  const errors: string[] = []
  const fields = form.fields ?? {}

  if (isBlank(fields.secretary_name)) errors.push('Secretary Name is required.')
  if (isBlank(fields.secretary_address)) errors.push('Secretary Address is required.')
  if (isBlank(fields.corporation_name)) errors.push('Corporation Name is required.')
  if (isBlank(fields.corporation_address)) errors.push('Corporation Address is required.')
  if (isBlank(fields.authorized_person_name)) errors.push('Authorized Person Name is required.')
  if (isBlank(fields.signing_date)) {
    errors.push('Signing Date is required.')
  } else if (!isIsoDate(fields.signing_date)) {
    errors.push('Signing Date must be a valid date.')
  }
  if (isBlank(fields.tin)) errors.push('TIN is required.')
  if (!isBlank(fields.tin) && !isTinFormat(String(fields.tin))) errors.push('TIN must be in 000-000-000-000 format.')
  if (isBlank(fields.doc_no)) errors.push('Doc No. is required.')
  if (!isBlank(fields.doc_no) && !isDigitsOnly(fields.doc_no)) errors.push('Doc No. must contain numbers only.')
  if (isBlank(fields.page_no)) errors.push('Page No. is required.')
  if (!isBlank(fields.page_no) && !isDigitsOnly(fields.page_no)) errors.push('Page No. must contain numbers only.')
  if (isBlank(fields.book_no)) errors.push('Book No. is required.')
  if (!isBlank(fields.book_no) && !isDigitsOnly(fields.book_no)) errors.push('Book No. must contain numbers only.')
  if (isBlank(fields.series)) errors.push('Series is required.')
  if (!isBlank(fields.series) && !isDigitsOnly(fields.series)) errors.push('Series must contain numbers only.')

  return errors
}

const validateAppointmentForm = (): string[] => {
  const errors: string[] = []
  const fields = form.fields ?? {}

  if (isBlank(fields.corporate_name)) errors.push('Corporate Name is required.')
  if (isBlank(fields.sec_registration_number)) errors.push('SEC Registration Number is required.')
  if (isBlank(fields.date_of_registration)) errors.push('Date of Registration is required.')
  if (isBlank(fields.fiscal_year_end)) {
    errors.push('Fiscal Year End is required.')
  } else if (!isIsoDate(fields.fiscal_year_end)) {
    errors.push('Fiscal Year End must be a valid date.')
  }
  if (isBlank(fields.complete_business_address)) errors.push('Complete Business Address is required.')
  if (isBlank(fields.email_address)) {
    errors.push('Email Address is required.')
  } else if (!isValidEmail(String(fields.email_address))) {
    errors.push('Email Address must be valid.')
  }
  if (isBlank(fields.telephone_number)) errors.push('Telephone Number is required.')
  if (!isBlank(fields.telephone_number) && !isPhoneLike(fields.telephone_number)) errors.push('Telephone Number format is invalid.')
  if (isBlank(fields.corporate_tin)) errors.push('Corporate TIN is required.')
  if (!isBlank(fields.corporate_tin) && !isTinFormat(String(fields.corporate_tin))) errors.push('Corporate TIN must be in 000-000-000-000 format.')
  if (isBlank(fields.primary_purpose_activity)) errors.push('Primary Purpose / Activity is required.')
  if (isBlank(fields.certifier_name)) errors.push('Certifier Name is required.')
  if (isBlank(fields.certifier_tin)) errors.push('Certifier TIN is required.')
  if (!isBlank(fields.certifier_tin) && !isTinFormat(String(fields.certifier_tin))) errors.push('Certifier TIN must be in 000-000-000-000 format.')

  const officers = Array.isArray(fields.officers) ? fields.officers : []
  if (officers.length !== 3) {
    errors.push('Exactly 3 officers are required: President, Treasurer, Corporate Secretary.')
  } else {
    officers.forEach((officer, index) => {
      const row = index + 1
      if (isBlank(officer.role)) errors.push(`Officer ${row} role is required.`)
      if (isBlank(officer.name_and_residential_address)) errors.push(`Officer ${row} Name & Residential Address is required.`)
      if (isBlank(officer.nationality)) errors.push(`Officer ${row} Nationality is required.`)
      if (isBlank(officer.gender)) errors.push(`Officer ${row} Gender is required.`)
      if (isBlank(officer.tin)) errors.push(`Officer ${row} TIN is required.`)
      if (!isBlank(officer.tin) && !isTinFormat(String(officer.tin))) errors.push(`Officer ${row} TIN must be in 000-000-000-000 format.`)
    })
  }

  return errors
}

const validateAllGisSteps = (): string[] => {
  const errors: string[] = []
  const fields = form.fields ?? {}
  const step1 = fields.step_1 ?? {}
  const step2 = fields.step_2 ?? {}
  const step3 = fields.step_3 ?? {}
  const step4 = Array.isArray(fields.step_4) ? fields.step_4 : []
  const step5 = Array.isArray(fields.step_5) ? fields.step_5 : []
  const step6 = Array.isArray(fields.step_6) ? fields.step_6 : []
  const step8 = fields.step_8 ?? {}
  const step9 = fields.step_9 ?? {}

  if (isBlank(step1.corporate_name)) errors.push('Step 1: Corporate Name is required.')
  if (isBlank(step1.sec_registration_number)) errors.push('Step 1: SEC Registration Number is required.')
  if (isBlank(step1.principal_office_address)) errors.push('Step 1: Principal Office Address is required.')
  if (isBlank(step1.business_address)) errors.push('Step 1: Business Address is required.')
  if (isBlank(step1.email) || !isValidEmail(String(step1.email ?? ''))) errors.push('Step 1: Valid Email is required.')
  if (!isBlank(step1.telephone) && !isPhoneLike(step1.telephone)) errors.push('Step 1: Telephone format is invalid.')

  if (typeof step2.amla_covered !== 'boolean') errors.push('Step 2: AMLA Covered must be yes/no.')
  if (typeof step2.amla_reporting_entity !== 'boolean') errors.push('Step 2: AMLA Reporting Entity must be yes/no.')

  if (isBlank(step3.authorized_capital_stock)) errors.push('Step 3: Authorized Capital Stock is required.')
  if (!isBlank(step3.authorized_capital_stock) && !isNonNegativeNumber(step3.authorized_capital_stock)) errors.push('Step 3: Authorized Capital Stock must be a valid number.')
  if (isBlank(step3.subscribed_capital_stock)) errors.push('Step 3: Subscribed Capital Stock is required.')
  if (!isBlank(step3.subscribed_capital_stock) && !isNonNegativeNumber(step3.subscribed_capital_stock)) errors.push('Step 3: Subscribed Capital Stock must be a valid number.')
  if (isBlank(step3.paid_up_capital_stock)) errors.push('Step 3: Paid-Up Capital Stock is required.')
  if (!isBlank(step3.paid_up_capital_stock) && !isNonNegativeNumber(step3.paid_up_capital_stock)) errors.push('Step 3: Paid-Up Capital Stock must be a valid number.')

  if (step4.length === 0) {
    errors.push('Step 4: At least one director row is required.')
  } else {
    step4.forEach((row, index) => {
      const i = index + 1
      if (isBlank(row.name)) errors.push(`Step 4 Row ${i}: Name is required.`)
      if (isBlank(row.nationality)) errors.push(`Step 4 Row ${i}: Nationality is required.`)
      if (isBlank(row.shareholdings)) errors.push(`Step 4 Row ${i}: Shareholdings is required.`)
      if (!isBlank(row.shareholdings) && !isNonNegativeNumber(row.shareholdings)) errors.push(`Step 4 Row ${i}: Shareholdings must be a valid number.`)
    })
  }

  if (step5.length === 0) {
    errors.push('Step 5: At least one officer row is required.')
  } else {
    step5.forEach((row, index) => {
      const i = index + 1
      if (isBlank(row.position)) errors.push(`Step 5 Row ${i}: Position is required.`)
      if (isBlank(row.name)) errors.push(`Step 5 Row ${i}: Name is required.`)
      if (!isBlank(row.tin) && !isTinFormat(String(row.tin))) errors.push(`Step 5 Row ${i}: TIN must be in 000-000-000-000 format.`)
    })
  }

  if (step6.length === 0) {
    errors.push('Step 6: At least one stockholder row is required.')
  } else {
    step6.forEach((row, index) => {
      const i = index + 1
      if (isBlank(row.stockholder_name)) errors.push(`Step 6 Row ${i}: Stockholder Name is required.`)
      if (isBlank(row.shares)) errors.push(`Step 6 Row ${i}: Shares is required.`)
      if (!isBlank(row.shares) && !isNonNegativeNumber(row.shares)) errors.push(`Step 6 Row ${i}: Shares must be a valid number.`)
    })
  }

  if (isBlank(step8.corporate_secretary_name)) errors.push('Step 8: Corporate Secretary Name is required.')
  if (!isBlank(step8.corporate_secretary_tin) && !isTinFormat(String(step8.corporate_secretary_tin))) errors.push('Step 8: Corporate Secretary TIN must be in 000-000-000-000 format.')
  if (isBlank(step9.certifier_name)) errors.push('Step 9: Certifier Name is required.')
  if (isBlank(step9.certifier_tin)) errors.push('Step 9: Certifier TIN is required.')
  if (!isBlank(step9.certifier_tin) && !isTinFormat(String(step9.certifier_tin))) errors.push('Step 9: Certifier TIN must be in 000-000-000-000 format.')
  if (isBlank(step9.certifier_date)) errors.push('Step 9: Certification Date is required.')

  return errors
}

const validateCurrentGisStep = (): string | null => {
  if (!isGis.value) return null

  if (gisStep.value === 1) {
    const step = form.fields.step_1 ?? {}
    if (!step.corporate_name || !step.sec_registration_number || !step.principal_office_address || !step.business_address || !step.email) {
      return 'Please complete all required corporate information fields.'
    }
  }

  if (gisStep.value === 3) {
    const step = form.fields.step_3 ?? {}
    if (step.authorized_capital_stock === '' || step.subscribed_capital_stock === '' || step.paid_up_capital_stock === '') {
      return 'Please complete capital structure values.'
    }
  }

  if (gisStep.value === 9) {
    const step = form.fields.step_9 ?? {}
    if (!step.certifier_name || !step.certifier_tin || !step.certifier_date) {
      return 'Please complete certifier details before generating the GIS PDF.'
    }
  }

  return null
}

const nextGisStep = () => {
  const error = validateCurrentGisStep()
  if (error) {
    toast.error(error)
    return
  }

  gisStep.value = Math.min(9, gisStep.value + 1)
}

const previousGisStep = () => {
  gisStep.value = Math.max(1, gisStep.value - 1)
}

const submit = () => {
  if (!activeType.value) return

  const errors = activeType.value === 'secretary_certificate'
    ? validateSecretaryForm()
    : activeType.value === 'appointment_form_opc'
      ? validateAppointmentForm()
      : validateAllGisSteps()

  if (errors.length > 0) {
    toast.error(errors[0])
    return
  }

  form.post(generateUrl.value, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Document generated successfully.')
      closeForm()
    },
    onError: (errors) => {
      const firstMessage = Object.values(errors).find((value) => typeof value === 'string')
      const message = (firstMessage as string | undefined) ?? (errors.document as string | undefined) ?? 'Failed to generate document. Please check your input fields.'
      toast.error(message)
    },
  })
}
</script>

<template>
  <div class="space-y-4">
    <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
      <div class="mb-3 flex items-center gap-2">
        <FileText class="h-4 w-4 text-[#2563EB] dark:text-[#60A5FA]" />
        <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Generated Document PDFs</h3>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <table class="min-w-full text-sm">
          <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
            <tr>
              <th class="px-4 py-3">Document</th>
              <th class="px-4 py-3">Generated By</th>
              <th class="px-4 py-3">Created At</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="generatedDocuments.length === 0">
              <td colspan="4" class="px-4 py-4 text-[#64748B] dark:text-[#9FB3C8]">No generated documents yet.</td>
            </tr>
            <tr
              v-for="row in generatedDocuments"
              :key="row.id"
              class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]"
            >
              <td class="px-4 py-3 font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ row.document_name }}</td>
              <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">{{ row.generated_by || 'Admin' }}</td>
              <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">{{ formatDateTime(row.created_at) }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                  <Button as="a" :href="row.view_url" target="_blank" variant="ghost" size="icon" class="h-8 w-8">
                    <Eye class="h-4 w-4" />
                  </Button>
                  <Button as="a" :href="row.download_url" variant="ghost" size="icon" class="h-8 w-8">
                    <Download class="h-4 w-4" />
                  </Button>
                  <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-red-600" @click="promptDelete(row.delete_url)">
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] p-4 shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
      <h3 class="mb-3 font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Document Forms</h3>
      <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <table class="min-w-full text-sm">
          <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
            <tr>
              <th class="px-4 py-3">Document Name</th>
              <th class="px-4 py-3">Description</th>
              <th class="px-4 py-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="doc in forms"
              :key="doc.type"
              class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]"
            >
              <td class="px-4 py-3 font-medium text-[#0B1F3A] dark:text-[#E6F1FF]">{{ doc.name }}</td>
              <td class="px-4 py-3 text-[#475569] dark:text-[#9FB3C8]">{{ doc.description }}</td>
              <td class="px-4 py-3 text-right">
                <Button type="button" variant="outline" class="cursor-pointer" @click="openForm(doc.type)">
                  Input Fields
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <Dialog :open="isModalOpen" @update:open="(value) => (!value ? closeForm() : (isModalOpen = value))">
    <DialogContent class="max-h-[90vh] overflow-y-auto sm:max-w-4xl dark:border-[#1E3A5F] dark:bg-[#12325B]">
      <DialogHeader>
        <DialogTitle>{{ activeForm?.name ?? 'Document Form' }}</DialogTitle>
      </DialogHeader>

      <div v-if="activeType === 'secretary_certificate'" class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <Label>Secretary Name</Label>
          <Input v-model="form.fields.secretary_name" />
        </div>
        <div class="space-y-2">
          <Label>Corporation Name</Label>
          <Input v-model="form.fields.corporation_name" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Secretary Address</Label>
          <textarea v-model="form.fields.secretary_address" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Corporation Address</Label>
          <textarea v-model="form.fields.corporation_address" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        </div>
        <div class="space-y-2">
          <Label>Authorized Person Name</Label>
          <Input v-model="form.fields.authorized_person_name" />
        </div>
        <div class="space-y-2">
          <Label>Signing Date</Label>
          <Input v-model="form.fields.signing_date" type="date" />
        </div>
        <div class="space-y-2">
          <Label>TIN (000-000-000-000)</Label>
          <Input v-model="form.fields.tin" />
        </div>
        <div class="space-y-2">
          <Label>Doc No.</Label>
          <Input v-model="form.fields.doc_no" type="number" min="0" step="1" />
        </div>
        <div class="space-y-2">
          <Label>Page No.</Label>
          <Input v-model="form.fields.page_no" type="number" min="0" step="1" />
        </div>
        <div class="space-y-2">
          <Label>Book No.</Label>
          <Input v-model="form.fields.book_no" type="number" min="0" step="1" />
        </div>
        <div class="space-y-2">
          <Label>Series</Label>
          <Input v-model="form.fields.series" type="number" min="0" step="1" />
        </div>
      </div>

      <div v-else-if="activeType === 'appointment_form_opc'" class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <Label>Corporate Name</Label>
          <Input v-model="form.fields.corporate_name" />
        </div>
        <div class="space-y-2">
          <Label>Trade Name</Label>
          <Input v-model="form.fields.trade_name" />
        </div>
        <div class="space-y-2">
          <Label>SEC Registration Number</Label>
          <Input v-model="form.fields.sec_registration_number" />
        </div>
        <div class="space-y-2">
          <Label>Date of Registration</Label>
          <Input v-model="form.fields.date_of_registration" type="date" />
        </div>
        <div class="space-y-2">
          <Label>Fiscal Year End</Label>
          <Input v-model="form.fields.fiscal_year_end" type="date" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Complete Business Address</Label>
          <textarea v-model="form.fields.complete_business_address" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        </div>
        <div class="space-y-2">
          <Label>Email Address</Label>
          <Input v-model="form.fields.email_address" type="email" />
        </div>
        <div class="space-y-2">
          <Label>Telephone Number</Label>
          <Input v-model="form.fields.telephone_number" type="tel" />
        </div>
        <div class="space-y-2">
          <Label>Corporate TIN (000-000-000-000)</Label>
          <Input v-model="form.fields.corporate_tin" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Primary Purpose / Activity</Label>
          <textarea v-model="form.fields.primary_purpose_activity" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        </div>

        <div class="space-y-2 md:col-span-2">
          <Label>Officers</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-5">
            <div>Role</div>
            <div>Name & Residential Address</div>
            <div>Nationality</div>
            <div>Gender</div>
            <div>TIN</div>
          </div>
          <div class="space-y-2">
            <div
              v-for="(officer, index) in form.fields.officers"
              :key="`officer-${index}`"
              class="grid gap-2 rounded-md border border-[#E2E8F0] p-3 dark:border-[#1E3A5F] md:grid-cols-5"
            >
              <Input v-model="officer.role" readonly />
              <Input v-model="officer.name_and_residential_address" />
              <Input v-model="officer.nationality" />
              <Input v-model="officer.gender" />
              <Input v-model="officer.tin" />
            </div>
          </div>
        </div>

        <div class="space-y-2">
          <Label>Certifier Name</Label>
          <Input v-model="form.fields.certifier_name" />
        </div>
        <div class="space-y-2">
          <Label>Certifier TIN (000-000-000-000)</Label>
          <Input v-model="form.fields.certifier_tin" />
        </div>
      </div>

      <div v-else-if="activeType === 'gis_stock_corporation'" class="space-y-4">
        <div class="rounded-md border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2 text-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]">
          Step {{ gisStep }} of 9
        </div>

        <div v-if="gisStep === 1" class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label>Corporate Name</Label>
            <Input v-model="form.fields.step_1.corporate_name" />
          </div>
          <div class="space-y-2">
            <Label>SEC Registration Number</Label>
            <Input v-model="form.fields.step_1.sec_registration_number" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Principal Office Address</Label>
            <textarea v-model="form.fields.step_1.principal_office_address" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Business Address</Label>
            <textarea v-model="form.fields.step_1.business_address" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
          </div>
          <div class="space-y-2">
            <Label>Email</Label>
            <Input v-model="form.fields.step_1.email" type="email" />
          </div>
          <div class="space-y-2">
            <Label>Telephone</Label>
            <Input v-model="form.fields.step_1.telephone" type="tel" />
          </div>
          <div class="space-y-2">
            <Label>Annual Meeting Date</Label>
            <Input v-model="form.fields.step_1.meeting_date_annual" type="date" />
          </div>
          <div class="space-y-2">
            <Label>Special Meeting Date</Label>
            <Input v-model="form.fields.step_1.meeting_date_special" type="date" />
          </div>
        </div>

        <div v-else-if="gisStep === 2" class="space-y-3">
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.fields.step_2.amla_covered" type="checkbox">
            AMLA Covered Person/Entity
          </label>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.fields.step_2.amla_reporting_entity" type="checkbox">
            AMLA Reporting Entity
          </label>
          <div class="space-y-2">
            <Label>Other AMLA Details</Label>
            <textarea v-model="form.fields.step_2.amla_other_details" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
          </div>
        </div>

        <div v-else-if="gisStep === 3" class="grid gap-4 md:grid-cols-3">
          <div class="space-y-2">
            <Label>Authorized Capital Stock</Label>
            <Input v-model="form.fields.step_3.authorized_capital_stock" type="number" />
          </div>
          <div class="space-y-2">
            <Label>Subscribed Capital Stock</Label>
            <Input v-model="form.fields.step_3.subscribed_capital_stock" type="number" />
          </div>
          <div class="space-y-2">
            <Label>Paid-Up Capital Stock</Label>
            <Input v-model="form.fields.step_3.paid_up_capital_stock" type="number" />
          </div>
        </div>

        <div v-else-if="gisStep === 4" class="space-y-2">
          <Label>Directors</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-3">
            <div>Name</div>
            <div>Nationality</div>
            <div>Shareholdings</div>
          </div>
          <div v-for="(row, index) in form.fields.step_4" :key="`dir-${index}`" class="grid gap-2 md:grid-cols-3">
            <Input v-model="row.name" />
            <Input v-model="row.nationality" />
            <Input v-model="row.shareholdings" type="number" />
          </div>
        </div>

        <div v-else-if="gisStep === 5" class="space-y-2">
          <Label>Officers</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-3">
            <div>Position</div>
            <div>Name</div>
            <div>TIN</div>
          </div>
          <div v-for="(row, index) in form.fields.step_5" :key="`off-${index}`" class="grid gap-2 md:grid-cols-3">
            <Input v-model="row.position" />
            <Input v-model="row.name" />
            <Input v-model="row.tin" />
          </div>
        </div>

        <div v-else-if="gisStep === 6" class="space-y-2">
          <Label>Stockholders</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-2">
            <div>Stockholder Name</div>
            <div>Shares</div>
          </div>
          <div v-for="(row, index) in form.fields.step_6" :key="`stk-${index}`" class="grid gap-2 md:grid-cols-2">
            <Input v-model="row.stockholder_name" />
            <Input v-model="row.shares" type="number" />
          </div>
        </div>

        <div v-else-if="gisStep === 7" class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label>External Auditor Name</Label>
            <Input v-model="form.fields.step_7.external_auditor_name" />
          </div>
          <div class="space-y-2">
            <Label>External Auditor TIN (000-000-000-000)</Label>
            <Input v-model="form.fields.step_7.external_auditor_tin" />
          </div>
        </div>

        <div v-else-if="gisStep === 8" class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label>Corporate Secretary Name</Label>
            <Input v-model="form.fields.step_8.corporate_secretary_name" />
          </div>
          <div class="space-y-2">
            <Label>Corporate Secretary TIN (000-000-000-000)</Label>
            <Input v-model="form.fields.step_8.corporate_secretary_tin" />
          </div>
        </div>

        <div v-else class="grid gap-4 md:grid-cols-3">
          <div class="space-y-2">
            <Label>Certifier Name</Label>
            <Input v-model="form.fields.step_9.certifier_name" />
          </div>
          <div class="space-y-2">
            <Label>Certifier TIN (000-000-000-000)</Label>
            <Input v-model="form.fields.step_9.certifier_tin" />
          </div>
          <div class="space-y-2">
            <Label>Certification Date</Label>
            <Input v-model="form.fields.step_9.certifier_date" type="date" />
          </div>
        </div>

        <div class="flex items-center justify-between pt-2">
          <Button type="button" variant="outline" :disabled="gisStep === 1 || form.processing" @click="previousGisStep">
            Previous Step
          </Button>
          <div class="flex items-center gap-2">
            <Button
              v-if="gisStep < 9"
              type="button"
              :disabled="form.processing"
              @click="nextGisStep"
            >
              Next Step
            </Button>
            <Button
              v-else
              type="button"
              :disabled="form.processing"
              @click="submit"
            >
              {{ form.processing ? 'Generating...' : 'Generate PDF' }}
            </Button>
          </div>
        </div>
      </div>

      <div v-if="activeType && !isGis" class="mt-2 flex justify-end">
        <Button type="button" :disabled="form.processing" @click="submit">
          {{ form.processing ? 'Generating...' : 'Generate PDF' }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>

  <AlertDialog :open="isOpen" @update:open="(value) => !value && reset()">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>Delete Generated Document</AlertDialogTitle>
        <AlertDialogDescription>
          Are you sure you want to delete this generated document PDF? This action cannot be undone.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel :disabled="deleting" @click="reset">Cancel</AlertDialogCancel>
        <AlertDialogAction :disabled="deleting" @click="confirmDelete">Delete</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
