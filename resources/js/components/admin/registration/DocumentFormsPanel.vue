<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Download, Eye, FileText, Mail, MoreHorizontal, Trash2 } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
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
import { Checkbox } from '@/components/ui/checkbox'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { toast } from '@/components/ui/sonner'
import { useDelete } from '@/composables/useDelete'

type DocumentForm = {
  type: 'secretary_certificate' | 'secretary_certificate_bank' | 'appointment_form_opc' | 'gis_stock_corporation'
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

type GisAutofillData = {
  corporate_name?: string
  business_trade_name?: string
  sec_registration_number?: string
  date_registered?: string
  principal_office_address?: string
  business_address?: string
  corporate_tin?: string
  branch_code?: string
  industry_classification?: string
  email?: string
  official_mobile?: string
  alternate_email?: string
  alternate_mobile?: string
  primary_purpose?: string
  step_3?: {
    authorized_capital_stock?: string
    subscribed_capital_stock?: string
    paid_up_capital_stock?: string
    authorized_rows?: Array<Record<string, unknown>>
    subscribed_filipino_rows?: Array<Record<string, unknown>>
    subscribed_foreign_rows?: Array<Record<string, unknown>>
    paidup_filipino_rows?: Array<Record<string, unknown>>
    paidup_foreign_rows?: Array<Record<string, unknown>>
    percentage_foreign_equity?: string
    total_subscribed_capital?: string
    total_paid_up_capital?: string
  }
  aoi_capital_stock_available?: boolean
  has_uploaded_sources?: boolean
  missing_fields?: string[]
}

type AppointmentAutofillData = {
  corporate_tin?: string
  complete_business_address?: string
  business_trade_name?: string
  date_of_registration?: string
  sec_registration_number?: string
  corporate_name?: string
  email_address?: string
  primary_purpose_activity?: string
  has_uploaded_sources?: boolean
  missing_fields?: string[]
}

const props = defineProps<{
  registrationId: string
  forms: DocumentForm[]
  generatedDocuments: GeneratedDocument[]
  gisAutofill?: GisAutofillData
  appointmentAutofill?: AppointmentAutofillData
}>()

const isModalOpen = ref(false)
const activeType = ref<DocumentForm['type'] | null>(null)
const gisStep = ref(1)
const selectedDocumentIds = ref<string[]>([])
const MIN_GIS_STOCKHOLDER_ROWS = 6
const GIS_TOTAL_PAGES = 7
const secretaryUseDefaultValues = ref(true)
const appointmentFieldsLocked = ref(true)
const actionConfirmOpen = ref(false)
const actionConfirmTitle = ref('')
const actionConfirmDescription = ref('')
const actionConfirmLabel = ref('Confirm')
const actionConfirmHandler = ref<(() => void) | null>(null)
const appointmentAutofillReadonly = computed(() => appointmentFieldsLocked.value && Boolean(props.appointmentAutofill?.has_uploaded_sources))

const SECRETARY_DEFAULT_NAME = 'Vince Anthony Feir'
const SECRETARY_DEFAULT_ADDRESS = '299 Purok 1 San Agustin Lubao Pampanga'
const SECRETARY_DEFAULT_TIN = '765-241-127-000'

const { isOpen, deleting, promptDelete, confirmDelete, reset } = useDelete()

const form = useForm<{ fields: Record<string, any>; fields_json: string }>({
  fields: {},
  fields_json: '',
})
const sendEmailForm = useForm<{ sections: string[]; document_ids: string[] }>({
  sections: [],
  document_ids: [],
})

const defaultAppointmentOfficers = () => ([
  { position: 'President', name_and_residential_address: '', nationality: 'Filipino', gender: '', tin: '' },
  { position: 'Treasurer', name_and_residential_address: '', nationality: 'Filipino', gender: '', tin: '' },
  { position: 'Corporate Secretary', name_and_residential_address: '', nationality: 'Filipino', gender: '', tin: '' },
])

type BankSignatoryRow = {
  name: string
  position: string
}

const createBankSignatoryRow = (): BankSignatoryRow => ({
  name: '',
  position: '',
})

const sanitizeBankSignatoryRows = (rows: unknown): BankSignatoryRow[] => {
  if (!Array.isArray(rows)) return [createBankSignatoryRow()]

  const sanitized = rows.map((row) => ({
    name: String((row as BankSignatoryRow | undefined)?.name ?? ''),
    position: String((row as BankSignatoryRow | undefined)?.position ?? ''),
  }))

  return sanitized.length > 0 ? sanitized : [createBankSignatoryRow()]
}

const defaultGisRows = () => Array.from({ length: 15 }, () => ({
  name: '',
  nationality: '',
  incorporator: '',
  board: '',
  gender: '',
  stockholder: '',
  officer: '',
  exec_comm: '',
  tin: '',
}))

const defaultGisStockholderRows = (startNumber: number, count = MIN_GIS_STOCKHOLDER_ROWS) => Array.from(
  { length: count },
  (_, index) => ({
    no: startNumber + index,
    name_address: '',
    nationality: '',
    share_type: '',
    number_of_shares: '',
    amount_subscribed: '',
    percent_ownership: '',
    amount_paid: '',
    tin: '',
  }),
)

const defaultCapitalRows = (count: number) => Array.from({ length: count }, () => ({
  type_of_shares: '',
  no_of_stockholders: '',
  number_of_shares: '',
  public_shares: '',
  par_or_stated_value: '',
  amount: '',
  ownership_percent: '',
}))

const amlaOptionGroups = {
  one: [
    { key: 'a', label: 'Banks' },
    { key: 'b', label: 'Offshore Banking Units' },
    { key: 'c', label: 'Quasi-Banks' },
    { key: 'd', label: 'Trust Entities' },
    { key: 'e', label: 'Non-Stock Savings and Loan Associations' },
    { key: 'f', label: 'Pawnshops' },
    { key: 'g', label: 'Foreign Exchange Dealers' },
    { key: 'h', label: 'Money Changers' },
    { key: 'i', label: 'Remittance Agents' },
    { key: 'j', label: 'Electronic Money Issuers' },
    { key: 'k', label: 'Financial institutions under BSP supervision/regulation, including subsidiaries/affiliates' },
  ],
  two: [
    { key: 'a', label: 'Insurance Companies' },
    { key: 'b', label: 'Insurance Agents' },
    { key: 'c', label: 'Insurance Brokers' },
    { key: 'd', label: 'Professional Reinsurers' },
    { key: 'e', label: 'Reinsurance Brokers' },
    { key: 'f', label: 'Holding Companies' },
    { key: 'g', label: 'Holding Company Systems' },
    { key: 'h', label: 'Pre-need Companies' },
    { key: 'i', label: 'Mutual Benefit Association' },
    { key: 'j', label: 'All other entities supervised and/or regulated by IC' },
  ],
  three: [
    { key: 'a', label: 'Securities Dealers' },
    { key: 'b', label: 'Securities Brokers' },
    { key: 'c', label: 'Securities Salesman' },
    { key: 'd', label: 'Investment Houses' },
    { key: 'e', label: 'Investment Agents and Consultants' },
    { key: 'f', label: 'Trading Advisors' },
    { key: 'g', label: 'Other entities managing securities or similar services' },
    { key: 'h', label: 'Mutual Funds / Open-end Investment Companies' },
    { key: 'i', label: 'Close-end Investment Companies' },
    { key: 'j', label: 'Common Trust Funds / Issuers and similar entities' },
    { key: 'k', label: 'Transfer Companies and similar entities' },
    { key: 'l', label: 'Entities dealing in currency/commodities/financial derivatives' },
    { key: 'm', label: 'Entities dealing in valuable objects' },
    { key: 'n', label: 'Entities dealing in cash substitutes and similar monetary instruments regulated by SEC' },
  ],
  six: [
    { key: 'a', label: 'Acting as a formation agent of juridical persons' },
    { key: 'b', label: 'Acting/arranging another as director/corporate secretary/partner/similar position' },
    { key: 'c', label: 'Providing registered office/business/correspondence/administrative address' },
    { key: 'd', label: 'Acting/arranging another as nominee shareholder' },
  ],
  seven: [
    { key: 'a', label: 'Managing client money, securities or other assets' },
    { key: 'b', label: 'Management of bank, savings or securities accounts' },
    { key: 'c', label: 'Organization of contributions for creation/operation/management of companies' },
    { key: 'd', label: 'Creation/operation/management of juridical persons and buying/selling business entities' },
  ],
} as const

const defaultFields = (type: DocumentForm['type']) => {
  if (type === 'secretary_certificate') {
    return {
      secretary_name: SECRETARY_DEFAULT_NAME,
      secretary_address: SECRETARY_DEFAULT_ADDRESS,
      secretary_signature_data_uri: '',
      corporation_name: '',
      corporation_address: '',
      authorized_person_name: 'Ronnel Landa',
      signing_date: '',
      tin: SECRETARY_DEFAULT_TIN,
      doc_no: '',
      page_no: '',
      book_no: '',
      series: '',
    }
  }

  if (type === 'appointment_form_opc') {
    return {
      for_the_year: '',
      corporate_name: '',
      business_trade_name: '',
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
      sworn_place: '',
      sworn_date: '',
      competent_evidence: '',
      issued_at: '',
      issued_on: '',
    }
  }

  if (type === 'secretary_certificate_bank') {
    return {
      secretary_name: '',
      secretary_address: '',
      corporation_name: '',
      principal_address: '',
      bank_name: '',
      branch: '',
      meeting_date: '',
      authorized_signatories_for_opening: [createBankSignatoryRow()],
      authorized_signatories_for_transacting: [createBankSignatoryRow()],
      corporate_secretary_name: '',
      certificate_date: '',
      certificate_location: '',
    }
  }

  return {
    step_1: {
      corporate_name: '',
      business_trade_name: '',
      sec_registration_number: '',
      date_registered: '',
      fiscal_year_end: '',
      corporate_tin: '',
      principal_office_address: '',
      business_address: '',
      email: '',
      alternate_email: '',
      official_mobile: '',
      alternate_mobile: '',
      website_url: '',
      fax_number: '',
      primary_purpose: '',
      industry_classification: '',
      geographical_code: '',
      external_auditor_name: '',
      sec_accreditation_number: '',
      telephone: '',
      meeting_date_annual: '',
      meeting_date_actual: '',
      intercompany_parent_company: '',
      intercompany_parent_sec_no: '',
      intercompany_parent_address: '',
      intercompany_subsidiary: '',
      intercompany_subsidiary_sec_no: '',
      intercompany_subsidiary_address: '',
    },
    step_2: {
      amla_covered: false,
      cdd_complied: false,
      amla_types: [] as string[],
      amla_detailed: {
        one: { a: false, b: false, c: false, d: false, e: false, f: false, g: false, h: false, i: false, j: false, k: false },
        two: { a: false, b: false, c: false, d: false, e: false, f: false, g: false, h: false, i: false, j: false },
        three: { a: false, b: false, c: false, d: false, e: false, f: false, g: false, h: false, i: false, j: false, k: false, l: false, m: false, n: false },
        four: false,
        five: false,
        six: { a: false, b: false, c: false, d: false },
        seven: { a: false, b: false, c: false, d: false },
        eight: false,
      },
      amla_other_details: '',
      nature_of_business: '',
    },
    step_3: {
      authorized_capital_stock: '',
      subscribed_capital_stock: '',
      paid_up_capital_stock: '',
      authorized_rows: defaultCapitalRows(3),
      subscribed_filipino_rows: defaultCapitalRows(2),
      subscribed_foreign_rows: defaultCapitalRows(2),
      paidup_filipino_rows: defaultCapitalRows(2),
      paidup_foreign_rows: defaultCapitalRows(2),
      percentage_foreign_equity: '',
      total_subscribed_capital: '',
      total_paid_up_capital: '',
    },
    step_4: defaultGisRows(),
    step_5: {
      total_stockholders: '',
      stockholders_with_100_plus: '',
      total_assets: '',
      rows: defaultGisStockholderRows(1),
    },
    step_8: {
      investment_stocks: '',
      investment_bonds: '',
      investment_loans_advances: '',
      investment_treasury_bills: '',
      investment_others: '',
      investment_board_resolution_date: '',
      investment_stocks_board_resolution_date: '',
      investment_bonds_board_resolution_date: '',
      investment_loans_advances_board_resolution_date: '',
      investment_treasury_bills_board_resolution_date: '',
      investment_others_board_resolution_date: '',
      secondary_purpose_activity: '',
      secondary_purpose_board_resolution_date: '',
      secondary_purpose_ratification_date: '',
      treasury_shares_count: '',
      treasury_shares_percent: '',
      retained_earnings: '',
      dividend_cash_amount: '',
      dividend_cash_date: '',
      dividend_stock_amount: '',
      dividend_stock_date: '',
      dividend_property_amount: '',
      dividend_property_date: '',
      dividend_total_amount: '',
      additional_shares: [
        { date: '', no_of_shares: '', amount: '' },
        { date: '', no_of_shares: '', amount: '' },
        { date: '', no_of_shares: '', amount: '' },
        { date: '', no_of_shares: '', amount: '' },
      ],
      agency_name: '',
      secondary_license_type: '',
      secondary_license_date_issued: '',
      secondary_license_date_started: '',
      secondary_license_sec: false,
      secondary_license_bsp: false,
      secondary_license_ic: false,
      total_annual_compensation_directors: '',
      total_no_officers: '',
      total_rank_file_employees: '',
      total_manpower_complement: '',
    },
    step_9: {
      certifier_name: '',
      certifier_tin: '',
      certifier_date: '',
      done_date: '',
      done_day: '',
      done_month: '',
      done_year: '',
      done_place: '',
      notary_place: '',
      notary_date: '',
      competent_evidence: '',
      issued_at: '',
      issued_on: '',
    },
  }
}

const activeForm = computed(() => props.forms.find((item) => item.type === activeType.value) ?? null)
const isGis = computed(() => activeType.value === 'gis_stock_corporation')

const openForm = (type: DocumentForm['type']) => {
  activeType.value = type
  gisStep.value = 1
  appointmentFieldsLocked.value = true
  form.clearErrors()
  secretaryUseDefaultValues.value = true
  form.reset()
  form.fields = defaultFields(type)
  if (type === 'secretary_certificate_bank') {
    form.fields.authorized_signatories_for_opening = sanitizeBankSignatoryRows(form.fields.authorized_signatories_for_opening)
    form.fields.authorized_signatories_for_transacting = sanitizeBankSignatoryRows(form.fields.authorized_signatories_for_transacting)
  }

  if (type === 'gis_stock_corporation') {
    applyGisAutofillDefaults()
  }
  if (type === 'appointment_form_opc') {
    applyAppointmentAutofillDefaults()
  }

  isModalOpen.value = true
}

const addBankSignatoryRow = (fieldName: 'authorized_signatories_for_opening' | 'authorized_signatories_for_transacting') => {
  const rows = sanitizeBankSignatoryRows(form.fields?.[fieldName])
  form.fields[fieldName] = [...rows, createBankSignatoryRow()]
}

const removeBankSignatoryRow = (
  fieldName: 'authorized_signatories_for_opening' | 'authorized_signatories_for_transacting',
) => {
  const rows = sanitizeBankSignatoryRows(form.fields?.[fieldName])
  if (rows.length <= 1) return

  const nextRows = rows.slice(0, -1)
  form.fields[fieldName] = nextRows.length > 0 ? nextRows : [createBankSignatoryRow()]
}

const normalizeSecretaryDefaults = () => {
  form.fields.secretary_name = SECRETARY_DEFAULT_NAME
  form.fields.secretary_address = SECRETARY_DEFAULT_ADDRESS
  form.fields.tin = SECRETARY_DEFAULT_TIN
  form.fields.secretary_signature_data_uri = ''
}

const useSecretaryDefaults = (enabled: boolean) => {
  secretaryUseDefaultValues.value = enabled
  if (enabled) {
    normalizeSecretaryDefaults()
    return
  }

  syncSecretarySignatureToName(form.fields.secretary_name)
}

const syncSecretarySignatureToName = (name: unknown) => {
  if (secretaryUseDefaultValues.value) {
    return
  }

  form.fields.secretary_signature_data_uri = String(name ?? '').trim()
}

watch(
  () => secretaryUseDefaultValues.value,
  (useDefault) => {
    if (useDefault) {
      normalizeSecretaryDefaults()
      return
    }

    syncSecretarySignatureToName(form.fields.secretary_name)
  },
)

watch(
  () => form.fields?.secretary_name,
  (secretaryName) => {
    syncSecretarySignatureToName(secretaryName)
  },
)

const closeForm = () => {
  isModalOpen.value = false
  activeType.value = null
  gisStep.value = 1
  form.clearErrors()
}

type GisStep1AutofillField =
  | 'corporate_name'
  | 'business_trade_name'
  | 'sec_registration_number'
  | 'date_registered'
  | 'principal_office_address'
  | 'business_address'
  | 'email'
  | 'alternate_email'
  | 'corporate_tin'
  | 'official_mobile'
  | 'alternate_mobile'
  | 'primary_purpose'
  | 'industry_classification'

const GIS_STEP1_AUTOFILL_FIELDS: GisStep1AutofillField[] = [
  'corporate_name',
  'business_trade_name',
  'sec_registration_number',
  'date_registered',
  'principal_office_address',
  'business_address',
  'email',
  'alternate_email',
  'corporate_tin',
  'official_mobile',
  'alternate_mobile',
  'primary_purpose',
  'industry_classification',
]
const GIS_STEP3_ROW_LIMITS = {
  authorized_rows: 3,
  subscribed_filipino_rows: 2,
  subscribed_foreign_rows: 2,
  paidup_filipino_rows: 2,
  paidup_foreign_rows: 2,
} as const

const gisAutofillMissingFields = computed(() => new Set(props.gisAutofill?.missing_fields ?? []))
const gisStep3Autofill = computed(() => {
  const step3 = props.gisAutofill?.step_3

  return step3 && typeof step3 === 'object' ? step3 : null
})

const gisAutofillValue = (fieldName: GisStep1AutofillField): string => {
  const source = props.gisAutofill ?? {}

  if (fieldName === 'corporate_name') return String(source.corporate_name ?? '').trim()
  if (fieldName === 'business_trade_name') return String(source.business_trade_name ?? '').trim()
  if (fieldName === 'sec_registration_number') return String(source.sec_registration_number ?? '').trim()
  if (fieldName === 'date_registered') return String(source.date_registered ?? '').trim()
  if (fieldName === 'principal_office_address') return String(source.principal_office_address ?? '').trim()
  if (fieldName === 'business_address') return String(source.business_address ?? '').trim()
  if (fieldName === 'email') return String(source.email ?? '').trim()
  if (fieldName === 'alternate_email') return String(source.alternate_email ?? '').trim()
  if (fieldName === 'official_mobile') return String(source.official_mobile ?? '').trim()
  if (fieldName === 'alternate_mobile') return String(source.alternate_mobile ?? '').trim()
  if (fieldName === 'primary_purpose') return String(source.primary_purpose ?? '').trim()
  if (fieldName === 'industry_classification') return String(source.industry_classification ?? '').trim()
  if (fieldName === 'corporate_tin') {
    const tin = String(source.corporate_tin ?? '').trim()
    const branchCode = String(source.branch_code ?? '').trim()

    return [tin, branchCode].filter((segment) => segment !== '').join('/')
  }

  return ''
}

const applyGisAutofillOnFocus = (fieldName: GisStep1AutofillField, force = false) => {
  if (activeType.value !== 'gis_stock_corporation') return
  if (gisStep.value !== 1) return

  const step1 = form.fields?.step_1 ?? {}
  const currentValue = String(step1[fieldName] ?? '').trim()
  if (!force && currentValue !== '') return

  const value = gisAutofillValue(fieldName)
  if (value === '') return

  form.fields.step_1[fieldName] = value
}

const applyGisAutofillDefaults = (force = false) => {
  if (activeType.value !== 'gis_stock_corporation') return

  GIS_STEP1_AUTOFILL_FIELDS.forEach((fieldName) => {
    applyGisAutofillOnFocus(fieldName, force)
  })
  applyGisStep3Autofill(force)
}

const hasValue = (value: unknown): boolean => String(value ?? '').trim() !== ''

const shouldOverwriteField = (current: unknown, incoming: unknown, force: boolean): boolean => {
  if (!hasValue(incoming)) return false
  if (force) return true

  return !hasValue(current)
}

const normalizeStep3Rows = (rows: unknown, maxRows: number): Record<string, string>[] => {
  if (!Array.isArray(rows)) return []

  return rows
    .slice(0, maxRows)
    .map((row: Record<string, unknown>) => Object.entries(row ?? {}).reduce<Record<string, string>>((carry, [key, value]) => {
      carry[key] = String(value ?? '').trim()

      return carry
    }, {}))
}

const rowHasAnyValue = (row: unknown): boolean => {
  if (!row || typeof row !== 'object') return false

  return Object.values(row as Record<string, unknown>).some((value) => hasValue(value))
}

const applyGisStep3Autofill = (force = false) => {
  if (activeType.value !== 'gis_stock_corporation') return
  const extracted = gisStep3Autofill.value
  if (!extracted) return

  form.fields.step_3 = form.fields.step_3 ?? {}
  const step3 = form.fields.step_3

  if (shouldOverwriteField(step3.authorized_capital_stock, extracted.authorized_capital_stock, force)) {
    step3.authorized_capital_stock = String(extracted.authorized_capital_stock ?? '').trim()
  }
  if (shouldOverwriteField(step3.subscribed_capital_stock, extracted.subscribed_capital_stock, force)) {
    step3.subscribed_capital_stock = String(extracted.subscribed_capital_stock ?? '').trim()
  }
  if (shouldOverwriteField(step3.paid_up_capital_stock, extracted.paid_up_capital_stock, force)) {
    step3.paid_up_capital_stock = String(extracted.paid_up_capital_stock ?? '').trim()
  }
  if (shouldOverwriteField(step3.percentage_foreign_equity, extracted.percentage_foreign_equity, force)) {
    step3.percentage_foreign_equity = String(extracted.percentage_foreign_equity ?? '').trim()
  }
  if (shouldOverwriteField(step3.total_subscribed_capital, extracted.total_subscribed_capital, force)) {
    step3.total_subscribed_capital = String(extracted.total_subscribed_capital ?? '').trim()
  }
  if (shouldOverwriteField(step3.total_paid_up_capital, extracted.total_paid_up_capital, force)) {
    step3.total_paid_up_capital = String(extracted.total_paid_up_capital ?? '').trim()
  }

  for (const fieldName of Object.keys(GIS_STEP3_ROW_LIMITS) as Array<keyof typeof GIS_STEP3_ROW_LIMITS>) {
    const maxRows = GIS_STEP3_ROW_LIMITS[fieldName]
    const extractedRows = normalizeStep3Rows(extracted[fieldName], maxRows)
    if (extractedRows.length === 0) continue

    const currentRows = Array.isArray(step3[fieldName]) ? step3[fieldName] : defaultCapitalRows(maxRows)
    const nextRows = defaultCapitalRows(maxRows).map((blankRow, index) => {
      const currentRow = (currentRows[index] ?? blankRow) as Record<string, unknown>
      const incomingRow = extractedRows[index] ?? {}
      if (!rowHasAnyValue(incomingRow)) return currentRow
      if (!force && rowHasAnyValue(currentRow)) return currentRow

      return { ...blankRow, ...incomingRow }
    })

    step3[fieldName] = nextRows
  }
}

const applyGisAutofillWithConfirmation = () => {
  if (activeType.value !== 'gis_stock_corporation' || gisStep.value !== 1) return
  if (!props.gisAutofill?.aoi_capital_stock_available) {
    toast.error('Articles of Incorporation is missing in Required Documents. Upload it first to autofill GIS Page 3.')
  }

  const hasStep1Overlaps = GIS_STEP1_AUTOFILL_FIELDS.some((fieldName) => {
    const currentValue = String(form.fields?.step_1?.[fieldName] ?? '').trim()
    const extractedValue = gisAutofillValue(fieldName)

    return currentValue !== '' && extractedValue !== ''
  })
  const extractedStep3 = gisStep3Autofill.value
  const hasStep3Overlaps = extractedStep3
    ? (
        ['authorized_capital_stock', 'subscribed_capital_stock', 'paid_up_capital_stock', 'percentage_foreign_equity', 'total_subscribed_capital', 'total_paid_up_capital'] as const
      ).some((fieldName) => hasValue(form.fields?.step_3?.[fieldName]) && hasValue(extractedStep3[fieldName]))
      || (Object.keys(GIS_STEP3_ROW_LIMITS) as Array<keyof typeof GIS_STEP3_ROW_LIMITS>).some((fieldName) => {
        const maxRows = GIS_STEP3_ROW_LIMITS[fieldName]
        const currentRows = Array.isArray(form.fields?.step_3?.[fieldName]) ? form.fields.step_3[fieldName] : defaultCapitalRows(maxRows)
        const incomingRows = normalizeStep3Rows(extractedStep3[fieldName], maxRows)

        return incomingRows.some((incomingRow, index) => rowHasAnyValue(currentRows[index]) && rowHasAnyValue(incomingRow))
      })
    : false
  const hasOverlaps = hasStep1Overlaps || hasStep3Overlaps

  requestActionConfirmation(
    'Apply Extracted Data',
    hasOverlaps
      ? 'Some fields already have values. Applying extracted data will overwrite those values.'
      : 'Apply extracted data to GIS fields?',
    'Apply',
    () => applyGisAutofillDefaults(true),
  )
}

const gisFieldNeedsManualInput = (fieldName: GisStep1AutofillField): boolean => {
  if (!props.gisAutofill?.has_uploaded_sources) return false

  const currentValue = String(form.fields?.step_1?.[fieldName] ?? '').trim()
  if (currentValue !== '') return false

  return gisAutofillMissingFields.value.has(fieldName)
}

const gisFieldClass = (fieldName: GisStep1AutofillField): string => (
  gisFieldNeedsManualInput(fieldName)
    ? 'border-[#F97316] ring-1 ring-[#F97316]/30'
    : ''
)

type AppointmentAutofillField =
  | 'corporate_tin'
  | 'complete_business_address'
  | 'business_trade_name'
  | 'date_of_registration'
  | 'sec_registration_number'
  | 'corporate_name'
  | 'email_address'
  | 'primary_purpose_activity'

const APPOINTMENT_AUTOFILL_FIELDS: AppointmentAutofillField[] = [
  'corporate_tin',
  'complete_business_address',
  'business_trade_name',
  'date_of_registration',
  'sec_registration_number',
  'corporate_name',
  'email_address',
  'primary_purpose_activity',
]

const appointmentMissingFields = computed(() => new Set(props.appointmentAutofill?.missing_fields ?? []))

const appointmentAutofillValue = (fieldName: AppointmentAutofillField): string => {
  const source = props.appointmentAutofill ?? {}

  return String(source[fieldName] ?? '').trim()
}

const applyAppointmentAutofillField = (fieldName: AppointmentAutofillField, force = false) => {
  if (activeType.value !== 'appointment_form_opc') return

  const currentValue = String(form.fields?.[fieldName] ?? '').trim()
  if (!force && currentValue !== '') return

  const value = appointmentAutofillValue(fieldName)
  if (value === '') return

  form.fields[fieldName] = value
}

const applyAppointmentAutofillDefaults = (force = false) => {
  if (activeType.value !== 'appointment_form_opc') return

  APPOINTMENT_AUTOFILL_FIELDS.forEach((fieldName) => {
    applyAppointmentAutofillField(fieldName, force)
  })
}

const applyAppointmentAutofillWithConfirmation = () => {
  if (activeType.value !== 'appointment_form_opc') return

  const hasOverlaps = APPOINTMENT_AUTOFILL_FIELDS.some((fieldName) => {
    const currentValue = String(form.fields?.[fieldName] ?? '').trim()
    const extractedValue = appointmentAutofillValue(fieldName)

    return currentValue !== '' && extractedValue !== ''
  })

  requestActionConfirmation(
    'Apply Extracted Data',
    hasOverlaps
      ? 'Some fields already have values. Applying extracted data will overwrite those values.'
      : 'Apply extracted data to Appointment Form - OPC fields?',
    'Apply',
    () => applyAppointmentAutofillDefaults(true),
  )
}

const setAppointmentFieldsEditable = (editable: boolean) => {
  appointmentFieldsLocked.value = !editable
}

const appointmentFieldNeedsManualInput = (fieldName: AppointmentAutofillField): boolean => {
  if (!props.appointmentAutofill?.has_uploaded_sources) return false

  const currentValue = String(form.fields?.[fieldName] ?? '').trim()
  if (currentValue !== '') return false

  return appointmentMissingFields.value.has(fieldName)
}

const appointmentFieldClass = (fieldName: AppointmentAutofillField): string => (
  appointmentFieldNeedsManualInput(fieldName)
    ? 'border-[#DC2626] ring-1 ring-[#DC2626]/30'
    : ''
)

const createGisStockholderRow = (no: number) => ({
  no,
  name_address: '',
  nationality: '',
  share_type: '',
  number_of_shares: '',
  amount_subscribed: '',
  percent_ownership: '',
  amount_paid: '',
  tin: '',
})

const sanitizeGisStockholderRowsForSubmit = (): Record<string, string>[] => {
  const rows = Array.isArray(form.fields?.step_5?.rows) ? form.fields.step_5.rows : []

  return rows
    .map((row: Record<string, unknown>, index: number) => ({
      no: String(index + 1),
      name_address: String(row?.name_address ?? '').trim(),
      nationality: String(row?.nationality ?? '').trim(),
      share_type: String(row?.share_type ?? '').trim(),
      number_of_shares: String(row?.number_of_shares ?? '').trim(),
      amount_subscribed: String(row?.amount_subscribed ?? '').trim(),
      percent_ownership: String(row?.percent_ownership ?? '').trim(),
      amount_paid: String(row?.amount_paid ?? '').trim(),
      tin: String(row?.tin ?? '').trim(),
    }))
}

const reorderGisStockholderRows = () => {
  const rows = Array.isArray(form.fields?.step_5?.rows) ? form.fields.step_5.rows : []
  rows.forEach((row: Record<string, unknown>, index: number) => {
    row.no = index + 1
  })
}

const addGisStockholderRow = () => {
  const rows = Array.isArray(form.fields?.step_5?.rows) ? form.fields.step_5.rows : []
  const nextNo = rows.length + 1
  form.fields.step_5.rows = [...rows, createGisStockholderRow(nextNo)]
}

const removeGisStockholderRow = (index: number) => {
  const rows = Array.isArray(form.fields?.step_5?.rows) ? form.fields.step_5.rows : []
  if (rows.length <= MIN_GIS_STOCKHOLDER_ROWS) return

  requestActionConfirmation(
    'Delete Stockholder',
    `Delete Stockholder #${index + 1}?`,
    'Delete',
    () => {
      form.fields.step_5.rows = rows.filter((_: unknown, rowIndex: number) => rowIndex !== index)
      reorderGisStockholderRows()
      toast.success('Stockholder row deleted.')
    },
  )
}

function requestActionConfirmation(
  title: string,
  description: string,
  label: string,
  onConfirm: () => void,
) {
  actionConfirmTitle.value = title
  actionConfirmDescription.value = description
  actionConfirmLabel.value = label
  actionConfirmHandler.value = onConfirm
  actionConfirmOpen.value = true
}

function closeActionConfirmation() {
  actionConfirmOpen.value = false
  actionConfirmHandler.value = null
}

function confirmAction() {
  const handler = actionConfirmHandler.value
  closeActionConfirmation()
  handler?.()
}

const formatDateTime = (value: string | null) => {
  if (!value) return 'n/a'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleString('en-PH', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  })
}

