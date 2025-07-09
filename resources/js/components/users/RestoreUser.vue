<script setup lang="ts">
import { router } from '@inertiajs/vue3';

import ConfirmDialog from '@/components/confirm-dialog/ConfirmDialog.vue';

interface Props {
    id?: number;
    title?: string;
    description?: string;
    confirmText?: string;
    dialogTitle?: string;
    dialogDescription?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Restore user',
    description: 'Restoring will reactivate this user, allowing changes to be made again.',
    confirmText: 'Restore User',
    dialogTitle: 'Are you sure you want to restore the user?',
    dialogDescription: 'Once the user is restored, all of its resources and data will also be restored.',
});

const handleRestore = () => {
    router.patch(route('users.restore', props.id));
};
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4 rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-200/10 dark:bg-green-700/10">
            <div class="relative space-y-0.5 text-green-600 dark:text-green-100">
                <p class="font-medium">{{ props.title }}</p>
                <p class="text-sm">{{ props.description }}</p>
            </div>

            <slot name="dialog">
                <ConfirmDialog
                    as-button
                    :confirm-text="props.confirmText"
                    :title="props.dialogTitle"
                    :description="props.dialogDescription"
                    @click="handleRestore"
                />
            </slot>
        </div>
    </div>
</template>
