<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import PermissionDataTable from '@/components/permissions/data-table/PermissionDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Permission } from '@/types';

interface Props {
    permissions: Permission[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Permissions',
        href: '/permissions',
    },
];

const props = defineProps<Props>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Permissions" />

        <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <PermissionDataTable :permissions="props.permissions" />
        </div>
    </AppLayout>
</template>
