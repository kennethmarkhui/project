<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useConfirmDialog } from '@/composables/useConfirmDialog';

interface Props {
    id: number;
    isDeleted?: boolean;
    isDisabled?: boolean;
}

const props = defineProps<Props>();

type Emits = {
    delete: [id: string];
    restore: [id: string];
};

const emits = defineEmits<Emits>();

const currentPath = route().current();

const { reveal } = useConfirmDialog();

const onDelete = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
        variant: 'destructive',
    });

    if (confirmed.data) emits('delete', String(props.id));
};

const onPermanentDelete = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to permanently delete?',
        description: 'Once deleted, all of its resources and data will also be permanently deleted.',
        confirmText: 'Delete Permanently',
        variant: 'destructive',
    });

    if (confirmed.data) emits('delete', String(props.id));
};

const onRestore = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to restore?',
        description: 'Once restored, all of its resources and data will also be restored.',
        confirmText: 'Restore',
    });

    if (confirmed.data) emits('restore', String(props.id));
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0" :disabled="props.isDisabled">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem as-child>
                <Link :href="route(`${currentPath}.edit`, props.id)">Edit</Link>
            </DropdownMenuItem>

            <template v-if="isDeleted">
                <DropdownMenuItem @select="onRestore">Restore</DropdownMenuItem>
                <DropdownMenuItem variant="destructive" @select="onPermanentDelete">Delete Permanently</DropdownMenuItem>
            </template>
            <DropdownMenuItem v-else variant="destructive" @select="onDelete">Delete</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
