<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import RoleDataTable from '@/components/roles/data-table/RoleDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Role } from '@/types';

interface Props {
    roles: Role[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
];

const props = defineProps<Props>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Roles" />

        <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <RoleDataTable :roles="props.roles" />
        </div>
    </AppLayout>
</template>
