<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { TableState } from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import type { Paginated, Role, User } from '@/types';
import { getUserDataTableColumn } from './column';
import InviteUserDialog from './InviteUserDialog.vue';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
    roles: Role[];
}

const props = defineProps<Props>();

const columns = getUserDataTableColumn(props.roles);

const queryObject = ref<{ [k: string]: string | number }>();

const buildQuery = (state: TableState) => {
    const query: { search?: string; page?: number; per_page?: number; sort?: string; filters?: string; deleted?: string } = {};
    const search = state.globalFilter;
    const filters = state.columnFilters.filter((item) => item.id !== 'deleted_at');
    const sort = state.sorting;
    const page = state.pagination.pageIndex;
    const per_page = state.pagination.pageSize !== 10 && state.pagination.pageSize;
    const deleted = state.columnFilters.find((item) => item.id === 'deleted_at');

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

    if (per_page) {
        query.per_page = per_page;
    }

    query.page = page && page + 1;

    queryObject.value = Object.fromEntries(Object.entries(query).filter(([_key, value]) => Boolean(value)));
};

const currentRoute = route().current();
const refetch = useDebounceFn(() => {
    if (!currentRoute) return;

    router.visit(currentRoute, {
        method: 'get',
        data: queryObject.value,
        preserveState: true,
        replace: true,
    });
}, 300);
</script>

<template>
    <DataTable
        :data="props.users.data"
        :columns="columns"
        :total="props.users.meta.total"
        :options="{
            state: {
                columnFilters: props.filters ?? undefined,
                globalFilter: props.search ?? undefined,
                sorting: props.sort ?? undefined,
                pagination: { pageIndex: props.users.meta.current_page - 1, pageSize: props.users.meta.per_page },
            },
            initialState: {
                columnVisibility: {
                    deleted_at: false,
                },
            },
            manualFiltering: true,
            manualPagination: true,
            manualSorting: true,
            getRowId: (row) => String(row.id),
            enableRowSelection: (row) =>
                !!row.original.can?.create ||
                !!row.original.can?.delete ||
                !!row.original.can?.force_delete ||
                !!row.original.can?.read ||
                !!row.original.can?.restore ||
                !!row.original.can?.update,
            enableMultiRowSelection: true,
            enableGlobalFilter: true,
            meta: {
                can: {
                    update: $page.props.auth.can.user?.update,
                    delete: $page.props.auth.can.user?.delete,
                    restore: $page.props.auth.can.user?.restore,
                    force_delete: $page.props.auth.can.user?.force_delete,
                },
                canRow: (row, action) => {
                    return !!row.can?.[action];
                },
            },
        }"
        @change="
            (state) => {
                buildQuery(state);
                refetch();
            }
        "
        @success="() => refetch()"
    >
        <template #toolbarButton>
            <InviteUserDialog v-if="$page.props.auth.can?.user?.invite" :roles="props.roles.map((role) => role.name)" />
        </template>

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
</template>
