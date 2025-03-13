<script setup lang="ts">
import { computed } from 'vue';
import type { Team, TeamInvitation, TeamMember } from '@/types';
import InviteMemberDialog from './InviteMemberDialog.vue';
import TeamMemberItem from './TeamMemberItem.vue';
import PendingInvitationItem from './PendingInvitationItem.vue';
import { useForm } from '@inertiajs/vue3';

interface Props {
    team: Team;
    canManageTeam: boolean;
}

const props = defineProps<Props>();

// Get owner's member record if it exists
const ownerMember = computed(() => {
    return props.team.members?.find(member => member.id === props.team.owner.id);
});

// Get all non-owner members
const otherMembers = computed(() => {
    return props.team.members?.filter(member => member.id !== props.team.owner.id) ?? [];
});

const removeMember = (memberId: number) => {
    if (confirm('Are you sure you want to remove this member?')) {
        useForm({}).delete(route('teams.members.destroy', { 
            team: props.team.id,
            user: memberId,
        }), {
            preserveScroll: true,
        });
    }
};

const cancelInvitation = (invitation: TeamInvitation) => {
    if (confirm('Are you sure you want to cancel this invitation?')) {
        useForm({}).delete(route('teams.invitations.destroy', { 
            team: props.team.id,
            invitation: invitation.id,
        }), {
            preserveScroll: true,
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
                :member="ownerMember || { ...team.owner, role: 'owner' as const }"
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
    </div>
</template> 