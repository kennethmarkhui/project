import '@tanstack/vue-table';
import { LucideIcon } from 'lucide-vue-next';

import { PermissionKey, ResourcePermissions } from '.';

declare module '@tanstack/vue-table' {
    interface ColumnMeta {
        label?: string;
        options?: Option[];
        variant?: 'dateRange' | 'select' | 'multiSelect';
        action?: {
            enabled: boolean;
            icon?: LucideIcon;
        };
    }

    interface TableMeta<TData extends RowData> {
        can?: ResourcePermissions;
        canRow?: (row: TData, action: PermissionKey) => boolean;
    }
}

export interface Option {
    label: string;
    value: string;
    count?: number;
    icon?: LucideIcon;
}
