<script setup lang="ts" generic="TData, TValue">
import { Column } from '@tanstack/vue-table';
import { ref } from 'vue';

import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import DataTableActionBarButton from './DataTableActionBarButton.vue';

interface Props {
    column?: Column<TData, TValue>;
    tooltip?: string;
    isDisabled?: boolean;
}

type Emits = {
    select: [value: string];
};

const props = defineProps<Props>();

const emit = defineEmits<Emits>();

const open = ref(false);
const options = ref(props.column?.columnDef.meta?.options ?? []);

const onSelect = (value: string) => {
    emit('select', value);
    open.value = false;
};
</script>

<template>
    <Popover :open="open" @update:open="(value) => (open = value)">
        <PopoverTrigger as-child>
            <DataTableActionBarButton :tooltip="props.tooltip" :disabled="props.isDisabled">
                <slot />
            </DataTableActionBarButton>
        </PopoverTrigger>
        <PopoverContent class="w-40 p-0" align="start">
            <Command>
                <CommandInput />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem v-for="option in options" :key="option.value" :value="option.value" @select="() => onSelect(option.value)">
                            {{ option.label }}
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
