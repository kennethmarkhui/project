<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ColumnDef } from '@tanstack/vue-table';
import { computed, h, ref } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableDropdown from '@/components/data-table/DataTableDropdown.vue';
import { Badge } from '@/components/ui/badge';
import { ROLES, STATUS } from '@/constants';
import { Paginated, User } from '@/types';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
}

const props = defineProps<Props>();

const filtersRef = ref(props.filters ?? []);
const searchRef = ref(props.search ?? '');
const sortingRef = ref(props.sort ?? []);
const paginationRef = ref({ pageIndex: props.users.meta.current_page - 1, pageSize: props.users.meta.per_page });

const total = computed(() => props.users.meta.total);

const refetch = () => {
    const query: { search?: string; page?: number; sort?: string; filters?: string; deleted?: string } = {};
    const search = searchRef.value;
    const filters = filtersRef.value.filter((item) => item.id !== 'deleted_at');
    const sort = sortingRef.value;
    const page = paginationRef.value.pageIndex;
    const deleted = filtersRef.value.find((item) => item.id === 'deleted_at');

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

    router.get(route('users'), data, {
        preserveState: true,
        replace: true,
    });
};

const columns: ColumnDef<User>[] = [
    {
        accessorKey: 'name',
        header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Name' }),
        enableColumnFilter: false,
    },
    {
        accessorKey: 'email',
        header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Email' }),
        enableColumnFilter: false,
    },
    {
        accessorKey: 'role',
        header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Role' }),
        cell: ({ cell }) => {
            const role = ROLES.find((role) => role === cell.getValue());

            if (!role) return null;

            return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, { default: () => h('span', { class: 'capitalize' }, role) });
        },
        enableColumnFilter: true,
        meta: {
            label: 'Role',
            options: ROLES.map((role) => ({
                label: role.charAt(0).toUpperCase() + role.slice(1),
                value: role,
            })),
            variant: 'multiSelect',
        },
    },
    {
        accessorKey: 'status',
        header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Status' }),
        cell: ({ cell }) => {
            const status = STATUS.find((status) => status === cell.getValue());

            if (!status) return null;

            return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, { default: () => h('span', { class: 'capitalize' }, status) });
        },
        enableColumnFilter: true,
        meta: {
            label: 'Status',
            options: STATUS.map((status) => ({
                label: status.charAt(0).toUpperCase() + status.slice(1),
                value: status,
            })),
            variant: 'multiSelect',
        },
    },
    {
        accessorKey: 'deleted_at',
        header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Deleted' }),
        cell: ({ cell }) => {
            const deletedAt = cell.getValue();

            if (!deletedAt) {
                return null;
            }

            const date = new Date(deletedAt as string);
            const formatted = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
            });

            return h('div', formatted);
        },
        enableColumnFilter: true,
        meta: {
            label: 'Deleted',
            options: [
                { label: 'Show with', value: 'with' },
                { label: 'Show only', value: 'only' },
            ],
            variant: 'select',
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const { id, deleted_at } = row.original;
            return h(
                'div',
                { class: 'relative' },
                h(DataTableDropdown, {
                    id,
                    isDeleted: Boolean(deleted_at),
                    onDelete: refetch,
                    onRestore: refetch,
                }),
            );
        },
    },
];
</script>

<template>
    <!-- <pre>{{ 'filtersRef: ' + JSON.stringify(filtersRef, null, 2) }}</pre>
    <pre>{{ 'searchRef: ' + searchRef }}</pre>
    <pre>{{ 'sortingRef: ' + JSON.stringify(sortingRef, null, 2) }}</pre>
    <pre>{{ 'paginationRef: ' + JSON.stringify(paginationRef, null, 2) }}</pre> -->
    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <DataTable
            :columns="columns"
            :data="props.users.data"
            :total="total"
            v-model:filters="filtersRef"
            v-model:search="searchRef"
            v-model:sort="sortingRef"
            v-model:pagination="paginationRef"
            @update:filters="refetch"
            @update:search="refetch"
            @update:sort="refetch"
            @update:pagination="refetch"
            @reset="refetch"
        />
    </div>
</template>
