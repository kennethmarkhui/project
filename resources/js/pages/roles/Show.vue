<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2, User, UserLock } from 'lucide-vue-next';

import TooltipButton from '@/components/TooltipButton.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { getlayout } from '@/lib/layout';
import { getUniqueValues } from '@/lib/utils';
import type { Permission, Role } from '@/types';

defineOptions({
    layout: getlayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Roles',
                href: '/roles',
            },
            {
                title: 'Details',
                href: '/roles/create',
            },
        ],
    })),
});

interface Props {
    role: Role;
    permissions: Permission[];
}

const props = defineProps<Props>();

const { reveal } = useConfirmDialog();

const rolePermissions = props.role.permissions?.map((permission) => permission.name) || [];

const permissions = props.permissions.map((permission) => permission.name);
const models = getUniqueValues(permissions, 0);
const actions = getUniqueValues(permissions, 1);

const isChecked = (model: string, action: string) => {
    const permission = permissions.find((permission) => permission === `${model}.${action}`);
    return permission ? rolePermissions.includes(permission) : false;
};

const handleDelete = async () => {
    if (props.role.is_system || !props.role.can?.delete) return;

    const confirmed = await reveal({
        variant: 'destructive',
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
    });

    if (confirmed.data) router.delete(route('roles.destroy', props.role.id));
};
</script>

<template>
    <Head title="Details" />

    <div class="container mx-auto w-full px-4 py-6">
        <div class="space-y-6">
            <div class="flex flex-row items-start justify-between">
                <div class="-space-y-2">
                    <h1 class="text-xl font-extrabold tracking-tight capitalize md:text-2xl">{{ props.role.name }}</h1>
                    <span class="text-sm text-muted-foreground">{{ props.role.is_system ? 'System Role' : 'Role' }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <TooltipButton tooltip="Edit" variant="clear" :disabled="props.role.is_system || !props.role.can?.update">
                        <Link :href="`/roles/${props.role.id}/edit`"><Pencil /></Link>
                    </TooltipButton>
                    <TooltipButton
                        v-if="props.role.can?.delete"
                        tooltip="Delete"
                        variant="clear"
                        @click="handleDelete"
                        :disabled="props.role.is_system || !props.role.can?.delete"
                    >
                        <Trash2 class="text-destructive" />
                    </TooltipButton>
                </div>
            </div>
            <div class="grid gap-4 xl:grid-cols-3">
                <div class="space-y-4 xl:col-span-3">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <Card>
                            <CardContent class="grid auto-cols-max grid-flow-col gap-4">
                                <User class="size-6 opacity-40" />
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm text-muted-foreground">Users</span>
                                    <span class="text-lg font-semibold">{{ props.role.users_count }}</span>
                                </div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardContent class="grid auto-cols-max grid-flow-col gap-4">
                                <UserLock class="size-6 opacity-40" />
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm text-muted-foreground">Permissions</span>
                                    <span class="text-lg font-semibold">{{ props.role.permissions_count }}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                    <Card>
                        <CardHeader>
                            <CardTitle>Permissions</CardTitle>
                        </CardHeader>
                        <Separator />
                        <CardContent>
                            <div v-for="model in models" :key="model" class="space-y-2">
                                <div class="text-md flex gap-1 font-medium">
                                    <p class="capitalize">{{ model }}</p>
                                    <span class="text-muted-foreground"> ({{ actions.filter((action) => isChecked(model, action)).length }}) </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
                                    <template v-for="action in actions" :key="action">
                                        <div v-if="isChecked(model, action)" class="flex items-center space-x-2">
                                            <span class="text-muted-foreground capitalize">{{ action }}</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
