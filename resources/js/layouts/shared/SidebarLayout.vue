<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';

interface Props {
    title?: string;
    description?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="px-4 py-6">
        <Heading v-if="props.title" :title="props.title" :description="props.description" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-2xl lg:w-48">
                <slot name="sidebar" />
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 overflow-x-auto md:max-w-2xl">
                <section class="max-w-2xl space-y-12">
                    <slot name="content" />
                </section>
            </div>
        </div>
    </div>
</template>
