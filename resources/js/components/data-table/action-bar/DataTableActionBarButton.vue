<script setup lang="ts">
import { useForwardProps } from 'reka-ui';

import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface Props {
    tooltip?: string;
}

const props = withDefaults(defineProps<Props>(), { tooltip: 'Tip here' });

const forwarded = useForwardProps(props);
</script>

<template>
    <Button
        v-bind="forwarded"
        variant="secondary"
        size="sm"
        class="size-7 gap-1.5 border border-secondary bg-secondary/50 hover:bg-secondary/70 [&>svg]:size-3.5"
    >
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger>
                    <div
                        tab-index="0"
                        class="rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    >
                        <slot />
                    </div>
                </TooltipTrigger>
                <TooltipContent :side-offset="10" class="border bg-accent font-semibold text-foreground dark:bg-zinc-900 [&>span]:hidden">
                    <p>{{ props.tooltip }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    </Button>
</template>
