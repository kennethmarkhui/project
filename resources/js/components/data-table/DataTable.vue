<script setup lang="ts" generic="TData, TValue">
import { router, usePage } from '@inertiajs/vue3';
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
import { Ref, ref } from 'vue';

import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import type { Paginated } from '@/types';
import DataTablePagination from './DataTablePagination.vue';
import DataTableToolbar from './DataTableToolbar.vue';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: Paginated<TData[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
}>();

const path = usePage().props.ziggy.location;

const columnFilters = ref<ColumnFiltersState>(props.filters ?? []);
const globalFilter = ref(props.search ?? '');
const sorting = ref<SortingState>(props.sort ?? []);
const pagination = ref<PaginationState>({
    pageIndex: props.data.meta.current_page - 1,
    pageSize: props.data.meta.per_page,
});

const refetch = () => {
    const query: { search?: string; page?: number; sort?: string; filters?: string } = {};
    const search = globalFilter.value;
    const filters = columnFilters.value;
    const sort = sorting.value;
    const page = pagination.value.pageIndex;

    if (search) {
        query.search = search;
    }

    if (filters.length > 0) {
        query.filters = JSON.stringify(filters);
    }

    if (sort.length > 0) {
        query.sort = JSON.stringify(sort);
    }

    query.page = page && page + 1;

    const data = Object.fromEntries(Object.entries(query).filter(([_key, value]) => Boolean(value)));

    router.get(path, data, {
        preserveState: true,
        replace: true,
    });
};

const debouncedInput = debounce((value: string | number) => table.setGlobalFilter(value), 300);

const valueUpdater = <T extends Updater<unknown>>(updaterOrValue: T, ref: Ref) => {
    ref.value = typeof updaterOrValue === 'function' ? updaterOrValue(ref.value) : updaterOrValue;
};

const handleFilterChange: OnChangeFn<ColumnFiltersState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, columnFilters);
    pagination.value = { pageIndex: 0, pageSize: props.data.meta.per_page };
    refetch();
};

const handleSearchChange: OnChangeFn<any> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, globalFilter);
    pagination.value = { pageIndex: 0, pageSize: props.data.meta.per_page };
    refetch();
};

const handleSortChange: OnChangeFn<SortingState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, sorting);
    refetch();
};

const handlePageChange: OnChangeFn<PaginationState> = (updaterOrValue) => {
    valueUpdater(updaterOrValue, pagination);
    refetch();
};

const handleReset = () => {
    columnFilters.value = [];
    globalFilter.value = '';
    pagination.value = { pageIndex: 0, pageSize: props.data.meta.per_page };
    refetch();
};

const table = useVueTable({
    get data() {
        return props.data.data;
    },
    get columns() {
        return props.columns;
    },
    get rowCount() {
        return props.data.meta.total;
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
    onColumnFiltersChange: handleFilterChange,
    onGlobalFilterChange: handleSearchChange,
    onSortingChange: handleSortChange,
    onPaginationChange: handlePageChange,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <!-- <pre>{{ 'columnFilters: ' + JSON.stringify(columnFilters, null, 2) }}</pre>
    <pre>{{ 'globalFilter: ' + globalFilter }}</pre>
    <pre>{{ 'pagination: ' + JSON.stringify(pagination, null, 2) }}</pre> -->
    <!-- <pre>{{ 'sorting: ' + JSON.stringify(sorting, null, 2) }}</pre> -->
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
