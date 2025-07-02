<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableDropdown from '@/components/data-table/DataTableDropdown.vue';
import { Badge } from '@/components/ui/badge';
import { ROLES, STATUS } from '@/constants';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Paginated, User } from '@/types';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const props = defineProps<Props>();

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
        enableColumnFilter: true,
        cell: ({ cell }) => {
            const role = ROLES.find((role) => role === cell.getValue());

            if (!role) return null;

            return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, { default: () => h('span', { class: 'capitalize' }, role) });
        },
        meta: {
            label: 'Role',
            options: ROLES.map((role) => ({
                label: role.charAt(0).toUpperCase() + role.slice(1),
                value: role,
            })),
        },
    },
    {
        accessorKey: 'status',
        header: ({ column }) => h(DataTableColumnHeader<User, unknown>, { column, title: 'Status' }),
        enableColumnFilter: true,
        cell: ({ cell }) => {
            const status = STATUS.find((status) => status === cell.getValue());

            if (!status) return null;

            return h(Badge, { variant: 'outline', class: 'py-1 [&>svg]:size-3.5' }, { default: () => h('span', { class: 'capitalize' }, status) });
        },
        meta: {
            label: 'Status',
            options: STATUS.map((status) => ({
                label: status.charAt(0).toUpperCase() + status.slice(1),
                value: status,
            })),
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const { id } = row.original;
            return h('div', { class: 'relative' }, h(DataTableDropdown, { id }));
        },
    },
];
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <DataTable :columns="columns" :data="props.users" :filters="props.filters" :search="props.search" :sort="props.sort" />
        </div>
    </AppLayout>
</template>
