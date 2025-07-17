import type { ColumnDef } from '@tanstack/vue-table';
import { CircleCheck, UserCog } from 'lucide-vue-next';
import { h, type Ref } from 'vue';

import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableDropdown from '@/components/data-table/DataTableDropdown.vue';
import Badge from '@/components/ui/badge/Badge.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import { ROLES, STATUS } from '@/constants';
import { formatDate } from '@/lib/utils';
import type { User } from '@/types';

interface GetUserDataTableColumnProps {
    authId: number;
    isSelectionDeleting: Ref<boolean | undefined>;
    onDelete: (id: string) => void;
    onRestore: (id: string) => void;
}

export function getUserDataTableColumn({ authId, isSelectionDeleting, onDelete, onRestore }: GetUserDataTableColumnProps): ColumnDef<User>[] {
    return [
        {
            id: 'select',
            header: ({ table }) =>
                h(Checkbox, {
                    modelValue: table.getIsAllRowsSelected() || (table.getIsSomeRowsSelected() && 'indeterminate'),
                    'onUpdate:modelValue': (value) => {
                        const columnFilters = table.getState().columnFilters;
                        const deletedFilter = columnFilters.find((column) => column.id === 'deleted_at')?.value;

                        if (!Array.isArray(deletedFilter)) return table.toggleAllRowsSelected(!!value);

                        const filterMode = deletedFilter[0];

                        if (filterMode === 'with') {
                            if (isSelectionDeleting.value === false) {
                                table.toggleAllRowsSelected(!!value);
                                return;
                            }
                            isSelectionDeleting.value = true;
                            table.toggleAllRowsSelected(!!value);
                        }

                        if (filterMode === 'only') {
                            if (isSelectionDeleting.value === true) {
                                table.toggleAllRowsSelected(!!value);
                                return;
                            }
                            isSelectionDeleting.value = false;
                            table.toggleAllRowsSelected(!!value);
                        }
                    },
                    attrs: { 'aria-label': 'Select all' },
                    class: 'translate-y-0.5',
                }),
            cell: ({ row }) =>
                h(Checkbox, {
                    modelValue: row.getIsSelected(),
                    'onUpdate:modelValue': (value) => row.toggleSelected(!!value),
                    attrs: { 'aria-label': 'Select row' },
                    class: 'translate-y-0.5',
                }),
            enableHiding: false,
            enableSorting: false,
            size: 40,
        },
        {
            accessorKey: 'name',
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Name' }),
            enableColumnFilter: false,
        },
        {
            accessorKey: 'email',
            header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Email' }),
            enableColumnFilter: false,
        },
        {
            accessorKey: 'role',
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
        },
        {
            accessorKey: 'status',
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
        },
        {
            accessorKey: 'deleted_at',
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
        },
        {
            id: 'actions',
            enableHiding: false,
            enableSorting: false,
            cell: ({ row }) => {
                const { id, deleted_at } = row.original;
                const isAuthUser = authId === row.original.id;
                const isSelecting = typeof isSelectionDeleting.value === 'boolean';
                return h(
                    'div',
                    { class: 'relative' },
                    h(DataTableDropdown, {
                        id,
                        isDeleted: Boolean(deleted_at),
                        isDisabled: isAuthUser || isSelecting,
                        onDelete,
                        onRestore,
                    }),
                );
            },
            size: 40,
        },
    ];
}
