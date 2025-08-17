<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';

import { Toaster } from '@/components/ui/sonner';

import 'vue-sonner/style.css';

const TOAST_TYPES = ['success'] as const;

export type ToastType = (typeof TOAST_TYPES)[number];

function isToastType(key: string): key is ToastType {
    return TOAST_TYPES.includes(key as ToastType);
}

router.on('success', (event) => {
    const flash = event.detail.page.props.flash;

    Object.keys(flash).forEach((key) => {
        if (!isToastType(key) || !flash[key]) return;
        toast[key](flash[key]);
    });
});
</script>

<template>
    <Toaster />
</template>
