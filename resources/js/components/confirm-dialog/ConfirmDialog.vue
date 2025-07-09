<script setup lang="ts">
import { type DialogRootEmits, type DialogRootProps, useForwardPropsEmits } from 'reka-ui';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';
import ConfirmDialogContentFooter from './ConfirmDialogContentFooter.vue';
import ConfirmDialogContentHeader from './ConfirmDialogContentHeader.vue';

interface Props extends DialogRootProps {
    confirmText?: string;
    description?: string;
    asButton?: boolean;
    isDisabled?: boolean;
    isForm?: boolean;
    title?: string;
    variant?: 'destructive' | 'default';
}

type Emits = { cancel: []; click: []; submit: [e: Event] } & DialogRootEmits;

const props = withDefaults(defineProps<Props>(), {
    confirmText: 'Confirm',
    variant: 'default',
});
const emits = defineEmits<Emits>();

const forwarded = useForwardPropsEmits(props, emits);

const onCancel = () => {
    emits('cancel');
};

const onClick = () => {
    emits('click');
};

const onSubmit = (e: Event) => {
    emits('submit', e);
};
</script>

<template>
    <Dialog v-bind="forwarded">
        <DialogTrigger v-if="props.asButton" as-child>
            <Button :variant="props.variant"> {{ props.confirmText }} </Button>
        </DialogTrigger>
        <DialogContent>
            <form v-if="$slots.contentForm" class="space-y-6" @submit="onSubmit">
                <ConfirmDialogContentHeader :title="props.title" :description="props.description" />

                <slot name="contentForm"></slot>

                <ConfirmDialogContentFooter
                    :confirm-text="props.confirmText"
                    :variant="props.variant"
                    :is-disabled="props.isDisabled"
                    :is-form="props.isForm"
                    @cancel="onCancel"
                />
            </form>

            <template v-else>
                <ConfirmDialogContentHeader :title="props.title" :description="props.description" />

                <ConfirmDialogContentFooter
                    :confirm-text="props.confirmText"
                    :variant="props.variant"
                    :is-disabled="props.isDisabled"
                    @click="onClick"
                />
            </template>
        </DialogContent>
    </Dialog>
</template>
