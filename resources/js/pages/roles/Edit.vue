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
import SidebarNavLayout from '@/layouts/shared/SidebarNavLayout.vue';
import { isArrayEqual } from '@/lib/utils';
import type { BreadcrumbItem, NavItem, Permission, Role } from '@/types';

interface Props {
    role: Role;
    permissions: Permission[];
    roles: Role[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: '/roles',
    },
    {
        title: 'Edit',
        href: '/roles/edit',
    },
];

const props = defineProps<Props>();

const { reveal } = useConfirmDialog();

const sidebarNavItems = computed<NavItem[]>(() =>
    props.roles.map((role) => ({
        title: role.name,
        href: `/roles/${role.id}/edit`,
    })),
);

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
    const confirmed = await reveal({
        variant: 'destructive',
        title: 'Are you sure you want to delete?',
        description: 'Once deleted, all of its resources and data will also be deleted.',
        confirmText: 'Delete',
    });

    if (confirmed.data) router.delete(route('roles.destroy', props.role.id));
};

const submit = () => {
    if (!isFormDirty.value) return;

    form.patch(route('roles.update', props.role.id), {
        onError: (errors) => {
            console.log(errors);

            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Role" />

        <SidebarNavLayout :nav-items="sidebarNavItems">
            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <EditableText v-model="form.name" activationMode="none" submitMode="none">
                    <TooltipButton v-if="$page.props.auth.can.role?.delete" tooltip="Delete" variant="clear" @click="handleDelete">
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
                            :disabled="!findPermission(`${headRow}.${headColumn}`)"
                        />
                    </template>
                </MatrixTable>

                <div class="ml-auto space-x-2">
                    <Button :disabled="!isFormDirty" variant="ghost" @click="form.reset()">Reset</Button>
                    <Button :disabled="!isFormDirty" type="submit">Save</Button>
                </div>
            </form>
        </SidebarNavLayout>
    </AppLayout>
</template>
