<script setup lang="ts">
import { router } from '@inertiajs/vue3';

import ConfirmDialog from '@/components/confirm-dialog/ConfirmDialog.vue';

interface Props {
    id: number;
    title?: string;
    description?: string;
    confirmText?: string;
    dialogTitle?: string;
    dialogDescription?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Warning',
    description: 'Please proceed with caution, this cannot be undone.',
    confirmText: 'Delete User',
    dialogTitle: 'Are you sure you want to delete the user?',
    dialogDescription: 'Once the user is deleted, all of its resources and data will also be deleted.',
});

const handleDelete = () => {
    router.delete(route('users.destroy', props.id));
};
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">{{ props.title }}</p>
                <p class="text-sm">{{ props.description }}</p>
            </div>

            <slot name="dialog">
                <ConfirmDialog
                    as-button
                    :confirm-text="props.confirmText"
                    :title="props.dialogTitle"
                    :description="props.dialogDescription"
                    variant="destructive"
                    @click="handleDelete"
                />
            </slot>
        </div>
    </div>
</template>
