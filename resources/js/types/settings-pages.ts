export type ProfileSettingsPageProps = {
    mustVerifyEmail: boolean;
    status?: string;
};

export type TwoFactorSettingsPageProps = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};
