<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DialogClose, DialogFooter } from '@/components/ui/dialog';

interface Props {
    confirmText?: string;
    variant?: 'destructive' | 'default';
    isForm?: boolean;
    isDisabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), { confirmText: 'Confirm', variant: 'default' });

const emit = defineEmits(['cancel', 'click']);

const onCancel = () => {
    if (!props.isForm) return;

    emit('cancel');
};

const onClick = () => {
    emit('click');
};
</script>

<template>
    <DialogFooter class="gap-2">
        <DialogClose as-child>
            <Button variant="secondary" @click="onCancel"> Cancel </Button>
        </DialogClose>

        <Button v-if="props.isForm" type="submit" :variant="props.variant" :disabled="props.isDisabled"> {{ props.confirmText }} </Button>
        <Button v-else :variant="props.variant" :disabled="props.isDisabled" @click="onClick"> {{ props.confirmText }} </Button>
    </DialogFooter>
</template>
