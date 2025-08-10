<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import type { Permission, Role } from '@/types';
import { getRoleDataTableColumn } from './column';

interface Props {
    roles: Role[];
    permissions: Permission[];
}

const props = defineProps<Props>();

const columns = getRoleDataTableColumn(props.permissions);
</script>

<template>
    <DataTable
        :data="props.roles"
        :columns="columns"
        :options="{
            getRowId: (row) => String(row.id),
            enableRowSelection: (row) =>
                !!row.original.can?.create || !!row.original.can?.delete || !!row.original.can?.read || !!row.original.can?.update,
            enableGlobalFilter: true,
            meta: {
                can: {
                    read: $page.props.auth.can.role?.read,
                    create: $page.props.auth.can.role?.create,
                    update: $page.props.auth.can.role?.update,
                    delete: $page.props.auth.can.role?.delete,
                },
                canRow: (row, action) => {
                    return !!row.can?.[action];
                },
            },
        }"
    />
</template>
