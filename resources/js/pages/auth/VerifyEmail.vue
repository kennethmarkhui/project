<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { getLayout } from '@/lib/layout';

defineOptions({
    layout: getLayout(AuthLayout, () => ({
        title: 'Verify email',
        description: 'Please verify your email address by clicking on the link we just emailed to you.',
    })),
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <Head title="Email verification" />

    <form @submit.prevent="submit" class="space-y-6 text-center">
        <Button :disabled="form.processing" variant="secondary">
            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
            Resend verification email
        </Button>

        <TextLink :href="route('logout')" method="post" as="button" class="mx-auto block text-sm"> Log out </TextLink>
    </form>
</template>
