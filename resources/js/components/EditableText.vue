<script setup lang="ts">
import { Check, Pencil, X } from 'lucide-vue-next';
import {
    EditableArea,
    EditableCancelTrigger,
    EditableEditTrigger,
    EditableInput,
    EditablePreview,
    EditableRoot,
    type EditableRootEmits,
    type EditableRootProps,
    EditableSubmitTrigger,
    useForwardPropsEmits,
} from 'reka-ui';

import TooltipButton from './TooltipButton.vue';

const props = defineProps<EditableRootProps>();

const emits = defineEmits<EditableRootEmits>();

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <div class="inline-flex">
        <EditableRoot v-slot="{ isEditing }" v-bind="forwarded" class="flex gap-4" auto-resize>
            <EditableArea class="text-stone-700 dark:text-white">
                <EditablePreview />
                <EditableInput class="placeholder:text-stone-700 dark:placeholder:text-white" />
            </EditableArea>
            <div v-if="!isEditing" class="flex gap-2">
                <EditableEditTrigger as-child>
                    <TooltipButton tooltip="Edit" variant="clear">
                        <Pencil />
                    </TooltipButton>
                </EditableEditTrigger>
                <slot />
            </div>
            <div v-else class="flex gap-2">
                <EditableSubmitTrigger as-child>
                    <TooltipButton tooltip="Confirm" variant="clear">
                        <Check />
                    </TooltipButton>
                </EditableSubmitTrigger>
                <EditableCancelTrigger as-child>
                    <TooltipButton tooltip="Cancel" variant="clear">
                        <X />
                    </TooltipButton>
                </EditableCancelTrigger>
            </div>
        </EditableRoot>
    </div>
</template>
