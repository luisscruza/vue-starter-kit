<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Team, TeamInvitation, TeamMember } from '@/types';
import { useForm } from '@inertiajs/vue3';
import InviteMemberDialog from './InviteMemberDialog.vue';
import TeamMemberItem from './TeamMemberItem.vue';
import PendingInvitationItem from './PendingInvitationItem.vue';
import ConfirmationDialog from '@/components/ui/ConfirmationDialog.vue';

interface Props {
    team: Team;
    canManageTeam: boolean;
}

const props = defineProps<Props>();

// State for confirmation dialogs
const showDeleteDialog = ref(false);
const showCancelInviteDialog = ref(false);
const pendingMemberId = ref<number | null>(null);
const pendingInvitation = ref<TeamInvitation | null>(null);

// Get owner's member record if it exists
const ownerMember = computed(() => {
    return props.team.members?.find(member => member.id === props.team.owner.id);
});

// Get all non-owner members
const otherMembers = computed(() => {
    return props.team.members?.filter(member => member.id !== props.team.owner.id) ?? [];
});

const removeMember = (memberId: number) => {
    pendingMemberId.value = memberId;
    showDeleteDialog.value = true;
};

const confirmRemoveMember = () => {
    if (pendingMemberId.value) {
        useForm({}).delete(route('teams.members.destroy', { 
            team: props.team.id,
            user: pendingMemberId.value,
        }), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false;
                pendingMemberId.value = null;
            },
        });
    }
};

const cancelInvitation = (invitation: TeamInvitation) => {
    pendingInvitation.value = invitation;
    showCancelInviteDialog.value = true;
};

const confirmCancelInvitation = () => {
    if (pendingInvitation.value) {
        useForm({}).delete(route('teams.invitations.destroy', { 
            team: props.team.id,
            invitation: pendingInvitation.value.id,
        }), {
            preserveScroll: true,
            onSuccess: () => {
                showCancelInviteDialog.value = false;
                pendingInvitation.value = null;
            },
        });
    }
};
</script>

<template>
    <div class="rounded-lg border bg-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium">Team Members</h3>
            <InviteMemberDialog 
                v-if="canManageTeam"
                :team="team"
            />
        </div>

        <div class="divide-y divide-border">
            <!-- Owner -->
            <TeamMemberItem
                :member="ownerMember || { ...team.owner, role: 'owner' as const, membership: { role: 'owner' } }"
                :can-manage-team="canManageTeam"
                :is-owner="true"
            />

            <!-- Members (excluding owner) -->
            <TeamMemberItem
                v-for="member in otherMembers"
                :key="member.id"
                :member="member"
                :can-manage-team="canManageTeam"
                :on-remove="removeMember"
            />

            <!-- Pending Invitations -->
            <PendingInvitationItem
                v-for="invitation in team.invitations"
                :key="invitation.id"
                :invitation="invitation"
                :can-manage-team="canManageTeam"
                :on-cancel="cancelInvitation"
            />
        </div>

        <!-- Remove Member Dialog -->
        <ConfirmationDialog
            v-model:open="showDeleteDialog"
            title="Remove Team Member"
            description="Are you sure you want to remove this team member? This action cannot be undone."
            confirm-text="Remove Member"
            @confirm="confirmRemoveMember"
        />

        <!-- Cancel Invitation Dialog -->
        <ConfirmationDialog
            v-model:open="showCancelInviteDialog"
            title="Cancel Invitation"
            description="Are you sure you want to cancel this invitation? This action cannot be undone."
            confirm-text="Cancel Invitation"
            @confirm="confirmCancelInvitation"
        />
    </div>
</template> 