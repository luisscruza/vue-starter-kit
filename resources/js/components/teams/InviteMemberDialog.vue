<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { Team } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Props {
    team: Team;
}

const props = defineProps<Props>();
const showDialog = ref(false);
const inviteEmailInput = ref<HTMLInputElement | null>(null);

const inviteForm = useForm({
    email: '',
    role: 'member' as 'admin' | 'member',
});

const inviteMember = () => {
    inviteForm.post(route('teams.invitations.store', { team: props.team.id }), {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            showDialog.value = false;
        },
    });
};
</script>

<template>
    <Dialog v-model:open="showDialog">
        <DialogTrigger asChild>
            <Button>Invite Member</Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Invite Team Member</DialogTitle>
                <DialogDescription>
                    Invite a new member to join your team.
                </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="inviteMember" class="space-y-4">
                <div class="grid gap-2">
                    <Label for="email">Email Address</Label>
                    <Input
                        id="email"
                        ref="inviteEmailInput"
                        v-model="inviteForm.email"
                        type="email"
                        required
                        placeholder="Enter email address"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="role">Role</Label>
                    <Select v-model="inviteForm.role">
                        <SelectTrigger>
                            <SelectValue placeholder="Select a role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="admin">Admin</SelectItem>
                            <SelectItem value="member">Member</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <DialogFooter>
                    <Button type="submit" :disabled="inviteForm.processing">
                        Send Invitation
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template> 