<script setup lang="ts" generic="TData extends RowData">
import { usePage } from '@inertiajs/vue3';
import {
    type ColumnDef,
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    type RowData,
    type TableOptionsWithReactiveData,
    type TableState,
    useVueTable,
} from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import { computed, h, ref, type Ref } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { HandleAction } from '@/types';
import DataTableActionBar from './action-bar/DataTableActionBar.vue';
import DataTableActionBarSelectionList from './action-bar/DataTableActionBarSelectionList.vue';
import DataTableDropdown from './DataTableDropdown.vue';
import DataTablePagination from './DataTablePagination.vue';
import DataTableToolbar from './DataTableToolbar.vue';

function hasSoftDelete(row: any): row is { deleted_at?: Date | string | null } {
    return row && typeof row === 'object' && 'deleted_at' in row;
}

interface Props {
    data: Array<TData>;
    columns: Array<ColumnDef<TData, unknown>>;
    total: number;
    options: Partial<Pick<TableOptionsWithReactiveData<TData>, 'initialState' | 'getRowId'>>;
}

type Emits = {
    delete: [id: string];
    restore: [id: string];
    bulkDelete: [ids: string];
    bulkRestore: [ids: string];
    bulkUpdate: [ids: string, column: string, payload: string];
    change: [state: TableState];
};

const props = defineProps<Props>();

const emits = defineEmits<Emits>();

const columnFilters = ref(props.options.initialState?.columnFilters ?? []);
const globalFilter = ref(props.options.initialState?.globalFilter ?? '');
const sorting = ref(props.options.initialState?.sorting ?? []);
const pagination = ref({
    pageIndex: props.options.initialState?.pagination?.pageIndex ?? 0,
    pageSize: props.options.initialState?.pagination?.pageSize ?? 10,
});
const rowSelection = ref<{
    [x: string]: boolean;
}>({});
const columnVisibility = ref(props.options.initialState?.columnVisibility ?? {});

const selectedRows: Ref<Map<string, TData>> = ref(new Map());
const selectedIds = computed(() => Object.keys(rowSelection.value));
const hasSelection = computed(() => selectedRows.value.size > 0);
const isDeletedSelection = computed(() => {
    if (!hasSelection.value) return undefined;
    const first = selectedRows.value.values().next().value;
    if (!hasSoftDelete(first)) return undefined;
    return !!first?.deleted_at;
});

const columnHelper = createColumnHelper<TData>();

