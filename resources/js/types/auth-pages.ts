export type LoginPageProps = {
    status?: string;
    canResetPassword: boolean;
};

export type ForgotPasswordPageProps = {
    status?: string;
};

export type VerifyEmailPageProps = {
    status?: string;
};

export type ResetPasswordPageProps = {
    token: string;
    email: string;
};
