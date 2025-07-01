<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

import DataTable from '@/components/data-table/DataTable.vue';
import DataTableDropdown from '@/components/data-table/DataTableDropdown.vue';
import { ROLES, STATUS } from '@/constants';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Paginated, User } from '@/types';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
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
        header: 'Name',
        enableColumnFilter: false,
    },
    {
        accessorKey: 'email',
        header: 'Email',
        enableColumnFilter: false,
    },
    {
        accessorKey: 'role',
        header: 'Role',
        enableColumnFilter: true,
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
        header: 'Status',
        enableColumnFilter: true,
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
            <DataTable :columns="columns" :data="props.users" :filters="props.filters" :search="props.search" />
        </div>
    </AppLayout>
</template>
