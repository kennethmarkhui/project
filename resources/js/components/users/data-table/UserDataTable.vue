<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { InitialTableState, TableState } from '@tanstack/vue-table';
import { ref } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import type { Paginated, User } from '@/types';
import { getUserDataTableColumn } from './column';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
}

const props = defineProps<Props>();

const queryState = ref<{ [k: string]: string | number }>({});

const dataTableRef = ref<{ onResetSelection: () => void } | null>(null);

const columns = getUserDataTableColumn();

const buildQuery = (initialState?: InitialTableState) => {
    const query: { search?: string; page?: number; sort?: string; filters?: string; deleted?: string } = {};
    const search = initialState?.globalFilter;
    const filters = initialState?.columnFilters?.filter((item) => item.id !== 'deleted_at');
    const sort = initialState?.sorting;
    const page = initialState?.pagination?.pageIndex;
    const deleted = initialState?.columnFilters?.find((item) => item.id === 'deleted_at');

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

    queryState.value = Object.fromEntries(Object.entries(query).filter(([_key, value]) => Boolean(value)));
};

const refetch = () => {
    router.visit(route('users'), {
        method: 'get',
        data: queryState.value,
        preserveState: true,
        replace: true,
    });
};

const handleChange = (state: TableState) => {
    buildQuery(state);
    refetch();
};

const handleDelete = (id: string) => {
    router.visit(route('users.destroy', id), {
        method: 'delete',
        data: { from: route().current() },
        preserveScroll: true,
        onFinish: () => {
            dataTableRef.value?.onResetSelection();
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
            dataTableRef.value?.onResetSelection();
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
            dataTableRef.value?.onResetSelection();
            refetch();
        },
    });
};
</script>

<template>
    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <DataTable
            ref="dataTableRef"
            :data="props.users.data"
            :columns="columns"
            :total="props.users.meta.total"
            :options="{
                initialState: {
                    columnFilters: props.filters ?? undefined,
                    globalFilter: props.search ?? undefined,
                    sorting: props.sort ?? undefined,
                    pagination: { pageIndex: props.users.meta.current_page - 1, pageSize: props.users.meta.per_page },
                    columnVisibility: {
                        deleted_at: false,
                    },
                },
                getRowId: (row) => String(row.id),
            }"
            @delete="(id) => handleDelete(id)"
            @restore="(id) => handleRestore(id)"
            @bulk-delete="(ids) => handleDelete(ids)"
            @bulk-restore="(ids) => handleRestore(ids)"
            @bulk-update="(ids, column, payload) => handleUpdate(ids, column, payload)"
            @change="(state) => handleChange(state)"
        >
            <template #selectionListPopover="{ item: user }">
                <div class="truncate">
                    <div class="text-xs capitalize">
                        <p>{{ user.status }} {{ user.role }}</p>
                    </div>
                    <h3 class="break-all">{{ user.name }}</h3>
                    <p class="text-sm font-semibold break-all text-muted-foreground">{{ user.email }}</p>
                </div>
            </template>
        </DataTable>
    </div>
</template>
