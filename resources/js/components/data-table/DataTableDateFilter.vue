<script setup lang="ts" generic="TData, TValue">
import { parseDate } from '@internationalized/date';
import { Column } from '@tanstack/vue-table';
import { CalendarIcon, XCircle } from 'lucide-vue-next';
import { type DateRange } from 'reka-ui';
import { computed, Ref, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import { Separator } from '@/components/ui/separator';
import { formatDate } from '@/lib/utils';

type DateRangeFilterValue = {
    start: string;
    end: string;
};

function isDateRangeFilterValue(value: unknown): value is DateRangeFilterValue {
    if (!value || typeof value !== 'object' || Array.isArray(value)) {
        return false;
    }

    return 'start' in value && 'end' in value;
}

interface Props {
    column?: Column<TData, TValue>;
}

const props = defineProps<Props>();

const title = props.column?.columnDef.meta?.label ?? 'Untitled';

const defaultValue = { start: undefined, end: undefined };
const dateFilterValue = props.column?.getFilterValue();
const dateRange = ref(
    isDateRangeFilterValue(dateFilterValue) ? { start: parseDate(dateFilterValue.start), end: parseDate(dateFilterValue.end) } : defaultValue,
) as Ref<DateRange>;

const hasSelectedDates = computed(() => dateRange.value.start && dateRange.value.end);
const dateText = computed(() => {
    if (!hasSelectedDates || !dateRange.value?.start || !dateRange.value?.end) return '';

    const start = formatDate(new Date(dateRange.value.start.toString()));
    const end = formatDate(new Date(dateRange.value.end.toString()));

    return `${start} - ${end}`;
});

watch(
    () => props.column?.getFilterValue(),
    (filterValue) => {
        if (filterValue) return;
        if (dateRange.value.start && !dateRange.value.end) return;
        dateRange.value = defaultValue;
    },
);

const onDateSelect = (date: DateRange) => {
    if (!date.end) {
        props.column?.setFilterValue(undefined);
        return;
    }

    props.column?.setFilterValue({
        start: date.start?.toString(),
        end: date.end?.toString(),
    });
};

const onReset = () => {
    dateRange.value = defaultValue;
    props.column?.setFilterValue(undefined);
};
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" size="sm" class="border-dashed">
                <div
                    v-if="hasSelectedDates"
                    role="button"
                    :aria-label="`Clear ${title} filter`"
                    tabindex="0"
                    @click.stop="onReset"
                    class="rounded-sm opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <XCircle />
                </div>
                <CalendarIcon v-else />
                <span class="flex items-center gap-2">
                    <span>{{ title }}</span>
                    <template v-if="hasSelectedDates">
                        <Separator orientation="vertical" class="mx-0.5 data-[orientation=vertical]:h-4" />
                        <span>{{ dateText }}</span>
                    </template>
                </span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <RangeCalendar v-model="dateRange" initial-focus @update:model-value="onDateSelect" />
        </PopoverContent>
    </Popover>
</template>
