<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import ActivityLogDataTable from '@/components/activity-logs/data-table/ActivityLogDataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { getLayout } from '@/lib/layout';
import type { ActivityLog, Paginated } from '@/types';

defineOptions({
    layout: getLayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Activity Logs',
                href: '/activity-logs',
            },
        ],
    })),
});

interface Props {
    activity_logs: Paginated<ActivityLog[]>;
    filters: Array<{ id: string; value: unknown }> | null;
    sort: Array<{ id: string; desc: boolean }> | null;
    events: string[];
    log_names: string[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Activity Logs" />

    <div class="container mx-auto flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <ActivityLogDataTable
            :activity_logs="props.activity_logs"
            :filters="props.filters"
            :sort="props.sort"
            :events="props.events"
            :log_names="props.log_names"
        />
    </div>
</template>
