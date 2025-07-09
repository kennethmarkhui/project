<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
import ConfirmDialog from '@/components/confirm-dialog/ConfirmDialog.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DeleteUser from './DeleteUser.vue';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const handleDelete = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => handleClose(),
        onError: () => {
            // TODO: passwordInput ref is not working
            if (passwordInput.value instanceof HTMLInputElement) {
                passwordInput.value.focus();
            }
        },
        onFinish: () => form.reset(),
    });
};

const handleClose = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <DeleteUser>
        <template #dialog>
            <ConfirmDialog
                is-form
                as-button
                confirm-text="Delete account"
                title="Are you sure you want to delete your account?"
                description="Once your account is deleted, all of its resources and data will also be deleted. Please enter your
                                password to confirm you would like to delete your account."
                variant="destructive"
                @submit="(e: Event) => handleDelete(e)"
                @cancel="handleClose"
            >
                <template #contentForm>
                    <div class="grid gap-2">
                        <Label for="password" class="sr-only">Password</Label>
                        <Input id="password" type="password" name="password" ref="passwordInput" v-model="form.password" placeholder="Password" />
                        <InputError :message="form.errors.password" />
                    </div>
                </template>
            </ConfirmDialog>
        </template>
    </DeleteUser>
</template>
