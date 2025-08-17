<script setup lang="ts">
import ConfirmDialog from '@/components/confirm-dialog/ConfirmDialog.vue';
import Toaster from '@/components/Toaster.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <ConfirmDialog />
    <Toaster />
</template>
