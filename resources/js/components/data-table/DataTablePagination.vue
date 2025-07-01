<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';

import { Pagination, PaginationContent, PaginationFirst, PaginationLast, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';

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
            <div className="flex-1 whitespace-nowrap text-muted-foreground text-sm">
                Showing {{ props.table.getState().pagination.pageIndex * props.table.getState().pagination.pageSize + 1 }} to
                {{
                    props.table.getState().pagination.pageIndex * props.table.getState().pagination.pageSize +
                    props.table.getState().pagination.pageSize
                }}
                of {{ props.table.getRowCount() }} results
            </div>
            <div className="flex flex-col-reverse items-center gap-4 sm:flex-row sm:gap-6 lg:gap-8">
                <div className="flex items-center justify-center font-medium text-sm">
                    Page {{ props.table.getState().pagination.pageIndex + 1 }} of{{ ' ' }} {{ props.table.getPageCount() }}
                </div>
                <div className="flex items-center space-x-2">
                    <PaginationFirst @click="() => props.table.firstPage()" />
                    <PaginationPrevious @click="() => props.table.previousPage()" />
                    <PaginationNext @click="() => props.table.nextPage()" />
                    <PaginationLast @click="() => props.table.lastPage()" />
                </div>
            </div>
        </PaginationContent>
    </Pagination>
</template>
