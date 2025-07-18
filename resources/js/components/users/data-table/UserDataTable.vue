<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import {
    type ColumnFiltersState,
    getCoreRowModel,
    type OnChangeFn,
    type PaginationState,
    type RowSelectionState,
    type SortingState,
    type Updater,
    useVueTable,
} from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import { computed, type Ref, ref, watch } from 'vue';

import DataTableActionBar from '@/components/data-table/action-bar/DataTableActionBar.vue';
import DataTableActionBarSelectionList from '@/components/data-table/action-bar/DataTableActionBarSelectionList.vue';
import DataTable from '@/components/data-table/DataTable.vue';
import DataTablePagination from '@/components/data-table/DataTablePagination.vue';
import DataTableToolbar from '@/components/data-table/DataTableToolbar.vue';
import type { HandleAction, Paginated, User } from '@/types';
import { getUserDataTableColumn } from './column';

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

watch(selectedIds, (newSelectedIds) => {
    const currentSelectedIds = new Set(newSelectedIds);

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
});

watch(
    () => selectedRows.value.size,
    () => {
        if (!selectedRows.value.size) {
            isSelectionDeleting.value = undefined;
            return;
        }

        const firstRow = selectedRows.value.values().next().value;
        const deleted = !!firstRow?.deleted_at;

        isSelectionDeleting.value = deleted ? false : true;
    },
);

const columns = getUserDataTableColumn({
    authId: usePage().props.auth.user.id,
    isSelectionDeleting,
    onDelete: (id) => handleDelete(id),
    onRestore: (id) => handleRestore(id),
});

const refetch = useDebounceFn(() => {
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

    router.visit(route('users'), {
        method: 'get',
        data,
        preserveState: true,
        replace: true,
    });
}, 300);

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
};

const handleDelete = (id: string) => {
    router.visit(route('users.destroy', id), {
        method: 'delete',
        data: { from: route().current() },
        preserveScroll: true,
        onFinish: () => {
            handleSelectionReset();
            refetch();
        },
    });
};
const handleRestore = (id: string) => {
    router.visit(route('users.restore', id), {
        method: 'patch',
        data: { from: route().current() },
        preserveScroll: true,
        onFinish: () => {
            handleSelectionReset();
            refetch();
        },
    });
};

const handleUpdate = (ids: string, column: string, payload: string) => {
    router.visit(route('users.update', ids), {
        method: 'patch',
        data: {
            from: route().current(),
            [column]: payload,
        },
        preserveScroll: true,
        onFinish: () => {
            handleSelectionReset();
            refetch();
        },
    });
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

const onRowRemove = (key: string) => {
    table.setRowSelection((prev) => {
        const updated = { ...prev };
        delete updated[key];
        return updated;
    });
};

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
    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <DataTable :table="table">
            <template #toolbar>
                <DataTableToolbar :table="table" v-model:search-term="globalFilter" @reset="handleReset" />
            </template>
            <template #pagination>
                <DataTablePagination :table="table" />
            </template>
            <template #actionBar>
                <DataTableActionBar :table="table" :is-deleting="isSelectionDeleting" @action="handleAction">
                    <template #popover>
                        <DataTableActionBarSelectionList :length="selectedIds.length" :items="Array.from(selectedRows)" @remove="onRowRemove">
                            <template #default="{ item: user }">
                                <div class="truncate">
                                    <div class="text-xs capitalize">
                                        <p>{{ user.status }} {{ user.role }}</p>
                                    </div>
                                    <h3 class="break-all">{{ user.name }}</h3>
                                    <p class="text-sm font-semibold break-all text-muted-foreground">{{ user.email }}</p>
                                </div>
                            </template>
                        </DataTableActionBarSelectionList>
                    </template>
                </DataTableActionBar>
            </template>
        </DataTable>
    </div>
</template>
