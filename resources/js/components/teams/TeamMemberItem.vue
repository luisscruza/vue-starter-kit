<script setup lang="ts">
import type { TeamMember, TeamRole } from '@/types';
import { Button } from '@/components/ui/button';
import { PencilIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import EditMemberRoleDialog from './EditMemberRoleDialog.vue';

interface Props {
    member: TeamMember;
    canManageTeam: boolean;
    isOwner?: boolean;
    onRemove?: (memberId: number) => void;
    teamRoles: TeamRole[];
}

const props = defineProps<Props>();

const showEditModal = ref(false);

const getRoleName = (roleId: number) => {
    console.log(props.teamRoles);
    return props.teamRoles.find(role => role.id === roleId)?.name ?? '';
};
</script>

<template>
    <div class="py-4 flex items-center justify-between">
        <div>
            <div class="flex items-center">
                <div class="text-sm font-medium">{{ member.name }}</div>
                <div class="flex gap-2 ml-2">
                    <span v-if="isOwner" class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">Owner</span>
                    <span 
                        v-else 
                        class="text-xs bg-primary/10 text-primary px-2 py-1 rounded capitalize"
                    >

                        {{ getRoleName(member.membership.role_id) }}
                    </span>
                </div>
            </div>
            <div class="text-sm text-muted-foreground">{{ member.email }}</div>
        </div>
        <div class="flex items-center gap-2">
            <Button 
                v-if="canManageTeam && !isOwner"
                variant="ghost"
                size="sm"
                @click="showEditModal = true"
            >
                <PencilIcon class="h-4 w-4" />
            </Button>
            <Button 
                v-if="canManageTeam && !isOwner" 
                variant="destructive" 
                size="sm"
                @click="onRemove?.(member.id)"
            >
                Remove
            </Button>
        </div>

        <EditMemberRoleDialog
            :open="showEditModal"
            :member="member"
            :team-roles="teamRoles"
            :on-open-change="(open: boolean) => showEditModal = open"
        />
    </div>
</template> 