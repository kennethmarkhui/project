<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';
import { Plus, X } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Link } from '@inertiajs/vue3';
import DataTableDateFilter from './DataTableDateFilter.vue';
import DataTableFacetedFilter from './DataTableFacetedFilter.vue';
import DataTableViewOptions from './DataTableViewOptions.vue';

interface Props {
    table: Table<TData>;
}

type Emits = {
    reset: [];
};

const props = defineProps<Props>();

const emit = defineEmits<Emits>();

const searchTerm = defineModel('searchTerm', { default: '' });

const isFiltered = computed(() => props.table.getState().columnFilters.length > 0 || Boolean(props.table.getState().globalFilter));

const columns = props.table.getAllColumns().filter((column) => column.getCanFilter() && column.columnDef.meta?.variant);

const onReset = () => emit('reset');
</script>

<template>
    <div class="flex w-full items-start justify-between gap-2 p-1">
        <div class="flex flex-1 flex-wrap items-center gap-2">
            <Input
                v-if="props.table.options.enableGlobalFilter"
                class="h-8 w-40 lg:w-56"
                placeholder="Search"
                :model-value="searchTerm"
                @update:model-value="(value) => table.setGlobalFilter(value)"
            />
            <template v-for="column in columns" :key="column.id">
                <DataTableFacetedFilter
                    v-if="column.columnDef.meta?.variant === 'select' || column.columnDef.meta?.variant === 'multiSelect'"
                    :column="column"
                    :multiple="column.columnDef.meta?.variant === 'multiSelect'"
                />
                <DataTableDateFilter v-else :column="column" />
            </template>

            <Button v-if="isFiltered" aria-label="Reset filters" variant="outline" size="sm" class="border-dashed" @click="onReset">
                <X />
                Reset
            </Button>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <slot name="button" />

            <Button v-if="props.table.options.meta?.can?.create" as-child aria-label="Create" variant="outline" size="sm" class="ml-auto flex h-8">
                <Link :href="`/${route().current()}/create`">
                    <Plus />
                    Create
                </Link>
            </Button>

            <DataTableViewOptions :table="props.table" />
        </div>
    </div>
</template>
