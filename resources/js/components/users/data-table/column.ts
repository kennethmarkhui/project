import { ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { CircleCheck, UserCog } from 'lucide-vue-next';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import { ROLES, STATUS } from '@/constants';
import { formatDate } from '@/lib/utils';
import type { User } from '@/types';

const columnHelper = createColumnHelper<User>();
export function getUserDataTableColumn() {
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
                const role = ROLES.find((role) => role === cell.getValue());

                if (!role) return null;

                return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, { default: () => h('span', { class: 'capitalize' }, role) });
            },
            enableColumnFilter: true,
            meta: {
                label: 'Role',
                options: ROLES.map((role) => ({
                    label: role.charAt(0).toUpperCase() + role.slice(1),
                    value: role,
                })),
                variant: 'multiSelect',
                action: true,
                icon: UserCog,
            },
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
                action: true,
                icon: CircleCheck,
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
    ] as Array<ColumnDef<User, unknown>>;
}
