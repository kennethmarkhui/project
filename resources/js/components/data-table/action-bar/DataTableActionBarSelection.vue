<script setup lang="ts">
import { X } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

interface Props {
    length: number;
}

type Emits = {
    clear: [];
};

const props = defineProps<Props>();

const emit = defineEmits<Emits>();
</script>

<template>
    <div class="flex h-7 items-center rounded-md border pr-1 pl-2.5">
        <slot name="popover">
            <span class="text-xs whitespace-nowrap"> {{ props.length }} selected </span>
        </slot>

        <Separator orientation="vertical" class="mr-1 ml-2 data-[orientation=vertical]:h-4" />
        <Tooltip>
            <TooltipTrigger as-child>
                <Button variant="ghost" size="icon" class="size-5" @click="emit('clear')">
                    <X class="size-3.5" />
                </Button>
            </TooltipTrigger>
            <TooltipContent
                :side-offset="10"
                class="flex items-center gap-2 border bg-accent px-2 py-1 font-semibold text-foreground dark:bg-zinc-900 [&>span]:hidden"
            >
                <p>Clear selection</p>
            </TooltipContent>
        </Tooltip>
    </div>
</template>
