<script setup lang="ts" generic="TData, TValue">
import type { Column } from '@tanstack/vue-table';
import { Check, PlusCircle, XCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import type { Option } from '@/types/tanstack-table';

interface Props {
    column?: Column<TData, TValue>;
    multiple?: boolean;
}

const props = defineProps<Props>();

const title = props.column?.columnDef.meta?.label ?? 'Untitled';

const options = ref(props.column?.columnDef.meta?.options ?? []);

const selectedValues = ref(new Set(Array.from((props.column?.getFilterValue() as Iterable<unknown>) ?? [])));

const filteredOptions = computed(() => options.value.filter((option) => selectedValues.value.has(option.value)));

watch(
    () => props.column?.getFilterValue(),
    (filterValue) => {
        if (!filterValue) {
            selectedValues.value.clear();
            return;
        }

        selectedValues.value = new Set(Array.from(filterValue as Iterable<unknown>));
    },
);

const onItemSelect = (option: Option, isSelected: boolean) => {
    if (!props.column) return;

    if (props.multiple) {
        if (isSelected) {
            selectedValues.value.delete(option.value);
        } else {
            selectedValues.value.add(option.value);
        }
        props.column.setFilterValue(selectedValues.value.size ? Array.from(selectedValues.value) : undefined);
    } else {
        props.column.setFilterValue(isSelected ? undefined : [option.value]);
    }
};

const onReset = () => {
    selectedValues.value.clear();
    props.column?.setFilterValue(undefined);
};
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="border-dashed">
                <div
                    v-if="selectedValues.size"
                    :aria-label="`Clear ${title} filter`"
                    tab-index="0"
                    @click.stop="() => onReset()"
                    class="rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <XCircle />
                </div>
                <PlusCircle v-else-if="!selectedValues.size" />
                {{ title }}
                <template v-if="selectedValues.size">
                    <Separator orientation="vertical" class="mx-0.5 data-[orientation=vertical]:h-4" />
                    <Badge variant="secondary" class="rounded-sm px-1 font-normal lg:hidden"> {{ selectedValues.size }} </Badge>
                    <div class="hidden items-center gap-1 lg:flex">
                        <Badge v-if="selectedValues.size > 2" variant="secondary" class="rounded-sm px-1 font-normal">
                            {{ selectedValues.size }} selected
                        </Badge>
                        <Badge v-else v-for="option in filteredOptions" :key="option.value" variant="secondary" class="rounded-sm px-1 font-normal">
                            {{ option.label }}
                        </Badge>
                    </div>
                </template>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-[12.5rem] p-0" align="start">
            <Command>
                <CommandInput :placeholder="title" />
                <CommandList class="max-h-full">
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup class="max-h-[18.75rem] overflow-x-hidden overflow-y-auto">
                        <CommandItem
                            v-for="option in options"
                            :key="option.value"
                            :value="option.value"
                            @select="() => onItemSelect(option, selectedValues.has(option.value))"
                        >
                            <div
                                :class="
                                    cn(
                                        'flex size-4 items-center justify-center rounded-sm border border-primary',
                                        selectedValues.has(option.value) ? 'bg-primary' : 'opacity-50 [&_svg]:invisible',
                                    )
                                "
                            >
                                <Check />
                            </div>
                            <span class="truncate">{{ option.label }}</span>
                            <span v-if="option.count" class="ml-auto font-mono text-xs"> {{ option.count }} </span>
                        </CommandItem>
                    </CommandGroup>
                    <template v-if="selectedValues.size">
                        <CommandSeparator />
                        <CommandGroup>
                            <CommandItem value="" @select.stop="() => onReset()" class="justify-center text-center"> Clear filters </CommandItem>
                        </CommandGroup>
                    </template>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
