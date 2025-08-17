<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { NavItem } from '@/types';
import SidebarLayout from './SidebarLayout.vue';

interface Props {
    navItems: NavItem[];
    title?: string;
    description?: string;
}

const props = defineProps<Props>();

const page = usePage();

const currentPath = computed(() => (page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : ''));
</script>

<template>
    <SidebarLayout :title="props.title" :description="props.description">
        <template #sidebar>
            <nav class="flex flex-col space-y-1 space-x-0">
                <Button
                    v-for="item in props.navItems"
                    :key="item.href"
                    variant="ghost"
                    :class="['w-full justify-center truncate lg:justify-start', { 'bg-muted': currentPath === item.href }]"
                    as-child
                >
                    <Link :href="item.href">
                        {{ item.title }}
                    </Link>
                </Button>
            </nav>
        </template>
        <template #content>
            <slot />
        </template>
    </SidebarLayout>
</template>
