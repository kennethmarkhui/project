<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ROLES, STATUS } from '@/constants';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, User } from '@/types';

interface Props {
    user: {
        data: User;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Edit',
        href: '/users/edit',
    },
];

const form = useForm({
    name: props.user.data.name,
    email: props.user.data.email,
    role: props.user.data.role,
    status: props.user.data.status,
});

const submit = () => {
    if (!form.isDirty) return;

    form.patch(route('users.update', props.user.data.id));
};
</script>

<template>
    <Head title="Edit User" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="px-4 py-6">
            <Heading
                title="Edit User Profile"
                description="Update user details such as name, email, role, and account status. Ensure all changes are accurate before saving."
            />

            <div class="flex items-center">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" required placeholder="Full name" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div>
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="form.email" required placeholder="Email address" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <Label for="role">Role</Label>
                        <Select v-model="form.role">
                            <SelectTrigger id="role">
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="role in ROLES" :key="role" :value="role" class="capitalize">{{ role }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <div>
                        <Label for="status">Status</Label>
                        <Select v-model="form.status">
                            <SelectTrigger id="status">
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
        </div>
    </AppLayout>
</template>
