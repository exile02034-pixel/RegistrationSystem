export type NotificationRow = {
    id: string;
    category: string;
    title: string;
    message: string | null;
    action_url: string | null;
    read_at: string | null;
    created_at: string | null;
};

export type PaginatedNotifications = {
    data: NotificationRow[];
    current_page: number;
    last_page: number;
    total: number;
};

export type NotificationsPageProps = {
    notifications: PaginatedNotifications;
};

export type NotificationSettingsPageProps = {
    preferences: Record<string, boolean>;
    labels: Record<string, string>;
};
