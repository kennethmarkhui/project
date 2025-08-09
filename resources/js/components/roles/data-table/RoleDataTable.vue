<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import { Role } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { columns } from './column';

interface Props {
    roles: Role[];
}

const props = defineProps<Props>();
const page = usePage();
</script>

<template>
    <DataTable
        :data="props.roles"
        :columns="columns"
        :options="{
            getRowId: (row) => String(row.id),
            enableRowSelection: (row) =>
                !!row.original.can?.create || !!row.original.can?.delete || !!row.original.can?.read || !!row.original.can?.update,
            meta: {
                can: {
                    read: page.props.auth.can.role?.read,
                    create: page.props.auth.can.role?.create,
                    update: page.props.auth.can.role?.update,
                    delete: page.props.auth.can.role?.delete,
                },
                canRow: (row, action) => {
                    return !!row.can?.[action];
                },
            },
        }"
    />
</template>
