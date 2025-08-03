import { ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Permission } from '@/types';

const columnHelper = createColumnHelper<Permission>();
export const columns = [
    columnHelper.accessor('name', {
        header: ({ column }) => h(DataTableColumnHeader<Permission, unknown>, { column, title: 'Permission' }),
        enableSorting: false,
        enableHiding: false,
    }),
    columnHelper.accessor('roles', {
        header: ({ column }) => h(DataTableColumnHeader<Permission, unknown>, { column, title: 'Roles' }),
        cell: ({ cell }) => {
            const roles = cell.getValue();
            if (!roles || roles?.length === 0) return null;

            return h(
                'div',
                { class: 'flex flex-wrap gap-2' },
                {
                    default: () => roles.map((role) => h(Badge, { variant: 'outline', class: 'py-1' }, { default: () => role.name })),
                },
            );
        },
        enableSorting: false,
    }),
] as ColumnDef<Permission>[];
