<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { getUniqueValues } from '@/lib/utils';

interface Props {
    items: string[];
}

const props = defineProps<Props>();

const headRows = getUniqueValues(props.items, 0);
const headColumns = getUniqueValues(props.items, 1);
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader>
                <TableRow class="*:border-border hover:bg-transparent [&>:not(:last-child)]:border-r">
                    <TableCell />
                    <TableHead v-for="headColumn in headColumns" :key="headColumn" class="h-auto py-3 text-foreground">
                        <span class="block w-full text-center leading-4 whitespace-nowrap capitalize">
                            {{ headColumn }}
                        </span>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="headRow in headRows" :key="headRow" class="*:border-border [&>:not(:last-child)]:border-r">
                    <TableHead class="text-right font-medium text-foreground capitalize">
                        {{ headRow }}
                    </TableHead>
                    <TableCell v-for="headColumn in headColumns" :key="`${headRow}.${headColumn}`" class="text-center">
                        <slot :headRow="headRow" :headColumn="headColumn" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
