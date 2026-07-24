export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    is_active?: boolean;
    roles: string[];
    permissions: string[];
}

export interface Site {
    id: number;
    code: string;
    name: string;
    location?: string;
}

export interface Pit {
    id: number;
    site_id: number;
    code: string;
    owner: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        permissions: string[];
    };
    sites: Site[];
    activeSite: Site;
    flash: {
        success?: string;
        error?: string;
    };
};
