<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DialogClose, DialogFooter } from '@/components/ui/dialog';

interface Props {
    confirmText: string;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    isForm?: boolean;
    isDisabled?: boolean;
}

type Emits = {
    cancel: [];
    click: [];
};

const props = defineProps<Props>();

const emit = defineEmits<Emits>();

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
