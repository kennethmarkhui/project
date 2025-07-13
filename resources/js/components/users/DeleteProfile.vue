<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

const { reveal } = useConfirmDialog();

const deleteUser = async () => {
    await reveal({
        title: 'Are you sure you want to delete your account?',
        description:
            'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
        confirmText: 'Delete account',
        variant: 'destructive',
        formSubmit: {
            method: 'delete',
            url: route('profile.destroy'),
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Delete account" description="Delete your account and all of its resources" />
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">Please proceed with caution, this cannot be undone.</p>
            </div>

            <Button variant="destructive" @click="deleteUser">Delete account</Button>
        </div>
    </div>
</template>