const selectionColumn = columnHelper.display({
    id: 'select',
    header: ({ table }) =>
        h(Checkbox, {
            modelValue: table.getIsAllRowsSelected() || (table.getIsSomeRowsSelected() && 'indeterminate'),
            'onUpdate:modelValue': (value) => {
                const rows = table.getRowModel().rows;

                rows.forEach((row) => {
                    if (row.getCanSelect()) {
                        row.toggleSelected(!!value);
                    }
                });
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
});

const actionColumn = columnHelper.display({
    id: 'actions',
    cell: ({ row }) => {
        const isAuthUser = usePage().props.auth.user.id === Number(row.id);
        const isDeleted = hasSoftDelete(row.original) ? Boolean(row.original.deleted_at) : undefined;
        return h(
            'div',
            { class: 'relative' },
            h(DataTableDropdown, {
                id: Number(row.id),
                isDeleted,
                isDisabled: isAuthUser || hasSelection.value,
                onDelete: (id) => emits('delete', id),
                onRestore: (id) => emits('restore', id),
            }),
        );
    },
    enableHiding: false,
    enableSorting: false,
    size: 40,
});

const tableColumns = computed(() => {
    return [selectionColumn, ...props.columns, actionColumn];
});

const tableOptions = computed<TableOptionsWithReactiveData<TData>>(() => {
    return {
        get data() {
            return props.data;
        },
        get columns() {
            return tableColumns.value;
        },
        get rowCount() {
            return props.total;
        },
        getCoreRowModel: getCoreRowModel(),

        initialState: {
            get columnVisibility() {
                return columnVisibility.value;
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

        onColumnFiltersChange: (updaterOrValue) => {
            columnFilters.value = typeof updaterOrValue === 'function' ? updaterOrValue(columnFilters.value) : updaterOrValue;
            pagination.value = { pageIndex: 0, pageSize: 10 };
            onChange();
        },
        onGlobalFilterChange: (updaterOrValue) => {
            globalFilter.value = typeof updaterOrValue === 'function' ? updaterOrValue(globalFilter.value) : updaterOrValue;
            pagination.value = { pageIndex: 0, pageSize: 10 };
            onChange();
        },
        onSortingChange: (updaterOrValue) => {
            sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
            onChange();
        },
        onPaginationChange: (updaterOrValue) => {
            pagination.value = typeof updaterOrValue === 'function' ? updaterOrValue(pagination.value) : updaterOrValue;
            onChange();
        },
        onRowSelectionChange: (updaterOrValue) => {
            rowSelection.value = typeof updaterOrValue === 'function' ? updaterOrValue(rowSelection.value) : updaterOrValue;

            const currentSelectedIds = new Set(selectedIds.value);

            table.getSelectedRowModel().rows.forEach((row) => {
                const id = row.id;
                if (!selectedRows.value.has(id)) {
                    selectedRows.value.set(id, row.original);
                }
            });

            for (const key of selectedRows.value.keys()) {
                if (!currentSelectedIds.has(key)) {
                    selectedRows.value.delete(key);
                }
            }
        },

        getRowId: props.options.getRowId,
        enableRowSelection: (row) => {
            const isAuthUser = usePage().props.auth.user.id === Number(row.id);
            if (isAuthUser) return false;

            if (!hasSoftDelete(row.original)) return true;
            const deleted = !!row.original.deleted_at;
            if (isDeletedSelection.value === true) return deleted;
            if (isDeletedSelection.value === false) return !deleted;

            return true;
        },
    };
});

const table = useVueTable(tableOptions.value);

const onChange = useDebounceFn(() => {
    emits('change', table.getState());
}, 300);

const onReset = () => {
    columnFilters.value = [];
    globalFilter.value = '';
    pagination.value = { pageIndex: 0, pageSize: 10 };
    onChange();
};

const onResetSelection = () => {
    rowSelection.value = {};
};

const onAction: HandleAction = (...args) => {
    const [action, column, payload] = args;
    const ids = selectedIds.value.join(',');

    switch (action) {
        case 'delete':
            emits('bulkDelete', ids);
            break;
        case 'restore':
            emits('bulkRestore', ids);
            break;
        case 'update':
            emits('bulkUpdate', ids, column, payload);
            break;
    }
};

const onRemoveRow = (key: string) => {
    table.setRowSelection((prev) => {
        const updated = { ...prev };
        delete updated[key];
        return updated;
    });
};

defineExpose({ onResetSelection });
</script>

<template>
    <div class="flex w-full flex-col gap-2.5 overflow-auto">
        <DataTableToolbar :table="table" v-model:search-term="globalFilter" @reset="onReset" />

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() ? 'selected' : undefined">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="table.getAllColumns().length" class="h-24 text-center"> No results. </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div :v-if="$slots.pagination" class="flex items-center justify-end space-x-2 p-1">
            <DataTablePagination :table="table" />
        </div>

        <DataTableActionBar :table="table" :is-deleted="isDeletedSelection" @action="onAction">
            <template #popover>
                <DataTableActionBarSelectionList :items="Array.from(selectedRows)" @remove="onRemoveRow">
                    <template #default="{ item }">
                        <slot name="selectionListPopover" :item="item" />
                    </template>
                </DataTableActionBarSelectionList>
            </template>
        </DataTableActionBar>
    </div>
</template>
