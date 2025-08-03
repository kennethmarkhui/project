<script setup lang="ts" generic="TData">
import { Link, router } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { TableMeta } from '@tanstack/vue-table';

interface Props {
    id: number;
    isDeleted?: boolean;
    isDisabled?: boolean;
    tableMeta?: TableMeta<TData>;
}

const props = defineProps<Props>();

type Emits = {
    success: [];
};

const emits = defineEmits<Emits>();

const { reveal } = useConfirmDialog();

const currentRoute = route().current();
const can = props.tableMeta?.can;

const handleDelete = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
        variant: 'destructive',
    });

    if (!confirmed.data || !currentRoute || !can?.delete) return;

    router.visit(`/${currentRoute}/${props.id}/delete`, {
        method: 'delete',
        data: { from: currentRoute },
        preserveScroll: true,
        onSuccess: () => emits('success'),
    });
};

const handlePermanentDelete = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to permanently delete?',
        description: 'Once deleted, all of its resources and data will also be permanently deleted.',
        confirmText: 'Delete Permanently',
        variant: 'destructive',
    });

    if (!confirmed.data || !currentRoute || !can?.force_delete) return;

    router.visit(`/${currentRoute}/${props.id}/force-delete`, {
        method: 'delete',
        data: { from: currentRoute },
        preserveScroll: true,
        onSuccess: () => emits('success'),
    });
};

const handleRestore = async () => {
    const confirmed = await reveal({
        title: 'Are you sure you want to restore?',
        description: 'Once restored, all of its resources and data will also be restored.',
        confirmText: 'Restore',
    });

    if (!confirmed.data || !currentRoute || !can?.restore) return;

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
            <DropdownMenuItem v-if="can?.update" as-child>
                <Link :href="`/${currentRoute}/${props.id}/edit`"> Edit </Link>
            </DropdownMenuItem>

            <template v-if="isDeleted">
                <DropdownMenuItem v-if="can?.restore" @select="handleRestore"> Restore </DropdownMenuItem>
                <DropdownMenuItem v-if="can?.force_delete" variant="destructive" @select="handlePermanentDelete">
                    Delete Permanently
                </DropdownMenuItem>
            </template>
            <DropdownMenuItem v-if="!isDeleted && can?.delete" variant="destructive" @select="handleDelete"> Delete </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
