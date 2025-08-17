<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import RoleDataTable from '@/components/roles/data-table/RoleDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { getlayout } from '@/lib/layout';
import type { Permission, Role } from '@/types';

defineOptions({
    layout: getlayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Roles',
                href: '/roles',
            },
        ],
    })),
});

interface Props {
    roles: Role[];
    permissions: Permission[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Roles" />

    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <RoleDataTable :roles="props.roles" :permissions="props.permissions" />
    </div>
</template>
