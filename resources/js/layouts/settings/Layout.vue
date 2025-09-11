<script setup lang="ts">
import { type NavItem } from '@/types';
import SidebarNavLayout from '../shared/SidebarNavLayout.vue';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
    {
        title: 'Two-Factor Auth',
        href: '/settings/two-factor',
    },
    {
        title: 'Appearance',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <SidebarNavLayout :nav-items="sidebarNavItems" title="Settings" description="Manage your profile and account settings">
        <slot />
    </SidebarNavLayout>
</template>
