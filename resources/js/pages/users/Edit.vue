<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import DeleteUser from '@/components/users/DeleteUser.vue';
import RestoreUser from '@/components/users/RestoreUser.vue';
import { STATUS } from '@/constants';
import AppLayout from '@/layouts/AppLayout.vue';
import { getlayout } from '@/lib/layout';
import type { User } from '@/types';

defineOptions({
    layout: getlayout(AppLayout, () => ({
        breadcrumbs: [
            {
                title: 'Users',
                href: '/users',
            },
            {
                title: 'Edit',
                href: '/users/edit',
            },
        ],
    })),
});

interface Props {
    user: User;
    roles: string[];
}

const props = defineProps<Props>();

const isDeleted = Boolean(props.user.deleted_at);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
});

const submit = () => {
    if (!form.isDirty) return;

    form.patch(route('users.update', props.user.id));
};
</script>

<template>
    <Head title="Edit User" />

    <div class="space-y-12 px-4 py-6">
        <Heading
            title="Edit User Profile"
            description="Update user details such as name, email, role, and account status. Ensure all changes are accurate before saving."
        />

        <div class="flex items-center">
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <Label for="name">Name</Label>
                    <Input id="name" :disabled="isDeleted" v-model="form.name" required placeholder="Full name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" :disabled="isDeleted" type="email" v-model="form.email" required placeholder="Email address" />
                    <InputError :message="form.errors.email" />
                </div>

                <div>
                    <Label for="role">Role</Label>
                    <Select v-model="form.role">
                        <SelectTrigger id="role" :disabled="isDeleted">
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="role in props.roles" :key="role" :value="role" class="capitalize">{{ role }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role" />
                </div>

                <div>
                    <Label for="status">Status</Label>
                    <Select v-model="form.status">
                        <SelectTrigger id="status" :disabled="isDeleted">
                            <SelectValue placeholder="Select a status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="status in STATUS" :key="status" :value="status" class="capitalize">{{ status }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.status" />
                </div>

                <Button :disabled="!form.isDirty">Save</Button>
            </form>
        </div>

        <template v-if="isDeleted">
            <RestoreUser v-if="props.user.can?.restore" :id="props.user.id" />
            <DeleteUser v-if="props.user.can?.force_delete" :id="props.user.id" permanent />
        </template>
        <DeleteUser v-if="!isDeleted && props.user.can?.delete" :id="props.user.id" />
    </div>
</template>
