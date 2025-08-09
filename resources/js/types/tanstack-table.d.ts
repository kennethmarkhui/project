import '@tanstack/vue-table';
import { LucideIcon } from 'lucide-vue-next';

import { PermissionKey, ResourcePermissions } from '.';

declare module '@tanstack/vue-table' {
    interface ColumnMeta {
        label?: string;
        options?: Option[];
        variant?: 'multiSelect' | 'select';
        action?: boolean;
        icon?: LucideIcon;
    }

    interface TableMeta<TData extends RowData> {
        can?: ResourcePermissions;
        enableSearch?: boolean;
        canRow?: (row: TData, action: PermissionKey) => boolean;
    }
}

export interface Option {
    label: string;
    value: string;
    count?: number;
    icon?: LucideIcon;
}
