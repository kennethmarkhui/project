<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { getLayout, getLayoutStack } from '@/lib/layout';

defineOptions({
    layout: getLayoutStack(
        getLayout(AppLayout, () => ({
            breadcrumbs: [
                {
                    title: 'Two-Factor Authentication',
                    href: '/settings/two-factor',
                },
            ],
        })),
        getLayout(SettingsLayout),
    ),
});

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);
const processing = ref<boolean>(false);

const enable2FA = () => {
    router.visit(route('two-factor.enable'), {
        method: 'post',
        preserveState: true,
        onStart: () => {
            processing.value = true;
        },
        onSuccess: () => {
            showSetupModal.value = true;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const disable2FA = () => {
    router.visit(route('two-factor.disable'), {
        method: 'delete',
        onStart: () => {
            processing.value = true;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <Head title="Two-Factor Authentication" />
    {{ showSetupModal }}
    <div class="space-y-6">
        <HeadingSmall title="Two-Factor Authentication" description="Manage your two-factor authentication settings" />

        <div v-if="!twoFactorEnabled" class="flex flex-col items-start justify-start space-y-4">
            <Badge variant="destructive">Disabled</Badge>

            <p class="text-muted-foreground">
                When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a
                TOTP-supported application on your phone.
            </p>

            <div>
                <Button v-if="hasSetupData" @click="showSetupModal = true"> <ShieldCheck />Continue Setup </Button>
                <Button v-else @click="() => enable2FA()" :disabled="processing"> <ShieldCheck />Enable 2FA</Button>
            </div>
        </div>

        <div v-else class="flex flex-col items-start justify-start space-y-4">
            <Badge variant="default">Enabled</Badge>

            <p class="text-muted-foreground">
                With two-factor authentication enabled, you will be prompted for a secure, random pin during login, which you can retrieve from the
                TOTP-supported application on your phone.
            </p>

            <TwoFactorRecoveryCodes />

            <div class="relative inline">
                <Button variant="destructive" @click="() => disable2FA()" :disabled="processing">
                    <ShieldBan />
                    Disable 2FA
                </Button>
            </div>
        </div>

        <TwoFactorSetupModal v-model:isOpen="showSetupModal" :requiresConfirmation="requiresConfirmation" :twoFactorEnabled="twoFactorEnabled" />
    </div>
</template>
