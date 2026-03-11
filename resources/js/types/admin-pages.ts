import type { SubmittedSection } from './registration';
import type { CompanyTypeValue } from './user-pages';

export type CompanyTypeOption = {
    value: CompanyTypeValue | string;
    label: string;
};

export type AdminDashboardStats = {
    totalUsers: number;
    pendingUsers: number;
    acceptedUsers: number;
    totalSubmissions: number;
};

export type AdminRecentActivityItem = {
    id: string;
    type: string;
    description: string;
    performed_by_name: string | null;
    performed_by_email: string | null;
    performed_by_role: string | null;
    company_type: string | null;
    created_at: string | null;
};

export type AdminDashboardPageProps = {
    stats?: AdminDashboardStats;
    recentActivities?: AdminRecentActivityItem[];
};

export type ActivityLogMetadata = {
    section?: string;
    section_label?: string;
    updated_fields?: string[];
};

export type ActivityLogItem = {
    id: string;
    type: string;
    description: string;
    performed_by_name: string | null;
    performed_by_email: string | null;
    performed_by_role: string | null;
    company_type: string | null;
    files_count: number | null;
    filenames: string[];
    file_types: string[];
    metadata?: ActivityLogMetadata;
    created_at: string | null;
};

export type PaginatedLogs = {
    data: ActivityLogItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type ActivityLogsPageProps = {
    logs: PaginatedLogs;
};

export type AdminTableFilters = {
    search: string;
    sort: 'created_at';
    direction: 'asc' | 'desc';
    company_type: '' | CompanyTypeValue;
};

export type RegistrationLink = {
    id: string;
    email: string;
    company_type_label: string;
    status: string;
    form_submitted: boolean;
    created_at: string | null;
    client_url: string;
    show_url: string;
};

export type PaginatedRegistrationLinks = {
    data: RegistrationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type AdminRegistrationIndexPageProps = {
    links: PaginatedRegistrationLinks;
    companyTypes: CompanyTypeOption[];
    filters: AdminTableFilters;
};

export type AdminRegistrationCreatePageProps = {
    companyTypes: CompanyTypeOption[];
};

export type FormSubmission = {
    id: string;
    email?: string;
    status: 'pending' | 'incomplete' | 'completed';
    submitted_at: string | null;
    sections: SubmittedSection[];
};

export type GeneratedDocument = {
    id: string;
    document_type: string;
    document_name: string;
    created_at: string | null;
    generated_by: string | null;
    view_url: string;
    download_url: string;
    delete_url: string;
};

export type DocumentFormType =
    | 'secretary_certificate'
    | 'secretary_certificate_bank'
    | 'appointment_form_opc'
    | 'gis_stock_corporation';

export type DocumentForm = {
    type: DocumentFormType;
    name: string;
    description: string;
};

export type RegistrationStatus = 'pending' | 'incomplete' | 'completed';

export type RequiredRegistrationDocument = {
    type: string;
    name: string;
    is_uploaded: boolean;
    original_filename: string | null;
    uploaded_at: string | null;
    uploaded_by: string | null;
    upload_url: string;
    view_url: string | null;
    download_url: string | null;
    delete_url: string | null;
};

export type GisAutofillData = {
    corporate_name: string;
    business_trade_name: string;
    sec_registration_number: string;
    date_registered: string;
    principal_office_address: string;
    business_address: string;
    corporate_tin: string;
    branch_code: string;
    industry_classification: string;
    email: string;
    official_mobile: string;
    alternate_email: string;
    alternate_mobile: string;
    primary_purpose: string;
    step_3?: {
        authorized_capital_stock?: string;
        subscribed_capital_stock?: string;
        paid_up_capital_stock?: string;
        authorized_rows?: Array<Record<string, string>>;
        subscribed_filipino_rows?: Array<Record<string, string>>;
        subscribed_foreign_rows?: Array<Record<string, string>>;
        paidup_filipino_rows?: Array<Record<string, string>>;
        paidup_foreign_rows?: Array<Record<string, string>>;
        percentage_foreign_equity?: string;
        total_subscribed_capital?: string;
        total_paid_up_capital?: string;
    };
    aoi_capital_stock_available?: boolean;
    has_uploaded_sources: boolean;
    missing_fields: string[];
};

export type AppointmentAutofillData = {
    corporate_tin: string;
    complete_business_address: string;
    business_trade_name: string;
    date_of_registration: string;
    sec_registration_number: string;
    corporate_name: string;
    email_address: string;
    primary_purpose_activity: string;
    has_uploaded_sources: boolean;
    missing_fields: string[];
};

export type AdminRegistrationShowRecord = {
    id: string;
    email: string;
    token: string;
    company_type: CompanyTypeValue;
    company_type_label: string;
    status: string;
    created_at: string | null;
    form_submission: FormSubmission | null;
    generated_documents: GeneratedDocument[];
    document_forms: DocumentForm[];
    gis_autofill: GisAutofillData;
    appointment_autofill: AppointmentAutofillData;
    required_documents: RequiredRegistrationDocument[];
    revision_count: number;
    last_revision_at: string | null;
};

export type AdminRegistrationShowPageProps = {
    registration: AdminRegistrationShowRecord;
};

export type AdminUserRow = {
    id: string;
    name: string;
    email: string;
    created_at: string | null;
    company_types: Array<{ value: CompanyTypeValue; label: string }>;
    company_type_values: CompanyTypeValue[];
    submissions_count: number;
    show_url: string;
};

export type PaginatedUsers = {
    data: AdminUserRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type AdminUsersIndexPageProps = {
    users: PaginatedUsers;
    filters: AdminTableFilters;
};

export type UserSubmission = {
    registration_id: string;
    company_type: CompanyTypeValue;
    company_type_label: string;
    registration_status: RegistrationStatus;
    created_at: string | null;
    form_submission: FormSubmission | null;
};

export type UserActivityItem = {
    id: string;
    type: string;
    description: string;
    created_at: string | null;
    files_count: number | null;
    filenames: string[];
    section_label?: string | null;
    updated_fields?: string[];
};

export type AdminUserProfile = {
    id: string;
    name: string;
    email: string;
    status: string;
    created_at: string | null;
    company_types: CompanyTypeOption[];
};

export type AdminUserShowPageProps = {
    user: AdminUserProfile;
    submissions: UserSubmission[];
    activities: UserActivityItem[];
};
