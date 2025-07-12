<script setup lang="ts" generic="TData">
import { FlexRender, type Table as TableType } from '@tanstack/vue-table';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Props {
    table: TableType<TData>;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="flex w-full flex-col gap-2.5 overflow-auto">
        <slot name="toolbar" />

        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in props.table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="props.table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in props.table.getRowModel().rows"
                            :key="row.id"
                            :data-state="row.getIsSelected() ? 'selected' : undefined"
                        >
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="props.table.getAllColumns().length" class="h-24 text-center"> No results. </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div :v-if="$slots.pagination" class="flex items-center justify-end space-x-2 p-1">
            <slot name="pagination" />
        </div>
        <slot name="actionBar" />
    </div>
</template>
