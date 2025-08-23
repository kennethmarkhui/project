<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import MatrixTable from '@/components/MatrixTable.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SidebarLayout from '@/layouts/shared/SidebarLayout.vue';
import { getLayout } from '@/lib/layout';
import type { Permission } from '@/types';

defineOptions({
    layout: getLayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Roles',
                href: '/roles',
            },
            {
                title: 'Create',
                href: '/roles/create',
            },
        ],
    })),
});

interface Props {
    permissions: Permission[];
}

const props = defineProps<Props>();

const form = useForm<{ name: string; permissions: number[] }>({
    name: '',
    permissions: [],
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

const submit = () => {
    if (!form.isDirty) return;

    form.post(route('roles.store'));
};
</script>

<template>
    <Head title="Create Role" />

    <SidebarLayout>
        <template #sidebar>
            <Heading title="Create New Role" description="Set up a role and assign permissions." />
        </template>
        <template #content>
            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" class="mt-1 block w-full" v-model="form.name" required placeholder="Role name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

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
                    <Button :disabled="!form.isDirty" variant="ghost" @click="form.reset()">Reset</Button>
                    <Button :disabled="!form.isDirty" type="submit">Save</Button>
                </div>
            </form>
        </template>
    </SidebarLayout>
</template>
