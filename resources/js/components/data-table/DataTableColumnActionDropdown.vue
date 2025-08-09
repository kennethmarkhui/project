<script setup lang="ts" generic="TData extends RowData">
import { Link, router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { RowData, TableMeta } from '@tanstack/vue-table';

interface Props {
    id: number;
    isDeleted?: boolean;
    isDisabled?: boolean;
    tableMeta?: TableMeta<TData>;
    row: TData;
}

const props = defineProps<Props>();

type Emits = {
    success: [];
};

const emits = defineEmits<Emits>();

const { reveal } = useConfirmDialog();

const currentRoute = route().current();
const canRow = props.tableMeta?.canRow;

const handleDelete = async () => {
    if (!currentRoute || !canRow?.(props.row, 'delete')) return;

    const confirmed = await reveal({
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
        variant: 'destructive',
    });

    if (!confirmed.data) return;

    router.visit(`/${currentRoute}/${props.id}/delete`, {
        method: 'delete',
        data: { from: currentRoute },
        preserveScroll: true,
        onSuccess: () => emits('success'),
    });
};

const handlePermanentDelete = async () => {
    if (!currentRoute || !canRow?.(props.row, 'force_delete')) return;

    const confirmed = await reveal({
        title: 'Are you sure you want to permanently delete?',
        description: 'Once deleted, all of its resources and data will also be permanently deleted.',
        confirmText: 'Delete Permanently',
        variant: 'destructive',
    });

    if (!confirmed.data) return;

    router.visit(`/${currentRoute}/${props.id}/force-delete`, {
        method: 'delete',
        data: { from: currentRoute },
        preserveScroll: true,
        onSuccess: () => emits('success'),
    });
};

const handleRestore = async () => {
    if (!currentRoute || !canRow?.(props.row, 'restore')) return;

    const confirmed = await reveal({
        title: 'Are you sure you want to restore?',
        description: 'Once restored, all of its resources and data will also be restored.',
        confirmText: 'Restore',
    });

    if (!confirmed.data) return;

    router.visit(`/${currentRoute}/${props.id}/restore`, {
        method: 'patch',
        data: { from: currentRoute },
        preserveScroll: true,
        onSuccess: () => emits('success'),
    });
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="h-8 w-8 p-0" :disabled="props.isDisabled">
                <span class="sr-only"> Open menu </span>
                <MoreHorizontal class="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem v-if="canRow?.(props.row, 'read')" as-child>
                <Link :href="`/${currentRoute}/${props.id}`"> View </Link>
            </DropdownMenuItem>
            <DropdownMenuItem v-if="canRow?.(props.row, 'update')" as-child>
                <Link :href="`/${currentRoute}/${props.id}/edit`"> Edit </Link>
            </DropdownMenuItem>

            <template v-if="isDeleted">
                <DropdownMenuItem v-if="canRow?.(props.row, 'restore')" @select="handleRestore"> Restore </DropdownMenuItem>
                <DropdownMenuItem v-if="canRow?.(props.row, 'force_delete')" variant="destructive" @select="handlePermanentDelete">
                    Delete Permanently
                </DropdownMenuItem>
            </template>
            <DropdownMenuItem v-if="!isDeleted && canRow?.(props.row, 'delete')" variant="destructive" @select="handleDelete">
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
