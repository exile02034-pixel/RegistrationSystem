export type CompanyTypeValue = 'opc' | 'sole_prop' | 'corp';

export type UserPageCompanyType = {
    value: CompanyTypeValue;
    label: string;
};

export type UserSubmittedField = {
    name: string;
    label: string;
    value: string | null;
};

export type UserSubmittedSection = {
    name: string;
    label: string;
    fields: UserSubmittedField[];
};

export type UserFormSubmission = {
    id: string;
    email?: string;
    status: 'pending' | 'incomplete' | 'completed';
    submitted_at: string | null;
    sections: UserSubmittedSection[];
};

export type UserSubmission = {
    registration_id: string;
    company_type: CompanyTypeValue;
    company_type_label: string;
    registration_status: 'pending' | 'incomplete' | 'completed';
    created_at: string | null;
    form_submission: UserFormSubmission | null;
};

export type UserFilesClientInfo = {
    name: string;
    email: string;
    company_types: UserPageCompanyType[];
};

export type UserFilesPageProps = {
    submissions: UserSubmission[];
    clientInfo: UserFilesClientInfo;
};

export type UserDashboardStats = {
    totalSubmissions: number;
    completedSubmissions: number;
    latestSubmissionAt: string | null;
};

export type UserDashboardPageProps = {
    stats: UserDashboardStats;
};

export type UserListItem = {
    id: number;
    name: string;
    email: string;
    role: string;
    status: 'Active' | 'Inactive';
    createdAt: string;
};
