import { type ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { CircleCheck, UserCog } from 'lucide-vue-next';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { STATUS } from '@/constants';
import { formatDate } from '@/lib/utils';
import type { Role, User } from '@/types';

const columnHelper = createColumnHelper<User>();
export function getUserDataTableColumn(roles: Role[]) {
    return [
        columnHelper.accessor('name', {
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Name' }),
            enableColumnFilter: false,
        }),
        columnHelper.accessor('email', {
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Email' }),
            enableColumnFilter: false,
        }),
        columnHelper.accessor('role', {
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Role' }),
            cell: ({ cell }) => {
                const role = roles.map((role) => role.name).find((role) => role === cell.getValue());

                if (!role) return null;

                return h(
                    Badge,
                    { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' },
                    { default: () => h('span', { class: 'capitalize text-center' }, role) },
                );
            },
            enableColumnFilter: true,
            meta: {
                label: 'Role',
                options: roles.map(({ name }) => ({
                    label: name.charAt(0).toUpperCase() + name.slice(1),
                    value: name,
                })),
                variant: 'multiSelect',
                action: {
                    enabled: true,
                    icon: UserCog,
                },
            },
            size: 40,
        }),
        columnHelper.accessor('status', {
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Status' }),
            cell: ({ cell }) => {
                const status = STATUS.find((status) => status === cell.getValue());

                if (!status) return null;

                return h(
                    Badge,
                    { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' },
                    { default: () => h('span', { class: 'capitalize' }, status) },
                );
            },
            enableColumnFilter: true,
            meta: {
                label: 'Status',
                options: STATUS.map((status) => ({
                    label: status.charAt(0).toUpperCase() + status.slice(1),
                    value: status,
                })),
                variant: 'multiSelect',
                action: {
                    enabled: true,
                    icon: CircleCheck,
                },
            },
        }),
        columnHelper.accessor('deleted_at', {
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Deleted' }),
            cell: ({ cell }) => {
                const deletedAt = cell.getValue();

                if (typeof deletedAt !== 'string') {
                    return null;
                }

                const formatted = formatDate(deletedAt);

                return h('div', formatted);
            },
            enableColumnFilter: true,
            meta: {
                label: 'Deleted',
                options: [
                    { label: 'Show with', value: 'with' },
                    { label: 'Show only', value: 'only' },
                ],
                variant: 'select',
            },
        }),
    ] as ColumnDef<User, unknown>[];
}
