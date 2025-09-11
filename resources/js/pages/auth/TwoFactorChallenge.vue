<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { getLayout } from '@/lib/layout';

// TODO: dynamic AuthLayout title and description using authConfigContent
defineOptions({
    layout: getLayout(AuthLayout, () => ({
        title: 'Two-Factor Authentication',
        description: 'Complete two-factor authentication using authentication or recovery code.',
    })),
});

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description: 'Please confirm access to your account by entering one of your emergency recovery codes.',
            toggleText: 'login using an authentication code',
        };
    }

    return {
        title: 'Authentication Code',
        description: 'Enter the authentication code provided by your authenticator application.',
        toggleText: 'login using a recovery code',
    };
});

const showRecoveryInput = ref<boolean>(false);

const form = useForm<{ code: number[] }>({
    code: [],
});

const recoveryCodeForm = useForm<{ recovery_code: string }>({
    recovery_code: '',
});

const submit = () => {
    form.transform((data) => ({
        code: data.code.join(''),
    })).post(route('two-factor.login.store'), {
        onError: () => form.reset(),
    });
};

const recoveryCodeSubmit = () => {
    recoveryCodeForm.post(route('two-factor.login.store'), {
        onError: () => recoveryCodeForm.reset(),
    });
};

const toggleRecoveryMode = (clearErrors: () => void, reset: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    reset();
};
</script>

<template>
    <Head title="Two-Factor Authentication" />

    <div class="space-y-6">
        <template v-if="!showRecoveryInput">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="flex flex-col items-center justify-center space-y-3 text-center">
                    <div class="flex w-full items-center justify-center">
                        <PinInput id="otp" placeholder="○" v-model="form.code" type="number" otp>
                            <PinInputGroup>
                                <PinInputSlot v-for="(id, index) in 6" :key="id" :index="index" :disabled="form.processing" autofocus />
                            </PinInputGroup>
                        </PinInput>
                    </div>
                    <InputError :message="form.errors.code" />
                </div>
                <Button type="submit" class="w-full" :disabled="form.processing">Continue</Button>
                <div class="text-center text-sm text-muted-foreground">
                    <span>or you can </span>
                    <button
                        type="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        @click="
                            () =>
                                toggleRecoveryMode(
                                    () => form.clearErrors(),
                                    () => form.reset(),
                                )
                        "
                    >
                        {{ authConfigContent.toggleText }}
                    </button>
                </div>
            </form>
        </template>

        <template v-else>
            <form @submit.prevent="recoveryCodeSubmit" class="space-y-4">
                <Input
                    name="recovery_code"
                    v-model="recoveryCodeForm.recovery_code"
                    type="text"
                    placeholder="Enter recovery code"
                    :autofocus="showRecoveryInput"
                    required
                />
                <InputError :message="recoveryCodeForm.errors.recovery_code" />
                <Button type="submit" class="w-full" :disabled="recoveryCodeForm.processing">Continue</Button>

                <div class="text-center text-sm text-muted-foreground">
                    <span>or you can </span>
                    <button
                        type="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        @click="
                            () =>
                                toggleRecoveryMode(
                                    () => recoveryCodeForm.clearErrors(),
                                    () => recoveryCodeForm.reset(),
                                )
                        "
                    >
                        {{ authConfigContent.toggleText }}
                    </button>
                </div>
            </form>
        </template>
    </div>
</template>
