<script setup lang="ts">
import type { TeamMember } from '@/types';
import { Button } from '@/components/ui/button';

interface Props {
    member: TeamMember;
    canManageTeam: boolean;
    isOwner?: boolean;
    onRemove?: (memberId: number) => void;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="py-4 flex items-center justify-between">
        <div>
            <div class="flex items-center">
                <div class="text-sm font-medium">{{ member.name }}</div>
                <div class="flex gap-2 ml-2">
                    <span v-if="isOwner" class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">Owner</span>
                    <span v-if="member.role === 'admin'" class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">Admin</span>
                    <span v-if="member.role === 'member'" class="text-xs bg-primary/10 text-primary px-2 py-1 rounded">Member</span>
                </div>
            </div>
            <div class="text-sm text-muted-foreground">{{ member.email }}</div>
        </div>
        <Button 
            v-if="canManageTeam && !isOwner" 
            variant="destructive" 
            size="sm"
            @click="onRemove?.(member.id)"
        >
            Remove
        </Button>
    </div>
</template> 