<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { type Team, type TeamInvitation } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
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

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.team.name,
        href: route('teams.settings', { team: props.team.id }),
    },
];

const nameInput = ref<HTMLInputElement | null>(null);
const inviteEmailInput = ref<HTMLInputElement | null>(null);
const showInviteDialog = ref(false);

const updateForm = useForm({
    name: props.team.name,
});

const inviteForm = useForm({
    email: '',
    role: 'member',
});

const updateTeam = () => {
    updateForm.put(route('teams.update', { team: props.team.id }), {
        preserveScroll: true,
        onSuccess: () => {
            updateForm.reset();
            nameInput.value?.blur();
        },
    });
};

const inviteMember = () => {
    inviteForm.post(route('teams.invitations.store', { team: props.team.id }), {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            showInviteDialog.value = false;
        },
    });
};

const cancelInvitation = (invitation: TeamInvitation) => {
    if (confirm('Are you sure you want to cancel this invitation?')) {
        useForm().delete(route('teams.invitations.destroy', { 
            team: props.team.id,
            invitation: invitation.id,
        }), {
            preserveScroll: true,
        });
    }
};

const removeMember = (memberId: number) => {
    if (confirm('Are you sure you want to remove this member?')) {
        useForm().delete(route('teams.members.destroy', { 
            team: props.team.id,
            user: memberId,
        }), {
            preserveScroll: true,
        });
    }
};

const canManageTeam = props.team.currentUserRole === 'owner' || props.team.currentUserRole === 'admin';

// Add new computed property to filter out owner from members
const filteredMembers = computed(() => {
    return props.team.members?.filter(member => member.id !== props.team.owner.id) ?? [];
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="team.name" />

        <div class="max-w-4xl space-y-6 p-4 sm:p-6 lg:p-8">
            <HeadingSmall 
                :title="team.name" 
                description="Team Settings" 
            />

            <!-- Team Information -->
            <div class="rounded-lg border bg-card p-6">
                <form @submit.prevent="updateTeam" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Team Name</Label>
                        <Input
                            id="name"
                            ref="nameInput"
                            v-model="updateForm.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            :disabled="!canManageTeam"
                        />
                    </div>

                    <div class="flex items-center gap-4" v-if="canManageTeam">
                        <Button :disabled="updateForm.processing">
                            Update Team
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Team Members -->
            <div class="rounded-lg border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium">Team Members</h3>
                    <Dialog v-model:open="showInviteDialog" v-if="canManageTeam">
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
                </div>

                <div class="divide-y divide-border">
                    <!-- Owner -->
                    <div class="py-4 flex items-center justify-between">
                        <div>
                            <div class="flex items-center">
                                <div class="text-sm font-medium">{{ team.owner.name }}</div>
                                <div class="flex gap-2 ml-2">
                                    <span class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">Owner</span>
                                    <span v-if="team.members?.find(m => m.id === team.owner.id)?.role === 'admin'" 
                                          class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">Admin</span>
                                </div>
                            </div>
                            <div class="text-sm text-muted-foreground">{{ team.owner.email }}</div>
                        </div>
                    </div>

                    <!-- Members (excluding owner) -->
                    <div v-for="member in filteredMembers" :key="member.id" class="py-4 flex items-center justify-between">
                        <div>
                            <div class="flex items-center">
                                <div class="text-sm font-medium">{{ member.name }}</div>
                                <span class="ml-2 text-xs bg-primary/10 text-primary px-2 py-1 rounded">{{ member.role }}</span>
                            </div>
                            <div class="text-sm text-muted-foreground">{{ member.email }}</div>
                        </div>
                        <Button 
                            v-if="canManageTeam" 
                            variant="destructive" 
                            size="sm"
                            @click="removeMember(member.id)"
                        >
                            Remove
                        </Button>
                    </div>

                    <!-- Pending Invitations -->
                    <div v-for="invitation in team.invitations" :key="invitation.id" class="py-4 flex items-center justify-between">
                        <div>
                            <div class="flex items-center">
                                <div class="text-sm font-medium">{{ invitation.email }}</div>
                                <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending</span>
                            </div>
                            <div class="text-sm text-muted-foreground">Invited {{ new Date(invitation.created_at).toLocaleDateString() }}</div>
                        </div>
                        <Button 
                            v-if="canManageTeam" 
                            variant="destructive" 
                            size="sm"
                            @click="cancelInvitation(invitation)"
                        >
                            Cancel
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 