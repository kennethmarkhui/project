<script setup lang="ts">
import { router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Props {
    id: number;
    permanent?: boolean;
}

const props = defineProps<Props>();

const { reveal } = useConfirmDialog();

const handleDelete = async () => {
    const confirmed = await reveal({
        title: props.permanent ? 'Are you sure you want to delete the user permanently?' : 'Are you sure you want to delete the user?',
        description: props.permanent
            ? 'Once the user is deleted, all of its resources and data will also be permanently deleted.'
            : 'Once the user is deleted, all of its resources and data will also be deleted.',
        confirmText: props.permanent ? 'Delete Permanently' : 'Delete User',
        variant: 'destructive',
    });

    if (confirmed.data) router.delete(route(props.permanent ? 'users.force_delete' : 'users.destroy', props.id));
};
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Warning</p>
                <p class="text-sm">Please proceed with caution, this cannot be undone.</p>
            </div>

            <Button variant="destructive" @click="handleDelete">Delete User</Button>
        </div>
    </div>
</template>