const selectedGeneratedDocuments = computed(() => {
  return props.generatedDocuments.filter((document) => selectedDocumentIds.value.includes(document.id))
})

const allGeneratedSelected = computed(() => {
  return props.generatedDocuments.length > 0
    && selectedGeneratedDocuments.value.length === props.generatedDocuments.length
})

const toggleAllGenerated = (checked: boolean) => {
  selectedDocumentIds.value = checked ? props.generatedDocuments.map((document) => document.id) : []
}

const toggleGeneratedDocument = (id: string, checked: boolean) => {
  if (checked) {
    if (!selectedDocumentIds.value.includes(id)) {
      selectedDocumentIds.value = [...selectedDocumentIds.value, id]
    }

    return
  }

  selectedDocumentIds.value = selectedDocumentIds.value.filter((value) => value !== id)
}

const sendGeneratedDocumentsByEmail = () => {
  if (selectedGeneratedDocuments.value.length === 0) return

  sendEmailForm.sections = []
  sendEmailForm.document_ids = selectedGeneratedDocuments.value.map((document) => document.id)

  sendEmailForm.post(`/admin/registration/${props.registrationId}/pdfs/send-email`, {
    preserveScroll: true,
    onSuccess: () => {
      selectedDocumentIds.value = []
      sendEmailForm.reset()
      toast.success('Selected generated PDF documents were sent to the registration email.')
    },
    onError: (errors) => {
      const firstMessage = Object.values(errors).find((value) => typeof value === 'string')
      toast.error((firstMessage as string | undefined) ?? 'Failed to send selected generated PDF documents.')
    },
  })
}

