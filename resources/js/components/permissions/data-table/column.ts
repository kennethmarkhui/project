import { type ColumnDef, createColumnHelper } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import { Badge } from '@/components/ui/badge';
import type { Permission, Role } from '@/types';

const columnHelper = createColumnHelper<Permission>();
export function getPermissionDataTableColumn(roles: Role[]) {
    return [
        columnHelper.accessor('name', {
            header: ({ column }) => h(DataTableColumnHeader<Permission, unknown>, { column, title: 'Permission' }),
            enableHiding: false,
        }),
        columnHelper.accessor((row) => row.roles?.map((role) => role.name), {
            id: 'roles',
            header: ({ column }) => h(DataTableColumnHeader<Permission, unknown>, { column, title: 'Roles' }),
            cell: ({ cell }) => {
                const roles = cell.getValue();
                if (!roles || roles?.length === 0) return null;

                return h(
                    'div',
                    { class: 'flex flex-wrap gap-2' },
                    {
                        default: () => roles.map((role) => h(Badge, { variant: 'outline', class: 'py-1' }, { default: () => role })),
                    },
                );
            },
            enableColumnFilter: true,
            filterFn: 'arrIncludesSome',
            meta: {
                label: 'Role',
                options: roles.map((role) => ({ label: role.name, value: role.name })),
                variant: 'multiSelect',
            },
        }),
    ] as ColumnDef<Permission>[];
}
