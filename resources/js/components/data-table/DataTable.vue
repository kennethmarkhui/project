<script setup lang="ts" generic="TData extends RowData">
import { router } from '@inertiajs/vue3';
import {
    type ColumnDef,
    type ColumnFiltersState,
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    type PaginationState,
    type Row,
    type RowData,
    type RowSelectionState,
    type SortingState,
    type TableOptionsWithReactiveData,
    useVueTable,
    type VisibilityState,
} from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import { computed, h, ref, type Ref } from 'vue';

import { Checkbox } from '@/components/ui/checkbox';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { getCommonPinningStyles } from '@/lib/utils';
import type { RequireAtLeastOne } from '@/types';
import DataTableActionBar from './action-bar/DataTableActionBar.vue';
import DataTableActionBarSelectionList from './action-bar/DataTableActionBarSelectionList.vue';
import DataTableColumnActionDropdown from './DataTableColumnActionDropdown.vue';
import DataTablePagination from './DataTablePagination.vue';
import DataTableToolbar from './DataTableToolbar.vue';

function hasSoftDelete(row: any): row is { deleted_at?: Date | string | null } {
    return row && typeof row === 'object' && 'deleted_at' in row;
}

interface Props {
    data: TData[];
    columns: ColumnDef<TData, unknown>[];
    total?: number;
    options?: RequireAtLeastOne<
        Partial<Pick<TableOptionsWithReactiveData<TData>, 'initialState' | 'getRowId' | 'enableRowSelection' | 'enableMultiRowSelection' | 'meta'>>
    >;
}

const props = defineProps<Props>();

const columnFilters = ref<ColumnFiltersState>(props.options?.initialState?.columnFilters ?? []);
const globalFilter = ref<string>(props.options?.initialState?.globalFilter ?? '');
const sorting = ref<SortingState>(props.options?.initialState?.sorting ?? []);
const pagination = ref<PaginationState>({
    pageIndex: props.options?.initialState?.pagination?.pageIndex ?? 0,
    pageSize: props.options?.initialState?.pagination?.pageSize ?? 10,
});
const rowSelection = ref<RowSelectionState>({});
const columnVisibility = ref<VisibilityState>(props.options?.initialState?.columnVisibility ?? {});

const selectedRows: Ref<Map<string, TData>> = ref(new Map());
const selectedIds = computed(() => Object.keys(rowSelection.value));
const hasSelection = computed(() => selectedRows.value.size > 0);
const isDeletedSelection = computed(() => {
    if (!hasSelection.value) return undefined;
    const first = selectedRows.value.values().next().value;
    if (!hasSoftDelete(first)) return undefined;
    return !!first?.deleted_at;
});

const isRowSelectionEnabled = (row: Row<TData>): boolean => {
    const enableRowSelection = props.options?.enableRowSelection;

    if (typeof enableRowSelection === 'boolean') {
        return enableRowSelection;
    }
    if (typeof enableRowSelection === 'function') {
        return enableRowSelection(row);
    }
    return true;
};

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
    cell: ({ row, table }) => {
        const isRowDisabled = !isRowSelectionEnabled(row);
        const isDeleted = hasSoftDelete(row.original) ? Boolean(row.original.deleted_at) : undefined;
        const isDisabled = isRowDisabled || hasSelection.value;

        return h(
            'div',
            { class: 'relative' },
            h(DataTableColumnActionDropdown, {
                id: Number(row.id),
                isDeleted,
                isDisabled,
                row: row.original,
                tableMeta: table.options.meta as any,
                onSuccess: () => refetch(),
            }),
        );
    },
    enableHiding: false,
    enableSorting: false,
    size: 40,
});

const tableColumns = computed(() => {
    const meta = props.options?.meta;

    const showActionColumn = meta?.can?.read || meta?.can?.update || meta?.can?.delete || meta?.can?.force_delete || meta?.can?.restore;
    const columns: ColumnDef<TData, unknown>[] = [
        ...(props.options?.enableMultiRowSelection ? [selectionColumn] : []),
        ...props.columns,
        ...(showActionColumn ? [actionColumn] : []),
    ];

    return columns.filter(Boolean);
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
            get columnPinning() {
                return { right: ['actions'] };
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

        getRowId: props.options?.getRowId,
        enableRowSelection: (row) => {
            if (!isRowSelectionEnabled(row)) return false;

            if (hasSoftDelete(row.original)) {
                const isDeleted = !!row.original.deleted_at;
                if (isDeletedSelection.value === true && !isDeleted) return false;
                if (isDeletedSelection.value === false && isDeleted) return false;
            }

            return true;
        },
        enableMultiRowSelection: props.options?.enableMultiRowSelection,

        meta: props.options?.meta,
    };
});

const table = useVueTable(tableOptions.value);

const buildQuery = () => {
    const query: { search?: string; page?: number; sort?: string; filters?: string; deleted?: string } = {};
    const search = globalFilter.value;
    const filters = columnFilters.value.filter((item) => item.id !== 'deleted_at');
    const sort = sorting.value;
    const page = pagination.value.pageIndex;
    const deleted = columnFilters.value.find((item) => item.id === 'deleted_at');

    if (search) {
        query.search = search;
    }

    if (Array.isArray(filters) && filters.length > 0) {
        query.filters = JSON.stringify(filters);
    }

    if (Array.isArray(sort) && sort.length > 0) {
        query.sort = JSON.stringify(sort);
    }

    if (deleted && Array.isArray(deleted.value) && deleted.value.length > 0) {
        query.deleted = deleted.value[0];
    }

    query.page = page && page + 1;

    return Object.fromEntries(Object.entries(query).filter(([_key, value]) => Boolean(value)));
};

const currentRoute = route().current();
const refetch = () => {
    if (!currentRoute) return;

    router.visit(currentRoute, {
        method: 'get',
        data: buildQuery(),
        preserveState: true,
        replace: true,
    });
};

const onChange = useDebounceFn(() => {
    refetch();
}, 300);

const onReset = () => {
    columnFilters.value = [];
    globalFilter.value = '';
    pagination.value = { pageIndex: 0, pageSize: 10 };
    onChange();
};

const onRemoveRow = (key: string) => {
    table.setRowSelection((prev) => {
        const updated = { ...prev };
        delete updated[key];
        return updated;
    });
};
</script>

<template>
    <div class="flex w-full flex-col gap-2.5 overflow-auto">
        <DataTableToolbar :table="table" v-model:search-term="globalFilter" @reset="onReset" />

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                            :style="{ ...getCommonPinningStyles({ column: header.column }) }"
                        >
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() ? 'selected' : undefined">
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                :style="{ ...getCommonPinningStyles({ column: cell.column }) }"
                            >
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

        <div v-if="props.options?.initialState?.pagination" class="flex items-center justify-end space-x-2 p-1">
            <DataTablePagination :table="table" />
        </div>

        <DataTableActionBar :table="table" :is-deleted="isDeletedSelection" @success="() => refetch()">
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
