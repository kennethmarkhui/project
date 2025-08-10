<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';

import { Pagination, PaginationContent, PaginationFirst, PaginationLast, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Props {
    table: Table<TData>;
}

const props = defineProps<Props>();
</script>

<template>
    <Pagination
        :items-per-page="props.table.getState().pagination.pageSize"
        :page="props.table.getState().pagination.pageIndex + 1"
        :total="props.table.getRowCount()"
    >
        <PaginationContent class="flex w-full flex-col-reverse items-center justify-between gap-4 overflow-auto p-1 sm:flex-row sm:gap-8">
            <div class="flex-1 text-sm whitespace-nowrap text-muted-foreground">
                Showing {{ props.table.getState().pagination.pageIndex * props.table.getState().pagination.pageSize + 1 }} to
                {{
                    Math.min(
                        (props.table.getState().pagination.pageIndex + 1) * props.table.getState().pagination.pageSize,
                        props.table.getRowCount(),
                    )
                }}
                of {{ props.table.getRowCount() }} results
            </div>
            <div class="flex flex-col-reverse items-center gap-4 sm:flex-row sm:gap-6 lg:gap-8">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium whitespace-nowrap">Rows per page</p>
                    <Select
                        :model-value="`${props.table.getState().pagination.pageSize}`"
                        @update:model-value="(value) => props.table.setPageSize(Number(value))"
                    >
                        <SelectTrigger class="h-8 w-[4.5rem]">
                            <SelectValue :placeholder="`${props.table.getState().pagination.pageSize}`" />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="`${pageSize}`">
                                {{ pageSize }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex items-center justify-center text-sm font-medium">
                    Page {{ props.table.getState().pagination.pageIndex + 1 }} of{{ ' ' }} {{ props.table.getPageCount() }}
                </div>
                <div class="flex items-center space-x-2">
                    <PaginationFirst @click="() => props.table.firstPage()" />
                    <PaginationPrevious @click="() => props.table.previousPage()" />
                    <PaginationNext @click="() => props.table.nextPage()" />
                    <PaginationLast @click="() => props.table.lastPage()" />
                </div>
            </div>
        </PaginationContent>
    </Pagination>
</template>
