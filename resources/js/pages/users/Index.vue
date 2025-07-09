<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import UserDataTable from '@/components/users/UserDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Paginated, User } from '@/types';

interface Props {
    users: Paginated<User[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    search: string | null;
    sort: Array<{ id: string; desc: boolean }> | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

const props = defineProps<Props>();
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <UserDataTable :users="props.users" :filters="props.filters" :search="props.search" :sort="props.sort" />
    </AppLayout>
</template>
