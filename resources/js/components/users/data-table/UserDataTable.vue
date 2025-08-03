<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

import DataTable from '@/components/data-table/DataTable.vue';
import type { Paginated, Role, User } from '@/types';
import { getUserDataTableColumn } from './column';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
    roles: Role[];
}

const props = defineProps<Props>();

const page = usePage();

const columns = getUserDataTableColumn(props.roles);
</script>

<template>
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
            enableRowSelection: (row) => page.props.auth.user.id !== Number(row.id),
            meta: {
                can: {
                    update: page.props.auth.can.user?.update,
                    delete: page.props.auth.can.user?.delete,
                    restore: page.props.auth.can.user?.restore,
                    force_delete: page.props.auth.can.user?.force_delete,
                },
                enableSearch: true,
            },
        }"
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
</template>
