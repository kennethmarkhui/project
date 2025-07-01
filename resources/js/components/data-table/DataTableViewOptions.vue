<script setup lang="ts" generic="TData">
import type { Table } from '@tanstack/vue-table';
import { Check, ChevronsUpDown, Settings2 } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

interface Props {
    table: Table<TData>;
}

const props = defineProps<Props>();

const columns = props.table.getAllColumns().filter((column) => typeof column.accessorFn !== undefined && column.getCanHide());
</script>

<template>
    <Popover>
        <PopoverTrigger :as-child="true">
            <Button aria-label="Toggle columns" role="combobox" variant="outline" size="sm" class="ml-auto hidden h-8 lg:flex">
                <Settings2 />
                View
                <ChevronsUpDown class="ml-auto opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent align="end" class="w-44 p-0">
            <Command>
                <CommandInput placeholder="Search columns..." />
                <CommandList>
                    <CommandEmpty>No columns found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem
                            v-for="column in columns"
                            :key="column.id"
                            :value="column.id"
                            @select="() => column.toggleVisibility(!column.getIsVisible())"
                        >
                            <span class="truncate">{{ column.columnDef.meta?.label ?? column.id }}</span>
                            <Check :class="cn('ml-auto size-4 shrink-0', column.getIsVisible() ? 'opacity-100' : 'opacity-0')" />
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
