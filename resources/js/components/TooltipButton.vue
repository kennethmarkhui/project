<script setup lang="ts">
import { useForwardProps } from 'reka-ui';

import { Button, ButtonVariants } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

interface Props {
    tooltip?: string;
    variant?: ButtonVariants['variant'];
}

const props = withDefaults(defineProps<Props>(), { tooltip: 'Tip here' });

const forwardedProps = useForwardProps(props);
</script>

<template>
    <Button v-bind="forwardedProps" :variant="props.variant" size="sm-icon">
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger as-child>
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
