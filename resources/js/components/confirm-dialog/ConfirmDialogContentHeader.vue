<script setup lang="ts">
import { DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';

interface Props {
    title: string;
    description: string;
}

const props = defineProps<Props>();
</script>

<template>
    <DialogHeader class="space-y-3">
        <DialogTitle> {{ props.title }} </DialogTitle>
        <DialogDescription> {{ props.description }} </DialogDescription>
    </DialogHeader>
</template>
