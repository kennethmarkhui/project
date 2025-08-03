<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import { Permission } from '@/types';
import { columns } from './column';

interface Props {
    permissions: Permission[];
}

const props = defineProps<Props>();
</script>

<template>
    <DataTable :data="props.permissions" :columns="columns" />
</template>
