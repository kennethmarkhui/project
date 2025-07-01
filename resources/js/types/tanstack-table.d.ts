import '@tanstack/vue-table';

declare module '@tanstack/vue-table' {
    interface ColumnMeta {
        label?: string;
        options?: Option[];
    }
}

export interface Option {
    label: string;
    value: string;
    count?: number;
    icon?: LucideIcon;
}
