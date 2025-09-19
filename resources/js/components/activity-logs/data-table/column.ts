import { ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/utils';
import type { ActivityLog } from '@/types';

interface ActivityLogDataTableColumn {
    events: string[];
    log_names: string[];
}

const columnHelper = createColumnHelper<ActivityLog>();
export function getActivityLogDataTableColumn({ events, log_names }: ActivityLogDataTableColumn) {
    return [
        columnHelper.accessor('event', {
            header: ({ column }) => h(DataTableColumnHeader<ActivityLog, unknown>, { column, title: 'Event' }),
            cell: ({ cell }) => {
                return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, () => h('span', { class: 'capitalize' }, cell.getValue()));
            },
            enableColumnFilter: true,
            meta: {
                label: 'Event',
                options: events.map((event) => ({
                    label: event.charAt(0).toUpperCase() + event.slice(1),
                    value: event,
                })),
                variant: 'multiSelect',
            },
        }),
        columnHelper.accessor('log_name', {
            header: ({ column }) => h(DataTableColumnHeader<ActivityLog, unknown>, { column, title: 'Type' }),
            cell: ({ cell, row }) => {
                return h('p', { class: 'capitalize', title: row.original.subject_type }, cell.getValue());
            },
            enableColumnFilter: true,
            meta: {
                label: 'Type',
                options: log_names.map((log_name) => ({
                    label: log_name.charAt(0).toUpperCase() + log_name.slice(1),
                    value: log_name,
                })),
                variant: 'multiSelect',
            },
        }),
        columnHelper.accessor('subject_id', {
            header: ({ column }) => h(DataTableColumnHeader<ActivityLog, unknown>, { column, title: 'Subject ID' }),
            enableColumnFilter: false,
            meta: { label: 'Subject ID' },
        }),
        columnHelper.accessor('causer', {
            header: ({ column }) => h(DataTableColumnHeader<ActivityLog, unknown>, { column, title: 'Caused by' }),
            cell: ({ cell }) => {
                const user = cell.getValue();

                if (!user) return 'System';

                return h('div', [
                    h('p', { class: 'text-sm text-muted-foreground' }, user.role),
                    h('p', { class: 'font-medium text-highlighted' }, user.email),
                ]);
            },
            enableColumnFilter: false,
            meta: { label: 'Causer' },
        }),
        // TODO: move this to expandable row
        columnHelper.accessor('properties', {
            header: ({ column }) => h(DataTableColumnHeader<ActivityLog, unknown>, { column, title: 'Properties' }),
            cell: ({ cell }) => {
                const properties = cell.getValue();

                if (typeof properties !== 'object' || Array.isArray(properties) || !properties) return;

                return h(
                    'div',
                    { class: 'truncate text-sm inline-flex gap-1' },
                    Object.entries(properties).map(([key, value]) => {
                        if (typeof value !== 'object' || Array.isArray(value) || !value) return;

                        return h('p', {}, [
                            h(Badge, { variant: 'secondary', class: 'mr-1' }, () => key),
                            Object.entries(value)
                                .map(([k, v]) => `${k}: ${v}`)
                                .join(' | '),
                        ]);
                    }),
                );
            },
            enableColumnFilter: false,
            enableSorting: false,
            meta: { label: 'Properties' },
        }),
        columnHelper.accessor('created_at', {
            header: ({ column }) => h(DataTableColumnHeader<ActivityLog, unknown>, { column, title: 'Logged at' }),
            cell: ({ cell }) => {
                const createdAt = cell.getValue();

                const formatted = formatDate(createdAt, { hour: 'numeric', minute: 'numeric' });

                return h('div', formatted);
            },
            meta: {
                label: 'Logged at',
                variant: 'dateRange',
            },
        }),
    ] as ColumnDef<ActivityLog, unknown>[];
}
