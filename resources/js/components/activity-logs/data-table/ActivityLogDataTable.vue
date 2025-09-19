<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import type { TableState } from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import type { ActivityLog, Paginated } from '@/types';
import { getActivityLogDataTableColumn } from './column';

interface Props {
    activity_logs: Paginated<ActivityLog[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    sort: Array<{ id: string; desc: boolean }> | null;
    events: string[];
    log_names: string[];
}

const props = defineProps<Props>();

const columns = getActivityLogDataTableColumn({ events: props.events, log_names: props.log_names });

const queryObject = ref<{ [k: string]: string | number }>();

const buildQuery = (state: TableState) => {
    const query: { page?: number; per_page?: number; sort?: string; filters?: string } = {};
    const filters = state.columnFilters;
    const sort = state.sorting;
    const page = state.pagination.pageIndex;
    const per_page = state.pagination.pageSize !== 10 && state.pagination.pageSize;

    if (Array.isArray(filters) && filters.length > 0) {
        query.filters = JSON.stringify(filters);
    }

    if (Array.isArray(sort) && sort.length > 0) {
        query.sort = JSON.stringify(sort);
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

    router.visit(route(currentRoute), {
        method: 'get',
        data: queryObject.value,
        preserveState: true,
        replace: true,
    });
}, 300);
</script>

<template>
    <DataTable
        :data="props.activity_logs.data"
        :columns="columns"
        :total="props.activity_logs.meta.total"
        :options="{
            state: {
                columnFilters: props.filters ?? undefined,
                sorting: props.sort ?? undefined,
                pagination: { pageIndex: props.activity_logs.meta.current_page - 1, pageSize: props.activity_logs.meta.per_page },
            },
            manualFiltering: true,
            manualPagination: true,
            manualSorting: true,
        }"
        @change="
            (state) => {
                buildQuery(state);
                refetch();
            }
        "
        @success="() => refetch()"
    />
</template>
