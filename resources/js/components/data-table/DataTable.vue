<script setup lang="ts" generic="TData, TValue">
import {
    type ColumnDef,
    type ColumnFiltersState,
    FlexRender,
    getCoreRowModel,
    type OnChangeFn,
    type PaginationState,
    type SortingState,
    type Updater,
    useVueTable,
} from '@tanstack/vue-table';
import { debounce } from 'lodash';
import { Ref } from 'vue';

import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DataTablePagination from './DataTablePagination.vue';
import DataTableToolbar from './DataTableToolbar.vue';

interface Props {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    total: number;
}

type Emits = {
    reset: [];
};

const props = defineProps<Props>();

const emits = defineEmits<Emits>();

const columnFilters = defineModel<ColumnFiltersState>('filters', { default: [] });
const globalFilter = defineModel<string>('search', { default: '' });
const sorting = defineModel<SortingState>('sort', { default: [] });
const pagination = defineModel<PaginationState>('pagination', {
    default: {
        pageIndex: 0,
        pageSize: 10,
    },
});

const debouncedInput = debounce((value: string | number) => table.setGlobalFilter(value), 300);

const valueUpdater = <T extends Updater<unknown>>(updaterOrValue: T, ref: Ref) => {
    ref.value = typeof updaterOrValue === 'function' ? updaterOrValue(ref.value) : updaterOrValue;
};

const onColumnFiltersChange: OnChangeFn<ColumnFiltersState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, columnFilters);
    pagination.value = { pageIndex: 0, pageSize: 10 };
};

const onGlobalFilterChange: OnChangeFn<any> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, globalFilter);
    pagination.value = { pageIndex: 0, pageSize: 10 };
};

const onSortingChange: OnChangeFn<SortingState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, sorting);
};

const onPaginationChange: OnChangeFn<PaginationState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, pagination);
};

const handleReset = () => {
    columnFilters.value = [];
    globalFilter.value = '';
    pagination.value = { pageIndex: 0, pageSize: 10 };
    emits('reset');
};

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    get rowCount() {
        return props.total;
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
    },
    manualFiltering: true,
    manualSorting: true,
    manualPagination: true,
    onColumnFiltersChange,
    onGlobalFilterChange,
    onSortingChange,
    onPaginationChange,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <!-- <pre>{{ 'columnFilters: ' + JSON.stringify(columnFilters, null, 2) }}</pre>
    <pre>{{ 'globalFilter: ' + globalFilter }}</pre>
    <pre>{{ 'sorting: ' + JSON.stringify(sorting, null, 2) }}</pre>
    <pre>{{ 'pagination: ' + JSON.stringify(pagination, null, 2) }}</pre> -->
    <div class="flex w-full flex-col gap-2.5 overflow-auto">
        <DataTableToolbar :table="table" @reset="handleReset">
            <template #search>
                <Input
                    class="h-8 w-40 lg:w-56"
                    placeholder="Search"
                    :default-value="globalFilter"
                    :model-value="globalFilter"
                    @update:model-value="debouncedInput"
                />
            </template>
        </DataTableToolbar>

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
                            <TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-end space-x-2 p-1">
            <DataTablePagination :table="table" />
        </div>
    </div>
</template>
