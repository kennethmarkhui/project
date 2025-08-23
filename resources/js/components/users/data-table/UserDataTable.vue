<script setup lang="ts">
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
