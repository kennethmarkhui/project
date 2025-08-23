<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';

import { Toaster } from '@/components/ui/sonner';

import 'vue-sonner/style.css';

const TOAST_TYPES = ['success', 'error'] as const;

export type ToastType = (typeof TOAST_TYPES)[number];

function isToastType(key: string): key is ToastType {
    return TOAST_TYPES.includes(key as ToastType);
}

onMounted(() => {
    watch(
        () => usePage().props.flash,
        (flash) => {
            Object.keys(flash).forEach((key) => {
                if (!isToastType(key) || !flash[key]) return;
                toast[key](flash[key]);
            });
        },
        { immediate: true },
    );
});
</script>

<template>
    <Toaster />
</template>
