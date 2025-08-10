import { type ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import { Badge } from '@/components/ui/badge';
import type { Permission, Role } from '@/types';

const columnHelper = createColumnHelper<Role>();
export function getRoleDataTableColumn(permissions: Permission[]) {
    return [
        columnHelper.accessor('name', {
            header: ({ column }) => h(DataTableColumnHeader<Role, unknown>, { column, title: 'Role' }),
            enableHiding: false,
        }),
        columnHelper.accessor((row) => row.permissions?.map((permission) => permission.name), {
            id: 'permissions',
            header: ({ column }) => h(DataTableColumnHeader<Role, unknown>, { column, title: 'Permissions' }),
            cell: ({ cell }) => {
                const permissions = cell.getValue();
                if (permissions?.length === 0) return null;

                return h(
                    'div',
                    { class: 'flex flex-wrap gap-2' },
                    {
                        default: () =>
                            permissions?.map((permission) => h(Badge, { variant: 'outline', class: 'py-1' }, { default: () => permission })),
                    },
                );
            },
            enableColumnFilter: true,
            filterFn: 'arrIncludesSome',
            meta: {
                label: 'Permission',
                options: permissions.map((permission) => ({ label: permission.name, value: permission.name })),
                variant: 'multiSelect',
            },
        }),
        columnHelper.accessor('users_count', {
            header: ({ column }) => h(DataTableColumnHeader<Role, unknown>, { column, title: 'Users' }),
            size: 40,
        }),
    ] as ColumnDef<Role>[];
}
