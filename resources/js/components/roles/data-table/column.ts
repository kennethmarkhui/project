import { ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Role } from '@/types';

const columnHelper = createColumnHelper<Role>();
export const columns = [
    columnHelper.accessor('name', {
        header: ({ column }) => h(DataTableColumnHeader<Role, unknown>, { column, title: 'Role' }),
        enableSorting: false,
        enableHiding: false,
    }),
    columnHelper.accessor('permissions', {
        header: ({ column }) => h(DataTableColumnHeader<Role, unknown>, { column, title: 'Permissions' }),
        cell: ({ cell }) => {
            const permissions = cell.getValue();
            if (permissions?.length === 0) return null;

            return h(
                'div',
                { class: 'flex flex-wrap gap-2' },
                {
                    default: () =>
                        permissions?.map((permission) => h(Badge, { variant: 'outline', class: 'py-1' }, { default: () => permission.name })),
                },
            );
        },
        enableSorting: false,
    }),
    columnHelper.accessor('users_count', {
        header: ({ column }) => h(DataTableColumnHeader<Role, unknown>, { column, title: 'Users' }),
        enableSorting: false,
        size: 40,
    }),
] as ColumnDef<Role>[];
