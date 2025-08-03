<script setup lang="ts">
import { router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Props {
    id: number;
}

const props = defineProps<Props>();

const { reveal } = useConfirmDialog();

const handleRestore = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to restore the user?',
        description: 'Once the user is restored, all of its resources and data will also be restored.',
        confirmText: 'Restore User',
    });

    if (confirmed.data) router.patch(route('users.restore', props.id));
};
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4 rounded-lg border border-green-100 bg-green-50 p-4 dark:border-green-200/10 dark:bg-green-700/10">
            <div class="relative space-y-0.5 text-green-600 dark:text-green-100">
                <p class="font-medium">Restore user</p>
                <p class="text-sm">Restoring will reactivate this user, allowing changes to be made again.</p>
            </div>

            <Button variant="outline" @click="handleRestore">Restore User</Button>
        </div>
    </div>
</template>