const generateUrl = computed(() => {
  if (!activeType.value) return '#'

  return `/admin/registration/${props.registrationId}/documents/${activeType.value}/generate`
})

const previewUrl = computed(() => {
  if (!activeType.value) return '#'

  return `/admin/registration/${props.registrationId}/documents/${activeType.value}/preview`
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
  if (!isBlank(fields.doc_no) && !isDigitsOnly(fields.doc_no)) errors.push('Doc No. must contain numbers only.')
  if (!isBlank(fields.page_no) && !isDigitsOnly(fields.page_no)) errors.push('Page No. must contain numbers only.')
  if (!isBlank(fields.book_no) && !isDigitsOnly(fields.book_no)) errors.push('Book No. must contain numbers only.')
  if (!isBlank(fields.series) && !isDigitsOnly(fields.series)) errors.push('Series must contain numbers only.')

  return errors
}

const validateAppointmentForm = (): string[] => {
  const errors: string[] = []
  const fields = form.fields ?? {}

  if (isBlank(fields.for_the_year)) errors.push('For The Year is required.')
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
  if (isBlank(fields.sworn_place)) errors.push('Subscribed and sworn place is required.')
  if (isBlank(fields.sworn_date) || !isIsoDate(fields.sworn_date)) errors.push('Subscribed and sworn date is required.')

  const officers = Array.isArray(fields.officers) ? fields.officers : []
  if (officers.length !== 3) {
    errors.push('Exactly 3 officers are required: President, Treasurer, Corporate Secretary.')
  } else {
    officers.forEach((officer, index) => {
      const row = index + 1
      if (isBlank(officer.position)) errors.push(`Officer ${row} position is required.`)
      if (isBlank(officer.name_and_residential_address)) errors.push(`Officer ${row} Name & Residential Address is required.`)
      if (isBlank(officer.nationality)) errors.push(`Officer ${row} Nationality is required.`)
      if (isBlank(officer.gender)) errors.push(`Officer ${row} Gender is required.`)
      if (isBlank(officer.tin)) errors.push(`Officer ${row} TIN is required.`)
      if (!isBlank(officer.tin) && !isTinFormat(String(officer.tin))) errors.push(`Officer ${row} TIN must be in 000-000-000-000 format.`)
    })
  }

  return errors
}

const validateSecCertBankForm = (): string[] => {
  const errors: string[] = []
  const fields = form.fields ?? {}
  const openingSignatories = sanitizeBankSignatoryRows(fields.authorized_signatories_for_opening)
  const transactingSignatories = sanitizeBankSignatoryRows(fields.authorized_signatories_for_transacting)

  if (isBlank(fields.secretary_name)) errors.push('Name of Secretary is required.')
  if (isBlank(fields.secretary_address)) errors.push('Address of Secretary is required.')
  if (isBlank(fields.corporation_name)) errors.push('Corporation Name is required.')
  if (isBlank(fields.principal_address)) errors.push('Principal Address is required.')
  if (isBlank(fields.bank_name)) errors.push('Bank Name is required.')
  if (isBlank(fields.branch)) errors.push('Branch is required.')
  if (isBlank(fields.meeting_date)) {
    errors.push('Meeting Date is required.')
  } else if (!isIsoDate(fields.meeting_date)) {
    errors.push('Meeting Date must be a valid date.')
  }
  openingSignatories.forEach((row, index) => {
    const rowNo = index + 1
    if (isBlank(row.name)) errors.push(`Authorized Signatory for Opening ${rowNo} Name is required.`)
    if (isBlank(row.position)) errors.push(`Authorized Signatory for Opening ${rowNo} Position is required.`)
  })
  transactingSignatories.forEach((row, index) => {
    const rowNo = index + 1
    if (isBlank(row.name)) errors.push(`Authorized Signatory for Transacting ${rowNo} Name is required.`)
    if (isBlank(row.position)) errors.push(`Authorized Signatory for Transacting ${rowNo} Position is required.`)
  })
  if (isBlank(fields.corporate_secretary_name)) errors.push('Corporate Secretary Name is required.')
  if (isBlank(fields.certificate_date)) {
    errors.push('Certificate Date is required.')
  } else if (!isIsoDate(fields.certificate_date)) {
    errors.push('Certificate Date must be a valid date.')
  }
  if (isBlank(fields.certificate_location)) errors.push('Certificate Location is required.')

  return errors
}

const validateAllGisSteps = (): string[] => {
  const errors: string[] = []
  const fields = form.fields ?? {}
  const step1 = fields.step_1 ?? {}
  const step2 = fields.step_2 ?? {}
  const step3 = fields.step_3 ?? {}
  const step4 = Array.isArray(fields.step_4) ? fields.step_4 : []
  const step5 = fields.step_5 ?? {}
  const step5Rows = Array.isArray(step5.rows) ? step5.rows : []
  const step9 = fields.step_9 ?? {}

  if (isBlank(step1.corporate_name)) errors.push('Step 1: Corporate Name is required.')
  if (isBlank(step1.sec_registration_number)) errors.push('Step 1: SEC Registration Number is required.')
  if (isBlank(step1.date_registered)) errors.push('Step 1: Date Registered is required.')
  if (isBlank(step1.fiscal_year_end)) errors.push('Step 1: Fiscal Year End is required.')
  if (isBlank(step1.principal_office_address)) errors.push('Step 1: Principal Office Address is required.')
  if (isBlank(step1.business_address)) errors.push('Step 1: Business Address is required.')
  if (isBlank(step1.email) || !isValidEmail(String(step1.email ?? ''))) errors.push('Step 1: Valid Email is required.')
  if (!isBlank(step1.alternate_email) && !isValidEmail(String(step1.alternate_email ?? ''))) errors.push('Step 1: Alternate Email must be valid.')
  if (isBlank(step1.official_mobile)) errors.push('Step 1: Official Mobile Number is required.')
  if (!isBlank(step1.official_mobile) && !isPhoneLike(step1.official_mobile)) errors.push('Step 1: Official Mobile Number format is invalid.')
  if (!isBlank(step1.alternate_mobile) && !isPhoneLike(step1.alternate_mobile)) errors.push('Step 1: Alternate Mobile Number format is invalid.')
  if (!isBlank(step1.telephone) && !isPhoneLike(step1.telephone)) errors.push('Step 1: Telephone format is invalid.')
  if (isBlank(step1.primary_purpose)) errors.push('Step 1: Primary Purpose/Activity is required.')
  if (isBlank(step1.industry_classification)) errors.push('Step 1: Industry Classification is required.')
  if (!isBlank(step1.meeting_date_annual) && !isIsoDate(step1.meeting_date_annual)) errors.push('Step 1: Annual Meeting Date must be a valid date.')
  if (!isBlank(step1.meeting_date_actual) && !isIsoDate(step1.meeting_date_actual)) errors.push('Step 1: Actual Annual Meeting Date must be a valid date.')

  if (typeof step2.amla_covered !== 'boolean') errors.push('Step 2: AMLA Covered must be yes/no.')
  if (typeof step2.cdd_complied !== 'boolean') errors.push('Step 2: CDD/KYC compliance must be yes/no.')

  if (isBlank(step3.authorized_capital_stock)) errors.push('Step 3: Authorized Capital Stock is required.')
  if (!isBlank(step3.authorized_capital_stock) && !isNonNegativeNumber(step3.authorized_capital_stock)) errors.push('Step 3: Authorized Capital Stock must be a valid number.')
  if (isBlank(step3.subscribed_capital_stock)) errors.push('Step 3: Subscribed Capital Stock is required.')
  if (!isBlank(step3.subscribed_capital_stock) && !isNonNegativeNumber(step3.subscribed_capital_stock)) errors.push('Step 3: Subscribed Capital Stock must be a valid number.')
  if (isBlank(step3.paid_up_capital_stock)) errors.push('Step 3: Paid-Up Capital Stock is required.')
  if (!isBlank(step3.paid_up_capital_stock) && !isNonNegativeNumber(step3.paid_up_capital_stock)) errors.push('Step 3: Paid-Up Capital Stock must be a valid number.')

  if (step4.length === 0) {
    errors.push('Step 4: At least one director row is required.')
  } else {
    const filledStep4Rows = step4.filter((row) => Object.values(row ?? {}).some((value) => !isBlank(value)))
    if (filledStep4Rows.length === 0) {
      errors.push('Step 4: Please provide at least one Director/Officer row.')
    }
    filledStep4Rows.forEach((row, index) => {
      const i = index + 1
      if (isBlank(row.name)) errors.push(`Step 4 Row ${i}: Name is required.`)
      if (isBlank(row.nationality)) errors.push(`Step 4 Row ${i}: Nationality is required.`)
      if (!isBlank(row.tin) && !isTinFormat(String(row.tin))) errors.push(`Step 4 Row ${i}: TIN must be in 000-000-000-000 format.`)
    })
  }

  if (step5Rows.length < MIN_GIS_STOCKHOLDER_ROWS) {
    errors.push(`Step 5: At least ${MIN_GIS_STOCKHOLDER_ROWS} stockholder rows are required.`)
  } else {
    step5Rows.forEach((row: Record<string, unknown>, index: number) => {
      const i = index + 1
      if (isBlank(row.name_address)) errors.push(`Step 5 Row ${i}: Name/Address is required.`)
      if (isBlank(row.nationality)) errors.push(`Step 5 Row ${i}: Nationality is required.`)
      if (!isBlank(row.tin) && !isTinFormat(String(row.tin))) errors.push(`Step 5 Row ${i}: TIN must be in 000-000-000-000 format.`)
    })
  }

  if (isBlank(step9.done_date)) errors.push('Step 7: Done this date is required.')
  if (isBlank(step9.done_place)) errors.push('Step 7: Done place is required.')

  return errors
}

const validateCurrentGisStep = (): string | null => {
  if (!isGis.value) return null

  if (gisStep.value === 1) {
    const step = form.fields.step_1 ?? {}
    if (!step.corporate_name || !step.sec_registration_number || !step.date_registered || !step.fiscal_year_end || !step.principal_office_address || !step.business_address || !step.email || !step.official_mobile || !step.primary_purpose || !step.industry_classification) {
      return 'Please complete all required corporate information fields.'
    }
  }

  if (gisStep.value === 3) {
    const step = form.fields.step_3 ?? {}
    if (step.authorized_capital_stock === '' || step.subscribed_capital_stock === '' || step.paid_up_capital_stock === '') {
      return 'Please complete capital structure values.'
    }
  }

  if (gisStep.value === 5) {
    const step = form.fields.step_5 ?? {}
    if (!step.total_stockholders || !step.total_assets) {
      return 'Please complete stockholder totals before continuing.'
    }
  }

  if (gisStep.value === GIS_TOTAL_PAGES) {
    const step = form.fields.step_9 ?? {}
    if (!step.done_date || !step.done_place) {
      return 'Please provide the done-this date and done place before generating the GIS PDF.'
    }
  }

  return null
}

const nextGisStep = () => {
  // GIS validation is temporarily disabled per request.
  void validateCurrentGisStep
  gisStep.value = Math.min(GIS_TOTAL_PAGES, gisStep.value + 1)
}

const previousGisStep = () => {
  gisStep.value = Math.max(1, gisStep.value - 1)
}

const activeValidationErrors = (): string[] => {
  if (activeType.value === 'secretary_certificate') return validateSecretaryForm()
  if (activeType.value === 'secretary_certificate_bank') return validateSecCertBankForm()
  if (activeType.value === 'appointment_form_opc') return []

  return []
}

const submitFieldsPayload = (): Record<string, any> => {
  if (activeType.value === 'secretary_certificate_bank') {
    return {
      ...form.fields,
      authorized_signatories_for_opening: sanitizeBankSignatoryRows(form.fields.authorized_signatories_for_opening),
      authorized_signatories_for_transacting: sanitizeBankSignatoryRows(form.fields.authorized_signatories_for_transacting),
    }
  }

  if (activeType.value !== 'gis_stock_corporation') {
    return { ...form.fields }
  }

  return {
    ...form.fields,
    step_5: {
      ...(form.fields.step_5 ?? {}),
      rows: sanitizeGisStockholderRowsForSubmit(),
    },
  }
}

const openPreview = () => {
  if (!activeType.value) return

  const errors = activeValidationErrors()
  if (errors.length > 0) {
    toast.error(errors[0])
    return
  }

  const fieldsPayload = submitFieldsPayload()
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''

  const previewForm = document.createElement('form')
  previewForm.method = 'POST'
  previewForm.action = previewUrl.value
  previewForm.target = '_blank'
  previewForm.style.display = 'none'

  const csrfInput = document.createElement('input')
  csrfInput.type = 'hidden'
  csrfInput.name = '_token'
  csrfInput.value = csrf
  previewForm.appendChild(csrfInput)

  const fieldsJsonInput = document.createElement('input')
  fieldsJsonInput.type = 'hidden'
  fieldsJsonInput.name = 'fields_json'
  fieldsJsonInput.value = JSON.stringify(fieldsPayload)
  previewForm.appendChild(fieldsJsonInput)

  document.body.appendChild(previewForm)
  previewForm.submit()
  previewForm.remove()
}

const submit = () => {
  if (!activeType.value) return
  // GIS validation is temporarily disabled per request.
  void validateAllGisSteps
  // Appointment validation is temporarily disabled per request.
  void validateAppointmentForm

  const errors = activeValidationErrors()

  if (errors.length > 0) {
    toast.error(errors[0])
    return
  }

  form.fields = submitFieldsPayload()

  form.fields_json = JSON.stringify(form.fields)

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
      <div class="mb-3 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <FileText class="h-4 w-4 text-[#2563EB] dark:text-[#60A5FA]" />
          <h3 class="font-['Space_Grotesk'] text-lg font-semibold text-[#0B1F3A] dark:text-[#E6F1FF]">Generated Document PDFs</h3>
        </div>
        <Button
          v-if="selectedGeneratedDocuments.length > 0"
          type="button"
          variant="outline"
          class="cursor-pointer"
          :disabled="sendEmailForm.processing"
          @click="sendGeneratedDocumentsByEmail"
        >
          <Mail class="mr-2 h-4 w-4" />
          Send Email ({{ selectedGeneratedDocuments.length }})
        </Button>
      </div>

      <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-[#FFFFFF] shadow-sm dark:border-[#1E3A5F] dark:bg-[#12325B]">
        <table class="min-w-full text-sm">
          <thead class="bg-[#EFF6FF] text-left text-[#475569] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
            <tr>
              <th class="w-10 px-4 py-3">
                <Checkbox
                  :model-value="allGeneratedSelected"
                  :disabled="generatedDocuments.length === 0"
                  @update:model-value="(checked) => toggleAllGenerated(Boolean(checked))"
                />
              </th>
              <th class="px-4 py-3">Document</th>
              <th class="px-4 py-3">Generated By</th>
              <th class="px-4 py-3">Created At</th>
              <th class="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="generatedDocuments.length === 0">
              <td colspan="5" class="px-4 py-4 text-[#64748B] dark:text-[#9FB3C8]">No generated documents yet.</td>
            </tr>
            <tr
              v-for="row in generatedDocuments"
              :key="row.id"
              class="border-t border-[#E2E8F0] dark:border-[#1E3A5F]"
            >
              <td class="px-4 py-3">
                <Checkbox
                  :model-value="selectedDocumentIds.includes(row.id)"
                  @update:model-value="(checked) => toggleGeneratedDocument(row.id, Boolean(checked))"
                />
              </td>
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
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="icon" class="h-8 w-8">
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-40">
                      <DropdownMenuItem class="text-destructive" @click="promptDelete(row.delete_url)">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
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
        <div class="flex items-center gap-2 md:col-span-2">
          <Checkbox
            id="choose-another-secretary"
            :model-value="!secretaryUseDefaultValues"
            @update:model-value="(checked) => useSecretaryDefaults(!Boolean(checked))"
          />
          <Label for="choose-another-secretary">Choose another</Label>
        </div>
        <div class="space-y-2">
          <Label>Secretary Name</Label>
          <Input
            v-model="form.fields.secretary_name"
            :readonly="secretaryUseDefaultValues"
          />
        </div>
        <div class="space-y-2">
          <Label>Corporation Name</Label>
          <Input v-model="form.fields.corporation_name" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Secretary Address</Label>
          <textarea
            v-model="form.fields.secretary_address"
            :readonly="secretaryUseDefaultValues"
            class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
          />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Signature Data URI</Label>
          <textarea
            v-model="form.fields.secretary_signature_data_uri"
            :readonly="secretaryUseDefaultValues"
            class="min-h-28 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            placeholder="Signature data (auto-fills with secretary name when custom mode)."
          />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Corporation Address</Label>
          <textarea v-model="form.fields.corporation_address" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
        </div>
        <div class="space-y-2">
          <Label>Authorized Person Name</Label>
          <Input v-model="form.fields.authorized_person_name" readonly />
        </div>
        <div class="space-y-2">
          <Label>Signing Date</Label>
          <Input v-model="form.fields.signing_date" type="date" />
        </div>
        <div class="space-y-2">
          <Label>TIN (000-000-000-000)</Label>
          <Input
            v-model="form.fields.tin"
            :readonly="secretaryUseDefaultValues"
          />
        </div>
        <div class="space-y-2">
          <Label>Doc No.</Label>
          <Input v-model="form.fields.doc_no" type="number" min="0" step="1" readonly />
        </div>
        <div class="space-y-2">
          <Label>Page No.</Label>
          <Input v-model="form.fields.page_no" type="number" min="0" step="1" readonly />
        </div>
        <div class="space-y-2">
          <Label>Book No.</Label>
          <Input v-model="form.fields.book_no" type="number" min="0" step="1" readonly />
        </div>
        <div class="space-y-2">
          <Label>Series</Label>
          <Input v-model="form.fields.series" type="number" min="0" step="1" readonly />
        </div>
      </div>

      <div v-else-if="activeType === 'secretary_certificate_bank'" class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <Label>Name of Secretary</Label>
          <Input v-model="form.fields.secretary_name" />
        </div>
        <div class="space-y-2">
          <Label>Address of Secretary</Label>
          <Input v-model="form.fields.secretary_address" />
        </div>
        <div class="space-y-2">
          <Label>Corporation Name</Label>
          <Input v-model="form.fields.corporation_name" />
        </div>
        <div class="space-y-2">
          <Label>Principal Address</Label>
          <Input v-model="form.fields.principal_address" />
        </div>
        <div class="space-y-2">
          <Label>Bank Name</Label>
          <Input v-model="form.fields.bank_name" />
        </div>
        <div class="space-y-2">
          <Label>Branch</Label>
          <Input v-model="form.fields.branch" />
        </div>
        <div class="space-y-2">
          <Label>Meeting Date</Label>
          <Input v-model="form.fields.meeting_date" type="date" />
        </div>
        <div class="space-y-2">
          <Label>Certificate Date</Label>
          <Input v-model="form.fields.certificate_date" type="date" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Authorized Signatory for Opening</Label>
          <div class="space-y-3">
            <div
              v-for="(row, index) in form.fields.authorized_signatories_for_opening"
              :key="`authorized-opening-${index}`"
              class="grid gap-2 md:grid-cols-2"
            >
              <Input v-model="row.name" :placeholder="`Name ${index + 1}`" />
              <Input v-model="row.position" :placeholder="`Position ${index + 1}`" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button type="button" variant="outline" size="sm" @click="addBankSignatoryRow('authorized_signatories_for_opening')">
              + Add Another
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              :disabled="form.fields.authorized_signatories_for_opening.length <= 1"
              @click="removeBankSignatoryRow('authorized_signatories_for_opening')"
            >
              Delete
            </Button>
          </div>
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Authorized Signatory for Transacting</Label>
          <div class="space-y-3">
            <div
              v-for="(row, index) in form.fields.authorized_signatories_for_transacting"
              :key="`authorized-transacting-${index}`"
              class="grid gap-2 md:grid-cols-2"
            >
              <Input v-model="row.name" :placeholder="`Name ${index + 1}`" />
              <Input v-model="row.position" :placeholder="`Position ${index + 1}`" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button type="button" variant="outline" size="sm" @click="addBankSignatoryRow('authorized_signatories_for_transacting')">
              + Add Another
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              :disabled="form.fields.authorized_signatories_for_transacting.length <= 1"
              @click="removeBankSignatoryRow('authorized_signatories_for_transacting')"
            >
              Delete
            </Button>
          </div>
        </div>
        <div class="space-y-2">
          <Label>Corporate Secretary Name</Label>
          <Input v-model="form.fields.corporate_secretary_name" />
        </div>
        <div class="space-y-2">
          <Label>Certificate Location</Label>
          <Input v-model="form.fields.certificate_location" />
        </div>
      </div>

      <div v-else-if="activeType === 'appointment_form_opc'" class="grid gap-4 md:grid-cols-2">
        <div v-if="props.appointmentAutofill?.has_uploaded_sources" class="md:col-span-2 rounded-md border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2 text-xs text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
          <div class="flex items-center justify-between gap-2">
            <span>Extracted data detected from uploaded required documents.</span>
            <Button type="button" size="sm" variant="outline" @click="applyAppointmentAutofillWithConfirmation">
              Apply extracted data
            </Button>
          </div>
          <div class="mt-2 flex items-center gap-2">
            <Checkbox
              id="appointment-unlock-edit"
              :model-value="!appointmentFieldsLocked"
              @update:model-value="(checked) => setAppointmentFieldsEditable(Boolean(checked))"
            />
            <Label for="appointment-unlock-edit">Unlock extracted fields for manual edit</Label>
          </div>
          <p v-if="(props.appointmentAutofill?.missing_fields?.length ?? 0) > 0" class="mt-1 text-[#B91C1C]">
            Some fields were not found and need manual input.
          </p>
        </div>
        <div class="space-y-2">
          <Label>For The Year</Label>
          <Input v-model="form.fields.for_the_year" readonly />
        </div>
        <div class="space-y-2">
          <Label>Corporate Name</Label>
            <Input
              v-model="form.fields.corporate_name"
              :readonly="appointmentAutofillReadonly"
              :class="appointmentFieldClass('corporate_name')"
              @focus="applyAppointmentAutofillField('corporate_name')"
            />
        </div>
        <div class="space-y-2">
          <Label>Date of Registration</Label>
            <Input
              v-model="form.fields.date_of_registration"
              type="date"
              :readonly="appointmentAutofillReadonly"
              :class="appointmentFieldClass('date_of_registration')"
              @focus="applyAppointmentAutofillField('date_of_registration')"
            />
        </div>
        <div class="space-y-2">
          <Label>Business / Trade Name</Label>
            <Input
              v-model="form.fields.business_trade_name"
              :readonly="appointmentAutofillReadonly"
              :class="appointmentFieldClass('business_trade_name')"
              @focus="applyAppointmentAutofillField('business_trade_name')"
            />
        </div>
        <div class="space-y-2">
          <Label>Fiscal Year End</Label>
          <Input v-model="form.fields.fiscal_year_end" type="date" readonly />
        </div>
        <div class="space-y-2">
          <Label>SEC Registration Number</Label>
            <Input
              v-model="form.fields.sec_registration_number"
              :readonly="appointmentAutofillReadonly"
              :class="appointmentFieldClass('sec_registration_number')"
              @focus="applyAppointmentAutofillField('sec_registration_number')"
            />
        </div>
        <div class="space-y-2">
          <Label>Email Address</Label>
            <Input
              v-model="form.fields.email_address"
              type="email"
              :readonly="appointmentAutofillReadonly"
              :class="appointmentFieldClass('email_address')"
              @focus="applyAppointmentAutofillField('email_address')"
            />
        </div>
        <div class="space-y-2">
          <Label>Corporate TIN</Label>
            <Input
              v-model="form.fields.corporate_tin"
              :readonly="appointmentAutofillReadonly"
              :class="appointmentFieldClass('corporate_tin')"
              @focus="applyAppointmentAutofillField('corporate_tin')"
            />
        </div>
        <div class="space-y-2">
          <Label>Telephone Number</Label>
          <Input v-model="form.fields.telephone_number" />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Complete Business Address</Label>
          <textarea
            v-model="form.fields.complete_business_address"
            :readonly="appointmentAutofillReadonly"
            :class="`min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ${appointmentFieldClass('complete_business_address')}`"
            @focus="applyAppointmentAutofillField('complete_business_address')"
          />
        </div>
        <div class="space-y-2">
          <Label>Primary Purpose / Activity / Industry Presently Engaged In</Label>
          <textarea
            v-model="form.fields.primary_purpose_activity"
            :readonly="appointmentAutofillReadonly"
            :class="`min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ${appointmentFieldClass('primary_purpose_activity')}`"
            @focus="applyAppointmentAutofillField('primary_purpose_activity')"
          />
        </div>

        <div class="space-y-2 md:col-span-2">
          <Label>Officers</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-5">
            <div>Officer / Position</div>
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
              <Input v-model="officer.position" readonly />
              <Input v-model="officer.name_and_residential_address" />
              <Input v-model="officer.nationality" readonly />
              <Input v-model="officer.gender" />
              <Input v-model="officer.tin" />
            </div>
          </div>
        </div>

        <div class="space-y-2">
          <Label>Certifier Name</Label>
          <Input v-model="form.fields.certifier_name" readonly />
        </div>
        <div class="space-y-2">
          <Label>Certifier TIN (000-000-000-000)</Label>
          <Input v-model="form.fields.certifier_tin" readonly />
        </div>
        <div class="space-y-2">
          <Label>Subscribed and Sworn Place</Label>
          <Input v-model="form.fields.sworn_place" readonly />
        </div>
        <div class="space-y-2">
          <Label>Subscribed and Sworn Date</Label>
          <Input v-model="form.fields.sworn_date" type="date" readonly />
        </div>
        <div class="space-y-2 md:col-span-2">
          <Label>Competent Evidence of Identity</Label>
          <Input v-model="form.fields.competent_evidence" readonly />
        </div>
        <div class="space-y-2">
          <Label>Issued At</Label>
          <Input v-model="form.fields.issued_at" readonly />
        </div>
        <div class="space-y-2">
          <Label>Issued On</Label>
          <Input v-model="form.fields.issued_on" type="date" readonly />
        </div>
      </div>

      <div v-else-if="activeType === 'gis_stock_corporation'" class="space-y-4">
        <div class="rounded-md border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2 text-sm dark:border-[#1E3A5F] dark:bg-[#0F2747]">
          GIS Page {{ gisStep }} of {{ GIS_TOTAL_PAGES }}
        </div>

        <div v-if="gisStep === 1" class="grid gap-4 md:grid-cols-2">
          <div v-if="props.gisAutofill?.has_uploaded_sources" class="md:col-span-2 rounded-md border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2 text-xs text-[#475569] dark:border-[#1E3A5F] dark:bg-[#0F2747] dark:text-[#9FB3C8]">
            <div class="flex items-center justify-between gap-2">
              <span>Extracted data detected from uploaded required documents.</span>
              <Button type="button" size="sm" variant="outline" @click="applyGisAutofillWithConfirmation">
                Apply extracted data
              </Button>
            </div>
            <p v-if="(props.gisAutofill?.missing_fields?.length ?? 0) > 0" class="mt-1 text-[#C2410C]">
              Some fields were not found and need manual input.
            </p>
            <p v-if="!props.gisAutofill?.aoi_capital_stock_available" class="mt-1 text-[#B91C1C]">
              Articles of Incorporation is not uploaded. Upload it in Required Documents to autofill GIS Page 3 (Capital Stock).
            </p>
          </div>
          <div class="space-y-2">
            <Label>Corporate Name</Label>
            <Input v-model="form.fields.step_1.corporate_name" :class="gisFieldClass('corporate_name')" @focus="applyGisAutofillOnFocus('corporate_name')" />
          </div>
          <div class="space-y-2">
            <Label>Business / Trade Name</Label>
            <Input v-model="form.fields.step_1.business_trade_name" :class="gisFieldClass('business_trade_name')" @focus="applyGisAutofillOnFocus('business_trade_name')" />
          </div>
          <div class="space-y-2">
            <Label>SEC Registration Number</Label>
            <Input v-model="form.fields.step_1.sec_registration_number" :class="gisFieldClass('sec_registration_number')" @focus="applyGisAutofillOnFocus('sec_registration_number')" />
          </div>
          <div class="space-y-2">
            <Label>Date Registered</Label>
            <Input v-model="form.fields.step_1.date_registered" type="date" :class="gisFieldClass('date_registered')" @focus="applyGisAutofillOnFocus('date_registered')" />
          </div>
          <div class="space-y-2">
            <Label>Fiscal Year End</Label>
            <Input v-model="form.fields.step_1.fiscal_year_end" type="date" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Complete Principal Office Address</Label>
            <textarea v-model="form.fields.step_1.principal_office_address" :class="`min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ${gisFieldClass('principal_office_address')}`" @focus="applyGisAutofillOnFocus('principal_office_address')" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Complete Business Address</Label>
            <textarea v-model="form.fields.step_1.business_address" :class="`min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ${gisFieldClass('business_address')}`" @focus="applyGisAutofillOnFocus('business_address')" />
          </div>
          <div class="space-y-2">
            <Label>Official Email</Label>
            <Input v-model="form.fields.step_1.email" type="email" :class="gisFieldClass('email')" @focus="applyGisAutofillOnFocus('email')" />
          </div>
          <div class="space-y-2">
            <Label>Alternate Email</Label>
            <Input v-model="form.fields.step_1.alternate_email" type="email" :class="gisFieldClass('alternate_email')" @focus="applyGisAutofillOnFocus('alternate_email')" />
          </div>
          <div class="space-y-2">
            <Label>Corporate TIN</Label>
            <Input v-model="form.fields.step_1.corporate_tin" :class="gisFieldClass('corporate_tin')" @focus="applyGisAutofillOnFocus('corporate_tin')" />
          </div>
          <div class="space-y-2">
            <Label>Official Mobile Number</Label>
            <Input v-model="form.fields.step_1.official_mobile" type="tel" :class="gisFieldClass('official_mobile')" @focus="applyGisAutofillOnFocus('official_mobile')" />
          </div>
          <div class="space-y-2">
            <Label>Alternate Mobile Number</Label>
            <Input v-model="form.fields.step_1.alternate_mobile" type="tel" :class="gisFieldClass('alternate_mobile')" @focus="applyGisAutofillOnFocus('alternate_mobile')" />
          </div>
          <div class="space-y-2">
            <Label>Telephone</Label>
            <Input v-model="form.fields.step_1.telephone" type="tel" />
          </div>
          <div class="space-y-2">
            <Label>Website / URL Address</Label>
            <Input v-model="form.fields.step_1.website_url" />
          </div>
          <div class="space-y-2">
            <Label>Fax Number</Label>
            <Input v-model="form.fields.step_1.fax_number" />
          </div>
          <div class="space-y-2">
            <Label>Date of Annual Meeting per By-Laws</Label>
            <Input v-model="form.fields.step_1.meeting_date_annual" type="date" />
          </div>
          <div class="space-y-2">
            <Label>Actual Date of Annual Meeting</Label>
            <Input v-model="form.fields.step_1.meeting_date_actual" type="date" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Primary Purpose / Activity / Industry Engaged In</Label>
            <textarea v-model="form.fields.step_1.primary_purpose" :class="`min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ${gisFieldClass('primary_purpose')}`" @focus="applyGisAutofillOnFocus('primary_purpose')" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Industry Classification</Label>
            <Input v-model="form.fields.step_1.industry_classification" :class="gisFieldClass('industry_classification')" @focus="applyGisAutofillOnFocus('industry_classification')" />
          </div>
          <div class="space-y-2">
            <Label>Geographical Code</Label>
            <Input v-model="form.fields.step_1.geographical_code" />
          </div>
          <div class="space-y-2">
            <Label>External Auditor & Signing Partner</Label>
            <Input v-model="form.fields.step_1.external_auditor_name" />
          </div>
          <div class="space-y-2">
            <Label>SEC Accreditation No.</Label>
            <Input v-model="form.fields.step_1.sec_accreditation_number" />
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Intercompany Affiliation - Parent Company</Label>
            <div class="grid gap-2 md:grid-cols-3">
              <Input v-model="form.fields.step_1.intercompany_parent_company" placeholder="Parent Company" />
              <Input v-model="form.fields.step_1.intercompany_parent_sec_no" placeholder="SEC Reg No." />
              <Input v-model="form.fields.step_1.intercompany_parent_address" placeholder="Address" />
            </div>
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label>Intercompany Affiliation - Subsidiary/Affiliate</Label>
            <div class="grid gap-2 md:grid-cols-3">
              <Input v-model="form.fields.step_1.intercompany_subsidiary" placeholder="Subsidiary/Affiliate" />
              <Input v-model="form.fields.step_1.intercompany_subsidiary_sec_no" placeholder="SEC Reg No." />
              <Input v-model="form.fields.step_1.intercompany_subsidiary_address" placeholder="Address" />
            </div>
          </div>
        </div>

        <div v-else-if="gisStep === 2" class="space-y-3">
          <p class="text-sm font-medium">A. Is the corporation a covered person under AMLA?</p>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.fields.step_2.amla_covered" type="checkbox">
            Yes, covered under AMLA
          </label>

          <p class="text-sm font-medium">No. 1 - Financial Institutions under BSP</p>
          <div class="grid gap-2 md:grid-cols-2">
            <label v-for="option in amlaOptionGroups.one" :key="`one-${option.key}`" class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.one[option.key]" type="checkbox">
              {{ option.key }}. {{ option.label }}
            </label>
          </div>

          <p class="text-sm font-medium">No. 2 - Insurance Commission Regulated Entities</p>
          <div class="grid gap-2 md:grid-cols-2">
            <label v-for="option in amlaOptionGroups.two" :key="`two-${option.key}`" class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.two[option.key]" type="checkbox">
              {{ option.key }}. {{ option.label }}
            </label>
          </div>

          <p class="text-sm font-medium">No. 3 - SEC Regulated Entities</p>
          <div class="grid gap-2 md:grid-cols-2">
            <label v-for="option in amlaOptionGroups.three" :key="`three-${option.key}`" class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.three[option.key]" type="checkbox">
              {{ option.key }}. {{ option.label }}
            </label>
          </div>

          <p class="text-sm font-medium">No. 4 and 5 - Jewelry Dealers</p>
          <div class="grid gap-2 md:grid-cols-2">
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.four" type="checkbox">
              4. Jewelry dealers in precious metals
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.five" type="checkbox">
              5. Jewelry dealers in precious stones
            </label>
          </div>

          <p class="text-sm font-medium">No. 6 - Company Service Providers</p>
          <div class="grid gap-2 md:grid-cols-2">
            <label v-for="option in amlaOptionGroups.six" :key="`six-${option.key}`" class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.six[option.key]" type="checkbox">
              {{ option.key }}. {{ option.label }}
            </label>
          </div>

          <p class="text-sm font-medium">No. 7 - Persons who provide AMLA-covered services</p>
          <div class="grid gap-2 md:grid-cols-2">
            <label v-for="option in amlaOptionGroups.seven" :key="`seven-${option.key}`" class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.seven[option.key]" type="checkbox">
              {{ option.key }}. {{ option.label }}
            </label>
          </div>

          <p class="text-sm font-medium">No. 8</p>
          <div>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_2.amla_detailed.eight" type="checkbox">
              8. None of the above
            </label>
          </div>

          <p class="text-sm font-medium">B. Has the corporation complied with CDD/KYC requirements since last GIS?</p>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.fields.step_2.cdd_complied" type="checkbox">
            Yes, complied with CDD/KYC
          </label>
          <div class="space-y-2">
            <Label>Other AMLA Details</Label>
            <textarea v-model="form.fields.step_2.amla_other_details" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
          </div>
          <div class="space-y-2">
            <Label>Describe Nature of Business</Label>
            <textarea v-model="form.fields.step_2.nature_of_business" class="min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
          </div>
        </div>

        <div v-else-if="gisStep === 3" class="space-y-4">
          <div class="space-y-2">
            <Label>Corporate Name (from Page 1)</Label>
            <Input :model-value="form.fields.step_1.corporate_name || ''" readonly />
          </div>

          <div class="grid gap-4 md:grid-cols-3">
            <div class="space-y-2">
              <Label>Authorized Capital Stock (Total)</Label>
              <Input v-model="form.fields.step_3.authorized_capital_stock" type="number" />
            </div>
            <div class="space-y-2">
              <Label>Subscribed Capital Stock (Total)</Label>
              <Input v-model="form.fields.step_3.subscribed_capital_stock" type="number" />
            </div>
            <div class="space-y-2">
              <Label>Paid-Up Capital Stock (Total)</Label>
              <Input v-model="form.fields.step_3.paid_up_capital_stock" type="number" />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Authorized Capital Stock - Breakdown</Label>
            <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-4">
              <div>Type of Shares</div>
              <div>Number of Shares</div>
              <div>Par/Stated Value</div>
              <div>Amount</div>
            </div>
            <div v-for="(row, index) in form.fields.step_3.authorized_rows" :key="`auth-${index}`" class="grid gap-2 md:grid-cols-4">
              <Input v-model="row.type_of_shares" />
              <Input v-model="row.number_of_shares" />
              <Input v-model="row.par_or_stated_value" />
              <Input v-model="row.amount" />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Subscribed Capital - Filipino</Label>
            <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-7">
              <div>No. of Stockholders</div>
              <div>Type</div>
              <div>Number of Shares</div>
              <div>Public Shares</div>
              <div>Par/Stated</div>
              <div>Amount</div>
              <div>% Ownership</div>
            </div>
            <div v-for="(row, index) in form.fields.step_3.subscribed_filipino_rows" :key="`sub-fil-${index}`" class="grid gap-2 md:grid-cols-7">
              <Input v-model="row.no_of_stockholders" />
              <Input v-model="row.type_of_shares" />
              <Input v-model="row.number_of_shares" />
              <Input v-model="row.public_shares" />
              <Input v-model="row.par_or_stated_value" />
              <Input v-model="row.amount" />
              <Input v-model="row.ownership_percent" />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Subscribed Capital - Foreign</Label>
            <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-7">
              <div>No. of Stockholders</div>
              <div>Type</div>
              <div>Number of Shares</div>
              <div>Public Shares</div>
              <div>Par/Stated</div>
              <div>Amount</div>
              <div>% Ownership</div>
            </div>
            <div v-for="(row, index) in form.fields.step_3.subscribed_foreign_rows" :key="`sub-for-${index}`" class="grid gap-2 md:grid-cols-7">
              <Input v-model="row.no_of_stockholders" />
              <Input v-model="row.type_of_shares" />
              <Input v-model="row.number_of_shares" />
              <Input v-model="row.public_shares" />
              <Input v-model="row.par_or_stated_value" />
              <Input v-model="row.amount" />
              <Input v-model="row.ownership_percent" />
            </div>
            <div class="grid gap-2 md:grid-cols-2">
              <div class="space-y-2">
                <Label>Percentage of Foreign Equity</Label>
                <Input v-model="form.fields.step_3.percentage_foreign_equity" />
              </div>
              <div class="space-y-2">
                <Label>Total Subscribed Capital</Label>
                <Input v-model="form.fields.step_3.total_subscribed_capital" />
              </div>
            </div>
          </div>

          <div class="space-y-2">
            <Label>Paid-Up Capital - Filipino</Label>
            <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-6">
              <div>No. of Stockholders</div>
              <div>Type</div>
              <div>Number of Shares</div>
              <div>Par/Stated</div>
              <div>Amount</div>
              <div>% Ownership</div>
            </div>
            <div v-for="(row, index) in form.fields.step_3.paidup_filipino_rows" :key="`paid-fil-${index}`" class="grid gap-2 md:grid-cols-6">
              <Input v-model="row.no_of_stockholders" />
              <Input v-model="row.type_of_shares" />
              <Input v-model="row.number_of_shares" />
              <Input v-model="row.par_or_stated_value" />
              <Input v-model="row.amount" />
              <Input v-model="row.ownership_percent" />
            </div>
          </div>

          <div class="space-y-2">
            <Label>Paid-Up Capital - Foreign</Label>
            <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-6">
              <div>No. of Stockholders</div>
              <div>Type</div>
              <div>Number of Shares</div>
              <div>Par/Stated</div>
              <div>Amount</div>
              <div>% Ownership</div>
            </div>
            <div v-for="(row, index) in form.fields.step_3.paidup_foreign_rows" :key="`paid-for-${index}`" class="grid gap-2 md:grid-cols-6">
              <Input v-model="row.no_of_stockholders" />
              <Input v-model="row.type_of_shares" />
              <Input v-model="row.number_of_shares" />
              <Input v-model="row.par_or_stated_value" />
              <Input v-model="row.amount" />
              <Input v-model="row.ownership_percent" />
            </div>
            <div class="space-y-2">
              <Label>Total Paid-Up Capital</Label>
              <Input v-model="form.fields.step_3.total_paid_up_capital" />
            </div>
          </div>
        </div>

        <div v-else-if="gisStep === 4" class="space-y-2">
          <div class="space-y-2">
            <Label>Corporate Name (from Page 1)</Label>
            <Input :model-value="form.fields.step_1.corporate_name || ''" readonly />
          </div>
          <Label>Directors / Officers (Page 4 Layout)</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-9">
            <div>Name / Current Residential Address</div>
            <div>Nationality</div>
            <div>INC'R</div>
            <div>Board</div>
            <div>Gender</div>
            <div>Stockholder</div>
            <div>Officer</div>
            <div>Exec. Comm.</div>
            <div>TIN</div>
          </div>
          <div v-for="(row, index) in form.fields.step_4" :key="`dir-${index}`" class="grid gap-2 md:grid-cols-9">
            <Input v-model="row.name" :placeholder="`${index + 1}.`" />
            <Input v-model="row.nationality" />
            <Input v-model="row.incorporator" placeholder="Y/N" />
            <Input v-model="row.board" placeholder="C/M/I" />
            <Input v-model="row.gender" placeholder="F/M" />
            <Input v-model="row.stockholder" placeholder="Y/N" />
            <Input v-model="row.officer" placeholder="Position" />
            <Input v-model="row.exec_comm" placeholder="C/A/N" />
            <Input v-model="row.tin" />
          </div>
        </div>

        <div v-else-if="gisStep === 5" class="space-y-3">
          <div class="space-y-2">
            <Label>Corporate Name (from Page 1)</Label>
            <Input :model-value="form.fields.step_1.corporate_name || ''" readonly />
          </div>
          <div class="grid gap-2 md:grid-cols-3">
            <div class="space-y-2">
              <Label>Total Number of Stockholders</Label>
              <Input v-model="form.fields.step_5.total_stockholders" />
            </div>
            <div class="space-y-2">
              <Label>No. of Stockholders with 100+ Shares</Label>
              <Input v-model="form.fields.step_5.stockholders_with_100_plus" />
            </div>
            <div class="space-y-2">
              <Label>Total Assets (Latest Audited FS)</Label>
              <Input v-model="form.fields.step_5.total_assets" />
            </div>
          </div>
          <div class="flex items-start justify-between gap-3">
            <Label>Stockholder Information</Label>
            <Button type="button" variant="outline" size="sm" @click="addGisStockholderRow">
              Add Stockholder
            </Button>
          </div>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-10">
            <div>No.</div>
            <div>Name / Address</div>
            <div>Nationality</div>
            <div>Type of Shares</div>
            <div>No. of Shares</div>
            <div>Amount Subscribed (PhP)</div>
            <div>% Ownership</div>
            <div>Amount Paid (PhP)</div>
            <div>TIN</div>
            <div></div>
          </div>
          <div v-for="(row, index) in form.fields.step_5.rows" :key="`stk5-${index}`" class="grid gap-2 md:grid-cols-10">
            <Input :model-value="row.no" readonly />
            <Input v-model="row.name_address" placeholder="Name / Address" />
            <Input v-model="row.nationality" />
            <Input v-model="row.share_type" />
            <Input v-model="row.number_of_shares" />
            <Input v-model="row.amount_subscribed" />
            <Input v-model="row.percent_ownership" />
            <Input v-model="row.amount_paid" />
            <Input v-model="row.tin" />
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="border-[#F87171] text-[#B91C1C] hover:bg-[#FEF2F2]"
              :disabled="form.fields.step_5.rows.length <= MIN_GIS_STOCKHOLDER_ROWS"
              @click="removeGisStockholderRow(index)"
            >
              Delete
            </Button>
          </div>
        </div>

        <div v-else-if="gisStep === 6" class="space-y-3">
          <div class="space-y-2">
            <Label>Corporate Name (from Page 1)</Label>
            <Input :model-value="form.fields.step_1.corporate_name || ''" readonly />
          </div>

          <Label>1. Investment of Corporate Funds in Another Corporation</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-3">
            <div>Item</div>
            <div>Amount (PhP)</div>
            <div>Date of Board Resolution</div>
          </div>
          <div class="grid gap-2 md:grid-cols-3">
            <Label>1.1 Stocks</Label>
            <Input v-model="form.fields.step_8.investment_stocks" />
            <Input v-model="form.fields.step_8.investment_stocks_board_resolution_date" type="date" />

            <Label>1.2 Bonds/Commercial Paper</Label>
            <Input v-model="form.fields.step_8.investment_bonds" />
            <Input v-model="form.fields.step_8.investment_bonds_board_resolution_date" type="date" />

            <Label>1.3 Loans/Credits/Advances</Label>
            <Input v-model="form.fields.step_8.investment_loans_advances" />
            <Input v-model="form.fields.step_8.investment_loans_advances_board_resolution_date" type="date" />

            <Label>1.4 Government Treasury Bills</Label>
            <Input v-model="form.fields.step_8.investment_treasury_bills" />
            <Input v-model="form.fields.step_8.investment_treasury_bills_board_resolution_date" type="date" />

            <Label>1.5 Others</Label>
            <Input v-model="form.fields.step_8.investment_others" />
            <Input v-model="form.fields.step_8.investment_others_board_resolution_date" type="date" />
          </div>
          <div class="space-y-2">
            <Label>Fallback Date of Board Resolution (if single date only)</Label>
            <Input v-model="form.fields.step_8.investment_board_resolution_date" type="date" />
          </div>

          <Label>2. Investment of Corporate Funds in Activities under Secondary Purpose</Label>
          <div class="grid gap-2 md:grid-cols-3">
            <div class="space-y-2">
              <Label>Activity / Specification</Label>
              <Input v-model="form.fields.step_8.secondary_purpose_activity" />
            </div>
            <div class="space-y-2">
              <Label>Date of Board Resolution</Label>
              <Input v-model="form.fields.step_8.secondary_purpose_board_resolution_date" type="date" />
            </div>
            <div class="space-y-2">
              <Label>Date of Stockholders Ratification</Label>
              <Input v-model="form.fields.step_8.secondary_purpose_ratification_date" type="date" />
            </div>
          </div>

          <Label>3 & 4. Treasury Shares / Retained Earnings</Label>
          <div class="grid gap-2 md:grid-cols-3">
            <div class="space-y-2">
              <Label>3. Treasury Shares - No. of Shares</Label>
              <Input v-model="form.fields.step_8.treasury_shares_count" />
            </div>
            <div class="space-y-2">
              <Label>3. Treasury Shares - % as to Total Shares Issued</Label>
              <Input v-model="form.fields.step_8.treasury_shares_percent" />
            </div>
            <div class="space-y-2">
              <Label>4. Unrestricted/Unappropriated Retained Earnings</Label>
              <Input v-model="form.fields.step_8.retained_earnings" />
            </div>
          </div>

          <Label>5. Dividends Declared During Immediately Preceding Year</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-3">
            <div>Type of Dividend</div>
            <div>Amount (PhP)</div>
            <div>Date Declared</div>
          </div>
          <div class="grid gap-2 md:grid-cols-3">
            <Label>5.1 Cash</Label>
            <Input v-model="form.fields.step_8.dividend_cash_amount" />
            <Input v-model="form.fields.step_8.dividend_cash_date" type="date" />

            <Label>5.2 Stock</Label>
            <Input v-model="form.fields.step_8.dividend_stock_amount" />
            <Input v-model="form.fields.step_8.dividend_stock_date" type="date" />

            <Label>5.3 Property</Label>
            <Input v-model="form.fields.step_8.dividend_property_amount" />
            <Input v-model="form.fields.step_8.dividend_property_date" type="date" />
          </div>
          <div class="space-y-2">
            <Label>5. Total Dividends Amount (PhP)</Label>
            <Input v-model="form.fields.step_8.dividend_total_amount" />
          </div>

          <Label>6. Additional Shares Issued During the Period</Label>
          <div class="grid gap-2 text-xs font-medium text-[#64748B] dark:text-[#9FB3C8] md:grid-cols-3">
            <div>Date</div>
            <div>No. of Shares</div>
            <div>Amount</div>
          </div>
          <div
            v-for="(row, index) in form.fields.step_8.additional_shares"
            :key="`add-shares-${index}`"
            class="grid gap-2 md:grid-cols-3"
          >
            <Input v-model="row.date" type="date" />
            <Input v-model="row.no_of_shares" />
            <Input v-model="row.amount" />
          </div>

          <Label>Secondary License/Registration with SEC and Other Gov't Agency</Label>
          <div class="grid gap-2 md:grid-cols-2">
            <div class="space-y-2">
              <Label>Name of Agency</Label>
              <Input v-model="form.fields.step_8.agency_name" />
            </div>
            <div class="space-y-2">
              <Label>Type of License/Registration</Label>
              <Input v-model="form.fields.step_8.secondary_license_type" />
            </div>
            <div class="space-y-2">
              <Label>Date Issued</Label>
              <Input v-model="form.fields.step_8.secondary_license_date_issued" type="date" />
            </div>
            <div class="space-y-2">
              <Label>Date Started Operations</Label>
              <Input v-model="form.fields.step_8.secondary_license_date_started" type="date" />
            </div>
          </div>
          <div class="grid gap-2 md:grid-cols-3">
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_8.secondary_license_sec" type="checkbox">
              SEC
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_8.secondary_license_bsp" type="checkbox">
              BSP
            </label>
            <label class="flex items-center gap-2 text-sm">
              <input v-model="form.fields.step_8.secondary_license_ic" type="checkbox">
              IC
            </label>
          </div>

          <Label>Total Annual Compensation / Officers / Employees / Manpower</Label>
          <div class="grid gap-2 md:grid-cols-4">
            <Input v-model="form.fields.step_8.total_annual_compensation_directors" placeholder="Total Annual Compensation of Directors" />
            <Input v-model="form.fields.step_8.total_no_officers" placeholder="Total No. of Officers" />
            <Input v-model="form.fields.step_8.total_rank_file_employees" placeholder="Total No. of Rank & File Employees" />
            <Input v-model="form.fields.step_8.total_manpower_complement" placeholder="Total Manpower Complement" />
          </div>
        </div>

        <div v-else-if="gisStep === 7" class="grid gap-4 md:grid-cols-2">
          <div class="space-y-2">
            <Label>Done This Date</Label>
            <Input v-model="form.fields.step_9.done_date" type="date" />
          </div>
          <div class="space-y-2">
            <Label>Done Place</Label>
            <Input v-model="form.fields.step_9.done_place" placeholder="City / Municipality" />
          </div>
        </div>

        <div class="flex items-center justify-between pt-2">
          <Button type="button" variant="outline" :disabled="gisStep === 1 || form.processing" @click="previousGisStep">
            Previous Step
          </Button>
          <div class="flex items-center gap-2">
            <Button
              v-if="gisStep < GIS_TOTAL_PAGES"
              type="button"
              :disabled="form.processing"
              @click="nextGisStep"
            >
              Next Page
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

      <div v-if="activeType && !isGis" class="mt-2 flex justify-end gap-2">
        <Button type="button" variant="outline" :disabled="form.processing" @click="openPreview">
          Preview
        </Button>
        <Button type="button" :disabled="form.processing" @click="submit">
          {{ form.processing ? 'Exporting...' : 'Export PDF' }}
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

  <AlertDialog :open="actionConfirmOpen" @update:open="(value) => !value && closeActionConfirmation()">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>{{ actionConfirmTitle }}</AlertDialogTitle>
        <AlertDialogDescription>
          {{ actionConfirmDescription }}
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel @click="closeActionConfirmation">Cancel</AlertDialogCancel>
        <AlertDialogAction @click="confirmAction">{{ actionConfirmLabel }}</AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
