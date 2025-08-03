import { Column } from '@tanstack/vue-table';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import { CSSProperties } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatDate(date: Date | string | number, opts: Intl.DateTimeFormatOptions = {}, locales: string = 'en-US') {
    const defaultOptions: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        ...opts,
    };
    return new Intl.DateTimeFormat(locales, defaultOptions).format(new Date(date));
}

export function isArrayEqual<T>(a: T[], b: T[]): boolean {
    if (a.length !== b.length) return false;
    return a.every((value, index) => value === b[index]);
}

export function getCommonPinningStyles<TData>({ column, withBorder = false }: { column: Column<TData>; withBorder?: boolean }): CSSProperties {
    const isPinned = column.getIsPinned();
    const isLastLeftPinnedColumn = isPinned === 'left' && column.getIsLastColumn('left');
    const isFirstRightPinnedColumn = isPinned === 'right' && column.getIsFirstColumn('right');

    return {
        boxShadow: withBorder
            ? isLastLeftPinnedColumn
                ? '-4px 0 4px -4px var(--border) inset'
                : isFirstRightPinnedColumn
                  ? '4px 0 4px -4px var(--border) inset'
                  : undefined
            : undefined,
        left: isPinned === 'left' ? `${column.getStart('left')}px` : undefined,
        right: isPinned === 'right' ? `${column.getAfter('right')}px` : undefined,
        opacity: isPinned ? 0.95 : 1,
        position: isPinned ? 'sticky' : 'relative',
        background: isPinned ? 'var(--background)' : 'var(--background)',
        width: `${column.getSize()}px`,
        zIndex: isPinned ? 1 : 0,
    };
}

export function getUniqueValues(items: string[], index: 0 | 1) {
    const values = new Set<string>();
    items.forEach((name) => {
        const parts = name.split('.');
        values.add(parts[index]);
    });
    return Array.from(values);
}
