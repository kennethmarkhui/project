<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
    type ColumnDef,
    type ColumnFiltersState,
    getCoreRowModel,
    type OnChangeFn,
    type PaginationState,
    type RowSelectionState,
    type SortingState,
    type Updater,
    useVueTable,
} from '@tanstack/vue-table';
import { debounce } from 'lodash';
import { CircleCheck, UserCog } from 'lucide-vue-next';
import { computed, h, type Ref, ref } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import DataTableActionBar from '@/components/data-table/DataTableActionBar.vue';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableDropdown from '@/components/data-table/DataTableDropdown.vue';
import DataTablePagination from '@/components/data-table/DataTablePagination.vue';
import DataTableToolbar from '@/components/data-table/DataTableToolbar.vue';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { ROLES, STATUS } from '@/constants';
import { formatDate } from '@/lib/utils';
import type { HandleAction, Paginated, User } from '@/types';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
}

const props = defineProps<Props>();

const columnFilters = ref(props.filters ?? []);
const globalFilter = ref(props.search ?? '');
const sorting = ref(props.sort ?? []);
const pagination = ref({ pageIndex: props.users.meta.current_page - 1, pageSize: props.users.meta.per_page });
const rowSelection = ref<{
    [x: string]: boolean;
}>({});

const selectedIds = computed(() => Object.keys(rowSelection.value));

const isSelectionDeleting = ref<boolean>();
const selectedRows = ref(new Map<string, User>());

const columns: ColumnDef<User>[] = [
    {
        id: 'select',
        header: ({ table }) =>
            h(Checkbox, {
                modelValue: table.getIsAllRowsSelected() || (table.getIsSomeRowsSelected() && 'indeterminate'),
                'onUpdate:modelValue': (value) => {
                    const deletedFilter = columnFilters.value.find((column) => column.id === 'deleted_at')?.value;

                    if (!Array.isArray(deletedFilter)) return table.toggleAllRowsSelected(!!value);

                    const filterMode = deletedFilter[0];

                    if (filterMode === 'with') {
                        if (typeof isSelectionDeleting.value === 'boolean' && isSelectionDeleting.value === false) {
                            table.toggleAllRowsSelected(!!value);
                            return;
                        }
                        isSelectionDeleting.value = true;
                        table.toggleAllRowsSelected(!!value);
                    }

                    if (filterMode === 'only') {
                        if (typeof isSelectionDeleting.value === 'boolean' && isSelectionDeleting.value === true) {
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

            return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, { default: () => h('span', { class: 'capitalize' }, status) });
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
            const isAuthUser = usePage().props.auth.user.id === row.original.id;
            const isSelecting = typeof isSelectionDeleting.value === 'boolean';
            return h(
                'div',
                { class: 'relative' },
                h(DataTableDropdown, {
                    id,
                    isDeleted: Boolean(deleted_at),
                    isDisabled: isAuthUser || isSelecting,
                    onDelete: handleDelete,
                    onRestore: handleRestore,
                }),
            );
        },
        size: 40,
    },
];

const refetch = () => {
    const query: { search?: string; page?: number; sort?: string; filters?: string; deleted?: string } = {};
    const search = globalFilter.value;
    const filters = columnFilters.value.filter((item) => item.id !== 'deleted_at');
    const sort = sorting.value;
    const page = pagination.value.pageIndex;
    const deleted = columnFilters.value.find((item) => item.id === 'deleted_at');

    if (search) {
        query.search = search;
    }

    if (filters.length > 0) {
        query.filters = JSON.stringify(filters);
    }

    if (sort.length > 0) {
        query.sort = JSON.stringify(sort);
    }

    if (deleted && Array.isArray(deleted.value) && deleted.value.length > 0) {
        query.deleted = deleted.value[0];
    }

    query.page = page && page + 1;

    const data = Object.fromEntries(Object.entries(query).filter(([_key, value]) => Boolean(value)));

    router.get(route('users'), data, {
        preserveState: true,
        replace: true,
    });
};

const valueUpdater = <T extends Updater<unknown>>(updaterOrValue: T, ref: Ref) => {
    ref.value = typeof updaterOrValue === 'function' ? updaterOrValue(ref.value) : updaterOrValue;
};

const onColumnFiltersChange: OnChangeFn<ColumnFiltersState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, columnFilters);
    pagination.value = { pageIndex: 0, pageSize: 10 };
    refetch();
};

const onGlobalFilterChange: OnChangeFn<any> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, globalFilter);
    pagination.value = { pageIndex: 0, pageSize: 10 };
    refetch();
};

const onSortingChange: OnChangeFn<SortingState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, sorting);
    refetch();
};

const onPaginationChange: OnChangeFn<PaginationState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, pagination);
    refetch();
};

