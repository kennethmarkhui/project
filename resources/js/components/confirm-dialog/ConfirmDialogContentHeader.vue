<script setup lang="ts">
import { DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';

interface Props {
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Are you sure you want to confirm?',
    description: 'Once confirmed, this action will be completed. Make sure you want to continue.',
});
</script>

<template>
    <DialogHeader class="space-y-3">
        <DialogTitle> {{ props.title }} </DialogTitle>
        <DialogDescription> {{ props.description }} </DialogDescription>
    </DialogHeader>
</template>
