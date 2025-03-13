<script setup lang="ts">
import type { TeamInvitation } from '@/types';
import { Button } from '@/components/ui/button';

interface Props {
    invitation: TeamInvitation;
    canManageTeam: boolean;
    onCancel?: (invitation: TeamInvitation) => void;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="py-4 flex items-center justify-between">
        <div>
            <div class="flex items-center">
                <div class="text-sm font-medium">{{ invitation.email }}</div>
                <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending</span>
            </div>
            <div class="text-sm text-muted-foreground">
                Invited {{ new Date(invitation.created_at).toLocaleDateString() }}
            </div>
        </div>
        <Button 
            v-if="canManageTeam" 
            variant="destructive" 
            size="sm"
            @click="onCancel?.(invitation)"
        >
            Cancel
        </Button>
    </div>
</template> 