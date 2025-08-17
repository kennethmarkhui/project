<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { getlayout, getlayoutStack } from '@/lib/layout';

defineOptions({
    layout: getlayoutStack(
        getlayout(AppLayout, () => ({
            breadcrumbs: [
                {
                    title: 'Appearance settings',
                    href: '/settings/appearance',
                },
            ],
        })),
        getlayout(SettingsLayout),
    ),
});
</script>

<template>
    <Head title="Appearance settings" />

    <div class="space-y-6">
        <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
        <AppearanceTabs />
    </div>
</template>
