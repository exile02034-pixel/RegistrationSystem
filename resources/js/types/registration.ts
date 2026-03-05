export type SubmittedField = {
    name: string;
    label: string;
    value: string | null;
};

export type SubmittedSection = {
    name: string;
    label: string;
    fields: SubmittedField[];
};

export type TrackingSummary = {
    sections?: SubmittedSection[];
} | null;

export type FormFieldSchema = {
    name: string;
    label: string;
    type?: string;
    required?: boolean;
    options?: Array<{ label: string; value: string }>;
};

export type FormSectionSchema = {
    name: string;
    label: string;
    fields: FormFieldSchema[];
};

export type RegistrationFormPageProps = {
    token: string;
    email: string;
    companyType: string;
    companyTypeLabel: string;
    formSchema: FormSectionSchema[];
    submitUrl: string;
    qrCodeDataUri: string;
    initialSections?: Record<string, Record<string, string>>;
    isEditing?: boolean;
    focusSection?: string | null;
};

export type TrackingLookupPageProps = {
    statusMessage: string;
    errorMessage: string;
    requestLinkUrl: string;
};

export type TrackingShowPageProps = {
    email: string;
    companyTypeLabel: string;
    status: string;
    statusLabel: string;
    submittedAt: string | null;
    canEdit: boolean;
    editableSections: string[];
    statusMessage: string;
    errorMessage: string;
    editUrl: string;
    requestEditPermissionUrl: string;
    logoutUrl: string;
    revisionCount: number;
    lastRevisionAt: string | null;
    summary: TrackingSummary;
};

export type RegistrationSuccessPageProps = {
    trackingLookupUrl: string;
};
