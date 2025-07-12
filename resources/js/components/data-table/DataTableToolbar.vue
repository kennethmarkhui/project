<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';
import { X } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import DataTableFacetedFilter from './DataTableFacetedFilter.vue';
import DataTableViewOptions from './DataTableViewOptions.vue';

interface Props {
    table: Table<TData>;
}

const props = defineProps<Props>();

const columns = props.table.getAllColumns().filter((column) => column.getCanFilter() && column.columnDef.meta);

const emit = defineEmits(['reset']);

const isFiltered = computed(() => props.table.getState().columnFilters.length > 0 || Boolean(props.table.getState().globalFilter));

const onReset = () => emit('reset');
</script>

<template>
    <div class="flex w-full items-start justify-between gap-2 p-1">
        <div class="flex flex-1 flex-wrap items-center gap-2">
            <slot name="search" />
            <DataTableFacetedFilter
                v-for="column in columns"
                :key="column.id"
                :column="column"
                :multiple="column.columnDef.meta?.variant === 'multiSelect'"
            />

            <Button v-if="isFiltered" aria-label="Reset filters" variant="outline" size="sm" class="border-dashed" @click="onReset">
                <X />
                Reset
            </Button>
        </div>
        <div class="flex items-center gap-2">
            <DataTableViewOptions :table="props.table" />
        </div>
    </div>
</template>
