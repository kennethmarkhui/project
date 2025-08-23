<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

import EditableText from '@/components/EditableText.vue';
import MatrixTable from '@/components/MatrixTable.vue';
import TooltipButton from '@/components/TooltipButton.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { getLayout } from '@/lib/layout';
import { isArrayEqual } from '@/lib/utils';
import type { Permission, Role } from '@/types';

defineOptions({
    layout: getLayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Roles',
                href: '/roles',
            },
            {
                title: 'Edit',
                href: '/roles/edit',
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

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions?.map((permission) => permission.id).sort() || [],
});

// had to use this, form.isDirty wont work for permissions array
const isFormDirty = computed(() => {
    const { name, permissions } = form;
    const original = props.role;

    if (name !== original.name) return true;

    const originalPermissions = original.permissions?.map((p) => p.id).sort() || [];
    const currentPermissions = permissions.sort();

    return !isArrayEqual(originalPermissions, currentPermissions);
});

const togglePermission = (permissionId: number, isChecked: boolean | 'indeterminate') => {
    const newPermissions = new Set(form.permissions);

    if (isChecked) {
        newPermissions.add(permissionId);
    } else {
        newPermissions.delete(permissionId);
    }

    form.permissions = Array.from(newPermissions);
};

const findPermission = (name: string) => {
    return props.permissions.find((permission) => permission.name === name);
};

const isChecked = (model: string, action: string) => {
    const permission = findPermission(`${model}.${action}`);
    return permission ? form.permissions.includes(permission.id) : false;
};

const handleDelete = async () => {
    if (props.role.is_system) return;

    const confirmed = await reveal({
        variant: 'destructive',
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
    });

    if (confirmed.data) router.delete(route('roles.destroy', props.role.id));
};

const submit = () => {
    if (!isFormDirty.value || props.role.is_system) return;

    form.patch(route('roles.update', props.role.id), {
        onError: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Edit Role" />

    <div class="mx-auto w-full max-w-7xl px-4 py-6">
        <div class="px-4 py-6">
            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <EditableText v-model="form.name" activationMode="none" submitMode="none" :disabled="props.role.is_system">
                    <TooltipButton
                        v-if="props.role.can?.delete"
                        tooltip="Delete"
                        variant="clear"
                        @click="handleDelete"
                        :disabled="props.role.is_system"
                    >
                        <Trash2 class="text-destructive" />
                    </TooltipButton>
                </EditableText>

                <MatrixTable :items="props.permissions.map((permission) => permission.name)">
                    <template #default="{ headRow, headColumn }">
                        <Checkbox
                            :modelValue="isChecked(headRow, headColumn)"
                            @update:modelValue="
                                (value) => {
                                    const permission = findPermission(`${headRow}.${headColumn}`);
                                    if (permission) togglePermission(permission.id, value);
                                }
                            "
                            :disabled="!findPermission(`${headRow}.${headColumn}`) || props.role.is_system"
                        />
                    </template>
                </MatrixTable>

                <div v-if="!props.role.is_system" class="ml-auto space-x-2">
                    <Button :disabled="!isFormDirty" variant="ghost" @click="form.reset()">Reset</Button>
                    <Button :disabled="!isFormDirty" type="submit">Save</Button>
                </div>
            </form>
        </div>
    </div>
</template>
