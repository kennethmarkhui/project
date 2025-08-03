<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import ConfirmDialogContentFooter from './ConfirmDialogContentFooter.vue';
import ConfirmDialogContentHeader from './ConfirmDialogContentHeader.vue';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const { isRevealed, confirm, title, description, confirmText, variant, formSubmit } = useConfirmDialog();

const onSubmit = () => {
    if (!formSubmit.value) return;

    form.submit(formSubmit.value.method, formSubmit.value.url, {
        preserveScroll: true,
        onSuccess: () => closeDialog(true),
        onError: () => {
            // TODO: passwordInput ref is not working
            if (passwordInput.value instanceof HTMLInputElement) {
                passwordInput.value.focus();
            }
        },
        onFinish: () => form.reset(),
    });
};

const closeDialog = (confirmed: boolean) => {
    form.clearErrors();
    form.reset();
    confirm(confirmed);
};
</script>

<template>
    <Dialog v-model:open="isRevealed" @update:open="(value) => value === false && closeDialog(value)">
        <DialogContent>
            <ConfirmDialogContentHeader :title="title" :description="description" />

            <form v-if="formSubmit" class="space-y-6" @submit.prevent="onSubmit">
                <div class="grid gap-2">
                    <Label for="password" class="sr-only">Password</Label>
                    <Input id="password" type="password" name="password" ref="passwordInput" v-model="form.password" placeholder="Password" />
                    <InputError :message="form.errors.password" />
                </div>

                <ConfirmDialogContentFooter :confirm-text="confirmText" :is-form="!!formSubmit" :variant="variant" @cancel="closeDialog(false)" />
            </form>

            <ConfirmDialogContentFooter v-else :confirm-text="confirmText" :variant="variant" @cancel="confirm(false)" @click="confirm(true)" />
        </DialogContent>
    </Dialog>
</template>
