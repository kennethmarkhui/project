<script setup lang="ts" generic="TData, TValue">
import { Column } from '@tanstack/vue-table';
import { ChevronDown, ChevronsUpDown, ChevronUp, EyeOff, X } from 'lucide-vue-next';

import { DropdownMenu, DropdownMenuCheckboxItem, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { computed, ref, watch } from 'vue';

interface Props {
    column: Column<TData, TValue>;
    title: string;
}

const props = defineProps<Props>();

const sortDirection = ref(props.column.getIsSorted());

const isAsc = computed({
    get: () => sortDirection.value === 'asc',
    set: (value) => value && (sortDirection.value = 'asc'),
});

const isDesc = computed({
    get: () => sortDirection.value === 'desc',
    set: (value) => value && (sortDirection.value = 'desc'),
});

watch(
    () => props.column.getIsSorted(),
    (sortBy) => {
        sortDirection.value = sortBy;
    },
    { immediate: true },
);

const onReset = () => {
    sortDirection.value = false;
    props.column.clearSorting();
};
</script>

<template>
    <DropdownMenu v-if="props.column.getCanSort()">
        <DropdownMenuTrigger
            class="'-ml-1.5 [&_svg]:text-muted-foreground', flex h-8 items-center gap-1.5 rounded-md px-2 py-1.5 hover:bg-accent focus:ring-1 focus:ring-ring focus:outline-none data-[state=open]:bg-accent [&_svg]:size-4 [&_svg]:shrink-0"
        >
            {{ props.title }}
            <ChevronDown v-if="props.column.getIsSorted() === 'desc'" />
            <ChevronUp v-else-if="props.column.getIsSorted() === 'asc'" />
            <ChevronsUpDown v-else />
        </DropdownMenuTrigger>
        <DropdownMenuContent align="start" class="w-28">
            <DropdownMenuCheckboxItem
                class="relative pr-8 pl-2 [&_svg]:text-muted-foreground [&>span:first-child]:right-2 [&>span:first-child]:left-auto"
                v-model="isAsc"
                @click="() => props.column.toggleSorting(false)"
            >
                <ChevronUp />
                Asc
            </DropdownMenuCheckboxItem>
            <DropdownMenuCheckboxItem
                class="relative pr-8 pl-2 [&_svg]:text-muted-foreground [&>span:first-child]:right-2 [&>span:first-child]:left-auto"
                v-model="isDesc"
                @click="() => props.column.toggleSorting(true)"
            >
                <ChevronDown />
                Desc
            </DropdownMenuCheckboxItem>
            <DropdownMenuItem v-if="props.column.getIsSorted()" class="pl-2 [&_svg]:text-muted-foreground" @click="onReset">
                <X />
                Reset
            </DropdownMenuItem>
            <DropdownMenuItem
                v-if="props.column.getCanHide()"
                class="relative pr-8 pl-2 [&_svg]:text-muted-foreground [&>span:first-child]:right-2 [&>span:first-child]:left-auto"
                @click="() => props.column.toggleVisibility(false)"
            >
                <EyeOff />
                Hide
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>

    <div v-else-if="!props.column.getCanSort() && !props.column.getCanHide()">{{ props.title }}</div>
</template>
