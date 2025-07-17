<script setup lang="ts" generic="TData">
import { X } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandShortcut } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

interface Props {
    length: number;
    items: Array<[string, TData]>;
}

type Emits = {
    remove: [key: string];
};

const props = defineProps<Props>();

const emit = defineEmits<Emits>();
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="link" class="p-0 text-xs font-normal whitespace-nowrap"> {{ props.length }} selected </Button>
        </PopoverTrigger>
        <PopoverContent :collision-padding="{ left: 20, right: 20 }" :side-offset="20">
            <Command>
                <CommandInput />
                <CommandList>
                    <CommandEmpty>No results found.</CommandEmpty>
                    <CommandGroup>
                        <CommandItem v-for="[key, item] in props.items" :key="key" :value="key">
                            <slot :item="item" />
                            <CommandShortcut>
                                <Button variant="ghost" size="icon" class="size-5" @click="emit('remove', key)">
                                    <X />
                                </Button>
                            </CommandShortcut>
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
