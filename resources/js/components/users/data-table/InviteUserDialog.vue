<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { MailPlus, Send } from 'lucide-vue-next';
import { ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Props {
    roles: string[];
}

const props = defineProps<Props>();

const open = ref(false);

const form = useForm({
    email: '',
    role: '',
});

const onSubmit = () => {
    form.post(route('invitation.store'), { onSuccess: () => closeDialog() });
};

const closeDialog = () => {
    form.clearErrors();
    form.reset();
    open.value = false;
};
</script>

<template>
    <Dialog v-model:open="open" @update:open="(value) => value === false && closeDialog()">
        <DialogTrigger as-child>
            <Button aria-label="Invite user" variant="outline" size="sm" class="ml-auto flex h-8">
                <MailPlus />
                Invite User
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <MailPlus />
                    Invite User
                </DialogTitle>
                <DialogDescription>
                    Invite a new user by sending them an email invitation. Assign a role to define their access level.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-6" @submit.prevent="onSubmit">
                <div class="grid gap-2">
                    <Label for="email" class="sr-only"> Email </Label>
                    <Input id="email" type="email" name="email" v-model="form.email" placeholder="Email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="role" class="sr-only">Role</Label>
                    <Select v-model="form.role">
                        <SelectTrigger id="role">
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="role in props.roles" :key="role" :value="role" class="capitalize">{{ role }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role" />
                </div>

                <DialogFooter>
                    <DialogClose as-child>
                        <Button variant="secondary"> Close </Button>
                    </DialogClose>
                    <Button type="submit">
                        <Send />
                        Invite
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
