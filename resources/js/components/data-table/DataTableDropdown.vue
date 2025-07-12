<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';

import ConfirmDialog from '@/components/confirm-dialog/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

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

const openDeleteDialog = defineModel('openDeleteDialog', { default: false });
const openDeletePermanentlyDialog = defineModel('openDeletePermanentlyDialog', { default: false });
const openRestoreDialog = defineModel('openRestoreDialog', { default: false });

const currentPath = route().current();
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
                <DropdownMenuItem @select="openRestoreDialog = true">Restore</DropdownMenuItem>
                <DropdownMenuItem variant="destructive" @select="openDeletePermanentlyDialog = true">Delete Permanently</DropdownMenuItem>
            </template>
            <DropdownMenuItem v-else variant="destructive" @select="openDeleteDialog = true">Delete</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <ConfirmDialog
        v-model:open="openDeleteDialog"
        confirm-text="Delete"
        title="Are you sure you want to delete?"
        description="Once deleted, all of its resources and data will also be deleted."
        variant="destructive"
        @click="emits('delete', String(props.id))"
    />
    <ConfirmDialog
        v-model:open="openDeletePermanentlyDialog"
        confirm-text="Delete Permanently"
        title="Are you sure you want to permenently delete?"
        description="Once deleted, all of its resources and data will also be permanently deleted."
        variant="destructive"
        @click="emits('delete', String(props.id))"
    />
    <ConfirmDialog
        v-model:open="openRestoreDialog"
        confirm-text="Restore"
        title="Are you sure you want to restore?"
        description="Once restored, all of its resources and data will also be restored."
        @click="emits('restore', String(props.id))"
    />
</template>