const onRowSelectionChange: OnChangeFn<RowSelectionState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, rowSelection);

    const selectedIds = new Set(
        Object.entries(rowSelection.value)
            .filter(([_, isSelected]) => isSelected)
            .map(([id]) => id),
    );

    selectedIds.forEach((id) => {
        if (!selectedRows.value.has(id)) {
            const foundRow = table.getSelectedRowModel().rows.find((row) => row.id === id);
            if (foundRow) {
                selectedRows.value.set(id, foundRow.original);
            }
        }
    });

    for (const key of selectedRows.value.keys()) {
        if (!selectedIds.has(key)) {
            selectedRows.value.delete(key);
        }
    }

    if (!selectedRows.value.size) {
        isSelectionDeleting.value = undefined;
        return;
    }

    const firstRow = selectedRows.value.values().next().value;
    const deleted = !!firstRow?.deleted_at;

    isSelectionDeleting.value = deleted ? false : true;
};

const handleReset = () => {
    columnFilters.value = [];
    globalFilter.value = '';
    pagination.value = { pageIndex: 0, pageSize: 10 };
    refetch();
};

const handleSelectionReset = () => {
    rowSelection.value = {};
    isSelectionDeleting.value = undefined;
    selectedRows.value = new Map();
};

const handleDelete = (id: string) => {
    router.delete(route('users.destroy', id), {
        preserveScroll: true,
        onFinish: () => {
            handleSelectionReset();

            refetch();
        },
    });
};

const handleRestore = (id: string) => {
    router.patch(route('users.restore', id), undefined, {
        preserveScroll: true,
        onFinish: () => {
            handleSelectionReset();
            refetch();
        },
    });
};

const handleUpdate = (ids: string, column: string, payload: string) => {
    router.patch(
        route('users.update', ids),
        {
            [column]: payload,
        },
        {
            onFinish: () => {
                handleSelectionReset();
                refetch();
            },
        },
    );
};

const handleAction: HandleAction = (...args) => {
    const [action, column, payload] = args;
    const ids = selectedIds.value.join(',');

    switch (action) {
        case 'delete':
            handleDelete(ids);
            break;
        case 'restore':
            handleRestore(ids);
            break;
        case 'update':
            handleUpdate(ids, column, payload);
            break;
    }
};

const debouncedInput = debounce((value: string | number) => table.setGlobalFilter(value), 300);

const table = useVueTable({
    get data() {
        return props.users.data;
    },
    get columns() {
        return columns;
    },
    get rowCount() {
        return props.users.meta.total;
    },
    initialState: {
        columnVisibility: {
            deleted_at: false,
        },
    },
    state: {
        get columnFilters() {
            return columnFilters.value;
        },
        get globalFilter() {
            return globalFilter.value;
        },
        get sorting() {
            return sorting.value;
        },
        get pagination() {
            return pagination.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
    },
    manualFiltering: true,
    manualSorting: true,
    manualPagination: true,
    onColumnFiltersChange,
    onGlobalFilterChange,
    onSortingChange,
    onPaginationChange,
    onRowSelectionChange,
    getCoreRowModel: getCoreRowModel(),
    getRowId: (row) => String(row.id),
    enableRowSelection: (row) => {
        const deleted = !!row.original.deleted_at;
        const isAuthUser = usePage().props.auth.user.id === row.original.id;

        if (isAuthUser) {
            return false;
        }

        if (isSelectionDeleting.value === true) {
            return !deleted;
        }

        if (isSelectionDeleting.value === false) {
            return deleted;
        }

        return true;
    },
});
</script>

<template>
    <!-- <pre>{{ 'columnFilters: ' + JSON.stringify(columnFilters, null, 2) }}</pre> -->
    <!-- <pre>{{ 'globalFilter: ' + globalFilter }}</pre> -->
    <!-- <pre>{{ 'sorting: ' + JSON.stringify(sorting, null, 2) }}</pre> -->
    <!-- <pre>{{ 'pagination: ' + JSON.stringify(pagination, null, 2) }}</pre> -->
    <!-- <pre>{{ 'isSelectionDeleting: ' + isSelectionDeleting }}</pre> -->
    <!-- <pre>{{ 'rowSelection: ' + JSON.stringify(rowSelection, null, 2) }}</pre> -->
    <!-- <pre>{{ 'selectedRows: ' + JSON.stringify(Object.fromEntries(selectedRows), null, 2) }}</pre> -->

    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <DataTable :table="table">
            <template #toolbar>
                <DataTableToolbar :table="table" @reset="handleReset">
                    <template #search>
                        <Input class="h-8 w-40 lg:w-56" placeholder="Search" :model-value="globalFilter" @update:model-value="debouncedInput" />
                    </template>
                </DataTableToolbar>
            </template>
            <template #pagination>
                <DataTablePagination :table="table" />
            </template>
            <template #actionBar>
                <DataTableActionBar :table="table" :is-deleting="isSelectionDeleting" @action="handleAction" />
            </template>
        </DataTable>
    </div>
</template>
