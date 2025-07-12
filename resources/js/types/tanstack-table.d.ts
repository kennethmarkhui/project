import '@tanstack/vue-table';
import { LucideIcon } from 'lucide-vue-next';

declare module '@tanstack/vue-table' {
    interface ColumnMeta {
        label?: string;
        options?: Option[];
        variant?: 'multiSelect' | 'select';
        action?: boolean;
        icon?: LucideIcon;
    }
}

export interface Option {
    label: string;
    value: string;
    count?: number;
    icon?: LucideIcon;
}
