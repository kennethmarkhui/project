<script setup lang="ts" generic="TData extends RowData">
import { router } from '@inertiajs/vue3';
import { RowData, Table } from '@tanstack/vue-table';
import { RotateCcw, Trash } from 'lucide-vue-next';
import { AnimatePresence, motion } from 'motion-v';
import { computed } from 'vue';

import TooltipButton from '@/components/TooltipButton.vue';
import { Separator } from '@/components/ui/separator';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import DataTableActionBarSelect from './DataTableActionBarSelect.vue';
import DataTableActionBarSelection from './DataTableActionBarSelection.vue';

interface Props {
    table: Table<TData>;
    isDeleted?: boolean;
}

type Emits = {
    success: [];
};

const props = defineProps<Props>();

const emits = defineEmits<Emits>();

const { reveal } = useConfirmDialog();

const show = computed(() => Object.keys(props.table.getState().rowSelection).length > 0);
const numberSelected = computed(() => Object.keys(props.table.getState().rowSelection).length);
const ids = computed(() => Object.keys(props.table.getState().rowSelection).join(','));

const columns = props.table
    .getAllColumns()
    .filter((column) => column.getCanFilter() && column.columnDef.meta?.variant === 'multiSelect' && column.columnDef.meta?.action?.enabled);

const currentRoute = route().current();
const can = props.table.options.meta?.can;

const handleBulkDelete = async () => {
    if (!currentRoute || !can?.delete) return;

    const confirmed = await reveal({
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
        variant: 'destructive',
    });

    if (!confirmed.data) return;

    router.visit(`/${currentRoute}/${ids.value}/bulk-delete`, {
        method: 'delete',
        preserveScroll: true,
        onSuccess: () => {
            clearSelection();
            emits('success');
        },
    });
};

const handleBulkPermanentDelete = async () => {
    if (!currentRoute || !can?.force_delete) return;

    const confirmed = await reveal({
        title: 'Are you sure you want to permanently delete?',
        description: 'Once deleted, all of its resources and data will also be permanently deleted.',
        confirmText: 'Delete Permanently',
        variant: 'destructive',
    });

    if (!confirmed.data) return;

    router.visit(`/${currentRoute}/${ids.value}/bulk-force-delete`, {
        method: 'delete',
        preserveScroll: true,
        onSuccess: () => {
            clearSelection();
            emits('success');
        },
    });
};

const handleBulkRestore = async () => {
    if (!currentRoute || !can?.restore) return;

    const confirmed = await reveal({
        title: 'Are you sure you want to restore?',
        description: 'Once restored, all of its resources and data will also be restored.',
        confirmText: 'Restore',
    });

    if (!confirmed.data) return;

    router.visit(`/${currentRoute}/${ids.value}/bulk-restore`, {
        method: 'patch',
        preserveScroll: true,
        onSuccess: () => {
            clearSelection();
            emits('success');
        },
    });
};

const handleBulkUpdate = (column: string, payload: string) => {
    if (!can?.update) return;

    router.visit(`/${currentRoute}/${ids.value}/bulk-update`, {
        method: 'patch',
        data: { [column]: payload },
        preserveScroll: true,
        onFinish: () => {
            clearSelection();
            emits('success');
        },
    });
};

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
                <DataTableActionBarSelection :length="numberSelected" @clear="clearSelection">
                    <template #popover>
                        <slot name="popover" />
                    </template>
                </DataTableActionBarSelection>

                <Separator orientation="vertical" class="hidden data-[orientation=vertical]:h-5 sm:block" />

                <div class="flex items-center gap-1.5">
                    <template v-if="props.isDeleted">
                        <TooltipButton v-if="can?.restore" tooltip="Restore" variant="secondary" @click="handleBulkRestore">
                            <RotateCcw />
                        </TooltipButton>
                        <TooltipButton v-if="can?.force_delete" tooltip="Permanently Delete" variant="secondary" @click="handleBulkPermanentDelete">
                            <Trash />
                        </TooltipButton>
                    </template>

                    <template v-if="!props.isDeleted">
                        <template v-if="can?.update">
                            <DataTableActionBarSelect
                                v-for="column in columns"
                                :key="column.id"
                                :column="column"
                                :tooltip="`Update ${column.id}`"
                                :is-disabled="numberSelected < 2"
                                @select="(value) => handleBulkUpdate(column.id, value)"
                            >
                                <component v-if="column?.columnDef.meta?.action?.icon" :is="column?.columnDef.meta?.action?.icon" />
                            </DataTableActionBarSelect>
                        </template>

                        <TooltipButton v-if="can?.delete" tooltip="Delete" variant="secondary" @click="handleBulkDelete">
                            <Trash />
                        </TooltipButton>
                    </template>
                </div>
            </motion.div>
        </AnimatePresence>
    </Teleport>
</template>
