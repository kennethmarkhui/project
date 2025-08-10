<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import type { Permission, Role } from '@/types';
import { getPermissionDataTableColumn } from './column';

interface Props {
    permissions: Permission[];
    roles: Role[];
}

const props = defineProps<Props>();

const columns = getPermissionDataTableColumn(props.roles);
</script>

<template>
    <DataTable :data="props.permissions" :columns="columns" :options="{ enableGlobalFilter: true }" />
</template>
