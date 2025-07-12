<script setup lang="ts" generic="TData">
import { Table } from '@tanstack/vue-table';
import { RotateCcw, Trash, X } from 'lucide-vue-next';
import { AnimatePresence, motion } from 'motion-v';
import { computed } from 'vue';

import ConfirmDialog from '@/components/confirm-dialog/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import DataTableActionBarButton from './DataTableActionBarButton.vue';
import DataTableActionBarSelect from './DataTableActionBarSelect.vue';

interface Props {
    table: Table<TData>;
    isDeleting?: boolean;
}

type Emits = {
    (e: 'action', action: 'update', column: string, payload: string): void;
    (e: 'action', action: 'delete' | 'restore'): void;
};

const props = defineProps<Props>();

const emits = defineEmits<Emits>();

const openDeleteDialog = defineModel('openDeleteDialog', { default: false });
const openDeletePermanentlyDialog = defineModel('openDeletePermanentlyDialog', { default: false });
const openRestoreDialog = defineModel('openRestoreDialog', { default: false });

const show = computed(() => Object.keys(props.table.getState().rowSelection).length > 0);
const numberSelected = computed(() => Object.keys(props.table.getState().rowSelection).length);

const columns = props.table
    .getAllColumns()
    .filter((column) => column.getCanFilter() && column.columnDef.meta?.variant === 'multiSelect' && column.columnDef.meta?.action);

const clearSelection = () => {
    props.table.resetRowSelection();
};
</script>

<template>
    <Teleport to="body">
        <AnimatePresence>
            <motion.div
                v-if="show"
                role="toolbar"
                aria-orientation="horizontal"
                :initial="{ opacity: 0, y: 20 }"
                :animate="{ opacity: 1, y: 0 }"
                :exit="{ opacity: 0, y: 20 }"
                :transition="{ duration: 0.2, ease: 'easeInOut' }"
                class="fixed inset-x-0 bottom-6 z-50 mx-auto flex w-fit flex-wrap items-center justify-center gap-2 rounded-md border bg-background p-2 text-foreground shadow-sm"
            >
                <!-- Rows Selected -->
                <div class="flex h-7 items-center rounded-md border pr-1 pl-2.5">
                    <span class="text-xs whitespace-nowrap"> {{ numberSelected }} selected </span>
                    <Separator orientation="vertical" class="mr-1 ml-2 data-[orientation=vertical]:h-4" />
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" class="size-5" @click="clearSelection">
                                <X class="size-3.5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent
                            :side-offset="10"
                            class="flex items-center gap-2 border bg-accent px-2 py-1 font-semibold text-foreground dark:bg-zinc-900 [&>span]:hidden"
                        >
                            <p>Clear selection</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
                <!-- End Rows Selected -->

                <Separator orientation="vertical" class="hidden data-[orientation=vertical]:h-5 sm:block" />

                <div class="flex items-center gap-1.5">
                    <template v-if="props.isDeleting === true">
                        <DataTableActionBarSelect
                            v-for="column in columns"
                            :key="column.id"
                            :column="column"
                            :tooltip="`Update ${column.id}`"
                            :is-disabled="numberSelected < 2"
                            @select="(value) => emits('action', 'update', column.id, value)"
                        >
                            <component v-if="column?.columnDef.meta?.icon" :is="column?.columnDef.meta?.icon" />
                        </DataTableActionBarSelect>

                        <DataTableActionBarButton tooltip="Delete" @click="openDeleteDialog = true">
                            <Trash />
                        </DataTableActionBarButton>
                    </template>

                    <template v-if="props.isDeleting === false">
                        <DataTableActionBarButton tooltip="Restore" @click="openRestoreDialog = true">
                            <RotateCcw />
                        </DataTableActionBarButton>
                        <DataTableActionBarButton tooltip="Permanently Delete" @click="openDeletePermanentlyDialog = true">
                            <Trash />
                        </DataTableActionBarButton>
                    </template>
                </div>
            </motion.div>
        </AnimatePresence>
    </Teleport>

    <ConfirmDialog
        v-model:open="openDeleteDialog"
        confirm-text="Delete"
        title="Are you sure you want to delete?"
        description="Once deleted, all of its resources and data will also be deleted."
        variant="destructive"
        @click="emits('action', 'delete')"
    />
    <ConfirmDialog
        v-model:open="openDeletePermanentlyDialog"
        confirm-text="Delete Permanently"
        title="Are you sure you want to permenently delete?"
        description="Once deleted, all of its resources and data will also be permanently deleted."
        variant="destructive"
        @click="emits('action', 'delete')"
    />
    <ConfirmDialog
        v-model:open="openRestoreDialog"
        confirm-text="Restore"
        title="Are you sure you want to restore?"
        description="Once restored, all of its resources and data will also be restored."
        @click="emits('action', 'restore')"
    />
</template>
