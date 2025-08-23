<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import PermissionDataTable from '@/components/permissions/data-table/PermissionDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { getLayout } from '@/lib/layout';
import type { Permission, Role } from '@/types';

defineOptions({
    layout: getLayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Permissions',
                href: '/permissions',
            },
        ],
    })),
});

interface Props {
    permissions: Permission[];
    roles: Role[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Permissions" />

    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <PermissionDataTable :permissions="props.permissions" :roles="props.roles" />
    </div>
</template>
