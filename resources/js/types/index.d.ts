import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export type ResourceKey = 'user' | 'role' | 'permission';

export type PermissionKey = 'create' | 'read' | 'update' | 'delete' | 'force_delete' | 'restore';

export type ResourcePermissions = {
    [P in PermissionKey]?: boolean;
};

export type AppPermissions = {
    [R in ResourceKey]?: ResourcePermissions;
};

export interface Auth {
    user: User;
    can: AppPermissions;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at?: string | null;
    role: string;
    status: string;
    created_at?: string;
    updated_at?: string;
    deleted_at: string | null;
    can?: ResourcePermissions;
}

export interface Permission {
    id: number;
    name: string;
    roles?: Role[];
    roles_count?: number;
}

export interface Role {
    id: number;
    name: string;
    is_system?: boolean;
    can?: ResourcePermissions;
    permissions?: Permission[];
    permissions_count?: number;
    users_count?: number;
}

export interface Paginated<T> {
    data: T;
    links: {
        first: string;
        last: string;
        next: string | null;
        prev: string | null;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        links: Array<{
            active: boolean;
            label: string;
            url: string | null;
        }>;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

export type BreadcrumbItemType = BreadcrumbItem;

// https://learn.microsoft.com/en-us/javascript/api/@azure/keyvault-certificates/requireatleastone?view=azure-node-latest
export type RequireAtLeastOne<T> = {
    [K in keyof T]-?: Required<Pick<T, K>> & Partial<Pick<T, Exclude<keyof T, K>>>;
}[keyof T];
