<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import UserDataTable from '@/components/users/data-table/UserDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { getlayout } from '@/lib/layout';
import type { Paginated, Role, User } from '@/types';

defineOptions({
    layout: getlayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Users',
                href: '/users',
            },
        ],
    })),
});

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
    roles: Role[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Users" />

    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <UserDataTable :users="props.users" :filters="props.filters" :search="props.search" :sort="props.sort" :roles="props.roles" />
    </div>
</template>
